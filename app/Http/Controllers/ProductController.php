<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::where('status', 'Active')
            ->with(['category', 'seller'])
            ->latest()
            ->paginate(12);

        return view('products.products', compact('products'));
    }

    public function trade()
    {
        $products = Product::where('is_tradable', true)->latest()->get();
        return view('products.trade', ['products' => $products]);
    }

    public function welcome()
    {
        $products = Product::latest()->get();
        return view('welcome', ['products' => $products]);
    }

    public function product_details($id)
    {
        $product = Product::findOrFail($id);
        $randomProducts = Product::inRandomOrder()->take(4)->get(); // Fetch 4 random products

        return view('products.prod_details', [
            'product' => $product,
            'randomProducts' => $randomProducts,
        ]);
    }

    public function show(Product $product)
    {
        if ($product->seller_code !== auth()->user()->seller_code) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($product->load('category'));
    }

    public function create(Request $request)
    {
        //no need since we are using a modal
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:Active,Inactive',
            'images.*' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $imagePaths[] = $path;
            }
        }

        $product = Product::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'category_id' => $validated['category_id'],
            'status' => $request->has('status') ? 'Active' : 'Inactive',
            'images' => $imagePaths,
            'seller_code' => auth()->user()->seller_code
        ]);

        return redirect()->route('dashboard.seller.products')
            ->with('success', 'Product created successfully');
    }

    public function edit(Product $product)
    {
        $product = Product::findOrFail($id);
        // Return a view for editing the product
        return view('user.edit', ['product' => $product]);
    }

    public function update(Request $request, Product $product)
    {
        if ($product->seller_code !== auth()->user()->seller_code) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:Active,Inactive',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        // Handle image updates
        $imagePaths = $product->images ?? [];
        if ($request->hasFile('images')) {
            // Delete old images
            foreach ($imagePaths as $path) {
                Storage::disk('public')->delete($path);
            }

            // Store new images
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $imagePaths[] = $path;
            }
        }

        $product->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'category_id' => $validated['category_id'],
            'status' => $request->has('status') ? 'Active' : 'Inactive',
            'images' => $imagePaths
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully'
        ]);
    }

    public function destroy(Product $product)
    {
        if ($product->seller_code !== auth()->user()->seller_code) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Delete product images
        if ($product->images) {
            foreach ($product->images as $path) {
                Storage::disk('public')->delete($path);
            }
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }
}
