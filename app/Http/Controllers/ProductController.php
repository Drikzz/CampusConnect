<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::latest()->get();
        return view('products.products', ['products' => $products]);

        // return response()->json($products);
    }

    public function welcome()
    {
        $products = Product::latest()->get();
        return view('welcome', ['products' => $products]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //no need since we are using a modal
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $fields = $request->validate([
            'name' => ['required'],
            'body' => ['required', 'max:255'],
            'price' => ['required', 'numeric'],
            'discounted_price' => ['nullable', 'numeric'],
            'image' => ['nullable', 'image', 'file', 'extensions:jpg,png', 'max:2048'],
        ]);

        // create a post
        Auth::user()->products()->create($fields);

        // redirect to dashboard
        return back()->with('success', 'Your product was added succesfully!');

        // return response()->json($product);
    }

    /**
     * Display the specified resource.
     */
    // Product $product remove this from the parameter
    public function show($id)
    {
        $product = Product::findOrFail($id);
        return view('products.prod_details', ['product' => $product]);

        // return response()->json($product);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $product = Product::findOrFail($id);
        // Return a view for editing the product
        return view('user.edit', ['product' => $product]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'discounted_price' => 'nullable|numeric',
            'image' => 'nullable|string',
        ]);

        $product = Product::findOrFail($id);
        $product->update($validatedData);

        // return response()->json($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // find the product using the id
        $product = Product::findOrFail($id);

        // delete the product
        $product->delete();

        return redirect()->route('seller.products')->with('success', 'Product removed.');

        // return response()->json(null, 204);
    }
}
