<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

        $user = auth()->user();
        $user->is_seller = true;
        $user->seller_code = 'S' . str_pad($user->id, 5, '0', STR_PAD_LEFT);
        $user->save();

        // Redirect to main dashboard instead of seller index
        return redirect()->route('dashboard.profile')->with('toast', [
            'title' => 'Success!',
            'description' => 'Congratulations! You are now registered as a seller.',
            'variant' => 'default'
        ]);
    }

    private function uploadImage($request, $user, $userType, $validated)
    {
        // ...existing code...
    }
}
