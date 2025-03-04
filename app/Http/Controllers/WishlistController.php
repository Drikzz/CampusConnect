<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class WishlistController extends Controller
{
    protected $dashboardController;

    public function __construct(DashboardController $dashboardController)
    {
        $this->dashboardController = $dashboardController;
    }

    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $stats = $this->dashboardController->getDashboardStats($user);

            $wishlists = Wishlist::with(['product' => function ($query) {
                $query->select('id', 'name', 'price', 'discounted_price', 'images', 'description');
            }])
                ->where('user_id', auth()->id())
                ->latest()
                ->get();

            $transformedWishlists = $wishlists->map(function ($item) {
                return [
                    'id' => $item->id,
                    'user_id' => $item->user_id,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'price' => $item->product->price,
                        'discounted_price' => $item->product->discounted_price,
                        'images' => $this->parseImages($item->product->images),
                        'description' => $item->product->description,
                    ],
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });

            return Inertia::render('Dashboard/Wishlist', [
                'wishlistItems' => $transformedWishlists,
                'user' => $user,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Wishlist fetch error: ' . $e->getMessage());

            return Inertia::render('Dashboard/Wishlist', [
                'wishlistItems' => [],
                'user' => $user,
                'stats' => $stats,
                'error' => 'Failed to fetch wishlist items'
            ]);
        }
    }

    // Add this helper method to parse images
    private function parseImages($images)
    {
        if (empty($images)) {
            return [];
        }

        if (is_string($images)) {
            try {
                $decoded = json_decode($images, true);
                return is_array($decoded) ? $decoded : [$images];
            } catch (\Exception $e) {
                return [$images];
            }
        }

        return is_array($images) ? $images : [$images];
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
        try {
            $wishlist = Wishlist::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $wishlist->delete();

            return redirect()->route('dashboard.wishlist')
                ->with('success', 'Item removed from wishlist')
                ->with('toast', [
                    'variant' => 'default',
                    'title' => 'Success!',
                    'description' => 'Item removed from wishlist successfully'
                ]);
        } catch (\Exception $e) {
            Log::error('Wishlist delete error: ' . $e->getMessage());

            return redirect()->route('dashboard.wishlist')
                ->with('error', 'Failed to remove item from wishlist')
                ->with('toast', [
                    'variant' => 'destructive',
                    'title' => 'Error!',
                    'description' => 'Failed to remove item from wishlist'
                ]);
        }
    }

    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        // Check if user is logged in
        if (!auth()->check()) {
            // Store the intended action in the session
            session()->put('wishlist_after_login', [
                'action' => 'toggle',
                'product_id' => $validated['product_id']
            ]);

            // If it's an AJAX request, return JSON response
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'auth_required',
                    'redirect' => route('login')
                ], 401);
            }

            // For regular requests, redirect directly
            return redirect()->route('login')->with('message', 'Please log in to add items to your wishlist');
        }

        $userId = auth()->id();
        $wishlist = Wishlist::where('user_id', $userId)
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($wishlist) {
            $wishlist->delete();

            if ($request->wantsJson()) {
                return response()->json(['status' => 'removed']);
            }

            return back()->with('success', 'Item removed from wishlist');
        }

        Wishlist::create([
            'user_id' => $userId,
            'product_id' => $validated['product_id']
        ]);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'added']);
        }

        return back()->with('success', 'Item added to wishlist');
    }

    public function checkStatus(Request $request, $product_id)
    {
        if (auth()->check()) {
            $exists = Wishlist::where('user_id', auth()->id())
                ->where('product_id', $product_id)
                ->exists();

            return response()->json(['inWishlist' => $exists]);
        }

        return response()->json(['inWishlist' => false]);
    }

    // Add a new method to handle post-login wishlist actions
    public function handleAfterLogin(Request $request)
    {
        if (session()->has('wishlist_after_login')) {
            $pendingAction = session()->pull('wishlist_after_login');

            if ($pendingAction['action'] === 'toggle' && isset($pendingAction['product_id'])) {
                // Toggle the wishlist item
                $this->toggle(new Request(['product_id' => $pendingAction['product_id']]));
            }
        }

        // Redirect to intended page or home
        return redirect()->intended('/');
    }
}
