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
                    $imagesJson = json_decode($item['images_json'] ?? '[]', true);
                    
                    foreach ($files as $fileIndex => $file) {
                        // Check if we have metadata for this file
                        $fileInfo = $imagesJson[$fileIndex] ?? null;
                        
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
                
                // Important: Store images as a plain JSON array of strings, not as a string of a JSON array
                TradeOfferedItem::create([
                    'trade_transaction_id' => $trade->id,
                    'name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'estimated_value' => $item['estimated_value'],
                    'description' => $item['description'] ?? '',
                    'condition' => $item['condition'],
                    'images' => json_encode($uploadedImages, JSON_UNESCAPED_SLASHES)
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
        
        // Format data directly here instead of using a separate method
        $trades->getCollection()->transform(function ($trade) {
            // Format seller product images
            if ($trade->sellerProduct && $trade->sellerProduct->images) {
                $trade->sellerProduct->images = array_map(function ($image) {
                    if ($image && file_exists(storage_path('app/public/' . $image))) {
                        return asset('storage/' . $image);
                    } else {
                        return asset('images/placeholder-product.jpg');
                    }
                }, is_array($trade->sellerProduct->images) ? $trade->sellerProduct->images : json_decode($trade->sellerProduct->images, true) ?? []);
            }
            
            // Process offered items images
            if ($trade->offeredItems) {
                $trade->offeredItems->transform(function ($item) {
                    // Ensure images field is properly formatted
                    if ($item->images) {
                        $imageData = is_string($item->images) ? json_decode($item->images, true) : $item->images;
                        // Make sure it's an array
                        $imageData = is_array($imageData) ? $imageData : [$imageData];
                        
                        // Map each path to its proper URL
                        $item->images = array_map(function($imagePath) {
                            return asset('storage/' . $imagePath);
                        }, $imageData);
                        
                        // Add a current_images field to use for editing
                        $item->current_images = $item->images;
                    } else {
                        $item->images = [];
                        $item->current_images = [];
                    }
                    return $item;
                });
            }
            
            // Format messages
            if ($trade->messages) {
                $trade->messages->transform(function ($message) {
                    // Format user profile picture
                    if ($message->user && $message->user->profile_picture) {
                        $message->user->profile_picture = file_exists(storage_path('app/public/' . $message->user->profile_picture))
                            ? asset('storage/' . $message->user->profile_picture) 
                            : asset('images/placeholder-avatar.jpg');
                    }
                    
                    // Add user_id to the message for frontend identification
                    $message->user_id = $message->sender_id;
                    
                    // Format created_at for display
                    $message->formatted_time = $message->created_at ? Carbon::parse($message->created_at)->format('M j, Y g:i A') : null;
                    
                    return $message;
                });
            }
            
            // Format profile pictures for users
            if ($trade->buyer && $trade->buyer->profile_picture) {
                $trade->buyer->profile_picture = file_exists(storage_path('app/public/' . $trade->buyer->profile_picture))
                    ? asset('storage/' . $trade->buyer->profile_picture) 
                    : asset('images/placeholder-avatar.jpg');
            }
            
            if ($trade->seller && $trade->seller->profile_picture) {
                $trade->seller->profile_picture = file_exists(storage_path('app/public/' . $trade->seller->profile_picture))
                    ? asset('storage/' . $trade->seller->profile_picture)
                    : asset('images/placeholder-avatar.jpg');
            }
            
            // Add a properly formatted meetup date
            if ($trade->meetup_schedule) {
                $trade->formatted_meetup_date = Carbon::parse($trade->meetup_schedule)->format('F j, Y g:i A');
            }
            
            // Add meetup location name if available
            if ($trade->meetupLocation && $trade->meetupLocation->location) {
                $trade->meetup_location_name = $trade->meetupLocation->location->name;
            } else {
                $trade->meetup_location_name = 'Location not specified';
            }
            
            return $trade;
        });
        
        return $trades;
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
            
            // Get the messages with user information - now using the TradeMessage model
            $messages = TradeMessage::with('user:id,first_name,last_name,username,profile_picture')
                ->where('trade_transaction_id', $tradeTransaction->id)
                ->orderBy('created_at', 'asc')
                ->get();
            
            // Format the messages for the response
            $formattedMessages = $messages->map(function($message) {
                $profilePicture = $message->user && $message->user->profile_picture ? 
                    (file_exists(storage_path('app/public/' . $message->user->profile_picture)) ? 
                        asset('storage/' . $message->user->profile_picture) : 
                        asset('images/placeholder-avatar.jpg')) : 
                    asset('images/placeholder-avatar.jpg');

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
            TradeMessage::where('trade_transaction_id', $tradeTransaction->id)
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
            
            // Format the sellerProduct images
            $sellerProduct = null;
            if ($tradeTransaction->sellerProduct) {
                $images = [];
                if ($tradeTransaction->sellerProduct->images) {
                    // Ensure images is decoded from JSON if needed
                    $productImages = is_array($tradeTransaction->sellerProduct->images) 
                        ? $tradeTransaction->sellerProduct->images 
                        : json_decode($tradeTransaction->sellerProduct->images, true) ?? [];
                    
                    foreach ($productImages as $image) {
                        if ($image && file_exists(storage_path('app/public/' . $image))) {
                            $images[] = asset('storage/' . $image);
                        } else {
                            $images[] = asset('images/placeholder-product.jpg');
                        }
                    }
                }
                
                $sellerProduct = [
                    'id' => $tradeTransaction->sellerProduct->id,
                    'name' => $tradeTransaction->sellerProduct->name,
                    'price' => $tradeTransaction->sellerProduct->price,
                    'description' => $tradeTransaction->sellerProduct->description,
                    'images' => $images
                ];
            }
            
            // Format offered items with proper image handling
            $offeredItems = [];
            foreach ($tradeTransaction->offeredItems as $item) {
                $itemImages = [];
                
                // Process the item images - handle JSON decoding
                if ($item->images) {
                    try {
                        // If it's a JSON string, decode it
                        $imageData = is_string($item->images) ? json_decode($item->images, true) : $item->images;
                        
                        // If decoding failed, try to use as single path
                        if (json_last_error() !== JSON_ERROR_NONE && is_string($item->images)) {
                            $imageData = [$item->images];
                        }
                        
                        // Ensure it's an array
                        $imageData = is_array($imageData) ? $imageData : [$imageData];
                        
                        // Map each path to its proper URL
                        foreach ($imageData as $imagePath) {
                            if (empty($imagePath)) continue;
                            
                            // Check different path possibilities
                            if (file_exists(public_path('app/trade_items/' . $imagePath))) {
                                $itemImages[] = asset('app/trade_items/' . $imagePath);
                                continue;
                            }
                            
                            if (file_exists(storage_path('app/public/trade_items/' . $imagePath))) {
                                $itemImages[] = asset('storage/trade_items/' . $imagePath);
                                continue;
                            }
                            
                            if (file_exists(storage_path('app/public/' . $imagePath))) {
                                $itemImages[] = asset('storage/' . $imagePath);
                                continue;
                            }
                            
                            // Default fallback
                            $itemImages[] = asset('images/placeholder-product.jpg');
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to process trade offered item images: ' . $e->getMessage(), [
                            'item_id' => $item->id,
                            'images' => $item->images
                        ]);
                        $itemImages[] = asset('images/placeholder-product.jpg');
                    }
                }
                
                // Add the formatted item to the array
                $offeredItems[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'estimated_value' => $item->estimated_value,
                    'description' => $item->description,
                    'images' => $itemImages,
                    'condition' => $item->condition ?: 'used_good',
                ];
            }
            
            // Format user data
            $buyerData = $tradeTransaction->buyer ? [
                'id' => $tradeTransaction->buyer->id,
                'name' => $tradeTransaction->buyer->first_name . ' ' . $tradeTransaction->buyer->last_name,
                'first_name' => $tradeTransaction->buyer->first_name,
                'last_name' => $tradeTransaction->buyer->last_name,
                'profile_picture' => $tradeTransaction->buyer->profile_picture 
                    ? (file_exists(storage_path('app/public/' . $tradeTransaction->buyer->profile_picture)) 
                        ? asset('storage/' . $tradeTransaction->buyer->profile_picture) 
                        : asset('images/placeholder-avatar.jpg'))
                    : asset('images/placeholder-avatar.jpg')
            ] : null;
            
            $sellerData = $tradeTransaction->seller ? [
                'id' => $tradeTransaction->seller->id,
                'name' => $tradeTransaction->seller->first_name . ' ' . $tradeTransaction->seller->last_name,
                'first_name' => $tradeTransaction->seller->first_name,
                'last_name' => $tradeTransaction->seller->last_name,
                'seller_code' => $tradeTransaction->seller->seller_code,
                'profile_picture' => $tradeTransaction->seller->profile_picture
                    ? (file_exists(storage_path('app/public/' . $tradeTransaction->seller->profile_picture))
                        ? asset('storage/' . $tradeTransaction->seller->profile_picture)
                        : asset('images/placeholder-avatar.jpg'))
                    : asset('images/placeholder-avatar.jpg')
            ] : null;
            
            // Format the complete trade data
            $formattedTrade = [
                'id' => $tradeTransaction->id,
                'buyer_id' => $tradeTransaction->buyer_id,
                'seller_id' => $tradeTransaction->seller_id,
                'seller_code' => $tradeTransaction->seller ? $tradeTransaction->seller->seller_code : null,
                'seller_product_id' => $tradeTransaction->seller_product_id,
                'meetup_location_id' => $tradeTransaction->meetup_location_id,
                'meetup_location_name' => $tradeTransaction->meetupLocation && $tradeTransaction->meetupLocation->location 
                    ? $tradeTransaction->meetupLocation->location->name 
                    : 'Location not specified',
                'additional_cash' => $tradeTransaction->additional_cash,
                'notes' => $tradeTransaction->notes,
                'status' => $tradeTransaction->status,
                'created_at' => $tradeTransaction->created_at,
                'updated_at' => $tradeTransaction->updated_at,
                'sellerProduct' => $sellerProduct,
                'offered_items' => $offeredItems,
                'buyer' => $buyerData,
                'seller' => $sellerData,
                'meetup_schedule' => $tradeTransaction->meetup_schedule,
                'formatted_meetup_date' => $tradeTransaction->meetup_schedule 
                    ? Carbon::parse($tradeTransaction->meetup_schedule)->format('F j, Y g:i A') 
                    : null,
            ];
            
            // Return the formatted trade data as JSON response
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

    // Add this method for handling trade updates
    public function updateTrade(Request $request, $id)
    {
        try {
            // Find the trade
            $trade = TradeTransaction::with(['offeredItems'])->findOrFail($id);
            
            // Check authorization - only allow the buyer to update the trade
            if (Auth::id() !== $trade->buyer_id) {
                return redirect()->back()->with('error', 'You are not authorized to update this trade');
            }
            
            // Verify that the trade is in a state that allows updates (pending only)
            if ($trade->status !== 'pending') {
                return redirect()->back()->with('error', 'Only pending trades can be updated');
            }
            
            // Extract and verify inputs
            $meetupSchedule = $request->input('meetup_schedule');
            $selectedDay = $request->input('selected_day');
            $meetupLocationId = (int)$request->input('meetup_location_id');
            $preferredTime = $request->input('preferred_time');
            $additionalCash = $request->input('additional_cash', 0);
            $notes = $request->input('notes');
            
            // Validate date format - expecting "YYYY-MM-DD, HH:MM"
            $parts = explode(',', $meetupSchedule);
            if (count($parts) !== 2) {
                return redirect()->back()->with('error', 'Invalid meetup schedule format');
            }
            
            // Store the updated trade details
            $trade->meetup_location_id = $meetupLocationId;
            $trade->meetup_schedule = trim($parts[0]) . ' ' . trim($parts[1]) . ':00';
            $trade->additional_cash = $additionalCash;
            $trade->notes = $notes;
            $trade->save();
            
            // Get the offered items
            $offeredItems = $request->input('offered_items', []);
            
            // Track existing item IDs
            $existingItemIds = [];
            $updatedItemIds = [];
            
            // Process each offered item
            foreach ($offeredItems as $itemData) {
                $itemId = isset($itemData['id']) ? $itemData['id'] : null;
                
                if ($itemId) {
                    // Update existing item
                    $item = TradeOfferedItem::where('id', $itemId)
                        ->where('trade_transaction_id', $trade->id)
                        ->first();
                        
                    if (!$item) {
                        continue; // Skip if item doesn't exist or doesn't belong to this trade
                    }
                    
                    $updatedItemIds[] = $item->id;
                    
                    // Update item details
                    $item->name = $itemData['name'];
                    $item->quantity = $itemData['quantity'];
                    $item->estimated_value = $itemData['estimated_value'];
                    $item->description = $itemData['description'] ?? '';
                    $item->condition = $itemData['condition'] ?? 'used_good';
                    
                    // Handle existing images
                    if (isset($itemData['current_images'])) {
                        $currentImages = is_string($itemData['current_images']) 
                            ? json_decode($itemData['current_images'], true) 
                            : $itemData['current_images'];
                            
                        // If current_images is provided but empty, clear all images
                        if (is_array($currentImages) && empty($currentImages)) {
                            $item->images = json_encode([]);
                        } else if (is_array($currentImages)) {
                            $item->images = json_encode($currentImages);
                        }
                    }
                    
                    // Process new images
                    if (isset($itemData['image_files']) && is_array($itemData['image_files'])) {
                        $imagesJson = isset($itemData['images_json']) ? json_decode($itemData['images_json'], true) : [];
                        $newImages = [];
                        
                        foreach ($itemData['image_files'] as $fileIndex => $file) {
                            // Check if we have metadata for this file
                            $fileInfo = !empty($imagesJson[$fileIndex]) ? $imagesJson[$fileIndex] : null;
                            
                            // If file is a cropped image with a storage path, use that path
                            if ($fileInfo && isset($fileInfo['is_cropped']) && $fileInfo['is_cropped'] && 
                                isset($fileInfo['storage_path']) && $fileInfo['storage_path']) {
                                
                                $path = $fileInfo['storage_path'];
                                Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));
                                $newImages[] = $path;
                            } else {
                                // Regular file upload
                                $path = $file->store('trade_items', 'public');
                                $newImages[] = $path;
                            }
                        }
                        
                        // If we have both current and new images, merge them
                        if (isset($itemData['current_images']) && !empty($itemData['current_images'])) {
                            $currentImages = is_string($itemData['current_images']) 
                                ? json_decode($itemData['current_images'], true) 
                                : $itemData['current_images'];
                                
                            if (is_array($currentImages)) {
                                $allImages = array_merge($currentImages, $newImages);
                                $item->images = json_encode($allImages);
                            } else {
                                $item->images = json_encode($newImages);
                            }
                        } else {
                            // Just new images
                            $item->images = json_encode($newImages);
                        }
                    }
                    
                    $item->save();
                } else {
                    // Create new offered item
                    $newItem = new TradeOfferedItem();
                    $newItem->trade_transaction_id = $trade->id;
                    $newItem->name = $itemData['name'];
                    $newItem->quantity = $itemData['quantity'];
                    $newItem->estimated_value = $itemData['estimated_value'];
                    $newItem->description = $itemData['description'] ?? '';
                    $newItem->condition = $itemData['condition'] ?? 'used_good';
                    
                    // Process images for new items
                    if (isset($itemData['image_files']) && is_array($itemData['image_files'])) {
                        $imagesJson = isset($itemData['images_json']) ? json_decode($itemData['images_json'], true) : [];
                        $newImages = [];
                        
                        foreach ($itemData['image_files'] as $fileIndex => $file) {
                            // Check if we have metadata for this file
                            $fileInfo = !empty($imagesJson[$fileIndex]) ? $imagesJson[$fileIndex] : null;
                            
                            // If file is a cropped image with a storage path, use that path
                            if ($fileInfo && isset($fileInfo['is_cropped']) && $fileInfo['is_cropped'] && 
                                isset($fileInfo['storage_path']) && $fileInfo['storage_path']) {
                                
                                $path = $fileInfo['storage_path'];
                                Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));
                                $newImages[] = $path;
                            } else {
                                // Regular file upload
                                $path = $file->store('trade_items', 'public');
                                $newImages[] = $path;
                            }
                        }
                        
                        $newItem->images = json_encode($newImages);
                    }
                    
                    $newItem->save();
                    $updatedItemIds[] = $newItem->id;
                }
            }
            
            // Get all existing item IDs for this trade
            $existingItemIds = $trade->offeredItems->pluck('id')->toArray();
            
            // Delete items that weren't included in the update
            $itemsToDelete = array_diff($existingItemIds, $updatedItemIds);
            if (!empty($itemsToDelete)) {
                TradeOfferedItem::whereIn('id', $itemsToDelete)->delete();
            }
            
            return redirect()->back()->with('success', 'Trade updated successfully');
        } catch (\Exception $e) {
            \Log::error('Error updating trade: ' . $e->getMessage(), [
                'trade_id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Failed to update trade: ' . $e->getMessage());
        }
    }

    // Helper method to process uploaded images
    private function processItemImages($files, $tradeId)
    {
        $images = [];
        
        foreach ($files as $file) {
            // Store the file
            $path = $file->store('public/trade_items');
            // Convert the path for database storage
            $dbPath = str_replace('public/', '', $path);
            // Add to images array
            $images[] = $dbPath;
        }
        
        return $images;
    }
}
