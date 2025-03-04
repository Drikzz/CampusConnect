<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        try {
            $products = Product::query()
                ->where('status', 'Active')
                ->with(['category', 'seller'])
                ->latest()
                ->paginate(12);

            $formattedProducts = $products->through(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => (float)$product->price,
                'discounted_price' => (float)$product->discounted_price,
                'discount' => (float)$product->discount,
                'images' => array_map(fn($image) => asset('storage/' . $image), $product->images ?? []),
                'category' => [
                    'id' => $product->category->id,
                    'name' => $product->category->name
                ],
                'seller' => [
                    'id' => $product->seller->id,
                    'name' => $product->seller->first_name . ' ' . $product->seller->last_name,
                    'username' => $product->seller->username,
                    'profile_picture' => $product->seller->profile_picture ?
                        asset('storage/' . $product->seller->profile_picture) : null
                ],
                'stock' => $product->stock,
                'is_buyable' => (bool)$product->is_buyable,
                'is_tradable' => (bool)$product->is_tradable,
                'status' => $product->status,
            ]);

            return Inertia::render('Products/Index', [
                'products' => $formattedProducts,
                'filters' => request()->all(['search', 'category', 'price']),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading products page: ' . $e->getMessage());
            return Inertia::render('Products/Index', [
                'products' => [],
                'filters' => request()->all(['search', 'category', 'price']),
            ]);
        }
    }

    public function trade()
    {
        // $products = Product::where('is_tradable', true)->latest()->get();
        // return view('products.trade', ['products' => $products]);
        $products = Product::with(['seller', 'images'])
            ->where('is_tradable', true)
            ->where('status', 'Active')
            ->where('stock', '>', 0)
            ->latest()
            ->get();
    
        return Inertia::render('Products/Trade', [
            'products' => $products
        ]);
    }

    public function welcome()
    {
        try {
            $products = Product::with(['category', 'seller'])
                ->where('status', 'Active')
                ->latest()
                ->take(8)
                ->get()
                ->transform(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'price' => (float)$product->price,
                        'discounted_price' => (float)$product->discounted_price,
                        'discount' => (float)$product->discount,
                        'images' => array_map(function ($image) {
                            return asset('storage/' . $image);
                        }, $product->images ?? []),
                        'category' => [
                            'id' => $product->category->id,
                            'name' => $product->category->name
                        ],
                        'seller' => [
                            'id' => $product->seller->id,
                            'name' => $product->seller->first_name . ' ' . $product->seller->last_name
                        ],
                        'is_buyable' => (bool)$product->is_buyable,
                        'is_tradable' => (bool)$product->is_tradable,
                        'status' => $product->status,
                    ];
                });

            return Inertia::render('Welcome', [
                'products' => $products,
                'canLogin' => Route::has('login'),
                'canRegister' => Route::has('register'),
            ]);
        } catch (\Exception $e) {
            // Log the error and return an empty products array
            \Log::error('Error loading welcome page: ' . $e->getMessage());
            return Inertia::render('Welcome', [
                'products' => [],
                'canLogin' => Route::has('login'),
                'canRegister' => Route::has('register'),
            ]);
        }
    }

    public function show($id)
    {
        try {
            $product = Product::with(['category', 'seller'])
                ->findOrFail($id);

            $randomProducts = Product::where('id', '!=', $id)
                ->with(['category', 'seller'])
                ->inRandomOrder()
                ->take(4)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'price' => (float)$product->price,
                        'discounted_price' => (float)$product->discounted_price,
                        'discount' => (float)$product->discount,
                        'stock' => $product->stock,
                        'images' => array_map(function ($image) {
                            return asset('storage/' . $image);
                        }, $product->images ?? []),
                        'category' => [
                            'id' => $product->category->id,
                            'name' => $product->category->name
                        ],
                        'seller' => [
                            'id' => $product->seller->id,
                            'name' => $product->seller->first_name . ' ' . $product->seller->last_name,
                            'username' => $product->seller->username,
                            'profile_picture' => $product->seller->profile_picture ?
                                asset('storage/' . $product->seller->profile_picture) : null
                        ],
                        'is_buyable' => (bool)$product->is_buyable,
                        'is_tradable' => (bool)$product->is_tradable,
                    ];
                });

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
                }, $product->images ?? []),
                'category' => [
                    'id' => $product->category->id,
                    'name' => $product->category->name
                ],
                'seller' => [
                    'id' => $product->seller->id,
                    'name' => $product->seller->first_name . ' ' . $product->seller->last_name,
                    'username' => $product->seller->username,
                    'profile_picture' => $product->seller->profile_picture ?
                        asset('storage/' . $product->seller->profile_picture) : null,
                    'rating' => 5,
                    'reviews_count' => 5,
                    'location' => 'Zamboanga City' // Add default location or get from seller's data
                ],
                'tags' => ['Uniforms', 'CCS', 'Male'],
                'is_buyable' => (bool)$product->is_buyable,
                'is_tradable' => (bool)$product->is_tradable,
                'status' => $product->status,
            ];

            // Change the render path to match the directory structure
            return Inertia::render('Products/Show', [
                'product' => $formattedProduct,
                'randomProducts' => $randomProducts
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading product details: ' . $e->getMessage());
            return redirect()->route('products')
                ->with('error', 'Unable to load product details');
        }
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
