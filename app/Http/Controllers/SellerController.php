<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use App\Models\Wishlist;
use App\Models\OrderItem;
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
        return view('seller.dashboard', compact('categories', 'totalOrders', 'totalSales', 'activeTrades', 'recentOrders', 'orderCounts'));
    }

    // Modify products listing to include option to show deleted
    public function products(Request $request)
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

        $query = Product::where('seller_code', $sellerCode)
            ->with(['category'])
            ->withTrashed(); // Always include trashed items

        $products = $query->latest()->paginate(10);
        $categories = Category::all();

        return view('dashboard.seller.products', compact(
            'products',
            'categories',
            'totalOrders',
            'activeOrders',
            'wishlistCount',
            'totalSales',
            'activeProducts',
            'pendingOrders'
        ));
    }

    // Add method to fetch single product for editing
    public function edit($id)
    {
        $product = Product::withTrashed()->findOrFail($id);

        if ($product->seller_code !== Auth::user()->seller_code) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Load relationships
        $product->load('category');
        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::withTrashed()->findOrFail($id);

        if ($product->seller_code !== Auth::user()->seller_code) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return redirect()->back()->with('error', 'Unauthorized action');
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
                'additional_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            DB::beginTransaction();

            // If status is being set to Inactive, handle soft delete
            if ($validated['status'] === 'Inactive') {
                // Store current state
                $product->old_attributes = [
                    'status' => $product->status,
                    'is_buyable' => $product->is_buyable,
                    'is_tradable' => $product->is_tradable
                ];

                // Update status and trade options
                $product->status = 'Inactive';
                $product->is_buyable = false;
                $product->is_tradable = false;
                $product->save();

                // Perform soft delete
                $product->delete();

                DB::commit();

                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Product has been deactivated and archived'
                    ]);
                }

                return redirect()->route('dashboard.seller.products')
                    ->with('success', 'Product has been deactivated and archived');
            }

            // Continue with normal update for active products
            $imagePaths = $product->images;
            if ($request->hasFile('main_image') || $request->hasFile('additional_images')) {
                $imagePaths = $this->handleProductImages($request, $product);
            }

            $discount = $validated['discount'] ? (float)($validated['discount'] / 100) : 0.0;
            $price = (float)$validated['price'];
            $discountedPrice = $price * (1 - $discount);

            $product->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'category_id' => $validated['category'],
                'price' => $price,
                'discount' => $discount,
                'discounted_price' => round($discountedPrice, 2),
                'stock' => $validated['stock'],
                'images' => $imagePaths,
                'status' => $validated['status'],
                'is_buyable' => in_array($validated['trade_availability'], ['buy', 'both']),
                'is_tradable' => in_array($validated['trade_availability'], ['trade', 'both'])
            ]);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product updated successfully'
                ]);
            }

            return redirect()->route('dashboard.seller.products')
                ->with('success', 'Product updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product update error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating product: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Error updating product: ' . $e->getMessage())
                ->withInput();
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
            // Handle images
            $imagePaths = $this->handleProductImages($request);

            // Set trade options
            $tradeOptions = $this->getTradeOptions($validated['trade_availability']);

            // Calculate discount as decimal (e.g., 15% becomes 0.15)
            $discount = $validated['discount'] ? (float)($validated['discount'] / 100) : 0.0;

            // Calculate discounted price using float values
            $price = (float)$validated['price'];
            $discountedPrice = $price * (1 - $discount);

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
            Log::error('Product creation error:', [
                'message' => $e->getMessage(),
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

    // Modify destroy method to use soft delete
    public function destroy($id)
    {
        $product = Product::withTrashed()->findOrFail($id);

        if ($product->seller_code !== Auth::user()->seller_code) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            DB::beginTransaction();

            // Store current state before soft delete
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

            return response()->json([
                'success' => true,
                'message' => 'Product successfully archived'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Product deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error archiving product'
            ], 500);
        }
    }

    // Optional: Add method to permanently delete if needed
    public function forceDelete($id)
    {
        $product = Product::withTrashed()->findOrFail($id);

        if ($product->seller_code !== Auth::user()->seller_code) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            // Delete images
            if ($product->images) {
                foreach ($product->images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            $product->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Product permanently deleted'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error permanently deleting product: ' . $e->getMessage()
            ], 500);
        }
    }

    // Optional: Add method to restore deleted products
    public function restore($id)
    {
        $product = Product::withTrashed()->findOrFail($id);

        if ($product->seller_code !== Auth::user()->seller_code) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            DB::beginTransaction();

            // First restore the product
            $product->restore();

            // Restore the old attributes if they exist, otherwise use defaults
            $oldAttributes = $product->old_attributes ?? [
                'status' => 'Active',
                'is_buyable' => false,
                'is_tradable' => false
            ];

            // Update with the stored attributes
            $product->update([
                'status' => $oldAttributes['status'],
                'is_buyable' => $oldAttributes['is_buyable'],
                'is_tradable' => $oldAttributes['is_tradable']
            ]);

            // Clear the stored old attributes
            $product->old_attributes = null;
            $product->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product restored successfully with original attributes'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product restore error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error restoring product: ' . $e->getMessage()
            ], 500);
        }
    }

    // Add orders method
    public function orders()
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

        $orders = Order::where('seller_code', $sellerCode)
            ->with(['items.product', 'buyer'])
            ->latest()
            ->paginate(10);

        return view('dashboard.seller.orders', compact(
            'orders',
            'totalOrders',
            'activeOrders',
            'wishlistCount',
            'totalSales',
            'activeProducts',
            'pendingOrders'
        ));
    }

    public function showOrder(Order $order)
    {
        if ($order->seller_code !== auth()->user()->seller_code) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

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

        $order->load(['items.product', 'buyer']);

        return view('dashboard.seller.order-details', compact(
            'order',
            'totalOrders',
            'activeOrders',
            'wishlistCount',
            'totalSales',
            'activeProducts',
            'pendingOrders'
        ));
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
}
