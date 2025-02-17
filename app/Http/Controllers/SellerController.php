<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

use function Pest\Laravel\get;

class SellerController extends Controller
{

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

    public function create()
    {
        $sellerCode = Auth::user()->seller_code;

        // Update order counts to use seller_code
        $orderCounts = (object)[
            'pendingCount' => Order::where('seller_code', $sellerCode)->where('status', 'Pending')->count(),
            'processingCount' => Order::where('seller_code', $sellerCode)->where('status', 'Processing')->count(),
            'completedCount' => Order::where('seller_code', $sellerCode)->where('status', 'Completed')->count(),
        ];
        View::share('orderCounts', $orderCounts);

        $categories = Category::all();
        return view('seller.addproduct', compact('categories', 'orderCounts'));
    }

    public function store(Request $request)
    {
        $sellerCode = Auth::user()->seller_code;

        if (!$sellerCode) {
            return redirect()->back()->with('error', 'Seller code not found. Please update your profile.');
        }

        // Update order counts to use seller_code
        $orderCounts = (object)[
            'pendingCount' => Order::where('seller_code', $sellerCode)->where('status', 'Pending')->count(),
            'processingCount' => Order::where('seller_code', $sellerCode)->where('status', 'Processing')->count(),
            'completedCount' => Order::where('seller_code', $sellerCode)->where('status', 'Completed')->count(),
        ];
        View::share('orderCounts', $orderCounts);

        // Ensure user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'You must be logged in to add products.');
        }

        // Get seller code from authenticated user
        $sellerCode = Auth::user()->seller_code;

        // dd($sellerCode);    
        if (!$sellerCode) {
            return redirect()->back()
                ->with('error', 'Seller code not found. Please update your profile.');
        }

