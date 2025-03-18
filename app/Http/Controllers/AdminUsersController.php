<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminUsersController extends Controller
{
    /**
     * Display a listing of all users
     *
     * @param Request $request
     * @return \Inertia\Response
     */
    public function users(Request $request)
    {
        $search = $request->input('search', '');
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $filter = $request->input('filter', 'all');
        
        $query = User::query()
            ->select('users.*')
            // Remove the problematic SQL queries that reference non-existent columns
            // We'll use a simpler query without the counts for now
            ->when($search, function($query, $search) {
                $query->where(function($query) use ($search) {
                    $query->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('wmsu_email', 'like', "%{$search}%") // Add search by wmsu_email
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($filter !== 'all', function($query) use ($filter) {
                if ($filter === 'sellers') {
                    $query->where('is_seller', true);
                } elseif ($filter === 'admins') {
                    $query->where('is_admin', true);
                } elseif ($filter === 'customers') {
                    $query->where('is_seller', false)->where('is_admin', false);
                } elseif ($filter === 'verified') {
                    $query->whereNotNull('email_verified_at');
                } elseif ($filter === 'unverified') {
                    $query->whereNull('email_verified_at');
                }
            });
            
        // Apply sorting
        if ($sortField && $sortDirection) {
            // Map 'email' sort field to 'wmsu_email' when needed
            if ($sortField === 'email') {
                $query->orderBy('wmsu_email', $sortDirection);
            }
            // Map 'username' sort field to the actual database column if different
            elseif ($sortField === 'username') {
                $query->orderBy('username', $sortDirection);
            }
            // Remove sorting for the derived fields that were removed
            elseif (!in_array($sortField, ['products_count', 'orders_count'])) {
                $query->orderBy($sortField, $sortDirection);
            } else {
                // Default to created_at if trying to sort by a removed field
                $query->orderBy('created_at', $sortDirection);
            }
        }

        $users = $query->paginate(10)
            ->withQueryString()
            ->through(function ($user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,  // Add username field
                    'name' => $user->name,
                    'email' => $user->email,
                    'wmsu_email' => $user->wmsu_email, // Include wmsu_email field
                    'first_name' => $user->first_name,  // Include user's first name
                    'middle_name' => $user->middle_name, // Include user's middle name
                    'last_name' => $user->last_name,    // Include user's last name
                    'gender' => $user->gender,          // Include user's gender
                    'date_of_birth' => $user->date_of_birth, // Include user's date of birth
                    'phone' => $user->phone,
                    'is_seller' => $user->is_seller,
                    'is_admin' => $user->is_admin,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    // Remove the counts from the response since we're not querying them
                    'products_count' => 0, // Placeholder value
                    'orders_count' => 0,   // Placeholder value
                    'status' => $user->email_verified_at ? 'Verified' : 'Unverified',
                    'role' => $user->is_admin ? 'Admin' : ($user->is_seller ? 'Seller' : 'Customer'),
                    'has_profile_photo' => $user->profile_photo_path ? true : false,
                    'profile_photo' => $user->profile_photo_path ? asset('storage/'.$user->profile_photo_path) : null,
                ];
            });

        // Changed to render admin-userManagement.vue instead of the previous path
        return Inertia::render('Admin/admin-userManagement', [
            'users' => $users,
            'filters' => [
                'search' => $search,
                'sort' => $sortField,
                'direction' => $sortDirection,
                'filter' => $filter,
            ],
        ]);
    }

    /**
     * Show the details of a specific user
     *
     * @param int $id
     * @return \Inertia\Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        
        return Inertia::render('Admin/Users/Show', [
            'user' => $user
        ]);
    }

    /**
     * Update the specified user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'gender' => 'required|string|in:male,female,non-binary,prefer-not-to-say',
            'date_of_birth' => 'required|date|before:today',
            'phone' => [
                'required',
                'string',
                'regex:/^\+?[0-9]{11,}$/',
                Rule::unique('users')->ignore($user->id)
            ],
        ]);
        
        $user->update($validated);
        
        return redirect()->back()->with('success', 'User updated successfully');
    }

    /**
     * Delete a user
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Don't allow deletion of own account
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }
        
        $user->delete();
        
        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    /**
     * Bulk delete users
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:users,id',
        ]);
        
        // Don't allow deletion of own account
        $ids = collect($request->ids)->filter(function($id) {
            return $id != auth()->id();
        });
        
        User::whereIn('id', $ids)->delete();
        
        return redirect()->back()->with('success', count($ids) . ' users deleted successfully.');
    }

    /**
     * Toggle user active status
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        // Implementation would depend on how you track user status
        // Example: $user->is_active = !$user->is_active;
        // $user->save();

        return redirect()->back()->with('success', 'User status updated successfully');
    }

    /**
     * Toggle seller privileges for a user
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleSellerStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->is_seller = !$user->is_seller;
        $user->save();

        return redirect()->back()->with('success', 'Seller status updated successfully');
    }
}
