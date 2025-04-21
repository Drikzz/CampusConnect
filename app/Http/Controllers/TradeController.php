<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\TradeTransaction;
use App\Models\TradeOfferedItem;
use App\Models\MeetupLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Carbon\Carbon;

class TradeController extends Controller
{
    /**
     * Display the trade form for a specific product
     *
     * @param int $id
     * @return \Inertia\Response
     */
    public function show($id)
    {
        $product = Product::with(['seller' => function($query) {
            $query->select('id', 'first_name', 'last_name', 'username', 'seller_code', 'profile_picture');
        }, 'category'])->findOrFail($id);
        
        if (!$product->is_tradable) {
            return redirect()->route('products.show', $id)
                ->with('error', 'This product is not available for trade');
        }
        
        return Inertia::render('Trade/Show', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => (float)$product->price,
                'discounted_price' => (float)$product->discounted_price,
                'discount' => (float)$product->discount,
                'stock' => $product->stock,
                'images' => $product->images ?? [],
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name
                ] : null,
                'seller' => $product->seller ? [
                    'id' => $product->seller->id,
                    'first_name' => $product->seller->first_name,
                    'last_name' => $product->seller->last_name,
                    'username' => $product->seller->username,
                    'seller_code' => $product->seller->seller_code,
                    'profile_picture' => $product->seller->profile_picture
                ] : null,
                'is_tradable' => (bool)$product->is_tradable,
                'status' => $product->status
            ]
        ]);
    }
    
    /**
     * Get meetup locations for a product's seller
     *
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMeetupLocations($productId)
    {
        try {
            $product = Product::with('seller')->findOrFail($productId);
            
            if (!$product->seller) {
                return response()->json([
                    'message' => 'Seller information not available'
                ], 404);
            }
            
            $meetupLocations = MeetupLocation::where('user_id', $product->seller->id)
                ->where('is_active', true)
                ->with('location')
                ->orderByDesc('is_default')
                ->get()
                ->map(function($loc) {
                    return [
                        'id' => $loc->id,
                        'name' => $loc->location ? $loc->location->name : $loc->custom_location,
                        'description' => $loc->description,
                        'available_days' => $loc->available_days,
                        'available_from' => $loc->available_from,
                        'available_until' => $loc->available_until,
                        'is_default' => $loc->is_default,
                        'latitude' => $loc->location ? $loc->location->latitude : $loc->latitude,
                        'longitude' => $loc->location ? $loc->location->longitude : $loc->longitude,
                        'location_id' => $loc->location_id,
                        'max_daily_meetups' => $loc->max_daily_meetups
                    ];
                });
            
            return response()->json([
                'meetupLocations' => $meetupLocations
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching meetup locations: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error fetching meetup locations: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Submit a trade offer
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submit(Request $request)
    {
        // Remove debug line
        // dd($request);
        
        try {
            // Log the incoming request for debugging
            Log::info('Trade submit request: ' . json_encode($request->all()));

            $validated = $request->validate([
                'meetup_schedule' => 'required|string',
                'selected_day' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                'seller_product_id' => 'required|exists:products,id',
                'meetup_location_id' => 'required|exists:meetup_locations,id',
                'preferred_time' => 'required|string',
                'additional_cash' => 'required|numeric|min:0',
                'notes' => 'nullable|string',
                'offered_items' => 'required|array|min:1',
                'offered_items.*.name' => 'required|string|max:255',
                'offered_items.*.quantity' => 'required|integer|min:1',
                'offered_items.*.estimated_value' => 'required|numeric|min:0',
                'offered_items.*.description' => 'nullable|string',
                'offered_items.*.condition' => 'required|string|in:new,used_like_new,used_good,used_fair',
                'offered_items.*.image_files' => 'required|array|min:1',
                'offered_items.*.image_files.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                'offered_items.*.images_json' => 'required|string',
            ]);
            
            DB::beginTransaction();
            
            // Parse the meetup schedule to get a valid datetime
            $scheduleComponents = explode(',', $validated['meetup_schedule']);
            
            if (count($scheduleComponents) < 2) {
                Log::warning('Invalid schedule components: ' . $validated['meetup_schedule']);
                return redirect()->back()->withErrors(['meetup_schedule' => 'Invalid meetup schedule format']);
            }
            
            $dateString = trim($scheduleComponents[0]);
            $timeString = $validated['preferred_time'];
            
            try {
                // Create a Carbon date from the parsed components
                $meetupSchedule = Carbon::createFromFormat('Y-m-d H:i', "$dateString $timeString");
            } catch (\Exception $e) {
                Log::error('Date parsing failed: ' . $e->getMessage());
                return redirect()->back()->withErrors(['meetup_schedule' => 'Invalid date/time format']);
            }
            
            // Rest of your existing code...
            $product = Product::findOrFail($validated['seller_product_id']);
            
            // Create the trade transaction
            $trade = new TradeTransaction();
            $trade->buyer_id = Auth::id();
            $trade->seller_id = $product->seller_id;
            $trade->seller_code = $product->seller_code;
            $trade->seller_product_id = $validated['seller_product_id'];
            $trade->meetup_location_id = $validated['meetup_location_id'];
            $trade->meetup_schedule = $meetupSchedule;
            $trade->meetup_day = $validated['selected_day'];
            $trade->preferred_time = $timeString;
            $trade->additional_cash = $validated['additional_cash'];
            $trade->notes = $validated['notes'];
            $trade->status = 'pending';
            $trade->save();
            
            // Process offered items
            foreach ($validated['offered_items'] as $index => $item) {
                $uploadedImages = [];
                
                // Check if image files were uploaded - use correct field name
                if ($request->hasFile("offered_items.{$index}.image_files")) {
                    $files = $request->file("offered_items.{$index}.image_files");
                    
                    foreach ($files as $fileIndex => $file) {
                        $path = $file->store('trade_items', 'public');
                        $uploadedImages[] = $path;
                    }
                }
                
                // Create the item record with JSON images - use $trade not $tradeTransaction
                TradeOfferedItem::create([
                    'trade_transaction_id' => $trade->id, // FIXED: use $trade->id instead of $tradeTransaction->id
                    'name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'estimated_value' => $item['estimated_value'],
                    'description' => $item['description'] ?? '',
                    'condition' => $item['condition'],
                    'images' => json_encode($uploadedImages) // Save as JSON string
                ]);
            }
            
            DB::commit();
            
            // Redirect with success message
            return redirect()->back()->with([
                'toast' => [
                    'title' => 'Success',
                    'description' => 'Your trade offer has been submitted successfully.',
                    'variant' => 'success',
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Trade submission error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()->withErrors([
                'error' => 'There was a problem submitting your trade offer: ' . $e->getMessage()
            ])->withInput();
        }
    }
    
    /**
     * Cancel a trade offer
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel($id)
    {
        try {
            $trade = TradeTransaction::findOrFail($id);
            
            // Verify user is the buyer or seller
            if ($trade->buyer_id !== Auth::id() && $trade->seller_id !== Auth::id()) {
                return back()->with('error', 'Unauthorized action');
            }
            
            // Only pending or accepted trades can be canceled
            if (!in_array($trade->status, ['pending', 'accepted'])) {
                return back()->with('error', 'This trade cannot be canceled');
            }
            
            $trade->update([
                'status' => 'canceled'
            ]);
            
            return back()->with('success', 'Trade offer canceled successfully');
        } catch (\Exception $e) {
            Log::error('Error canceling trade: ' . $e->getMessage());
            return back()->with('error', 'Error canceling trade: ' . $e->getMessage());
        }
    }
    
    /**
     * List all tradable products
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $products = Product::where('is_tradable', true)
            ->where('status', 'Active')
            ->with(['seller:id,first_name,last_name,username,seller_code,profile_picture', 'category:id,name'])
            ->latest()
            ->paginate(12);
            
        return Inertia::render('Trade/Index', [
            'products' => $products
        ]);
    }
}