        // Validate incoming request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|exists:categories,id',
            'trade_availability' => 'required|in:buy,trade,both',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'quantity' => 'required|integer|min:1',
            'main_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:Active,Inactive'
        ]);

        // Handle images with full URLs
        $imagePaths = [];

        // Handle main image (required)
        if ($request->hasFile('main_image')) {
            $path = $request->file('main_image')->store('products', 'public');
            $imagePaths[] = ($path);
        }

        // Handle additional images (optional)
        if ($request->hasFile('additional_images')) {
            foreach ($request->file('additional_images') as $image) {
                if ($image) {
                    $path = $image->store('products', 'public');
                    $imagePaths[] = ($path);
                }
            }
        }

        // Set is_buyable and is_tradable based on trade_availability
        $is_buyable = in_array($validated['trade_availability'], ['buy', 'both']);
        $is_tradable = in_array($validated['trade_availability'], ['trade', 'both']);

        // Calculate discounted price
        $price = (int)(floatval($validated['price']) * 100); // Convert to cents
        $discount = ($validated['discount'] ?? 0) / 100; // Convert percentage to decimal
        $taxPrice = $price * $discount;
        $discountedPrice = $price - $taxPrice;
        $price = $price / 100; // Convert back to dollars

        // Create product
        $product = Product::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $price,
            'discount' => $validated['discount'] ?? 0,
            'discounted_price' => $discountedPrice,
            'stock' => $validated['quantity'],
            'images' => $imagePaths,
            'seller_code' => $sellerCode,
            'category_id' => $validated['category'],
            'is_buyable' => $is_buyable,
            'is_tradable' => $is_tradable,
            'status' => $validated['status']
        ]);

        if ($product) {
            return redirect()->route('seller.products')->with('success', 'Product added successfully!');
        }

        // return redirect()->back()->with('error', 'Failed to add product. Please try again.');
    }

    public function products()
    {
        $sellerCode = Auth::user()->seller_code;

        // Update order counts to use seller_code
        $orderCounts = (object)[
            'pendingCount' => Order::where('seller_code', $sellerCode)->where('status', 'Pending')->count(),
            'processingCount' => Order::where('seller_code', $sellerCode)->where('status', 'Processing')->count(),
            'completedCount' => Order::where('seller_code', $sellerCode)->where('status', 'Completed')->count(),
        ];
        View::share('orderCounts', $orderCounts);

        $products = Product::where('seller_code', $sellerCode)
            ->with(['category'])
            ->latest()
            ->paginate(25);  // Increased from 10 to 25 items per page

        $categories = Category::all();

        return view('seller.product', compact('products', 'orderCounts', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        try {
            // Verify seller ownership
            if ($product->seller_code !== Auth::user()->seller_code) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'category' => 'required|exists:categories,id',
                'price' => 'required|numeric|min:0',
                'quantity' => 'required|integer|min:1',
                'discount' => 'nullable|numeric|min:0|max:100',
                'is_buyable' => 'required|boolean',
                'is_tradable' => 'required|boolean',
            ]);

            // Convert array to object for easier manipulation
            $imageObj = [];
            foreach ($product->images ?? [] as $index => $path) {
                $imageObj[$index] = $path;
            }

            // Handle main image (index 0)
            if ($request->hasFile('main_image')) {
                // Delete old main image if exists
                if (isset($imageObj[0])) {
                    $oldPath = str_replace('/storage/', '', parse_url($imageObj[0], PHP_URL_PATH));
                    Storage::disk('public')->delete($oldPath);
                }

                // Store new image
                $path = $request->file('main_image')->store('products', 'public');
                $imageObj[0] = Storage::url($path);
            }

            // Handle additional images
            for ($i = 1; $i <= 4; $i++) {
                $inputName = "additional_image_{$i}";
                if ($request->hasFile($inputName)) {
                    // Delete old image if exists
                    if (isset($imageObj[$i])) {
                        $oldPath = str_replace('/storage/', '', parse_url($imageObj[$i], PHP_URL_PATH));
                        Storage::disk('public')->delete($oldPath);
                    }

                    // Store new image
                    $path = $request->file($inputName)->store('products', 'public');
                    $imageObj[$i] = Storage::url($path);
                }
            }

            // Remove any null values and reindex array
            $imagePaths = array_values(array_filter($imageObj));

            // Calculate discounted price
            $price = (int)(floatval($validated['price']) * 100); // Convert to cents
            $discount = ($validated['discount'] ?? 0) / 100; // Convert percentage to decimal
            $taxPrice = $price * $discount;
            $discountedPrice = $price - $taxPrice;
            $price = $price / 100; // Convert back to dollars

            // Update product without trade method
            $product->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'price' => $price,
                'discount' => $validated['discount'] ?? 0,
                'discounted_price' => $discountedPrice,
                'stock' => $validated['quantity'],
                'quantity' => $validated['quantity'],
                'category_id' => $validated['category'],
                'images' => $imagePaths ?? $product->images,
                'is_buyable' => filter_var($request->input('is_buyable'), FILTER_VALIDATE_BOOLEAN),
                'is_tradable' => filter_var($request->input('is_tradable'), FILTER_VALIDATE_BOOLEAN),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'product' => $product
            ]);
        } catch (\Exception $e) {
            Log::error('Product update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating product: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Product $product)
    {
        try {
            // Verify seller ownership
            if ($product->seller_code !== Auth::user()->seller_code) {
                return redirect()->back()->with('error', 'Unauthorized action.');
            }

            // Delete associated images from storage
            if ($product->images) {
                foreach ($product->images as $image) {
                    $path = str_replace('/storage/', '', parse_url($image, PHP_URL_PATH));
                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                }
            }

            // Delete the product
            $product->delete();

            return redirect()->route('seller.products')
                ->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Product deletion error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting product. Please try again.');
        }
    }

    public function categories()
    {
        $sellerCode = Auth::user()->seller_code;

        $orderCounts = (object)[
            'pendingCount' => Order::where('seller_code', $sellerCode)->where('status', 'Pending')->count(),
            'processingCount' => Order::where('seller_code', $sellerCode)->where('status', 'Processing')->count(),
            'completedCount' => Order::where('seller_code', $sellerCode)->where('status', 'Completed')->count(),
        ];
        View::share('orderCounts', $orderCounts);

        return view('seller.categories', compact('orderCounts'));
    }

    public function orders()
    {
        $sellerCode = Auth::user()->seller_code;

        // Update order counts - removed processing
        $orderCounts = (object)[
            'pendingCount' => Order::where('seller_code', $sellerCode)->pending()->count(),
            'completedCount' => Order::where('seller_code', $sellerCode)->completed()->count(),
        ];
        View::share('orderCounts', $orderCounts);

        // Get all orders for the seller with buyer information
        $orders = Order::where('seller_code', $sellerCode)
            ->with(['buyer', 'items.product'])
            ->latest()
            ->paginate(10);

        return view('seller.orders', compact('orders', 'orderCounts'));
    }

    // Add new methods for order details and completion
    public function getOrderDetails(Order $order)
    {
        // Verify seller ownership
        if ($order->seller_code !== Auth::user()->seller_code) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($order->load('buyer', 'items.product'));
    }

    public function completeOrder(Order $order)
    {
        // Verify seller ownership
        if ($order->seller_code !== Auth::user()->seller_code) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $order->update(['status' => 'Completed']);

        return response()->json(['success' => true]);
    }
}
