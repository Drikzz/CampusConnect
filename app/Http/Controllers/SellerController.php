<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use App\Models\Order;
use App\Models\TradeTransaction;  // Add this import if it doesn't exist
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use App\Models\Wishlist;
use App\Models\OrderItem;
use App\Models\SellerWallet;
use App\Models\Tag;
use Inertia\Inertia;

class SellerController extends Controller
{
    // Keep index for dashboard
    public function index()
    {
        $sellerCode = Auth::user()->seller_code;

        $categories = Category::latest()->get();

        // Update order counts to use seller_code
        $orderCounts = (object)[
            'pendingCount' => Order::where('seller_code', $sellerCode)->pending()->count(),
            'processingCount' => Order::where('seller_code', $sellerCode)->processing()->count(),
            'completedCount' => Order::where('seller_code', $sellerCode)->completed()->count(),
        ];
        View::share('orderCounts', $orderCounts);

        // Get total orders
        $totalOrders = Order::where('seller_code', $sellerCode)->count();

        // Calculate total sales
        $totalSales = Order::where('seller_code', $sellerCode)
            ->where('status', 'Completed')
            ->sum('sub_total');

        // Get active trades (orders with payment_method as 'trade')
        $activeTrades = Order::where('seller_code', $sellerCode)
            ->where('payment_method', 'trade')
            ->where('status', '!=', 'Completed')
            ->count();

        // Get recent orders
        $recentOrders = Order::where('seller_code', $sellerCode)
            ->with('buyer')
            ->latest()
            ->take(5)
            ->get();

        // dd($recentOrders);
        return view('seller.index', compact('categories', 'totalOrders', 'totalSales', 'activeTrades', 'recentOrders', 'orderCounts'));
    }

