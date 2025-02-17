<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserType;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Initialize all variables with default values
        $totalOrders = Order::where('buyer_id', $user->id)->count();
        $activeOrders = Order::where('buyer_id', $user->id)
            ->whereNotIn('status', ['Completed', 'Cancelled'])
            ->count();
        $wishlistCount = Wishlist::where('user_id', $user->id)->count();
        $totalSales = 0; // Initialize with 0 for non-sellers
        $activeProducts = 0;
        $pendingOrders = 0;

        // Additional data for sellers
        if ($user->is_seller) {
            $totalSales = OrderItem::where('seller_code', $user->seller_code)
                ->whereHas('order', function ($query) {
                    $query->where('status', 'Completed');
                })
                ->sum('subtotal');

            $activeProducts = Product::where('seller_code', $user->seller_code)
                ->where('status', 'Active')
                ->count();

            $pendingOrders = OrderItem::where('seller_code', $user->seller_code)
                ->whereHas('order', function ($query) {
                    $query->where('status', 'Pending');
                })->count();
        }

        return view('dashboard.dashboard', compact(
            'totalOrders',
            'activeOrders',
            'wishlistCount',
            'totalSales',
            'activeProducts',
            'pendingOrders'
        ));
    }

    public function profile()
    {
        $user = auth()->user();
        $is_student = $user->user_type_id == 2;
        $is_seller = $user->is_seller;
        $profile_picture = $user->profile_picture;
        $first_name = $user->first_name;
        $last_name = $user->last_name;
        $username = $user->username;
        $email = $user->email;
        $wmsu_id_front = $user->wmsu_id_front;
        $wmsu_id_back = $user->wmsu_id_back;

        return view('dashboard.profile', compact(
            'user',
            'is_student',
            'is_seller',
            'profile_picture',
            'first_name',
            'last_name',
            'username',
            'email',
            'wmsu_id_front',
            'wmsu_id_back'
        ));
    }

    public function wishlist()
    {
        $wishlists = Wishlist::where('user_id', auth()->id())
            ->with('product')
            ->paginate(10);

        return view('dashboard.wishlist', compact('wishlists'));
    }

    public function orders()
    {
        $orders = Order::where('buyer_id', auth()->id())
            ->with(['items.product'])
            ->latest()
            ->paginate(10);

        return view('dashboard.orders', compact('orders'));
    }

    public function favorites()
    {
        return view('components.myFavorites');
    }

    public function sell()
    {
        return view('components.sell');
    }

    public function terms()
    {
        return view('buyer.terms');
    }

    public function reviews()
    {
        return $this->index();
    }

    public function products()
    {
        return $this->index();
    }

    public function analytics()
    {
        return $this->index();
    }

    public function removeFromWishlist(Wishlist $wishlist)
    {
        if ($wishlist->user_id !== auth()->id()) {
            return back()->with('error', 'Unauthorized action');
        }

        $wishlist->delete();
        return back()->with('success', 'Item removed from wishlist');
    }

    public function address()
    {
        return view('dashboard.address', [
            'user' => auth()->user()
        ]);
    }

    public function showSellerRegistration()
    {
        if (auth()->user()->is_seller) {
            return redirect()->route('dashboard');
        }
        return view('dashboard.become-seller');
    }

    public function showSellerTerms()
    {
        if (auth()->user()->is_seller) {
            return redirect()->route('dashboard');
        }
        return view('dashboard.seller.terms');
    }

    public function acceptSellerTerms(Request $request)
    {
        $request->validate([
            'acceptTerms' => 'required|accepted'
        ]);

        $user = auth()->user();
        $user->is_seller = true;
        $user->seller_code = 'S' . str_pad($user->id, 5, '0', STR_PAD_LEFT);
        $user->save();

        return redirect()->route('dashboard')
            ->with('success', 'Congratulations! You are now registered as a seller.');
    }

    private function uploadImage($request, $user, $userType, $validated)
    {
        if ($request->hasFile('profile_picture')) {
            $oldPicture = $user->profile_picture;
            $path = $request->file('profile_picture')->store($userType . '/profile_pictures', 'public');
            $validated['profile_picture'] = $path;

            if ($oldPicture) {
                Storage::disk('public')->delete($oldPicture);
            }
        }

        // Handle WMSU ID front
        if ($request->hasFile('wmsu_id_front')) {
            $oldFront = $user->wmsu_id_front;
            $path = $request->file('wmsu_id_front')->store($userType . '/id_front', 'public');
            $validated['wmsu_id_front'] = $path;

            if (isset($oldFront)) {
                Storage::disk('public')->delete($oldFront);
            }
        }

        // Handle WMSU ID back
        if ($request->hasFile('wmsu_id_back')) {
            $oldBack = $user->wmsu_id_back;
            $path = $request->file('wmsu_id_back')->store($userType . '/id_back', 'public');
            $validated['wmsu_id_back'] = $path;

            if (isset($oldBack)) {
                Storage::disk('public')->delete($oldBack);
            }
        }

        return $validated;
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'wmsu_id_front' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'wmsu_id_back' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            if ($user->user_type_id == 2) {
                $userType = 'college';
                $validated = $this->uploadImage($request, $user, $userType, $validated);
            }

            $user->update($validated);
            return back()->with('success', 'Profile updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update profile: ' . $e->getMessage());
        }
    }
}
