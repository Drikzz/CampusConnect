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

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users')->ignore($user->id),
                function ($attribute, $value, $fail) use ($user) {
                    // Check if username was changed within last 30 days
                    if (
                        $user->username_changed_at &&
                        $user->username_changed_at->addDays(30) > now() &&
                        $value !== $user->username
                    ) {
                        $daysLeft = now()->diffInDays($user->username_changed_at->addDays(30));
                        $fail("Username can only be changed once every 30 days. Please wait {$daysLeft} more days.");
                    }

                    // Check if username was recently used by someone else
                    $recentlyUsed = UsernameHistory::where('old_username', $value)
                        ->where('created_at', '>', now()->subDays(30))
                        ->exists();
                    if ($recentlyUsed) {
                        $fail('This username was recently in use and cannot be claimed yet.');
                    }
                }
            ],
            'phone' => ['required', 'string', 'regex:/^[0-9]{11}$/'],
            'wmsu_email' => ['required', 'email', 'regex:/^[a-zA-Z0-9._%+-]+@wmsu\.edu\.ph$/'],
        ]);

        // Handle username change
        if ($user->username !== $validated['username']) {
            UsernameHistory::create([
                'user_id' => $user->id,
                'old_username' => $user->username,
                'new_username' => $validated['username']
            ]);
            $user->username_changed_at = now();

            // Send email notification
            Mail::to($user->wmsu_email)->send(new UsernameChanged($user, $validated['username']));
        }

        // Handle phone verification
        if ($user->phone !== $validated['phone']) {
            $code = rand(100000, 999999);
            $user->phone_verification_code = $code;
            // Send SMS with verification code
            // SMS::send($validated['phone'], "Your verification code is: {$code}");
            return redirect()->back()->with('verify-phone', true);
        }

        // Handle email verification
        if ($user->wmsu_email !== $validated['wmsu_email']) {
            $code = Str::random(32);
            $user->email_verification_code = $code;
            $user->email_verification_code_expires_at = now()->addHours(24);
            Mail::to($validated['wmsu_email'])->send(new VerifyEmail($code));
            return redirect()->back()->with('verify-email', true);
        }

        $user->update($validated);
        return redirect()->back()->with('success', 'Profile updated successfully!');
    }

    public function verifyPhone(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);
        $user = Auth::user();

        if ($request->code === $user->phone_verification_code) {
            $user->phone_verified_at = now();
            $user->phone_verification_code = null;
            $user->save();
            return redirect()->back()->with('success', 'Phone number verified successfully!');
        }

        return redirect()->back()->withErrors(['code' => 'Invalid verification code']);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        $user = Auth::user();

        if (
            $request->code === $user->email_verification_code &&
            $user->email_verification_code_expires_at > now()
        ) {
            $user->email_verified_at = now();
            $user->email_verification_code = null;
            $user->email_verification_code_expires_at = null;
            $user->save();
            return redirect()->back()->with('success', 'Email verified successfully!');
        }

        return redirect()->back()->withErrors(['code' => 'Invalid or expired verification code']);
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
