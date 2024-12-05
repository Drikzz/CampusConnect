<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Facades\Storage;

class SellerController extends Controller
{
    public function index()
    {
        $sellerId = auth()->id();

        // Get total orders
        $totalOrders = Order::where('seller_id', $sellerId)->count();

        // Calculate total sales
        $totalSales = Order::where('seller_id', $sellerId)
            ->where('status', 'Completed')
            ->sum('sub_total');

        // Get active trades (orders with payment_method as 'trade')
        $activeTrades = Order::where('seller_id', $sellerId)
            ->where('payment_method', 'trade')
            ->where('status', '!=', 'Completed')
            ->count();

        // Get recent orders
        $recentOrders = Order::where('seller_id', $sellerId)
            ->with('buyer')
            ->latest()
            ->take(5)
            ->get();

        return view('seller.dashboard', compact('totalOrders', 'totalSales', 'activeTrades', 'recentOrders'));
    }

    public function create()
    {
        return view('seller.addproduct');
    }

    public function store(Request $request)
    {
        // Validate incoming request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'trade_method' => 'required|in:sell,trade,all',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Handle image uploads
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $imagePaths[] = $path;
            }
        }

        // Create product
        $product = Product::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'trade_method' => $validated['trade_method'],
            'price' => $validated['price'],
            'quantity' => $validated['quantity'],
            'images' => json_encode($imagePaths),
            'user_id' => auth()->id()
        ]);

        return redirect()->route('seller.products.add')
            ->with('success', 'Product added successfully!');
    }
}
