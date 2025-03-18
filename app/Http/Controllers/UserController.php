<?php

namespace App\Http\Controllers;

use App\Models\SellerWallet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\SellerRegistrationConfirmation;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
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
                }
            ],
            'phone' => ['required', 'string', 'regex:/^[0-9]{11}$/'],
        ]);

        // Update username_changed_at if username is changing
        if ($user->username !== $validated['username']) {
            $user->username_changed_at = now();
        }

        // Handle phone verification
        if ($user->phone !== $validated['phone']) {
            $code = rand(100000, 999999);
            $user->phone_verification_code = $code;
            // Send SMS with verification code
            // SMS::send($validated['phone'], "Your verification code is: {$code}");
            return redirect()->back()->with('verify-phone', true);
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

    public function is_seller()
    {
        $user = Auth::user();
        $user->update([
            'is_seller' => true,
            'seller_code' => strtoupper(uniqid())
        ]);
        return redirect()->route('seller.index')->with('success', 'You are now a verified seller');
    }

    public function showBecomeSeller()
    {
        return inertia('Dashboard/BecomeSeller', [
            'user' => auth()->user(),
            'stats' => [
                'totalOrders' => 0,
                'wishlistCount' => 0,
                'activeOrders' => 0
            ]
        ]);
    }

    public function becomeSeller(Request $request)
    {
        $request->validate([
            'acceptTerms' => 'required|accepted'
        ]);

        // Remove the try-catch blocks and perform operations directly
        $user = auth()->user();
        $user->is_seller = true;
        $user->seller_code = 'S' . str_pad($user->id, 5, '0', STR_PAD_LEFT);
        $user->save();

        // Create inactive wallet automatically
        SellerWallet::create([
            'user_id' => $user->id,
            'seller_code' => $user->seller_code,
            'balance' => 0.00,
            'is_activated' => false,
            'status' => 'pending'
        ]);

        // Send email directly without try-catch
        Mail::to($user->wmsu_email)->send(new SellerRegistrationConfirmation($user));

        return redirect()->route('dashboard.profile')->with('toast', [
            'title' => 'Success!',
            'description' => 'Your seller account has been created. Please set up your wallet to start selling.',
            'variant' => 'default'
        ]);
    }

    private function uploadImage($request, $user, $userType, $validated)
    {
        // ...existing code...
    }
}
