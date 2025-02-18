<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

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
        $sellerCode = Auth::user()->seller_code;
        $orderCounts = $this->getOrderCounts($sellerCode);

        $query = Product::where('seller_code', $sellerCode)
            ->with(['category']);

        // Include deleted items if requested
        if ($request->show_deleted) {
            $query->withTrashed();
        }

        $products = $query->latest()->paginate(10);
        $categories = Category::all();

        return view('dashboard.products', compact('products', 'categories', 'orderCounts'));
    }

    // Add method to fetch single product for editing
    public function edit(Product $product)
    {
        if ($product->seller_code !== Auth::user()->seller_code) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $product->load('category'); // Load the category relationship
        return response()->json($product);
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

    // Modify update method
    public function update(Request $request, Product $product)
    {
        if ($product->seller_code !== Auth::user()->seller_code) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            // Calculate discount as decimal
            $discount = $request->discount ? (float)($request->discount / 100) : 0.0;

            // Calculate discounted price using float values
            $price = (float)$request->price;
            $discountedPrice = $price * (1 - $discount);

            // Debug logging
            Log::info('Product update calculations:', [
                'original_price' => $price,
                'discount_percentage' => $request->discount,
                'discount_decimal' => $discount,
                'final_price' => $discountedPrice
            ]);

            // Handle update logic
            $product->update([
                'name' => $request->name,
                'description' => $request->description,
                'category_id' => $request->category,
                'price' => $price,
                'discount' => $discount,
                'discounted_price' => round($discountedPrice, 2),
                'stock' => $request->stock,
                'status' => $request->status ?? 'Active',
                'is_buyable' => in_array($request->trade_availability, ['buy', 'both']),
                'is_tradable' => in_array($request->trade_availability, ['trade', 'both'])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'product' => $product
            ]);
        } catch (\Exception $e) {
            Log::error('Product update error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error updating product: ' . $e->getMessage()
            ], 500);
        }
    }

    // Modify destroy method to use soft delete
    public function destroy(Product $product)
    {
        if ($product->seller_code !== Auth::user()->seller_code) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            // Set product status to Inactive before soft deleting
            $product->update([
                'status' => 'Inactive',
                'is_buyable' => false,
                'is_tradable' => false
            ]);

            // Perform soft delete
            $product->delete();

            // Update any active orders containing this product
            $product->orderItems()
                ->whereHas('order', function ($query) {
                    $query->whereIn('status', ['Pending', 'Processing']);
                })
                ->each(function ($orderItem) {
                    $orderItem->order->update(['status' => 'Cancelled']);
                });

            return response()->json([
                'success' => true,
                'message' => 'Product has been deactivated and archived'
            ]);
        } catch (\Exception $e) {
            Log::error('Product deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error archiving product: ' . $e->getMessage()
            ], 500);
        }
    }

    // Optional: Add method to permanently delete if needed
    public function forceDelete(Product $product)
    {
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
            $product->restore();

            return response()->json([
                'success' => true,
                'message' => 'Product restored successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error restoring product: ' . $e->getMessage()
            ], 500);
        }
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
}