    // Modify products listing to include option to show deleted
    public function products()
    {
        $user = auth()->user();
        $sellerCode = $user->seller_code;
        $stats = $this->getDashboardStats($user);

        // Update the query to include tags and category
        $products = Product::where('seller_code', $sellerCode)
            ->with(['category', 'tags'])  // Ensure tags and category are being loaded
            ->withTrashed()
            ->latest()
            ->paginate(10);

        // Format the products data for the data-table
        $productsData = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'category' => [
                    'id' => $product->category->id,
                    'name' => $product->category->name
                ],
                'price' => number_format($product->price, 2),
                'discounted_price' => $product->discounted_price
                    ? number_format($product->discounted_price, 2)
                    : null,
                'stock' => $product->stock,
                'status' => $product->status,
                'is_buyable' => $product->is_buyable,
                'is_tradable' => $product->is_tradable,
                'images' => $product->images,
                'tags' => $product->tags,
                'deleted_at' => $product->deleted_at,
                'category_id' => $product->category_id
            ];
        });

        return Inertia::render('Dashboard/seller/Products', [
            'user' => $user,
            'stats' => $stats,
            'products' => [
                'data' => $productsData,
                'meta' => [
                    'total' => $products->total(),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage()
                ]
            ],
            'categories' => Category::all(),
            'availableTags' => Tag::all()
        ]);
    }

    // Add method to fetch single product for editing
    public function edit($id)
    {
        $product = Product::withTrashed()->findOrFail($id);

        if ($product->seller_code !== Auth::user()->seller_code) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Load relationships and format data
        $product->load(['category', 'tags']);

        // Format the product data to match the form structure
        $formattedProduct = [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'category' => $product->category_id,
            'price' => $product->price,
            'discount' => $product->discount * 100, // Convert decimal to percentage
            'stock' => $product->stock,
            'trade_availability' => $this->determineTradeAvailability($product),
            'status' => $product->status,
            'images' => $product->images,
            'tags' => $product->tags->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name
                ];
            }),
        ];

        return response()->json($formattedProduct);
    }

    public function update(Request $request, $id)
    {
        $product = Product::withTrashed()->findOrFail($id);

        if ($product->seller_code !== Auth::user()->seller_code) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'category' => 'required|exists:categories,id',
                'price' => 'required|numeric|min:0',
                'discount' => 'nullable|numeric|min:0|max:100',
                'stock' => 'required|integer|min:0',
                'trade_availability' => 'required|in:buy,trade,both',
                'status' => 'required|in:Active,Inactive',
                'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'additional_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'removed_images' => 'nullable|string',
                'tags' => 'nullable|string'
            ]);

            DB::beginTransaction();

            // Handle images
            $currentImages = $product->images ?? [];
            $imagePaths = [];

            // Handle removed images
            $removedImages = json_decode($request->input('removed_images', '[]'), true);
            if (!empty($removedImages)) {
                foreach ($removedImages as $path) {
                    Storage::disk('public')->delete($path);
                    $currentImages = array_diff($currentImages, [$path]);
                }
            }

            // Handle main image
            if ($request->hasFile('main_image')) {
                // Delete old main image if exists
                if (!empty($currentImages[0])) {
                    Storage::disk('public')->delete($currentImages[0]);
                }
                $imagePaths[] = $request->file('main_image')->store('products', 'public');
            } elseif (!empty($currentImages[0]) && !in_array($currentImages[0], $removedImages)) {
                $imagePaths[] = $currentImages[0];
            }

            // Handle additional images
            if ($request->hasFile('additional_images')) {
                foreach ($request->file('additional_images') as $image) {
                    $imagePaths[] = $image->store('products', 'public');
                }
            }

            // Add remaining current images
            foreach ($currentImages as $image) {
                if (!in_array($image, $imagePaths) && !in_array($image, $removedImages)) {
                    $imagePaths[] = $image;
                }
            }

            // Calculate prices
            $discount = $validated['discount'] ? (float)($validated['discount'] / 100) : 0.0;
            $price = (float)$validated['price'];
            $discountedPrice = $price * (1 - $discount);

            // Update product
            $product->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'category_id' => $validated['category'],
                'price' => $price,
                'discount' => $discount,
                'discounted_price' => round($discountedPrice, 2),
                'stock' => $validated['stock'],
                'images' => array_values($imagePaths),
                'status' => $validated['status'],
                'is_buyable' => in_array($validated['trade_availability'], ['buy', 'both']),
                'is_tradable' => in_array($validated['trade_availability'], ['trade', 'both'])
            ]);

            // Handle status changes
            if ($validated['status'] === 'Inactive' && !$product->trashed()) {
                $product->delete();
            } elseif ($validated['status'] === 'Active' && $product->trashed()) {
                $product->restore();
            }

            // Handle tags - Update this section
            if ($request->has('tags')) {
                $tagIds = json_decode($request->input('tags'), true);
                if (is_array($tagIds)) {
                    $product->tags()->sync($tagIds);
                    Log::info('Syncing tags for product update:', [
                        'product_id' => $product->id,
                        'tag_ids' => $tagIds
                    ]);
                }
            } else {
                // If no tags provided, remove all tags
                $product->tags()->sync([]);
            }

            DB::commit();

            // Fetch fresh data for the response
            $updatedProducts = Product::where('seller_code', Auth::user()->seller_code)
                ->with(['category', 'tags'])
                ->withTrashed()
                ->latest()
                ->paginate(10);

            // Format the products data
            $productsData = $updatedProducts->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => [
                        'id' => $product->category->id,
                        'name' => $product->category->name
                    ],
                    'price' => number_format($product->price, 2),
                    'discounted_price' => $product->discounted_price
                        ? number_format($product->discounted_price, 2)
                        : null,
                    'stock' => $product->stock,
                    'status' => $product->status,
                    'is_buyable' => $product->is_buyable,
                    'is_tradable' => $product->is_tradable,
                    'images' => $product->images,
                    'tags' => $product->tags,
                    'deleted_at' => $product->deleted_at,
                    'category_id' => $product->category_id
                ];
            });

            // Return Inertia response with updated data
            return Inertia::render('Dashboard/seller/Products', [
                'user' => Auth::user(),
                'stats' => $this->getDashboardStats(Auth::user()),
                'products' => [
                    'data' => $productsData,
                    'meta' => [
                        'total' => $updatedProducts->total(),
                        'per_page' => $updatedProducts->perPage(),
                        'current_page' => $updatedProducts->currentPage(),
                        'last_page' => $updatedProducts->lastPage()
                    ]
                ],
                'categories' => Category::all(),
                'availableTags' => Tag::all(),
                'flash' => [
                    'message' => 'Product updated successfully',
                    'type' => 'success'
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product update error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error updating product: ' . $e->getMessage()
            ], 500);
        }
    }

    // Modify store method
    public function store(Request $request)
    {
        $sellerCode = Auth::user()->seller_code;
        if (!$sellerCode) {
            return response()->json([
                'success' => false,
                'message' => 'Seller code not found. Please update your profile.'
            ], 400);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|exists:categories,id',
            'trade_availability' => 'required|in:buy,trade,both',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|integer|min:0|max:100',
            'stock' => 'required|integer|min:0',
            'main_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            DB::beginTransaction();

            // Handle images
            $imagePaths = $this->handleProductImages($request);

            // Set trade options
            $tradeOptions = $this->getTradeOptions($validated['trade_availability']);

            // Calculate discount as decimal (e.g., 15% becomes 0.15)
            $discount = $validated['discount'] ? (float)($validated['discount'] / 100) : 0.0;

            // Calculate discounted price using float values
            $price = (float)$validated['price'];
            $discountedPrice = $price * (1 - $discount);

            // Log received tags data for debugging
            Log::info('Received tags data:', ['tags' => $request->input('tags')]);

            // Create product with Active status by default
            $product = Product::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'price' => $price,
                'discount' => $discount, // Store as decimal (0.15 for 15%)
                'discounted_price' => round($discountedPrice, 2), // Round to 2 decimal places
                'stock' => $validated['stock'],
                'images' => $imagePaths,
                'seller_code' => $sellerCode,
                'category_id' => $validated['category'],
                'is_buyable' => $tradeOptions['is_buyable'],
                'is_tradable' => $tradeOptions['is_tradable'],
                'status' => 'Active' // Default status
            ]);

            // Handle tags - decode JSON string to array of IDs
            $tagIds = json_decode($request->input('tags'), true);
            if (!empty($tagIds)) {
                // Ensure we're working with an array of integers
                $tagIds = array_map('intval', $tagIds);
                $product->tags()->sync($tagIds);

                // Log tag sync operation for debugging
                Log::info('Syncing tags for product:', [
                    'product_id' => $product->id,
                    'tag_ids' => $tagIds
                ]);
            }

            // dd($tagIds);
            DB::commit();

            // Check if request wants JSON
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product added successfully',
                    'product' => $product
                ]);
            }

            // Redirect for regular form submit
            return redirect()->route('dashboard.seller.products')
                ->with('success', 'Product added successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product creation error:', [
                'message' => $e->getMessage(),
                'tags_input' => $request->input('tags'),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating product: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Error creating product: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->seller_code !== Auth::user()->seller_code) {
            return redirect()->route('seller.products')
                ->with('error', 'Unauthorized action');
        }

        try {
            DB::beginTransaction();

            $product->update([
                'old_attributes' => [
                    'status' => $product->status,
                    'is_buyable' => $product->is_buyable,
                    'is_tradable' => $product->is_tradable
                ],
                'status' => 'Inactive',
                'is_buyable' => false,
                'is_tradable' => false
            ]);

            $product->delete();

            DB::commit();

            return redirect()->route('seller.products')
                ->with('success', 'Product archived successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product deletion error: ' . $e->getMessage());

            return redirect()->route('seller.products')
                ->with('error', 'Error archiving product: ' . $e->getMessage());
        }
    }

    public function forceDelete($id)
    {
        $product = Product::withTrashed()->findOrFail($id);

        if ($product->seller_code !== Auth::user()->seller_code) {
            return redirect()->route('seller.products')
                ->with('error', 'Unauthorized action');
        }

        try {
            DB::beginTransaction();

            // Delete images
            if ($product->images) {
                foreach ($product->images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            // Delete tags association
            $product->tags()->detach();

            $product->forceDelete();

            DB::commit();

            return redirect()->route('seller.products')
                ->with('success', 'Product permanently deleted');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('seller.products')
                ->with('error', 'Error deleting product: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        $product = Product::withTrashed()->findOrFail($id);

        if ($product->seller_code !== Auth::user()->seller_code) {
            return redirect()->route('seller.products')
                ->with('error', 'Unauthorized action');
        }

        try {
            DB::beginTransaction();

            $product->restore();

            $oldAttributes = $product->old_attributes ?? [
                'status' => 'Active',
                'is_buyable' => false,
                'is_tradable' => false
            ];

            $product->update([
                'status' => $oldAttributes['status'],
                'is_buyable' => $oldAttributes['is_buyable'],
                'is_tradable' => $oldAttributes['is_tradable'],
                'old_attributes' => null
            ]);

            DB::commit();

            return redirect()->route('seller.products')
                ->with('success', 'Product restored successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product restore error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('seller.products')
                ->with('error', 'Error restoring product: ' . $e->getMessage());
        }
    }

    // Add orders method
    public function orders()
    {
        $user = auth()->user();
        $sellerCode = $user->seller_code;
        $stats = $this->getDashboardStats($user);

        $orders = Order::where('seller_code', $sellerCode)
            ->with(['items.product', 'buyer'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Dashboard/seller/Orders', [
            'user' => $user,
            'stats' => $stats,
            'orders' => [
                'data' => $orders->items(),
                'meta' => [
                    'total' => $orders->total(),
                    'per_page' => $orders->perPage(),
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage()
                ]
            ],
            'orderCounts' => [
                'pendingOrders' => Order::where('seller_code', $sellerCode)->pending()->count(),
                'activeOrders' => Order::where('seller_code', $sellerCode)
                    ->whereIn('status', ['Accepted', 'Processing', 'Meetup Scheduled'])->count(),
                'totalOrders' => Order::where('seller_code', $sellerCode)->count(),
                'totalSales' => Order::where('seller_code', $sellerCode)
                    ->where('status', 'Completed')
                    ->sum('sub_total')
            ]
        ]);
    }

    public function showOrder(Order $order)
    {
        if ($order->seller_code !== auth()->user()->seller_code) {
            abort(403);
        }

        $user = auth()->user();
        $stats = $this->getDashboardStats($user);

        return Inertia::render('Dashboard/seller/OrderDetails', [
            'user' => $user,
            'stats' => $stats,
            'order' => $order->load(['items.product', 'buyer']),
            'meetupLocations' => auth()->user()->meetupLocations()
                ->with('location')
                ->get()
        ]);
    }

    public function updateOrderStatus(Request $request, Order $order)
    {
        if ($order->seller_code !== Auth::user()->seller_code) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:Accepted,Delivered,Completed,Cancelled'
        ]);

        try {
            $order->update(['status' => $validated['status']]);
            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating order status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function completeOrder(Order $order)
    {
        if ($order->seller_code !== Auth::user()->seller_code) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $order->update(['status' => 'Completed']);
            return response()->json([
                'success' => true,
                'message' => 'Order marked as completed'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error completing order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function scheduleMeetup(Request $request, Order $order)
    {
        if ($order->seller_code !== Auth::user()->seller_code) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'meetup_location' => 'required|string',
            'meetup_schedule' => 'required|date|after:now'
        ]);

        try {
            $order->update([
                'meetup_location' => $validated['meetup_location'],
                'meetup_schedule' => $validated['meetup_schedule'],
                'status' => 'Meetup Scheduled'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Meetup scheduled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error scheduling meetup: ' . $e->getMessage()
            ], 500);
        }
    }

    public function analytics()
    {
        $user = auth()->user();
        $sellerCode = $user->seller_code;

        // Get seller statistics
        $totalOrders = Order::where('buyer_id', $user->id)->count();
        $activeOrders = Order::where('buyer_id', $user->id)
            ->whereNotIn('status', ['Completed', 'Cancelled'])
            ->count();
        $wishlistCount = Wishlist::where('user_id', $user->id)->count();
        $totalSales = OrderItem::where('seller_code', $sellerCode)
            ->whereHas('order', function ($query) {
                $query->where('status', 'Completed');
            })
            ->sum('subtotal');

        $activeProducts = Product::where('seller_code', $sellerCode)
            ->where('status', 'Active')
            ->count();

        $pendingOrders = OrderItem::where('seller_code', $sellerCode)
            ->whereHas('order', function ($query) {
                $query->where('status', 'Pending');
            })->count();

        // Get sales data for the last 30 days
        $thirtyDaysAgo = now()->subDays(30);
        $salesData = Order::where('seller_code', $sellerCode)
            ->where('status', 'Completed')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->get();

        $averageOrderValue = $salesData->count() > 0 ? $totalSales / $salesData->count() : 0;

        return view('dashboard.seller.analytics', compact(
            'totalOrders',
            'activeOrders',
            'wishlistCount',
            'totalSales',
            'activeProducts',
            'pendingOrders',
            'averageOrderValue',
            'salesData'
        ));
    }

    // Helper methods
    private function getOrderCounts($sellerCode)
    {
        return (object)[
            'pendingCount' => Order::where('seller_code', $sellerCode)->where('status', 'Pending')->count(),
            'processingCount' => Order::where('seller_code', $sellerCode)->where('status', 'Processing')->count(),
            'completedCount' => Order::where('seller_code', $sellerCode)->where('status', 'Completed')->count(),
        ];
    }

    private function handleProductImages(Request $request, Product $product = null)
    {
        $imagePaths = [];

        // Handle main image
        if ($request->hasFile('main_image')) {
            // Delete old main image if exists
            if ($product && isset($product->images[0])) {
                Storage::disk('public')->delete($product->images[0]);
            }
            $imagePaths[] = $request->file('main_image')->store('products', 'public');
        }

        // Handle additional images
        if ($request->hasFile('additional_images')) {
            foreach ($request->file('additional_images') as $index => $image) {
                // Delete old additional image if exists
                if ($product && isset($product->images[$index + 1])) {
                    Storage::disk('public')->delete($product->images[$index + 1]);
                }
                $imagePaths[] = $image->store('products', 'public');
            }
        }

        return $imagePaths ?: ($product ? $product->images : []);
    }

    private function getTradeOptions($tradeAvailability)
    {
        return [
            'is_buyable' => in_array($tradeAvailability, ['buy', 'both']),
            'is_tradable' => in_array($tradeAvailability, ['trade', 'both'])
        ];
    }

    public function meetupLocations()
    {
        $user = auth()->user();
        $stats = $this->getDashboardStats($user);

        return Inertia::render('Dashboard/seller/MeetupLocations', [
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'middle_name' => $user->middle_name,
                'last_name' => $user->last_name,
                'phone' => $user->phone,
                'is_seller' => $user->is_seller,
                'seller_code' => $user->seller_code,
            ],
            'stats' => $stats,
            'meetupLocations' => $user->meetupLocations()
                ->with('location')
                ->orderByDesc('is_default')
                ->get(),
            'locations' => Location::select('id', 'name', 'latitude', 'longitude')
                ->orderBy('name')
                ->get()
        ]);
    }

    private function getDashboardStats($user)
    {
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
            // Existing seller stats
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

            // Add wallet stats
            $wallet = SellerWallet::where('seller_code', $user->seller_code)
                ->with(['transactions' => function ($query) {
                    $query->latest()->take(5);
                }])
                ->first();

            if ($wallet) {
                $stats['wallet'] = [
                    'id' => $wallet->id,
                    'balance' => $wallet->balance,
                    'is_activated' => $wallet->is_activated,
                    'status' => $wallet->status,
                    'transactions' => $wallet->transactions,
                    'total_transactions' => $wallet->transactions()->count(),
                    'total_credits' => $wallet->transactions()
                        ->where('reference_type', 'refill')  // Changed from type = credit
                        ->where('status', 'completed')
                        ->sum('amount'),
                    'total_debits' => $wallet->transactions()
                        ->whereIn('reference_type', ['withdraw', 'deduction'])  // Changed from type = debit
                        ->where('status', 'completed')
                        ->sum('amount'),
                    'pending_transactions' => $wallet->transactions()
                        ->where('status', 'pending')
                        ->count()
                ];
            } else {
                $stats['wallet'] = [
                    'balance' => 0,
                    'is_activated' => false,
                    'status' => 'pending',
                    'transactions' => [],
                    'total_transactions' => 0,
                    'total_credits' => 0,
                    'total_debits' => 0,
                    'pending_transactions' => 0
                ];
            }
        }

        return $stats;
    }

    public function storeMeetupLocation(Request $request)
    {
        try {
            $validated = $request->validate([
                'location_id' => 'required|exists:locations,id',
                'full_name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'description' => 'nullable|string',
                'available_from' => 'required|date_format:H:i',
                'available_until' => 'required|date_format:H:i|after:available_from',
                'available_days' => 'required|array|min:1',
                'available_days.*' => 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                'max_daily_meetups' => 'required|integer|min:1|max:50',
                'is_default' => 'boolean'
            ]);

            DB::beginTransaction();

            $meetupLocation = auth()->user()->meetupLocations()->create([
                'location_id' => $validated['location_id'],
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'],
                'description' => $validated['description'] ?? '',
                'available_from' => $validated['available_from'],
                'available_until' => $validated['available_until'],
                'available_days' => $validated['available_days'],
                'max_daily_meetups' => $validated['max_daily_meetups'],
                'latitude' => Location::find($validated['location_id'])->latitude,
                'longitude' => Location::find($validated['location_id'])->longitude,
                'is_active' => true,
                'is_default' => $validated['is_default'] ?? false,
            ]);

            if (auth()->user()->meetupLocations()->count() === 1 || ($validated['is_default'] ?? false)) {
                auth()->user()->meetupLocations()
                    ->where('id', '!=', $meetupLocation->id)
                    ->update(['is_default' => false]);
            }

            DB::commit();
            return redirect()->back()->with(['message' => 'Meetup location added successfully', 'type' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create meetup location: ' . $e->getMessage()]);
        }
    }

    public function updateMeetupLocation(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'location_id' => 'required|exists:locations,id',
                'full_name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'description' => 'nullable|string',
                'available_from' => 'required|date_format:H:i',
                'available_until' => 'required|date_format:H:i|after:available_from',
                'available_days' => 'required|array|min:1',
                'available_days.*' => 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                'max_daily_meetups' => 'required|integer|min:1|max:50',
                'is_default' => 'boolean'
            ]);

            DB::beginTransaction();

            $meetupLocation = auth()->user()->meetupLocations()->findOrFail($id);
            $location = Location::findOrFail($validated['location_id']);

            $meetupLocation->update([
                'location_id' => $validated['location_id'],
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'],
                'description' => $validated['description'] ?? '',
                'available_from' => $validated['available_from'],
                'available_until' => $validated['available_until'],
                'available_days' => $validated['available_days'],
                'max_daily_meetups' => $validated['max_daily_meetups'],
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'is_active' => true,
                'is_default' => $validated['is_default'] ?? false,
            ]);

            if ($validated['is_default'] ?? false) {
                auth()->user()->meetupLocations()
                    ->where('id', '!=', $id)
                    ->update(['is_default' => false]);
            }

            DB::commit();
            return redirect()->back()->with(['message' => 'Meetup location updated successfully', 'type' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update meetup location: ' . $e->getMessage()]);
        }
    }

    public function deleteMeetupLocation($id)
    {
        try {
            $user = auth()->user();
            $location = $user->meetupLocations()->findOrFail($id);

            DB::beginTransaction();

            $wasDefault = $location->is_default;
            $location->delete();

            if ($wasDefault) {
                $newDefault = $user->meetupLocations()->first();
                if ($newDefault) {
                    $newDefault->update(['is_default' => true]);
                }
            }

            DB::commit();

            // Return Inertia redirect with success message
            return redirect()->route('seller.meetup-locations.index')->with([
                'message' => 'Meetup location deleted successfully',
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting meetup location: ' . $e->getMessage());

            // Redirect back with error message
            return back()->withErrors([
                'error' => 'Failed to delete meetup location: ' . $e->getMessage()
            ]);
        }
    }

    // Add this helper method for edit function
    private function determineTradeAvailability($product)
    {
        if ($product->is_buyable && $product->is_tradable) {
            return 'both';
        } elseif ($product->is_buyable) {
            return 'buy';
        } elseif ($product->is_tradable) {
            return 'trade';
        }
        return 'buy'; // default value
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id'
        ]);

        try {
            DB::beginTransaction();

            $products = Product::whereIn('id', $validated['ids'])
                ->where('seller_code', Auth::user()->seller_code)
                ->get();

            if ($products->isEmpty()) {
                throw new \Exception('No products found to archive');
            }

            foreach ($products as $product) {
                $product->update([
                    'old_attributes' => [
                        'status' => $product->status,
                        'is_buyable' => $product->is_buyable,
                        'is_tradable' => $product->is_tradable
                    ],
                    'status' => 'Inactive',
                    'is_buyable' => false,
                    'is_tradable' => false
                ]);
                $product->delete();
            }

            DB::commit();

            return $this->returnFormattedResponse('Products archived successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnFormattedResponse($e->getMessage(), false);
        }
    }

    public function bulkRestore(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id'
        ]);

        try {
            DB::beginTransaction();

            $products = Product::withTrashed()
                ->whereIn('id', $validated['ids'])
                ->where('seller_code', Auth::user()->seller_code)
                ->get();

            if ($products->isEmpty()) {
                throw new \Exception('No products found to restore');
            }

            foreach ($products as $product) {
                $oldAttributes = $product->old_attributes ?? [
                    'status' => 'Active',
                    'is_buyable' => false,
                    'is_tradable' => false
                ];

                $product->restore();
                $product->update([
                    'status' => $oldAttributes['status'],
                    'is_buyable' => $oldAttributes['is_buyable'],
                    'is_tradable' => $oldAttributes['is_tradable'],
                    'old_attributes' => null
                ]);
            }

            DB::commit();

            return $this->returnFormattedResponse('Products restored successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnFormattedResponse($e->getMessage(), false);
        }
    }

    public function bulkForceDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id'
        ]);

        try {
            DB::beginTransaction();

            $products = Product::withTrashed()
                ->whereIn('id', $validated['ids'])
                ->where('seller_code', Auth::user()->seller_code)
                ->get();

            if ($products->isEmpty()) {
                throw new \Exception('No products found to delete');
            }

            foreach ($products as $product) {
                if ($product->images) {
                    foreach ($product->images as $image) {
                        Storage::disk('public')->delete($image);
                    }
                }
                $product->tags()->detach();
                $product->forceDelete();
            }

            DB::commit();

            return $this->returnFormattedResponse('Products permanently deleted');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnFormattedResponse($e->getMessage(), false);
        }
    }

    // Add helper method to get formatted products
    private function getFormattedProducts()
    {
        $products = Product::where('seller_code', Auth::user()->seller_code)
            ->with(['category', 'tags'])
            ->withTrashed()
            ->latest()
            ->paginate(10);

        return [
            'data' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => [
                        'id' => $product->category->id,
                        'name' => $product->category->name
                    ],
                    'price' => number_format($product->price, 2),
                    'discounted_price' => $product->discounted_price
                        ? number_format($product->discounted_price, 2)
                        : null,
                    'stock' => $product->stock,
                    'status' => $product->status,
                    'is_buyable' => $product->is_buyable,
                    'is_tradable' => $product->is_tradable,
                    'images' => $product->images,
                    'tags' => $product->tags,
                    'deleted_at' => $product->deleted_at,
                    'category_id' => $product->category_id
                ];
            }),
            'meta' => [
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage()
            ]
        ];
    }

    // Add this new helper method
    private function returnFormattedResponse($message, $success = true)
    {
        $user = Auth::user();
        $products = Product::where('seller_code', $user->seller_code)
            ->with(['category', 'tags'])
            ->withTrashed()
            ->latest()
            ->paginate(10);

        return Inertia::render('Dashboard/seller/Products', [
            'user' => $user,
            'stats' => $this->getDashboardStats($user),
            'products' => [
                'data' => $this->formatProductsData($products),
                'meta' => [
                    'total' => $products->total(),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage()
                ]
            ],
            'categories' => Category::all(),
            'availableTags' => Tag::all(),
            'flash' => [
                'message' => $message,
                'type' => $success ? 'success' : 'error'
            ]
        ]);
    }

    private function formatProductsData($products)
    {
        return $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'category' => [
                    'id' => $product->category->id,
                    'name' => $product->category->name
                ],
                'price' => number_format($product->price, 2),
                'discounted_price' => $product->discounted_price
                    ? number_format($product->discounted_price, 2)
                    : null,
                'stock' => $product->stock,
                'status' => $product->status,
                'is_buyable' => $product->is_buyable,
                'is_tradable' => $product->is_tradable,
                'images' => $product->images,
                'tags' => $product->tags,
                'deleted_at' => $product->deleted_at,
                'category_id' => $product->category_id,
                'description' => $product->description
            ];
        });
    }

    public function tradeOffers()
    {
        $user = auth()->user();
        $stats = $this->getDashboardStats($user);
        
        // Get trade offers for this seller
        $tradeOffers = TradeTransaction::where(function($query) use ($user) {
                $query->where('seller_id', $user->id)
                      ->orWhere('seller_code', $user->seller_code);
            })
            ->with(['buyer:id,first_name,last_name,username,wmsu_email,profile_picture', 
                    'sellerProduct:id,name,price,images,description',
                    'offeredItems'])
            ->latest()
            ->get()
            ->map(function($trade) {
                return [
                    'id' => $trade->id,
                    'buyer_id' => $trade->buyer_id,
                    'buyer_name' => $trade->buyer ? $trade->buyer->first_name . ' ' . $trade->buyer->last_name : 'Unknown',
                    'buyer_username' => $trade->buyer ? $trade->buyer->username : '',
                    'buyer_email' => $trade->buyer ? $trade->buyer->wmsu_email : '',
                    'buyer_avatar' => $trade->buyer && $trade->buyer->profile_picture ? 
                        '/storage/' . $trade->buyer->profile_picture : null,
                    'product_id' => $trade->seller_product_id,
                    'product_name' => $trade->sellerProduct ? $trade->sellerProduct->name : 'Unknown Product',
                    'product_price' => $trade->sellerProduct ? $trade->sellerProduct->price : 0,
                    'seller_product' => $trade->sellerProduct ? [
                        'id' => $trade->sellerProduct->id,
                        'name' => $trade->sellerProduct->name,
                        'price' => $trade->sellerProduct->price,
                        'description' => $trade->sellerProduct->description,
                        'images' => $trade->sellerProduct->images ? array_map(function ($image) {
                            if ($image && file_exists(storage_path('app/public/' . $image))) {
                                return asset('storage/' . $image);
                            } else {
                                return asset('images/placeholder-product.jpg');
                            }
                        }, $trade->sellerProduct->images) : []
                    ] : null,
                    'additional_cash' => $trade->additional_cash,
                    'status' => $trade->status,
                    'notes' => $trade->notes,
                    'meetup_schedule' => $trade->meetup_schedule,
                    'offered_items' => $trade->offeredItems->map(function($item) {
                        // Handle image paths, decode JSON if needed
                        $images = is_string($item->images) ? json_decode($item->images, true) : $item->images;
                        $firstImage = !empty($images) ? $images[0] : null;
                        
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'quantity' => $item->quantity,
                            'estimated_value' => $item->estimated_value,
                            'description' => $item->description,
                            'image_url' => $firstImage ? '/storage/' . $firstImage : null,
                            'images' => $images ? array_map(function($img) {
                                return '/storage/' . $img;
                            }, $images) : []
                        ];
                    }),
                    'offered_items_count' => $trade->offeredItems->count(),
                    'created_at' => $trade->created_at,
                    'updated_at' => $trade->updated_at,
                ];
            });
        
        return Inertia::render('Dashboard/seller/TradeOffers', [
            'user' => $user,
            'stats' => $stats,
            'tradeOffers' => $tradeOffers
        ]);
    }

    public function acceptTradeOffer($id)
    {
        try {
            $trade = TradeTransaction::findOrFail($id);
            
            // Verify that the user is the seller for this trade
            if ($trade->seller_id !== auth()->id() && $trade->seller_code !== auth()->user()->seller_code) {
                return redirect()->back()->with('error', 'Unauthorized action');
            }
            
            // Only pending trades can be accepted
            if ($trade->status !== 'pending') {
                return redirect()->back()->with('error', 'Only pending trades can be accepted');
            }
            
            // Update trade status
            $trade->update(['status' => 'accepted']);
            
            // Here you could implement additional logic like:
            // - Notify the buyer
            // - Update product availability
            // - Create a new order based on the trade
            
            return redirect()->back()->with('success', 'Trade offer accepted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error accepting trade: ' . $e->getMessage());
        }
    }

    public function rejectTradeOffer($id)
    {
        try {
            $trade = TradeTransaction::findOrFail($id);
            
            // Verify that the user is the seller for this trade
            if ($trade->seller_id !== auth()->id() && $trade->seller_code !== auth()->user()->seller_code) {
                return redirect()->back()->with('error', 'Unauthorized action');
            }
            
            // Only pending trades can be rejected
            if ($trade->status !== 'pending') {
                return redirect()->back()->with('error', 'Only pending trades can be rejected');
            }
            
            // Update trade status
            $trade->update(['status' => 'rejected']);
            
            // Here you could implement notification to the buyer
            
            return redirect()->back()->with('success', 'Trade offer rejected successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error rejecting trade: ' . $e->getMessage());
        }
    }
}