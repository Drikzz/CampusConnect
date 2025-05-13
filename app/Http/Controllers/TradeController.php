<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\TradeTransaction;
use App\Models\TradeOfferedItem;
use App\Models\MeetupLocation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Rating;
use App\Models\SellerReview;
use App\Models\SellerWallet;
use App\Models\Wishlist;
use App\Models\TradeMessage;
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
        try {
            $product = Product::with(['seller' => function($query) {
                $query->select('id', 'first_name', 'last_name', 'username', 'seller_code', 'profile_picture');
            }, 'category'])->findOrFail($id);
            
            if (!$product->is_tradable) {
                return redirect()->route('products.show', $id)
                    ->with('error', 'This product is not available for trade');
            }
            
            // Transform the product data with complete seller information
            $formattedProduct = [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => (float)$product->price,
                'discounted_price' => (float)$product->discounted_price,
                'discount' => (float)$product->discount,
                'stock' => $product->stock,
                'images' => array_map(function ($image) {
                    return asset('storage/' . $image);
                }, is_array($product->images) ? $product->images : json_decode($product->images, true) ?? []),
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name
                ] : null,
                'seller' => $product->seller ? [
                    'id' => $product->seller->id,
                    'name' => $product->seller->first_name . ' ' . $product->seller->last_name,
                    'first_name' => $product->seller->first_name,
                    'last_name' => $product->seller->last_name,
                    'username' => $product->seller->username,
                    'seller_code' => $product->seller->seller_code,
                    'profile_picture' => $product->seller->profile_picture ? 
                        asset('storage/' . $product->seller->profile_picture) : 
                        asset('images/placeholder-avatar.jpg'),
                    'rating' => $product->seller->ratings()->avg('rating') ?? 0,
                    'reviews_count' => $product->seller->reviews()->count(),
                    'location' => $product->seller->location ?? 'Zamboanga City'
                ] : null,
                'is_tradable' => (bool)$product->is_tradable,
                'status' => $product->status
            ];
            
            return Inertia::render('Trade/Show', [
                'product' => $formattedProduct
            ]);
        } catch (\Exception $e) {
            Log::error('Error showing trade form: ' . $e->getMessage(), [
                'product_id' => $id, 
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('products')
                ->with('error', 'Unable to load trade form. Please try again later.');
        }
    }
    
    /**
     * Get complete product details with seller information
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductDetails($id)
    {
        try {
            $product = Product::with(['category', 'seller'])
                ->findOrFail($id);

            // Format all product information including seller data
            $formattedProduct = [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => (float)$product->price,
                'discounted_price' => (float)$product->discounted_price,
                'discount' => (float)$product->discount,
                'stock' => $product->stock,
                'images' => $this->formatProductImages($product->images),
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name
                ] : null,
                'seller' => $this->formatSellerData($product->seller),
                'is_buyable' => (bool)$product->is_buyable,
                'is_tradable' => (bool)$product->is_tradable,
                'status' => $product->status,
            ];

            return response()->json($formattedProduct);
        } catch (\Exception $e) {
            Log::error('Error retrieving product details via API: ' . $e->getMessage(), [
                'product_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to load product details',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method to format product images
     */
    private function formatProductImages($images)
    {
        if (!$images) {
            return [];
        }
        
        // Debug the incoming images format
        \Log::debug("Formatting product images - raw input:", [
            'type' => gettype($images),
            'value' => $images
        ]);
        
        // If already an array, use it directly
        if (is_array($images)) {
            $formattedImages = [];
            foreach ($images as $image) {
                if (empty($image)) continue;
                
                // Store paths without domain references and with correct format
                if (is_string($image)) {
                    // Remove domain if present
                    $cleanPath = preg_replace('#^https?://[^/]+/#', '', $image);
                    
                    // Handle storage paths with consistent format
                    if (strpos($cleanPath, 'storage/') === 0) {
                        $formattedImages[] = $cleanPath;
                    } else if (strpos($cleanPath, '/storage/') === 0) {
                        $formattedImages[] = substr($cleanPath, 1); // Remove leading slash
                    } else {
                        $formattedImages[] = 'storage/' . $cleanPath;
                    }
                } else {
                    $formattedImages[] = $image;
                }
            }
            \Log::debug("Formatted array images:", $formattedImages);
            return $formattedImages;
        }
        
        // If JSON string, decode first
        try {
            if (is_string($images) && (str_starts_with($images, '[') || str_starts_with($images, '{'))) {
                $decodedImages = json_decode($images, true);
                if (is_array($decodedImages)) {
                    \Log::debug("Decoded JSON images:", $decodedImages);
                    return $this->formatProductImages($decodedImages);
                }
            }
        } catch (\Exception $e) {
            \Log::error("Error parsing JSON image data: " . $e->getMessage());
            // If JSON decode fails, treat as a single image
        }
        
        // Treat as a single image string
        if (is_string($images)) {
            // Clean any domain reference
            $cleanPath = preg_replace('#^https?://[^/]+/#', '', $images);
            
            // Handle storage prefix
            if (strpos($cleanPath, 'storage/') === 0) {
                return [$cleanPath];
            } else if (strpos($cleanPath, '/storage/') === 0) {
                return [substr($cleanPath, 1)]; // Remove leading slash
            } else {
                return ['storage/' . $cleanPath];
            }
        }
        
        return [];
    }

    /**
     * Helper method to format seller data
     */
    private function formatSellerData($seller)
    {
        if (!$seller) {
            return null;
        }
        
        return [
            'id' => $seller->id,
            'first_name' => $seller->first_name,
            'last_name' => $seller->last_name,
            'username' => $seller->username,
            'seller_code' => $seller->seller_code,
            'profile_picture' => $seller->profile_picture ? 
                asset('storage/' . $seller->profile_picture) : 
                asset('images/placeholder-avatar.jpg'),
            'location' => $seller->location ?? 'Zamboanga City'
        ];
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
                'offered_items.*.image_files' => 'sometimes|array',
                'offered_items.*.image_files.*' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                'offered_items.*.images_json' => 'sometimes|string',
            ]);
            
            DB::beginTransaction();
            
            // Process the meetup_schedule (format: "YYYY-MM-DD, HH:MM")
            try {
                // Parse the schedule to create a proper datetime string
                $scheduleComponents = explode(',', $validated['meetup_schedule']);
                
                if (count($scheduleComponents) >= 2) {
                    $date = trim($scheduleComponents[0]);
                    $time = trim($scheduleComponents[1]);
                    
                    // Create a Carbon instance from the date and time
                    $meetupSchedule = Carbon::parse("$date $time");
                    Log::info('Parsed meetup schedule: ' . $meetupSchedule->toDateTimeString());
                } else {
                    Log::error('Invalid meetup_schedule format: ' . $validated['meetup_schedule']);
                    return back()->with('error', 'Invalid meetup schedule format');
                }
            } catch (\Exception $e) {
                Log::error('Error parsing meetup schedule: ' . $e->getMessage());
                return back()->with('error', 'Unable to parse meetup schedule: ' . $e->getMessage());
            }
            
            // Get the product
            $product = Product::findOrFail($validated['seller_product_id']);
            $sellerId = $product->seller_id;
            $sellerCode = $product->seller_code;
            
            // Create the trade transaction
            $trade = TradeTransaction::create([
                'buyer_id' => Auth::id(),
                'seller_id' => $sellerId,
                'seller_code' => $sellerCode,
                'seller_product_id' => $validated['seller_product_id'],
                'meetup_location_id' => $validated['meetup_location_id'],
                'meetup_schedule' => $meetupSchedule,
                'meetup_day' => $validated['selected_day'],
                'preferred_time' => $validated['preferred_time'],
                'additional_cash' => $validated['additional_cash'],
                'notes' => $validated['notes'],
                'status' => 'pending',
            ]);
            
            // Process each offered item
            foreach ($validated['offered_items'] as $index => $item) {
                $existingImages = [];
                $uploadedImages = [];
                
                // Process existing images if this is an update
                if (isset($item['current_images']) && is_array($item['current_images'])) {
                    foreach ($item['current_images'] as $currentImage) {
                        // Clean up storage path format
                        $path = str_replace('/storage/', '', $currentImage);
                        if (Storage::disk('public')->exists($path)) {
                            $existingImages[] = $path;
                        }
                    }
                }
                
                // Process image files
                if ($request->hasFile("offered_items.{$index}.image_files")) {
                    $files = $request->file("offered_items.{$index}.image_files");
                    $imagesJson = isset($item['images_json']) ? json_decode($item['images_json'] ?? '[]', true) : [];
                    
                    foreach ($files as $fileIndex => $file) {
                        // Check if we have metadata for this file
                        $fileInfo = isset($imagesJson[$fileIndex]) ? $imagesJson[$fileIndex] : null;
                        
                        // If file is a cropped image with a storage path, use that path
                        if ($fileInfo && isset($fileInfo['is_cropped']) && $fileInfo['is_cropped'] && 
                            isset($fileInfo['storage_path']) && $fileInfo['storage_path']) {
                            
                            // Make sure we're getting a clean path
                            $path = trim($fileInfo['storage_path']);
                            
                            // Store the file at that specific path
                            Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));
                            
                            $uploadedImages[] = $path;
                        } else {
                            // Regular file upload
                            $path = $file->store('trade_items', 'public');
                            $uploadedImages[] = $path;
                        }
                    }
                }
                
                // Handle base64 images for blob URLs (when coming from MyTrades)
                if (isset($item['blob_images']) && is_array($item['blob_images'])) {
                    foreach ($item['blob_images'] as $blobIndex => $blobData) {
                        if (isset($blobData['data']) && isset($blobData['extension'])) {
                            // Generate a unique filename
                            $filename = 'trade_items/' . uniqid() . '.' . $blobData['extension'];
                            
                            // Convert base64 to file and store
                            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $blobData['data']));
                            Storage::disk('public')->put($filename, $imageData);
                            
                            $uploadedImages[] = $filename;
                        }
                    }
                }
                
                // Combine existing and new images
                $allImages = array_merge($existingImages, $uploadedImages);
                
                // Important: Store images as a plain JSON array of strings, not as a string of a JSON array
                TradeOfferedItem::create([
                    'trade_transaction_id' => $trade->id,
                    'name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'estimated_value' => $item['estimated_value'],
                    'description' => $item['description'] ?? '',
                    'condition' => $item['condition'],
                    'images' => json_encode($allImages, JSON_UNESCAPED_SLASHES)
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
    public function cancelTrade($id)
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
            
        return Inertia::render('Products/Trade', [
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
        
        // Get trade transactions with all related information
        $trades = $this->getTradesForUser($user);
        
        // Get dashboard stats
        $stats = $this->getDashboardStats();
        
        // Calculate unread messages count
        $unreadMessages = $this->getUnreadMessagesCount($user->id);
        
        return Inertia::render('Dashboard/MyTrades', [
            'trades' => $trades,
            'stats' => $stats,
            'user' => $user,
            'unreadMessages' => $unreadMessages,
            'flash' => [
                'success' => session('success'),
                'error' => session('error')
            ]
        ]);
    }
    
    /**
     * Get all trade transactions for a user with proper formatting
     * 
     * @param User $user
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    private function getTradesForUser($user)
    {
        try {
            \Log::info('Fetching trades for user', ['user_id' => $user->id]);
            
            // Eager load all relations to prevent N+1 query issues
            $trades = TradeTransaction::with([
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
            
            \Log::info('Trade count', ['count' => $trades->count()]);
            
            // Simple data structure for frontend to avoid nested object issues
            $trades->getCollection()->transform(function ($trade) {
                // Get seller product image URL safely
                $productImageUrl = '/storage/imgs/download-Copy.jpg';
                if ($trade->sellerProduct && $trade->sellerProduct->images) {
                    $images = $trade->sellerProduct->images;
                    
                    if (is_string($images)) {
                        try {
                            $decodedImages = json_decode($images, true);
                            if (is_array($decodedImages) && !empty($decodedImages)) {
                                $imagePath = $decodedImages[0];
                                if (is_string($imagePath)) {
                                    $productImageUrl = $this->formatImageUrl($imagePath);
                                }
                            } else if (is_string($images) && !empty($images)) {
                                $productImageUrl = $this->formatImageUrl($images);
                            }
                        } catch (\Exception $e) {
                            \Log::error('Error decoding product images: ' . $e->getMessage());
                            $productImageUrl = $this->formatImageUrl($images);
                        }
                    } else if (is_array($images) && !empty($images)) {
                        $productImageUrl = $this->formatImageUrl($images[0]);
                    }
                }
                
                // Format all offered items with consistent image paths
                $offeredItems = $trade->offeredItems->map(function($item) {
                    $imageUrl = '/storage/imgs/download-Copy.jpg';
                    
                    if ($item->images) {
                        $images = $item->images;
                        if (is_array($images) && !empty($images)) {
                            $imageUrl = $this->formatImageUrl($images[0]);
                        } else if (is_string($images) && !empty($images)) {
                            $imageUrl = $this->formatImageUrl($images);
                        }
                    }
                    
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'quantity' => $item->quantity,
                        'estimated_value' => (float)$item->estimated_value,
                        'description' => $item->description ?? '',
                        'condition' => $item->condition ?? 'used_good',
                        'images' => [$imageUrl],
                        'image_url' => $imageUrl
                    ];
                });
                
                // Create flattened data structure with all necessary properties
                return [
                    'id' => $trade->id,
                    'status' => $trade->status,
                    'additional_cash' => $trade->additional_cash,
                    'notes' => $trade->notes,
                    'created_at' => $trade->created_at,
                    'updated_at' => $trade->updated_at,
                    'meetup_schedule' => $trade->meetup_schedule,
                    'meetup_day' => $trade->meetup_day,
                    'preferred_time' => $trade->preferred_time ?? '',
                    'formatted_meetup_date' => $trade->meetup_schedule ? 
                        $trade->meetup_schedule->format('F j, Y \a\t h:i A') : null,
                    'meetup_location_name' => $trade->meetupLocation && $trade->meetupLocation->location ? 
                        $trade->meetupLocation->location->name : 
                        ($trade->meetupLocation ? $trade->meetupLocation->custom_location : 'Location not specified'),
                    
                    // Buyer data
                    'buyer_id' => $trade->buyer_id,
                    'buyer' => [
                        'id' => $trade->buyer ? $trade->buyer->id : null,
                        'name' => $trade->buyer ? $trade->buyer->first_name . ' ' . $trade->buyer->last_name : 'Unknown Buyer',
                        'profile_picture' => $trade->buyer && $trade->buyer->profile_picture ? 
                            $this->formatImageUrl($trade->buyer->profile_picture) : 
                            '/storage/imgs/download-Copy.jpg'
                    ],
                    
                    // Seller data
                    'seller_id' => $trade->seller_id,
                    'seller_code' => $trade->seller_code,
                    'seller' => [
                        'id' => $trade->seller ? $trade->seller->id : null,
                        'name' => $trade->seller ? $trade->seller->first_name . ' ' . $trade->seller->last_name : 'Unknown Seller',
                        'seller_code' => $trade->seller_code,
                        'profile_picture' => $trade->seller && $trade->seller->profile_picture ? 
                            $this->formatImageUrl($trade->seller->profile_picture) : 
                            '/storage/imgs/download-Copy.jpg'
                    ],
                    
                    // Product data
                    'seller_product_id' => $trade->seller_product_id,
                    'seller_product' => [
                        'id' => $trade->sellerProduct ? $trade->sellerProduct->id : null,
                        'name' => $trade->sellerProduct ? $trade->sellerProduct->name : 'Unknown Product',
                        'price' => $trade->sellerProduct ? (float)$trade->sellerProduct->price : 0,
                        'images' => [$productImageUrl]
                    ],
                    
                    // Offered items
                    'offered_items' => $offeredItems,
                    
                    // Messages
                    'messages' => $trade->messages->map(function($message) {
                        $profilePicture = $message->user && $message->user->profile_picture ? 
                            $this->formatImageUrl($message->user->profile_picture) : 
                            '/storage/imgs/download-Copy.jpg';
                        
                        return [
                            'id' => $message->id,
                            'sender_id' => $message->sender_id,
                            'message' => $message->message,
                            'created_at' => $message->created_at,
                            'read_at' => $message->read_at,
                            'user' => [
                                'id' => $message->user ? $message->user->id : null,
                                'name' => $message->user ? $message->user->first_name . ' ' . $message->user->last_name : 'Unknown User',
                                'profile_picture' => $profilePicture
                            ],
                            'user_id' => $message->sender_id,
                            'formatted_time' => $message->created_at ? $message->created_at->format('M j, Y g:i A') : null
                        ];
                    })
                ];
            });
            
            return $trades;
        } catch (\Exception $e) {
            \Log::error('Error getting trades for user: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return empty paginator
            return new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 10, 1
            );
        }
    }
    
    /**
     * Format image URLs consistently
     *
     * @param string $imagePath
     * @return string
     */
    private function formatImageUrl($imagePath)
    {
        if (empty($imagePath)) {
            return '/storage/imgs/download-Copy.jpg';
        }
        
        // If already a full URL, return as is
        if (strpos($imagePath, 'http://') === 0 || strpos($imagePath, 'https://') === 0) {
            return $imagePath;
        }
        
        // Clean up path format
        if (strpos($imagePath, 'storage/') === 0) {
            return '/' . $imagePath;
        } else if (strpos($imagePath, '/storage/') !== 0) {
            return '/storage/' . $imagePath;
        }
        
        return $imagePath;
    }

    /**
     * Get messages for a trade and ensure a message thread exists if needed
     *
     * @param Request $request
     * @param int $tradeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMessages(Request $request, $tradeId)
    {
        try {
            // Find the trade
            $tradeTransaction = TradeTransaction::findOrFail($tradeId);
            
            // Verify that the user is either the buyer or the seller
            if (Auth::id() !== $tradeTransaction->buyer_id && Auth::id() !== $tradeTransaction->seller_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to view messages for this trade'
                ], 403);
            }
            
            // Check if there are any messages
            $messageCount = TradeMessage::where('trade_transaction_id', $tradeId)->count();
            
            // If no messages, create an initial welcome message
            if ($messageCount === 0) {
                // Create a welcome message from the seller
                TradeMessage::create([
                    'trade_transaction_id' => $tradeId,
                    'sender_id' => $tradeTransaction->seller_id,
                    'message' => "Welcome to your trade conversation! You can discuss details about your trade here."
                ]);
            }
            
            // Get the messages with user information
            $messages = TradeMessage::with('user:id,first_name,last_name,username,profile_picture')
                ->where('trade_transaction_id', $tradeId)
                ->orderBy('created_at', 'asc')
                ->get();
            
            // Format the messages for the response
            $formattedMessages = $messages->map(function($message) {
                $profilePicture = '/images/placeholder-avatar.jpg';
                
                if ($message->user && $message->user->profile_picture) {
                    $imagePath = $message->user->profile_picture;
                    if (file_exists(storage_path('app/public/' . $imagePath))) {
                        $profilePicture = asset('storage/' . $imagePath);
                    }
                }
                
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'created_at' => $message->created_at,
                    'read_at' => $message->read_at,
                    'user' => [
                        'id' => $message->user->id,
                        'name' => $message->user->first_name . ' ' . $message->user->last_name,
                        'first_name' => $message->user->first_name,
                        'last_name' => $message->user->last_name,
                        'profile_picture' => $profilePicture
                    ],
                    'user_id' => $message->sender_id
                ];
            });
            
            // Mark messages as read if the user is not the sender
            TradeMessage::where('trade_transaction_id', $tradeId)
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
                'trade_id' => $tradeId,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get messages: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send a message in a trade conversation
     *
     * @param Request $request
     * @param int $tradeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessage(Request $request, $tradeId)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'message' => 'required|string|max:1000'
            ]);
            
            // Find the trade
            $tradeTransaction = TradeTransaction::findOrFail($tradeId);
            
            // Verify that the user is either the buyer or the seller
            if (Auth::id() !== $tradeTransaction->buyer_id && Auth::id() !== $tradeTransaction->seller_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to send messages for this trade'
                ], 403);
            }
            
            // Create the message
            $message = TradeMessage::create([
                'trade_transaction_id' => $tradeId,
                'sender_id' => Auth::id(),
                'message' => $validated['message']
            ]);
            
            // Load the user relationship
            $message->load('user:id,first_name,last_name,username,profile_picture');
            
            // Format profile picture URL
            $profilePicture = '/images/placeholder-avatar.jpg';
            if ($message->user && $message->user->profile_picture) {
                $imagePath = $message->user->profile_picture;
                if (file_exists(storage_path('app/public/' . $imagePath))) {
                    $profilePicture = asset('storage/' . $imagePath);
                }
            }
            
            // Format the message for the response
            $formattedMessage = [
                'id' => $message->id,
                'message' => $message->message,
                'created_at' => $message->created_at,
                'read_at' => null,
                'user' => [
                    'id' => $message->user->id,
                    'name' => $message->user->first_name . ' ' . $message->user->last_name,
                    'first_name' => $message->user->first_name,
                    'last_name' => $message->user->last_name,
                    'profile_picture' => $profilePicture
                ],
                'user_id' => $message->sender_id
            ];
            
            // Return success response
            return response()->json([
                'success' => true,
                'data' => $formattedMessage
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending trade message: ' . $e->getMessage(), [
                'trade_id' => $tradeId,
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
     * Get detailed information for a specific trade
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTradeDetails($trade)
    {
        try {
            // Find the trade with all related information
            $tradeTransaction = TradeTransaction::with([
                'sellerProduct', 
                'offeredItems', 
                'buyer:id,first_name,last_name,username,profile_picture', 
                'seller:id,first_name,last_name,username,profile_picture,seller_code',
                'messages.user:id,first_name,last_name,username,profile_picture',
                'meetupLocation.location'
            ])->findOrFail($trade);
            
            // Check authorization - only buyer or seller can view trade details
            if (Auth::id() !== $tradeTransaction->buyer_id && Auth::id() !== $tradeTransaction->seller_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to view this trade'
                ], 403);
            }
            
            // Format all data for frontend display
            $formattedTrade = [
                'id' => $tradeTransaction->id,
                'status' => $tradeTransaction->status,
                'additional_cash' => $tradeTransaction->additional_cash,
                'notes' => $tradeTransaction->notes,
                'created_at' => $tradeTransaction->created_at,
                'updated_at' => $tradeTransaction->updated_at,
                'meetup_schedule' => $tradeTransaction->meetup_schedule,
                'formatted_meetup_date' => $tradeTransaction->formatted_meetup_date,
                'meetup_location_name' => $tradeTransaction->meetup_location_name,
                'preferred_time' => $tradeTransaction->preferred_time,
                
                // Buyer data with safe image path
                'buyer' => [
                    'id' => $tradeTransaction->buyer?->id,
                    'name' => $tradeTransaction->buyer ? $tradeTransaction->buyer->first_name . ' ' . $tradeTransaction->buyer->last_name : 'Unknown Buyer',
                    'profile_image' => $tradeTransaction->buyer_profile_image
                ],
                
                // Seller data with safe image path
                'seller' => [
                    'id' => $tradeTransaction->seller?->id,
                    'name' => $tradeTransaction->seller ? $tradeTransaction->seller->first_name . ' ' . $tradeTransaction->seller->last_name : 'Unknown Seller',
                    'seller_code' => $tradeTransaction->seller_code,
                    'profile_image' => $tradeTransaction->seller_profile_image
                ],
                
                // Product data with safe image
                'sellerProduct' => [
                    'id' => $tradeTransaction->sellerProduct?->id,
                    'name' => $tradeTransaction->sellerProduct?->name ?? 'Unknown Product',
                    'price' => (float)($tradeTransaction->sellerProduct?->price ?? 0),
                    'description' => $tradeTransaction->sellerProduct?->description ?? '',
                    'images' => [$tradeTransaction->product_image_url]
                ],
                
                // Offered items with processed images
                'offered_items' => $tradeTransaction->offeredItems->map(function($item) {
                    $image = '/storage/imgs/download-Copy.jpg';
                    
                    if (!empty($item->images) && is_array($item->images) && count($item->images) > 0) {
                        $itemImage = $item->images[0];
                        
                        // Clean up path format
                        if (strpos($itemImage, 'storage/') === 0) {
                            $image = '/' . $itemImage;
                        } else if (strpos($itemImage, '/storage/') !== 0) {
                            $image = '/storage/' . $itemImage;
                        } else {
                            $image = $itemImage;
                        }
                    }
                                
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'quantity' => $item->quantity,
                        'estimated_value' => (float)$item->estimated_value,
                        'description' => $item->description,
                        'condition' => $item->condition,
                        'images' => [$image],
                        'image_url' => $image
                    ];
                })
            ];
            
            return response()->json([
                'success' => true,
                'trade' => $formattedTrade
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching trade details: ' . $e->getMessage(), [
                'trade_id' => $trade,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch trade details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate unread messages count for a user
     * 
     * @param int $userId
     * @return int
     */
    private function getUnreadMessagesCount($userId)
    {
        return TradeMessage::join('trade_transactions', 'trade_messages.trade_transaction_id', '=', 'trade_transactions.id')
            ->where('trade_messages.sender_id', '!=', $userId)
            ->where(function($query) use ($userId) {
                $query->where('trade_transactions.buyer_id', $userId)
                      ->orWhere('trade_transactions.seller_id', $userId);
            })
            ->whereNull('trade_messages.read_at')
            ->count();
    }

    /**
     * Get dashboard statistics for the user
     */
    protected function getDashboardStats()
    {
        $user = Auth::user();
        $stats = [
            'totalOrders' => Order::where('buyer_id', $user->id)->count(),
            'activeOrders' => Order::where('buyer_id', $user->id)
                ->whereNotIn('status', ['Completed', 'Cancelled'])
                ->count(),
            'wishlistCount' => Wishlist::where('user_id', $user->id)->count(),
            'totalSales' => 0,
            'activeProducts' => 0,
            'pendingOrders' => 0
        ];

        if ($user->is_seller) {
            $stats['totalSales'] = OrderItem::where('seller_code', $user->seller_code)
                ->whereHas('order', function ($query) {
                    $query->where('status', 'Completed');
                })
                ->sum('subtotal');

            $stats['activeProducts'] = Product::where('seller_code', $user->seller_code)
                ->where('status', 'Active')
                ->count();

            $stats['pendingOrders'] = OrderItem::where('seller_code', $user->seller_code)
                ->whereHas('order', function ($query) {
                    $query->where('status', 'Pending');
                })->count();

            // Fix: Update how we get the wallet data to match SellerWalletController
            $wallet = SellerWallet::where('seller_code', $user->seller_code)
                ->with(['transactions' => function ($query) {
                    $query->latest()->take(5);
                }])
                ->first();

            $stats['wallet'] = $wallet ? [
                'id' => $wallet->id,
                'balance' => $wallet->balance,
                'is_activated' => $wallet->is_activated,
                'status' => $wallet->status,
                'transactions' => $wallet->transactions
            ] : null;
        }

        return $stats;
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
