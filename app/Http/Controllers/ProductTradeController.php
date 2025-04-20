<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\TradeTransaction;
use App\Models\TradeOfferedItem;
use App\Models\User;
use App\Models\MeetupLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ProductTradeController extends Controller
{
    public function index(Request $request)
    {
        // Start with a query for tradable products only
        $query = Product::where('is_tradable', true)
                        ->where('status', 'Active')
                        ->with(['seller' => function($query) {
                            $query->select('id', 'first_name', 'last_name', 'username', 'seller_code', 'profile_picture')
                                ->with(['meetupLocations' => function($q) {
                                    $q->where('is_active', true)
                                      ->with('location');
                                }]);
                        }, 'category']);
        
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
        
        // Get the tradable products with pagination
        $products = $query->paginate(9)->withQueryString();
        
        // Transform product data to ensure full seller info is included
        $products->getCollection()->transform(function ($product) {
            // Include full seller information
            if ($product->seller) {
                $meetupLocations = $product->seller->meetupLocations->map(function($location) {
                    return [
                        'id' => $location->id,
                        'name' => $location->location ? $location->location->name : null,
                        'description' => $location->description,
                        'available_days' => $location->available_days,
                        'available_from' => $location->available_from,
                        'available_until' => $location->available_until,
                        'is_default' => $location->is_default
                    ];
                });

                $product->seller = [
                    'id' => $product->seller->id,
                    'first_name' => $product->seller->first_name,
                    'last_name' => $product->seller->last_name,
                    'username' => $product->seller->username,
                    'seller_code' => $product->seller->seller_code,
                    'profile_picture' => $product->seller->profile_picture,
                    'meetup_locations' => $meetupLocations
                ];
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

        dd($request);

        // Add detailed logging
        Log::info('Trade offer submission started', [
            'request_data' => $request->all(),
            'has_meetup_date' => $request->has('meetup_date'),
            'meetup_date_value' => $request->input('meetup_date'),
            'files' => $request->hasFile('offered_items') ? 'Yes' : 'No',
        ]);
        
        try {
            // Validate the request first
            $validated = $request->validate([
                'seller_product_id' => 'required|exists:products,id',
                'meetup_location_id' => 'required|exists:meetup_locations,id',
                'meetup_schedule' => 'required|string',
                'meetup_date' => 'required|date|after_or_equal:today',
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

            // Log the validated data
            Log::info('Validation passed', [
                'validated_data' => $validated
            ]);

            // Get the product to retrieve the seller information
            $product = Product::with('seller')->findOrFail($validated['seller_product_id']);
            
            // Extract meetup date from the input
            $meetupDate = new Carbon($validated['meetup_date']);
            
            // Begin database transaction
            DB::beginTransaction();
            
            try {
                // 1. Create the trade transaction with ALL required fields
                $tradeTransaction = new TradeTransaction([
                    'buyer_id' => Auth::id(),
                    'seller_id' => $product->seller->id, 
                    'seller_code' => $product->seller->seller_code,
                    'seller_product_id' => $validated['seller_product_id'],
                    'meetup_location_id' => $validated['meetup_location_id'],
                    'meetup_schedule' => $meetupDate,
                    'additional_cash' => $validated['additional_cash'] ?? 0,
                    'notes' => $validated['notes'] ?? null,
                    'status' => 'pending'
                ]);
                
                $tradeTransaction->save();
                
                Log::info('Trade transaction created', [
                    'trade_id' => $tradeTransaction->id
                ]);
                
                // 2. Process and save each offered item
                foreach ($validated['offered_items'] as $index => $itemData) {
                    // Process and store images
                    $images = [];
                    
                    if (isset($itemData['images']) && is_array($itemData['images'])) {
                        foreach ($itemData['images'] as $image) {
                            if ($this->processAndStoreImage($image, $images, $index)) {
                                Log::debug("Processed image for item", [
                                    'item_index' => $index,
                                    'image' => end($images)
                                ]);
                            }
                        }
                    }
                    
                    // Make sure we have at least one image
                    if (empty($images)) {
                        throw new \Exception("No valid images uploaded for item " . ($index + 1));
                    }
                    
                    // Create trade offered item
                    $offeredItem = new TradeOfferedItem([
                        'trade_transaction_id' => $tradeTransaction->id,
                        'name' => $itemData['name'],
                        'quantity' => $itemData['quantity'],
                        'estimated_value' => $itemData['estimated_value'],
                        'description' => $itemData['description'] ?? null,
                        'images' => json_encode($images)
                    ]);
                    
                    $offeredItem->save();
                    
                    Log::info('Trade offered item created', [
                        'item_id' => $offeredItem->id,
                        'trade_id' => $tradeTransaction->id
                    ]);
                }
                
                // Commit the transaction
                DB::commit();
                
                Log::info('Trade offer submission completed successfully', [
                    'trade_id' => $tradeTransaction->id,
                    'user_id' => Auth::id()
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Trade offer submitted successfully',
                    'trade_id' => $tradeTransaction->id
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            Log::error('Validation failed', [
                'errors' => $e->errors()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Trade offer submission error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw ValidationException::withMessages([
                'general' => ['An error occurred while submitting your trade offer: ' . $e->getMessage()]
            ]);
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
        // Validate the image is present and valid
        if (!$image) {
            Log::warning('Invalid file upload detected: Image is null', [
                'item_index' => $itemIndex
            ]);
            return false;
        }
        
        if (!$image->isValid()) {
            $errorMessage = $image->getErrorMessage() ?: 'Image validation failed';
            Log::warning('Invalid file upload detected', [
                'item_index' => $itemIndex,
                'original_name' => $image->getClientOriginalName(),
                'error' => $errorMessage
            ]);
            return false;
        }
        
        try {
            // Store the image and add to the array
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
            return false;
        }
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
            \Log::info('Fetching meetup locations for product:', ['product_id' => $id]); // Add logging

            $product = Product::with(['seller' => function($query) {
                $query->select('id', 'first_name', 'last_name', 'username', 'seller_code', 'profile_picture')
                    ->with(['meetupLocations' => function($q) {
                        $q->where('is_active', true)
                          ->with('location');
                    }]);
            }])->findOrFail($id);
            
            if (!$product->is_tradable) {
                return response()->json([
                    'success' => false,
                    'message' => 'This product is not available for trade.'
                ], 400);
            }
            
            // Get the seller and their meetup locations
            $seller = $product->seller;
            if (!$seller) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seller information not found for this product.'
                ], 404);
            }

            // Transform meetup locations
            $meetupLocations = $seller->meetupLocations->map(function($location) {
                return [
                    'id' => $location->id,
                    'name' => $location->location ? $location->location->name : null,
                    'description' => $location->description,
                    'available_days' => $location->available_days,
                    'available_from' => $location->available_from,
                    'available_until' => $location->available_until,
                    'is_default' => $location->is_default,
                    'location' => $location->location ? [
                        'id' => $location->location->id,
                        'name' => $location->location->name
                    ] : null
                ];
            });

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
                        'profile_picture' => $seller->profile_picture ? asset('storage/' . $seller->profile_picture) : null
                    ]
                ],
                'meetup_locations' => $meetupLocations,
                'default_location' => $meetupLocations->firstWhere('is_default', true)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error fetching meetup locations: ' . $e->getMessage(), [
                'product_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
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