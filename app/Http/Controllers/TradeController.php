<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\TradeTransaction;
use App\Models\TradeOfferedItem;
use App\Models\MeetupLocation;
use App\Models\Order;
use App\Models\Rating;
use App\Models\SellerReview;
use App\Models\Wishlist;
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
        try {
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
            
            $scheduleComponents = explode(',', $validated['meetup_schedule']);
            
            if (count($scheduleComponents) < 2) {
                Log::warning('Invalid schedule components: ' . $validated['meetup_schedule']);
                return redirect()->back()->withErrors(['meetup_schedule' => 'Invalid meetup schedule format']);
            }
            
            $dateString = trim($scheduleComponents[0]);
            $timeString = $validated['preferred_time'];
            
            try {
                $meetupSchedule = Carbon::createFromFormat('Y-m-d H:i', "$dateString $timeString");
            } catch (\Exception $e) {
                Log::error('Date parsing failed: ' . $e->getMessage());
                return redirect()->back()->withErrors(['meetup_schedule' => 'Invalid date/time format']);
            }
            
            $product = Product::findOrFail($validated['seller_product_id']);
            
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
            
            foreach ($validated['offered_items'] as $index => $item) {
                $uploadedImages = [];
                
                if ($request->hasFile("offered_items.{$index}.image_files")) {
                    $files = $request->file("offered_items.{$index}.image_files");
                    
                    foreach ($files as $fileIndex => $file) {
                        $path = $file->store('trade_items', 'public');
                        $uploadedImages[] = $path;
                    }
                }
                
                TradeOfferedItem::create([
                    'trade_transaction_id' => $trade->id,
                    'name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'estimated_value' => $item['estimated_value'],
                    'description' => $item['description'] ?? '',
                    'condition' => $item['condition'],
                    'images' => json_encode($uploadedImages)
                ]);
            }
            
            DB::commit();
            
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
            
            if ($trade->buyer_id !== Auth::id() && $trade->seller_id !== Auth::id()) {
                return back()->with('error', 'Unauthorized action');
            }
            
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

    /**
     * Display the user's trades dashboard
     * 
     * @return \Inertia\Response
     */
    public function trades()
    {
        $user = Auth::user();
        
        $trades = $this->getTradesWithRelations($user);
        
        $trades = $this->formatTradesData($trades);
        
        $stats = $this->getDashboardStats();
        
        $unreadMessages = $this->getUnreadMessagesCount($user->id);
        
        return Inertia::render('Dashboard/MyTrades', [
            'trades' => $trades,
            'stats' => $stats,
            'user' => $user,
            'unreadMessages' => $unreadMessages
        ]);
    }
    
    private function getTradesWithRelations($user)
    {
        return TradeTransaction::with([
            'sellerProduct', 
            'offeredItems', 
            'buyer:id,first_name,last_name,username,profile_picture', 
            'seller:id,first_name,last_name,username,profile_picture,seller_code',
            'meetupLocation.location',
            'messages.user:id,first_name,last_name,username,profile_picture'
        ])
        ->where(function ($query) use ($user) {
            $query->where('buyer_id', $user->id)
                  ->orWhere('seller_id', $user->id);
        })
        ->orderBy('created_at', 'desc')
        ->paginate(10);
    }
    
    private function formatTradesData($trades)
    {
        $trades->getCollection()->transform(function ($trade) {
            if ($trade->sellerProduct && $trade->sellerProduct->images) {
                $trade->sellerProduct->images = $this->formatImageUrls($trade->sellerProduct->images);
            }
            
            if ($trade->offeredItems) {
                $trade->offeredItems->transform(function ($item) {
                    $item->images = $this->formatItemImages($item->images);
                    return $item;
                });
            }
            
            $trade->buyer = $this->formatUserData($trade->buyer);
            $trade->seller = $this->formatUserData($trade->seller);
            
            if ($trade->messages) {
                $trade->messages->transform(function ($message) {
                    $message->user = $this->formatUserData($message->user);
                    
                    $message->user_id = $message->sender_id;
                    $message->formatted_time = $message->created_at ? 
                        Carbon::parse($message->created_at)->format('M j, Y g:i A') : null;
                    
                    return $message;
                });
            }
            
            if ($trade->meetup_schedule) {
                $trade->formatted_meetup_date = Carbon::parse($trade->meetup_schedule)->format('F j, Y g:i A');
            }
            
            return $trade;
        });
        
        return $trades;
    }
    
    private function formatImageUrls($images)
    {
        if (!$images) return [];
        
        if (is_array($images)) {
            return array_map(function ($image) {
                if ($image && file_exists(storage_path('app/public/' . $image))) {
                    return asset('storage/' . $image);
                }
                return asset('images/placeholder-product.jpg');
            }, $images);
        }
        
        return [$images ? asset('storage/' . $images) : asset('images/placeholder-product.jpg')];
    }
    
    private function formatUserData($user)
    {
        if (!$user) return null;
        
        if ($user->profile_picture) {
            $user->profile_picture = file_exists(storage_path('app/public/' . $user->profile_picture))
                ? asset('storage/' . $user->profile_picture)
                : asset('images/placeholder-avatar.jpg');
        }
        
        return $user;
    }
    
    private function formatItemImages($images)
    {
        if (!$images) return [];
        
        $processedImages = [];
        
        if (is_string($images)) {
            try {
                $decodedImages = json_decode($images, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decodedImages)) {
                    $images = $decodedImages;
                } else {
                    $images = [$images];
                }
            } catch (\Exception $e) {
                $images = [$images];
            }
        }
        
        $imageArray = is_array($images) ? $images : [$images];
        
        foreach ($imageArray as $imagePath) {
            if (empty($imagePath)) continue;
            
            if (file_exists(storage_path('app/public/' . $imagePath))) {
                $processedImages[] = asset('storage/' . $imagePath);
            } else {
                $processedImages[] = asset('storage/' . $imagePath);
            }
        }
        
        return $processedImages;
    }
    
    private function getUnreadMessagesCount($userId)
    {
        return DB::table('trade_messages')
            ->join('trade_transactions', 'trade_messages.trade_transaction_id', '=', 'trade_transactions.id')
            ->where('trade_messages.sender_id', '!=', $userId)
            ->where(function($query) use ($userId) {
                $query->where('trade_transactions.buyer_id', $userId)
                      ->orWhere('trade_transactions.seller_id', $userId);
            })
            ->whereNull('trade_messages.read_at')
            ->count();
    }
    
    protected function getDashboardStats()
    {
        $user = Auth::user();
        $userStats = [];
        
        $userStats['order_count'] = Order::where('buyer_id', $user->id)->count();
        
        $userStats['total_spent'] = Order::where('buyer_id', $user->id)
        ->where('status', 'Completed')  // Capitalized first letter to match the constant in Order model
        ->sum('sub_total');  // Changed from 'total' to 'sub_total'
        
        $userStats['wishlist_count'] = Wishlist::where('user_id', $user->id)->count();
        
        $userStats['review_count'] = Rating::where('user_id', $user->id)->count();
        
        $userStats['pending_orders'] = Order::where('buyer_id', $user->id)
            ->where('status', 'pending')
            ->count();
        
        $userStats['trade_count'] = TradeTransaction::where('buyer_id', $user->id)->count();
        
        $totalTradeValue = $this->calculateTotalTradeValue($user->id);
        $userStats['total_trade_value'] = $totalTradeValue;
        
        $sellerStats = [];
        if ($user->is_seller) {
            $sellerStats['product_count'] = Product::where('user_id', $user->id)->count();
            
            $sellerStats['active_product_count'] = Product::where('user_id', $user->id)
                ->where('status', 'Active')
                ->count();
            
            $sellerStats['order_count'] = Order::where('seller_id', $user->id)->count();
            
            $sellerStats['revenue'] = Order::where('seller_id', $user->id)
                ->where('status', 'completed')
                ->sum('total');
            
            $sellerStats['review_count'] = SellerReview::where('seller_id', $user->id)->count();
            
            $sellerStats['trade_offers_count'] = TradeTransaction::where('seller_id', $user->id)->count();
            
            $sellerStats['pending_trade_offers'] = TradeTransaction::where('seller_id', $user->id)
                ->where('status', 'pending')
                ->count();
                
            $sellerStats['pending_orders'] = Order::where('seller_id', $user->id)
                ->whereIn('status', ['pending', 'processing'])
                ->count();
                
            $avgRating = Rating::where('seller_id', $user->id)->avg('rating');
            $sellerStats['avg_rating'] = $avgRating ? number_format($avgRating, 1) : 0;
            
            $sellerStats['total_ratings'] = Rating::where('seller_id', $user->id)->count();
        }
        
        return [
            'user' => $userStats,
            'seller' => $sellerStats
        ];
    }
    
    private function calculateTotalTradeValue($userId)
    {
        $totalValue = 0;
        
        $trades = TradeTransaction::where('buyer_id', $userId)->get();
        
        foreach ($trades as $trade) {
            $totalValue += $trade->additional_cash ?? 0;
            
            $offeredItems = TradeOfferedItem::where('trade_transaction_id', $trade->id)->get();
            foreach ($offeredItems as $item) {
                $totalValue += $item->estimated_value * $item->quantity;
            }
        }
        
        return $totalValue;
    }
}
