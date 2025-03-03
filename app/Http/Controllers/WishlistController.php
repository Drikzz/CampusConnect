<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        try {
            $wishlists = Wishlist::with(['product' => function($query) {
                    $query->select('id', 'name', 'price', 'discounted_price', 'images', 'description');
                }])
                ->where('user_id', auth()->id())
                ->latest()
                ->paginate(9);

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'wishlists' => $wishlists
                ]);
            }

            return Inertia::render('Dashboard/Wishlist', [
                'wishlists' => $wishlists
            ]);
        } catch (\Exception $e) {
            Log::error('Wishlist fetch error: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch wishlist items'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $wishlist = Wishlist::firstOrCreate([
            'user_id' => auth()->id(),
            'product_id' => $validated['product_id']
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Product added to wishlist'
        ]);
    }

    public function destroy($id)
    {
        $wishlist = Wishlist::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $wishlist->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Product removed from wishlist'
        ]);
    }

    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $userId = auth()->id();
        $wishlist = Wishlist::where('user_id', $userId)
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            return response()->json(['status' => 'removed']);
        }

        Wishlist::create([
            'user_id' => $userId,
            'product_id' => $validated['product_id']
        ]);

        return response()->json(['status' => 'added']);
    }

    public function checkStatus(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $exists = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $validated['product_id'])
            ->exists();

        return response()->json(['inWishlist' => $exists]);
    }
}
