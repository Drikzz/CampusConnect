<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
// use App\Models\Transaction;
// use App\Models\Review;
// use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // Profile
    public function showProfile()
    {
        $user = auth()->user();
        return view('users.profile', compact('user'));
    }

    // Products
    public function showProducts()
    {
        $products = auth()->user()->products()->latest()->paginate(12);
        return view('users.products.index', compact('products'));
    }

    public function addProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'condition' => 'required|in:new,used,good,fair',
            'images.*' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $product = auth()->user()->products()->create($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = Storage::disk('public')->put('products', $image);
                $product->images()->create(['path' => $path]);
            }
        }

        return redirect()->route('user.products')->with('success', 'Product added successfully');
    }

    public function updateProduct(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'condition' => 'required|in:new,used,good,fair',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $product->update($validated);

        if ($request->hasFile('images')) {
            // Delete old images
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            }

            // Upload new images
            foreach ($request->file('images') as $image) {
                $path = Storage::disk('public')->put('products', $image);
                $product->images()->create(['path' => $path]);
            }
        }

        return back()->with('success', 'Product updated successfully');
    }

    public function deleteProduct(Product $product)
    {
        $this->authorize('delete', $product);

        // Delete images
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $product->delete();
        return back()->with('success', 'Product deleted successfully');
    }

    // Transactions
    public function showTransactions()
    {
        $sales = auth()->user()->sales()->latest()->paginate(10);
        $purchases = auth()->user()->purchases()->latest()->paginate(10);
        return view('users.transactions', compact('sales', 'purchases'));
    }

    // Reviews
    // public function showProductReviews()
    // {
    //     $reviews = Review::whereIn('product_id', auth()->user()->products()->pluck('id'))
    //         ->with('user', 'product')
    //         ->latest()
    //         ->paginate(15);
    //     return view('users.reviews.products', compact('reviews'));
    // }

    // public function showUserReviews()
    // {
    //     $reviews = auth()->user()->receivedReviews()
    //         ->with('reviewer')
    //         ->latest()
    //         ->paginate(15);
    //     return view('users.reviews.user', compact('reviews'));
    // }

    // Bookmarks
    public function showBookmarks()
    {
        $bookmarks = auth()->user()->bookmarks()
            ->with('seller')
            ->latest()
            ->paginate(12);
        return view('users.bookmarks', compact('bookmarks'));
    }

    // Messages
    // public function showInbox()
    // {
    //     $threads = auth()->user()->messageThreads()
    //         ->with(['latestMessage', 'participants'])
    //         ->latest()
    //         ->paginate(20);
    //     return view('users.inbox', compact('threads'));
    // }

    public function is_seller()
    {
        $user = Auth::user();
        $user->update([
            'is_seller' => true,
            'seller_code' => strtoupper(uniqid())  // Generate uppercase unique string
        ]);
        return redirect()->route('seller.dashboard')->with('success', 'You are now a verified seller');
    }

    public function myOrders(Request $request)
    {
        $status = $request->get('status', 'all');
        $query = auth()->user()->orders()->with(['seller', 'items']);

        switch ($status) {
            case 'pending':
                $query->pending();
                break;
            case 'processing':
                $query->processing();
                break;
            case 'completed':
                $query->completed();
                break;
        }

        $orders = $query->latest()->paginate(10);
        return view('users.orders', compact('orders', 'status'));
    }

    public function favorites()
    {
        $favorites = auth()->user()->bookmarks()
            ->with(['images', 'seller'])
            ->latest()
            ->paginate(12);
        return view('users.favorites', compact('favorites'));
    }
}
