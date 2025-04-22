<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserBan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminUserBanController extends Controller
{
    /**
     * Ban a user
     */
    public function banUser(Request $request, $userId)
    {
        try {
            $user = User::findOrFail($userId);
            
            // Prevent self-banning
            if ($user->id === Auth::id()) {
                return back()->with('error', 'You cannot ban yourself.');
            }

            // Validate the request with updated rules
            $validated = $request->validate([
                'reason' => 'required|string|max:500',
                'duration' => 'required|string',
                'custom_days' => 'nullable|integer|min:1|max:365',
            ]);

            // Calculate expiry date based on duration
            $expiresAt = null;
            $isPermanent = false;

            if ($validated['duration'] === 'permanent') {
                $isPermanent = true;
            } elseif ($validated['duration'] === 'custom') {
                // Handle custom duration
                if (empty($validated['custom_days'])) {
                    return back()->with('error', 'Custom duration requires specifying the number of days.');
                }
                $expiresAt = now()->addDays((int)$validated['custom_days']);
            } else {
                // Handle predefined durations (1_day, 7_days, etc.)
                $days = explode('_', $validated['duration'])[0];
                $expiresAt = now()->addDays((int)$days);
            }

            // Create the ban record
            UserBan::create([
                'user_id' => $user->id,
                'banned_by' => Auth::id(),
                'reason' => $validated['reason'],
                'expires_at' => $expiresAt,
                'is_permanent' => $isPermanent
            ]);

            return back()->with('success', "User {$user->username} has been banned.");
        } catch (\Exception $e) {
            Log::error('Error banning user: ' . $e->getMessage());
            return back()->with('error', 'Failed to ban user: ' . $e->getMessage());
        }
    }

    /**
     * Unban a user
     */
    public function unbanUser($userId)
    {
        try {
            $user = User::findOrFail($userId);
            
            // Delete all active bans for this user
            UserBan::where('user_id', $user->id)
                ->where(function($query) {
                    $query->where('is_permanent', true)
                          ->orWhere('expires_at', '>', now());
                })
                ->delete();
                
            return back()->with('success', "User {$user->username} has been unbanned.");
        } catch (\Exception $e) {
            Log::error('Error unbanning user: ' . $e->getMessage());
            return back()->with('error', 'Failed to unban user: ' . $e->getMessage());
        }
    }

    /**
     * Get ban status for a specific user
     */
    public function getBanStatus($userId)
    {
        $user = User::findOrFail($userId);
        $activeBan = UserBan::where('user_id', $user->id)
            ->where(function($query) {
                $query->where('is_permanent', true)
                      ->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->first();
            
        return response()->json([
            'is_banned' => !is_null($activeBan),
            'ban_details' => $activeBan
        ]);
    }

    /**
     * Check if user has active ban
     */
    public function checkIsBanned($userId)
    {
        $user = User::findOrFail($userId);
        $isBanned = $user->userBans()
            ->where(function($query) {
                $query->where('is_permanent', true)
                      ->orWhere('expires_at', '>', now());
            })
            ->exists();
            
        return $isBanned;
    }
}
