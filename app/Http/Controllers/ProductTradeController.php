<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\TradeTransaction;
use App\Models\TradeOfferedItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ProductTradeController extends Controller
{
    public function index(Request $request)
    {
        // Start with a query for tradable products only
        $query = Product::where('is_tradable', true)
                        ->where('status', 'Active');
        
        // Apply category filter
        if ($request->filled('category') && $request->category !== 'all') {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('name', $request->category);
            });
        }
        
        // Apply price filters
        if ($request->filled('price.min')) {
            $query->where('price', '>=', $request->input('price.min'));
        }
        
        if ($request->filled('price.max')) {
            $query->where('price', '<=', $request->input('price.max'));
        }
        
        // Apply matching type logic if needed
        if ($request->filled('matchingType')) {
            // This logic would depend on your specific implementation
            // of 'any' vs 'all' matching
        }
        
        // Get the tradable products with pagination - ensure seller info is loaded
        $products = $query->with([
            'category', 
            'seller:id,first_name,last_name,username,seller_code'
        ])
        ->paginate(9)
        ->withQueryString();
        
        // Transform product data to ensure seller information is correctly included
        $products->getCollection()->transform(function ($product) {
            // Make sure seller_code is directly accessible on the product
            if ($product->seller) {
                $product->seller_code = $product->seller->seller_code;
            }
            return $product;
        });
        
        return Inertia::render('Products/Trade', [
            'products' => $products,
            'filters' => $request->all()
        ]);
    }
    
    /**
     * Submit a trade offer
     */
    public function submitTradeOffer(Request $request)
    {
        // Log request information to help with debugging
        Log::info('Trade offer submission started', [
            'user_id' => Auth::id(),
            'data' => $request->except('offered_items.*.images'),
            'has_files' => $request->hasFile('offered_items') ? 'Yes' : 'No',
            'files_structure' => $request->file() ? array_keys($request->file()) : 'No files'
        ]);
        
        // CRITICAL DEBUGGING: Directly log the entire FILES array to see exactly what we're receiving
        Log::debug('RAW FILES INPUT', [
            'files' => print_r($_FILES, true)
        ]);
        
        try {
            $validated = $request->validate([
                'seller_product_id' => 'required|exists:products,id',
                'meetup_location_id' => 'nullable|exists:meetup_locations,id',
                'meetup_schedule' => 'nullable|string', 
                'meetup_date' => 'nullable|date|after_or_equal:today', // Add validation for meetup_date
                'additional_cash' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string|max:1000',
                'offered_items' => 'required|array|min:1',
                'offered_items.*.name' => 'required|string|max:255',
                'offered_items.*.quantity' => 'required|integer|min:1',
                'offered_items.*.estimated_value' => 'required|numeric|min:0',
                'offered_items.*.description' => 'nullable|string|max:1000',
                'offered_items.*.images' => 'required|array|min:1',
                'offered_items.*.images.*' => 'required|file|image|max:2048',
            ]);
            
            // Get the product with seller information
            $product = Product::findOrFail($validated['seller_product_id']);
            
            // Get seller_code from product
            $sellerCode = $product->seller_code;
            
            if (empty($sellerCode)) {
                Log::error('Product has no seller_code', [
                    'product_id' => $product->id
                ]);
                
                return redirect()->back()
                    ->with('error', 'Cannot identify the seller of this product.')
                    ->withInput();
            }
            
            // Find seller_id from seller_code
            $seller = User::where('seller_code', $sellerCode)->first();
            
            if (!$seller) {
                Log::error('No seller found with seller_code', [
                    'seller_code' => $sellerCode,
                    'product_id' => $product->id
                ]);
                
                return redirect()->back()
                    ->with('error', 'Cannot find the seller for this product.')
                    ->withInput();
            }
            
            Log::info('Found seller for trade transaction', [
                'seller_id' => $seller->id,
                'seller_code' => $sellerCode
            ]);
            
            // Now start the transaction
            DB::beginTransaction();
            
            try {
                // Combine meetup_date and meetup_schedule to create a specific datetime
                $meetupSchedule = null;
                if (!empty($validated['meetup_schedule']) && !empty($validated['meetup_date'])) {
                    // Create a date from the selected date
                    $meetupDate = new \DateTime($validated['meetup_date']);
                    
                    // Get the day from meetup_schedule
                    $dayName = $validated['meetup_schedule'];
                    
                    // Ensure the date matches the expected day of week
                    $selectedDay = strtolower(date('l', strtotime($validated['meetup_date'])));
                    if (strtolower($dayName) !== $selectedDay) {
                        // If there's a mismatch, log it - this should be prevented by the frontend
                        Log::warning('Selected date does not match day of week', [
                            'expected_day' => strtolower($dayName),
                            'selected_date_day' => $selectedDay,
                            'selected_date' => $validated['meetup_date']
                        ]);
                    }
                    
                    // Store only the date without time
                    $meetupSchedule = $meetupDate->format('Y-m-d');
                    
                    Log::info('Using specific meetup date', [
                        'meetup_date' => $validated['meetup_date'],
                        'day_name' => $dayName, 
                        'converted_date' => $meetupSchedule
                    ]);
                }
                
                // Create trade transaction with proper datetime
                $tradeTransaction = TradeTransaction::create([
                    'buyer_id' => Auth::id(),
                    'seller_id' => $seller->id,
                    'seller_code' => $sellerCode, // Store both for redundancy
                    'seller_product_id' => $product->id,
                    'meetup_location_id' => $validated['meetup_location_id'] ?? null,
                    'meetup_schedule' => $meetupSchedule, // Use combined date and time
                    'additional_cash' => $validated['additional_cash'] ?? 0,
                    'notes' => $validated['notes'] ?? null,
                    'status' => 'pending'
                ]);
                
                // Log meetup information if available
                if ($validated['meetup_location_id'] || $meetupSchedule) {
                    Log::info('Trade includes meetup information', [
                        'trade_id' => $tradeTransaction->id,
                        'meetup_location_id' => $validated['meetup_location_id'] ?? 'Not specified',
                        'meetup_schedule' => $meetupSchedule ?? 'Not specified',
                        'original_date' => $validated['meetup_date'] ?? 'Not specified'
                    ]);
                }
                
                Log::info('Created trade transaction', [
                    'trade_id' => $tradeTransaction->id
                ]);
                
                // Process each offered item
                foreach ($validated['offered_items'] as $itemIndex => $itemData) {
                    $images = [];
                    
                    // Debug log to see the structure of the request data
                    Log::debug('Processing offered item', [
                        'index' => $itemIndex,
                        'item_data' => array_diff_key($itemData, ['images' => 'removed_for_logging']),
                        'has_files' => $request->hasFile('offered_items') ? 'Yes' : 'No'
                    ]);
                    
                    // Check for images - this is now required so we must find them
                    $imagesFound = false;
                    
                    // Method 1: Direct array access to the files
                    if (isset($request->file('offered_items')[$itemIndex]['images'])) {
                        $uploadedImages = $request->file('offered_items')[$itemIndex]['images'];
                        Log::debug('Found images using direct array access', [
                            'item_index' => $itemIndex,
                            'count' => count($uploadedImages)
                        ]);
                        
                        foreach ($uploadedImages as $image) {
                            if ($this->processAndStoreImage($image, $images, $itemIndex)) {
                                $imagesFound = true;
                            }
                        }
                    } 
                    // Method 2: Looking for flattened keys
                    else {
                        $prefix = "offered_items.{$itemIndex}.images.";
                        
                        foreach ($request->allFiles() as $key => $value) {
                            if (strpos($key, $prefix) === 0) {
                                Log::debug('Found image using flattened key format', [
                                    'key' => $key,
                                    'item_index' => $itemIndex
                                ]);
                                if ($this->processAndStoreImage($value, $images, $itemIndex)) {
                                    $imagesFound = true;
                                }
                            }
                        }
                    }
                    
                    // Verify we found and processed at least one image
                    if (!$imagesFound || empty($images)) {
                        throw new \Exception('Required images for item #' . ($itemIndex + 1) . ' were not found or could not be processed');
                    }
                    
                    // Create offered item record
                    $offeredItem = TradeOfferedItem::create([
                        'trade_transaction_id' => $tradeTransaction->id,
                        'name' => $itemData['name'],
                        'quantity' => $itemData['quantity'],
                        'estimated_value' => $itemData['estimated_value'],
                        'description' => $itemData['description'] ?? null,
                        'images' => json_encode($images), // This should now always have at least one image
                    ]);
                    
                    Log::info('Created offered item', [
                        'item_id' => $offeredItem->id,
                        'name' => $offeredItem->name,
                        'image_count' => count($images),
                        'image_paths' => $images
                    ]);
                }
                
                DB::commit();
                
                // Send notification to seller here (if implemented)
                
                // Check if this is an AJAX request
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Trade offer submitted successfully! The seller will be notified.',
                        'trade_id' => $tradeTransaction->id
                    ]);
                }
                
                // For regular requests, use redirect with flash
                return redirect()->back()->with([
                    'success' => 'Trade offer submitted successfully! The seller will be notified.',
                    'trade_id' => $tradeTransaction->id
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e; // Re-throw for the outer catch
            }
            
        } catch (\Exception $e) {
            Log::error('Trade offer submission error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with([
                'error' => 'Failed to submit trade offer: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Helper method to process and store an uploaded image
     * 
     * @param \Illuminate\Http\UploadedFile $image
     * @param array &$images Reference to the images array to append to
     * @param int $itemIndex For logging purposes
     * @return bool True if successful, false otherwise
     */
    private function processAndStoreImage($image, array &$images, int $itemIndex): bool
    {
        if ($image && $image->isValid()) {
            try {
                $path = $image->store('trade_images', 'public');
                $images[] = $path;
                Log::debug('Image uploaded successfully', [
                    'item_index' => $itemIndex,
                    'original_name' => $image->getClientOriginalName(),
                    'stored_path' => $path
                ]);
                return true;
            } catch (\Exception $e) {
                Log::error('Failed to store image', [
                    'item_index' => $itemIndex,
                    'original_name' => $image->getClientOriginalName(),
                    'error' => $e->getMessage(),
                    'error_type' => get_class($e)
                ]);
            }
        } else {
            $errorMessage = $image ? $image->getErrorMessage() : 'Image is null';
            Log::warning('Invalid file upload detected', [
                'item_index' => $itemIndex,
                'error' => $errorMessage
            ]);
        }
        return false;
    }

    /**
     * Cancel a trade offer
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function cancelTrade($id)
    {
        try {
            // Find the trade
            $trade = TradeTransaction::findOrFail($id);
            
            // Check authorization - only the buyer who made the offer can cancel it
            if ($trade->buyer_id !== Auth::id()) {
                return redirect()->back()->with('error', 'You are not authorized to cancel this trade offer');
            }
            
            // Check if the trade is in a cancellable state (only pending trades can be cancelled)
            if ($trade->status !== 'pending') {
                return redirect()->back()->with('error', 'Only pending trade offers can be cancelled');
            }
            
            // Update the trade status to 'canceled'
            $trade->status = 'canceled';
            $trade->save();
            
            Log::info('Trade offer cancelled', [
                'trade_id' => $trade->id,
                'user_id' => Auth::id()
            ]);
            
            return redirect()->back()->with('success', 'Trade offer cancelled successfully');
        } catch (\Exception $e) {
            Log::error('Error cancelling trade offer: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'trade_id' => $id,
                'user_id' => Auth::id()
            ]);
            
            return redirect()->back()->with('error', 'Failed to cancel trade offer: ' . $e->getMessage());
        }
    }

    /**
     * Get detailed information for a specific trade
     *
     * @param int $id
     * @return \Inertia\Response|\Illuminate\Http\JsonResponse
     */
    public function getTradeDetails(Request $request, $id)
    {
        try {
            // Find the trade with all related information
            $trade = TradeTransaction::with([
                'sellerProduct', 
                'offeredItems', 
                'buyer:id,first_name,last_name,username,profile_picture', 
                'seller:id,first_name,last_name,username,profile_picture,seller_code',
                'negotiations.user:id,first_name,last_name,username,profile_picture',
                'meetupLocation.location'
            ])->findOrFail($id);
            
            // Check authorization - only buyer or seller can view trade details
            if (Auth::id() !== $trade->buyer_id && Auth::id() !== $trade->seller_id) {
                return redirect()->back()->with('error', 'You are not authorized to view this trade');
            }
            
            // Format the trade data
            $formattedTrade = [
                'id' => $trade->id,
                'buyer_id' => $trade->buyer_id,
                'seller_id' => $trade->seller_id,
                'seller_code' => $trade->seller_code,
                'seller_product_id' => $trade->seller_product_id,
                'additional_cash' => $trade->additional_cash,
                'notes' => $trade->notes,
                'status' => $trade->status,
                'created_at' => $trade->created_at,
                'updated_at' => $trade->updated_at,
                'seller_product' => $trade->sellerProduct ? [
                    'id' => $trade->sellerProduct->id,
                    'name' => $trade->sellerProduct->name,
                    'price' => $trade->sellerProduct->price,
                    'description' => $trade->sellerProduct->description,
                    'images' => $trade->sellerProduct->images ? array_map(function ($image) {
                        // Make sure we have a valid image path
                        if ($image && file_exists(storage_path('app/public/' . $image))) {
                            return asset('storage/' . $image);
                        } else {
                            return asset('images/placeholder-product.jpg');
                        }
                    }, $trade->sellerProduct->images) : []
                ] : null,
                'offered_items' => $trade->offeredItems->map(function ($item) {
                    // Debug logging to identify image path issues
                    Log::debug('Processing offered item: ' . $item->id);
                    
                    $processedImages = [];
                    
                    if ($item->images) {
                        // If it's a JSON string, decode it safely
                        $imageData = $item->images;
                        
                        if (is_string($imageData)) {
                            try {
                                $decodedImages = json_decode($imageData, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decodedImages)) {
                                    $imageData = $decodedImages;
                                } else {
                                    Log::warning('Invalid JSON in offered item images: ' . json_last_error_msg());
                                    $imageData = [$imageData]; // Treat as single path
                                }
                            } catch (\Exception $e) {
                                Log::error('JSON decode exception: ' . $e->getMessage());
                                $imageData = [$imageData]; // Fallback to single path
                            }
                        }
                        
                        // Ensure we're working with an array
                        $imageArray = is_array($imageData) ? $imageData : [$imageData];
                        
                        // Process each image path - simplified to match product image handling
                        foreach ($imageArray as $imagePath) {
                            if (empty($imagePath)) continue;
                            
                            // Log the raw path
                            Log::debug('Raw image path from database: ' . $imagePath);
                            
                            // Use the same simple check that works for product images
                            if (file_exists(storage_path('app/public/' . $imagePath))) {
                                $processedImages[] = asset('storage/' . $imagePath);
                                Log::debug('Image found using standard path: ' . asset('storage/' . $imagePath));
                            } else {
                                // If file doesn't exist at the expected location, create URL anyway
                                // The frontend's error handler will show a placeholder if needed
                                $processedImages[] = asset('storage/' . $imagePath);
                                Log::debug('Image not found at expected path, using URL anyway: ' . asset('storage/' . $imagePath));
                            }
                        }
                    }
                    
                    // Make sure we have at least an empty array
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'quantity' => $item->quantity,
                        'estimated_value' => $item->estimated_value,
                        'description' => $item->description,
                        'images' => $processedImages
                    ];
                }),
                'buyer' => $trade->buyer ? [
                    'id' => $trade->buyer->id,
                    'name' => $trade->buyer->first_name . ' ' . $trade->buyer->last_name,
                    'profile_picture' => $trade->buyer->profile_picture && file_exists(storage_path('app/public/' . $trade->buyer->profile_picture)) 
                        ? asset('storage/' . $trade->buyer->profile_picture) : null
                ] : null,
                'seller' => $trade->seller ? [
                    'id' => $trade->seller->id,
                    'name' => $trade->seller->first_name . ' ' . $trade->seller->last_name,
                    'profile_picture' => $trade->seller->profile_picture && file_exists(storage_path('app/public/' . $trade->seller->profile_picture))
                        ? asset('storage/' . $trade->seller->profile_picture) : null
                ] : null,
                'negotiations' => $trade->negotiations ? $trade->negotiations->map(function ($negotiation) {
                    return [
                        'id' => $negotiation->id,
                        'message' => $negotiation->message,
                        'created_at' => $negotiation->created_at,
                        'user' => [
                            'id' => $negotiation->user->id,
                            'name' => $negotiation->user->first_name . ' ' . $negotiation->user->last_name,
                            'profile_picture' => $negotiation->user->profile_picture && file_exists(storage_path('app/public/' . $negotiation->user->profile_picture))
                                ? asset('storage/' . $negotiation->user->profile_picture) : null
                        ]
                    ];
                }) : [],
                'meetup_location' => $trade->meetupLocation ? [
                    'id' => $trade->meetupLocation->id,
                    'full_name' => $trade->meetupLocation->full_name,
                    'phone' => $trade->meetupLocation->phone,
                    'description' => $trade->meetupLocation->description,
                    'location' => $trade->meetupLocation->location ? [
                        'id' => $trade->meetupLocation->location->id,
                        'name' => $trade->meetupLocation->location->name,
                        'latitude' => $trade->meetupLocation->location->latitude,
                        'longitude' => $trade->meetupLocation->location->longitude
                    ] : null
                ] : null,
                'meetup_schedule' => $trade->meetup_schedule,
                'formatted_meetup_date' => $trade->meetup_schedule ? date('F j, Y', strtotime($trade->meetup_schedule)) : null,
            ];
            
            // Return as Inertia response with trade data as a prop
            return Inertia::render('Dashboard/MyTrades', [
                'trade' => $formattedTrade
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching trade details: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Failed to fetch trade details: ' . $e->getMessage());
        }
    }

    /**
     * Soft delete a trade transaction
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function deleteTrade($id)
    {
        try {
            // Find the trade
            $trade = TradeTransaction::findOrFail($id);
            
            // Check authorization - only the user involved in the trade can delete it
            if (Auth::id() !== $trade->buyer_id && Auth::id() !== $trade->seller_id) {
                return redirect()->back()->with('error', 'You are not authorized to delete this trade');
            }
            
            // Check if the trade is eligible for deletion (completed, cancelled, or rejected)
            if (!in_array($trade->status, ['completed', 'canceled', 'rejected'])) {
                return redirect()->back()->with('error', 'Only completed, cancelled, or rejected trades can be deleted');
            }
            
            // Soft delete the trade
            $trade->delete();
            
            Log::info('Trade soft-deleted', [
                'trade_id' => $trade->id,
                'user_id' => Auth::id()
            ]);
            
            return redirect()->back()->with('success', 'Trade deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting trade: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'trade_id' => $id,
                'user_id' => Auth::id()
            ]);
            
            return redirect()->back()->with('error', 'Failed to delete trade: ' . $e->getMessage());
        }
    }

    /**
     * Bulk soft delete trade transactions
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function bulkDeleteTrades(Request $request)
    {
        try {
            $validated = $request->validate([
                'trade_ids' => 'required|array',
                'trade_ids.*' => 'exists:trade_transactions,id'
            ]);
            
            // Get trades that belong to the user and have eligible statuses
            $trades = TradeTransaction::whereIn('id', $validated['trade_ids'])
                ->where(function($query) {
                    $query->where('buyer_id', Auth::id())
                          ->orWhere('seller_id', Auth::id());
                })
                ->whereIn('status', ['completed', 'canceled', 'rejected'])
                ->get();
            
            if ($trades->isEmpty()) {
                return redirect()->back()->with('error', 'No eligible trades found to delete');
            }
            
            foreach ($trades as $trade) {
                $trade->delete();
            }
            
            Log::info('Bulk trades soft-deleted', [
                'count' => $trades->count(),
                'trade_ids' => $trades->pluck('id'),
                'user_id' => Auth::id()
            ]);
            
            return redirect()->back()->with('success', $trades->count() . ' trades deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error bulk deleting trades: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            
            return redirect()->back()->with('error', 'Failed to delete trades: ' . $e->getMessage());
        }
    }

    /**
     * Get meetup locations for a specific product
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductMeetupLocations($id)
    {
        try {
            $product = Product::with(['seller' => function($query) {
                $query->select('id', 'first_name', 'last_name', 'username', 'seller_code', 'profile_picture', 'location');
            }])->findOrFail($id);
            
            if (!$product->is_tradable) {
                return response()->json([
                    'success' => false,
                    'message' => 'This product is not available for trade.'
                ], 400);
            }
            
            // Find the seller by seller_code
            $seller = null;
            if (!empty($product->seller_code)) {
                $seller = User::where('seller_code', $product->seller_code)->first();
            } else if ($product->seller) {
                $seller = $product->seller;
            }
            
            if (!$seller) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seller information not found for this product.'
                ], 404);
            }
            
            // Get the seller's meetup locations
            $meetupLocations = $seller->meetupLocations()
                ->where('is_active', true)
                ->with('location')
                ->get();
            
            // Get default location
            $defaultLocation = $meetupLocations->where('is_default', true)->first();
            
            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'seller' => [
                        'id' => $seller->id,
                        'name' => $seller->first_name . ' ' . $seller->last_name,
                        'first_name' => $seller->first_name,
                        'last_name' => $seller->last_name,
                        'username' => $seller->username,
                        'seller_code' => $seller->seller_code,
                        'profile_picture' => $seller->profile_picture ? asset('storage/' . $seller->profile_picture) : null,
                        'location' => $seller->location
                    ]
                ],
                'meetup_locations' => $meetupLocations,
                'default_location' => $defaultLocation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching meetup locations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark a trade as completed
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function completeTrade($id)
    {
        try {
            // Find the trade
            $trade = TradeTransaction::findOrFail($id);
            
            // Verify that the user is the seller for this trade
            if ($trade->seller_id !== Auth::id() && $trade->seller_code !== Auth::user()->seller_code) {
                return redirect()->back()->with('error', 'Unauthorized action');
            }
            
            // Only accepted trades can be marked as completed
            if ($trade->status !== 'accepted') {
                return redirect()->back()->with('error', 'Only accepted trades can be marked as completed');
            }
            
            // Update trade status to completed
            $trade->update(['status' => 'completed']);
            
            // Here you could implement additional logic like:
            // - Notify the buyer that the trade is complete
            // - Update inventory/stock if needed
            // - Release payment if there's a payment escrow system
            
            Log::info('Trade marked as completed', [
                'trade_id' => $trade->id,
                'user_id' => Auth::id()
            ]);
            
            return redirect()->back()->with('success', 'Trade marked as completed successfully');
        } catch (\Exception $e) {
            Log::error('Error completing trade: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'trade_id' => $id,
                'user_id' => Auth::id()
            ]);
            
            return redirect()->back()->with('error', 'Failed to mark trade as completed: ' . $e->getMessage());
        }
    }

    /**
     * Update a trade transaction
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function updateTrade(Request $request, $id)
    {
        try {
            // Find the trade
            $trade = TradeTransaction::findOrFail($id);
            
            // Check authorization - only the buyer who made the offer can update it
            if ($trade->buyer_id !== Auth::id()) {
                return redirect()->back()->with('error', 'You are not authorized to update this trade offer');
            }
            
            // Check if the trade is in an editable state (only pending trades can be updated)
            if ($trade->status !== 'pending') {
                return redirect()->back()->with('error', 'Only pending trade offers can be updated');
            }
            
            // Validate the input - expand validation to include offered items if they exist
            $baseValidation = [
                'additional_cash' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string|max:1000',
                'meetup_location_id' => 'nullable|exists:meetup_locations,id',
                'meetup_date' => 'nullable|date|after_or_equal:today',
            ];
            
            // Check if we have offered item updates
            if ($request->has('offered_items')) {
                $baseValidation['offered_items'] = 'array';
                $baseValidation['offered_items.*.id'] = 'required|exists:trade_offered_items,id';
                $baseValidation['offered_items.*.name'] = 'required|string|max:255';
                $baseValidation['offered_items.*.quantity'] = 'required|integer|min:1';
                $baseValidation['offered_items.*.estimated_value'] = 'required|numeric|min:0';
                $baseValidation['offered_items.*.description'] = 'nullable|string|max:1000';
                
                // Image validation - check for either new images or current images
                $baseValidation['offered_items.*.images'] = 'nullable|array';
                $baseValidation['offered_items.*.images.*'] = 'nullable|file|image|max:2048';
                $baseValidation['offered_items.*.current_images'] = 'nullable';
            }
            
            $validated = $request->validate($baseValidation);
            
            // Process and update meetup information
            $meetupSchedule = null;
            if (!empty($validated['meetup_date'])) {
                $meetupDate = new \DateTime($validated['meetup_date']);
                $meetupSchedule = $meetupDate->format('Y-m-d');
                
                // Verify that the meetup location belongs to the seller
                if (!empty($validated['meetup_location_id'])) {
                    $locationBelongsToSeller = DB::table('meetup_locations')
                        ->where('id', $validated['meetup_location_id'])
                        ->whereRaw('LOWER(user_id::text) = ?', [strtolower($trade->seller_id)])
                        ->exists();
                    
                    if (!$locationBelongsToSeller) {
                        return redirect()->back()->with('error', 'Invalid meetup location selected');
                    }
                }
            }
            
            // Begin database transaction
            DB::beginTransaction();

            try {
                // Update the trade basic information
                $trade->update([
                    'meetup_location_id' => $validated['meetup_location_id'] ?? $trade->meetup_location_id,
                    'meetup_schedule' => $meetupSchedule ?? $trade->meetup_schedule,
                    'additional_cash' => $validated['additional_cash'] ?? $trade->additional_cash,
                    'notes' => $validated['notes'] ?? $trade->notes,
                ]);
                
                // Handle offered items updates if present
                if (isset($validated['offered_items']) && is_array($validated['offered_items'])) {
                    foreach ($validated['offered_items'] as $itemData) {
                        // Find the item to update
                        $offeredItem = TradeOfferedItem::where('id', $itemData['id'])
                            ->where('trade_transaction_id', $trade->id)
                            ->first();
                            
                        if (!$offeredItem) {
                            Log::warning("Offered item not found or does not belong to this trade", [
                                'item_id' => $itemData['id'],
                                'trade_id' => $trade->id
                            ]);
                            continue;
                        }
                        
                        // Update basic item info
                        $offeredItem->name = $itemData['name'];
                        $offeredItem->quantity = $itemData['quantity'];
                        $offeredItem->estimated_value = $itemData['estimated_value'];
                        $offeredItem->description = $itemData['description'] ?? '';
                        
                        // Process images
                        $images = [];
                        
                        // Keep current images that weren't removed
                        if (!empty($itemData['current_images'])) {
                            $currentImages = is_string($itemData['current_images']) ? 
                                json_decode($itemData['current_images'], true) : 
                                $itemData['current_images'];
                                
                            if (is_array($currentImages)) {
                                $images = array_merge($images, $currentImages);
                            }
                        }
                        
                        // Add any new uploaded images
                        if (isset($itemData['images']) && is_array($itemData['images'])) {
                            foreach ($itemData['images'] as $image) {
                                if ($image instanceof \Illuminate\Http\UploadedFile) {
                                    $path = $image->store('trade_images', 'public');
                                    $images[] = $path;
                                    
                                    Log::info("New image uploaded for trade item", [
                                        'item_id' => $offeredItem->id,
                                        'image_path' => $path
                                    ]);
                                }
                            }
                        }
                        
                        // Make sure we have at least one image
                        if (empty($images)) {
                            return redirect()->back()->with('error', "Each offered item must have at least one image");
                        }
                        
                        // Update the item's images
                        $offeredItem->images = json_encode($images);
                        $offeredItem->save();
                        
                        Log::info("Updated offered item for trade", [
                            'item_id' => $offeredItem->id,
                            'trade_id' => $trade->id,
                            'image_count' => count($images)
                        ]);
                    }
                }
                
                DB::commit();
                
                Log::info('Trade offer updated', [
                    'trade_id' => $trade->id,
                    'user_id' => Auth::id()
                ]);
                
                // Return success response
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Trade updated successfully',
                    ]);
                }
                
                return redirect()->back()->with('success', 'Trade offer updated successfully');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error updating trade offer: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'trade_id' => $id,
                'user_id' => Auth::id()
            ]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update trade: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to update trade offer: ' . $e->getMessage());
        }
    }

    /**
     * Send a message in a trade conversation
     * 
     * @param Request $request
     * @param TradeTransaction $trade
     * @return \Illuminate\Http\Response
     */
    public function sendMessage(Request $request, $trade)
    {
        try {
            // Find the trade
            $tradeTransaction = TradeTransaction::findOrFail($trade);
            
            // Verify that the user is either the buyer or the seller
            if (Auth::id() !== $tradeTransaction->buyer_id && Auth::id() !== $tradeTransaction->seller_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to send messages in this trade'
                ], 403);
            }
            
            // Validate the request
            $validated = $request->validate([
                'message' => 'required|string|max:1000'
            ]);
            
            // Create the message
            $message = DB::table('trade_messages')->insert([
                'trade_transaction_id' => $tradeTransaction->id,
                'sender_id' => Auth::id(),
                'message' => $validated['message'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Get the created message with user information for immediate return
            $newMessage = DB::table('trade_messages as tm')
                ->join('users as u', 'tm.sender_id', '=', 'u.id')
                ->select(
                    'tm.id', 
                    'tm.message', 
                    'tm.created_at',
                    'tm.read_at',
                    'tm.sender_id',
                    'u.first_name',
                    'u.last_name',
                    'u.profile_picture'
                )
                ->where('tm.trade_transaction_id', $tradeTransaction->id)
                ->orderBy('tm.created_at', 'desc')
                ->first();
            
            // Format the message for the response
            $formattedMessage = [
                'id' => $newMessage->id,
                'message' => $newMessage->message,
                'created_at' => $newMessage->created_at,
                'read_at' => $newMessage->read_at,
                'user' => [
                    'id' => $newMessage->sender_id,
                    'name' => $newMessage->first_name . ' ' . $newMessage->last_name,
                    'profile_picture' => $newMessage->profile_picture ? asset('storage/' . $newMessage->profile_picture) : null
                ]
            ];
            
            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => $formattedMessage
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending trade message: ' . $e->getMessage(), [
                'trade_id' => $trade,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get messages for a trade
     * 
     * @param TradeTransaction $trade
     * @return \Illuminate\Http\Response
     */
    public function getMessages(Request $request, $trade)
    {
        try {
            // Find the trade
            $tradeTransaction = TradeTransaction::findOrFail($trade);
            
            // Verify that the user is either the buyer or the seller
            if (Auth::id() !== $tradeTransaction->buyer_id && Auth::id() !== $tradeTransaction->seller_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to view messages for this trade'
                ], 403);
            }
            
            // Get the messages with user information
            $messages = DB::table('trade_messages as tm')
                ->join('users as u', 'tm.sender_id', '=', 'u.id')
                ->select(
                    'tm.id', 
                    'tm.message', 
                    'tm.created_at',
                    'tm.read_at',
                    'tm.sender_id',
                    'u.first_name',
                    'u.last_name',
                    'u.profile_picture'
                )
                ->where('tm.trade_transaction_id', $tradeTransaction->id)
                ->orderBy('tm.created_at', 'asc')
                ->get();
            
            // Format the messages for the response
            $formattedMessages = $messages->map(function($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'created_at' => $message->created_at,
                    'read_at' => $message->read_at,
                    'user' => [
                        'id' => $message->sender_id,
                        'name' => $message->first_name . ' ' . $message->last_name,
                        'profile_picture' => $message->profile_picture ? asset('storage/' . $message->profile_picture) : null
                    ]
                ];
            });
            
            // Mark messages as read if the user is not the sender
            DB::table('trade_messages')
                ->where('trade_transaction_id', $tradeTransaction->id)
                ->where('sender_id', '!=', Auth::id())
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
            
            // Return success response
            return response()->json([
                'success' => true,
                'data' => $formattedMessages
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting trade messages: ' . $e->getMessage(), [
                'trade_id' => $trade,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get messages: ' . $e->getMessage()
            ], 500);
        }
    }
}