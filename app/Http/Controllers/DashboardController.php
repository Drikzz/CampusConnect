<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\GradeLevel;
use App\Models\Location;
use App\Models\MeetupLocation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserType;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $stats = $this->getDashboardStats($user);

        // Load the user with their relationships
        $user->load(['department', 'userType']);

        // Get the grade level separately if needed
        $gradeLevel = null;
        if ($user->grade_level_id) {
            $gradeLevel = GradeLevel::find($user->grade_level_id);
        }

        return Inertia::render('Dashboard/Profile', [
            'user' => array_merge($user->toArray(), [
                'profile_picture' => $user->profile_picture
                    ? asset('storage/' . $user->profile_picture)
                    : null,
                'wmsu_id_front' => $user->wmsu_id_front
                    ? asset('storage/' . $user->wmsu_id_front)
                    : null,
                'wmsu_id_back' => $user->wmsu_id_back
                    ? asset('storage/' . $user->wmsu_id_back)
                    : null,
                'user_type_code' => $user->userType->code ?? null,
                'grade_level' => $gradeLevel,
            ]),
            'stats' => $stats,
            'departments' => Department::orderBy('name')->get(),
            'gradeLevels' => GradeLevel::orderBy('level')->get()
        ]);
    }

    public function profile()
    {
        return $this->index();
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();
            $userType = $user->userType; // Get the user type object

            if (!$userType) {
                throw new \Exception('User type not found');
            }

            // Store previous values in session before update
            session(['previous_user_state' => [
                'data' => $user->toArray(),
                'files' => [
                    'profile_picture' => $user->profile_picture,
                    'wmsu_id_front' => $user->wmsu_id_front,
                    'wmsu_id_back' => $user->wmsu_id_back,
                ]
            ]]);

            // Basic validation rules that apply to all users
            $rules = [
                'first_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'gender' => 'required|in:male,female,non-binary,prefer-not-to-say',
                'date_of_birth' => 'required|date',
                'phone' => ['required', 'string', 'regex:/^[0-9]{11}$/'],
                'username' => [
                    'required',
                    'string',
                    'max:255',
                    'unique:users,username,' . $user->id,
                ],
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ];

            // Add WMSU email validation based on user type
            if (in_array($userType->code, ['HS', 'COL', 'EMP', 'PG'])) {
                $rules['wmsu_email'] = [
                    'required',
                    'email',
                    'ends_with:wmsu.edu.ph',
                    'unique:users,wmsu_email,' . $user->id,
                ];
            } else {
                $rules['wmsu_email'] = [
                    'nullable',
                    'email',
                    'ends_with:wmsu.edu.ph',
                    'unique:users,wmsu_email,' . $user->id,
                ];
            }

            // Add WMSU ID validation based on user type
            if ($userType->code !== 'ALM') {
                $hasExistingIdFront = !empty($user->wmsu_id_front);
                $hasExistingIdBack = !empty($user->wmsu_id_back);

                $rules['wmsu_id_front'] = $hasExistingIdFront
                    ? 'nullable|image|mimes:jpeg,png,jpg|max:2048'
                    : 'required|image|mimes:jpeg,png,jpg|max:2048';

                $rules['wmsu_id_back'] = $hasExistingIdBack
                    ? 'nullable|image|mimes:jpeg,png,jpg|max:2048'
                    : 'required|image|mimes:jpeg,png,jpg|max:2048';
            } else {
                $rules['wmsu_id_front'] = 'nullable|image|mimes:jpeg,png,jpg|max:2048';
                $rules['wmsu_id_back'] = 'nullable|image|mimes:jpeg,png,jpg|max:2048';
            }

            // Add conditional validation based on user type
            if ($userType->code === 'HS') {
                $rules['grade_level_id'] = 'required|exists:grade_levels,id';
            } elseif (in_array($userType->code, ['COL', 'PG'])) {
                $rules['wmsu_dept_id'] = 'required|exists:departments,id';
            }

            $validated = $request->validate($rules);

            // Start with the existing data
            $dataToUpdate = $validated;

            // Handle profile picture
            if ($request->hasFile('profile_picture')) {
                if ($user->profile_picture) {
                    Storage::disk('public')->delete($user->profile_picture);
                }
                $dataToUpdate['profile_picture'] = $request->file('profile_picture')
                    ->store($userType->code . '/profile_pictures', 'public');
            }

            // Handle WMSU ID front
            if ($request->hasFile('wmsu_id_front')) {
                if ($user->wmsu_id_front) {
                    Storage::disk('public')->delete($user->wmsu_id_front);
                }
                $dataToUpdate['wmsu_id_front'] = $request->file('wmsu_id_front')
                    ->store($userType->code . '/id_front', 'public');
            }

            // Handle WMSU ID back
            if ($request->hasFile('wmsu_id_back')) {
                if ($user->wmsu_id_back) {
                    Storage::disk('public')->delete($user->wmsu_id_back);
                }
                $dataToUpdate['wmsu_id_back'] = $request->file('wmsu_id_back')
                    ->store($userType->code . '/id_back', 'public');
            }

            // Remove file fields if they're not being updated
            if (!$request->hasFile('profile_picture')) {
                unset($dataToUpdate['profile_picture']);
            }
            if (!$request->hasFile('wmsu_id_front')) {
                unset($dataToUpdate['wmsu_id_front']);
            }
            if (!$request->hasFile('wmsu_id_back')) {
                unset($dataToUpdate['wmsu_id_back']);
            }

            // Update user with validated data
            $user->update($dataToUpdate);

            return back()->with('success', 'Profile updated successfully')
                ->with('toast', [
                    'variant' => 'default',
                    'title' => 'Success!',
                    'description' => 'Your profile has been updated successfully. You can undo this change within the next 30 seconds.'
                ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            Log::error('Profile update error:', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTrace()
            ]);

            return back()->withErrors(['error' => 'Failed to update profile'])
                ->with('toast', [
                    'variant' => 'destructive',
                    'title' => 'Error!',
                    'description' => 'There was a problem updating your profile. Please try again.'
                ]);
        }
    }

    public function revertProfileUpdate()
    {
        try {
            $user = Auth::user();
            $previousState = session('previous_user_state');

            if (!$previousState) {
                return back()->with('toast', [
                    'variant' => 'destructive',
                    'title' => 'Error!',
                    'description' => 'No previous state found to revert to.'
                ]);
            }

            // Store current files before reverting
            $currentFiles = [
                'profile_picture' => $user->profile_picture,
                'wmsu_id_front' => $user->wmsu_id_front,
                'wmsu_id_back' => $user->wmsu_id_back,
            ];

            // Revert the database changes
            $previousData = $previousState['data'];
            unset($previousData['updated_at']); // Don't revert the timestamp
            $user->update($previousData);

            // Handle file reversions
            $previousFiles = $previousState['files'];
            foreach ($currentFiles as $field => $currentPath) {
                if ($currentPath && $currentPath !== $previousFiles[$field]) {
                    // Delete the current file
                    Storage::disk('public')->delete($currentPath);
                }
            }

            // Clear the stored previous state
            session()->forget('previous_user_state');

            return back()->with('toast', [
                'variant' => 'default',
                'title' => 'Success!',
                'description' => 'Profile changes have been reverted successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Profile revert error:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTrace()
            ]);

            return back()->with('toast', [
                'variant' => 'destructive',
                'title' => 'Error!',
                'description' => 'Failed to revert profile changes.'
            ]);
        }
    }

    public function storeMeetupLocation(Request $request)
    {
        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'location_id' => 'required|exists:locations,id',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'available_from' => 'required',
                'available_until' => 'required|after:available_from',
                'available_days' => 'required|array',
                'available_days.*' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
                'max_daily_meetups' => 'required|integer|min:1|max:50',
                'is_default' => 'nullable|boolean',
            ]);

            $user = auth()->user();
            $meetupLocation = $user->meetupLocations()->create($validated);

            return back()->with('success', 'Meetup location added successfully');
        } catch (\Exception $e) {
            Log::error('Error storing meetup location: ' . $e->getMessage());
            return back()->with('error', 'Failed to add meetup location: ' . $e->getMessage());
        }
    }

    public function updateMeetupLocation(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'location_id' => 'required|exists:locations,id',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'available_from' => 'required',
                'available_until' => 'required|after:available_from',
                'available_days' => 'required|array',
                'available_days.*' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
                'max_daily_meetups' => 'required|integer|min:1|max:50',
                'is_default' => 'nullable|boolean',
            ]);

            $user = auth()->user();
            $meetupLocation = $user->meetupLocations()->findOrFail($id);
            $meetupLocation->update($validated);

            return back()->with('success', 'Meetup location updated successfully');
        } catch (\Exception $e) {
            Log::error('Error updating meetup location: ' . $e->getMessage());
            return back()->with('error', 'Failed to update meetup location: ' . $e->getMessage());
        }
    }

    public function deleteMeetupLocation($id)
    {
        try {
            $user = auth()->user();
            $location = $user->meetupLocations()->findOrFail($id);
            $wasDefault = $location->is_default;

            $location->delete();

            // If we deleted the default location, make another one default
            if ($wasDefault) {
                $newDefault = $user->meetupLocations()->first();
                if ($newDefault) {
                    $newDefault->update(['is_default' => true]);
                }
            }

            return back()->with('success', 'Meetup location deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting meetup location: ' . $e->getMessage());
            return back()->with('error', 'Error deleting meetup location: ' . $e->getMessage());
        }
    }

    public function wishlist()
    {
        $user = auth()->user();
        $stats = $this->getDashboardStats($user);

        $wishlists = Wishlist::where('user_id', auth()->id())
            ->with('product')
            ->paginate(10);

        return Inertia::render('Dashboard/Wishlist', [
            'user' => $user,
            'stats' => $stats,
            'wishlists' => $wishlists->items(),
            'links' => [
                'first' => $wishlists->url(1),
                'last' => $wishlists->url($wishlists->lastPage()),
                'prev' => $wishlists->previousPageUrl(),
                'next' => $wishlists->nextPageUrl(),
                'pages' => collect($wishlists->links()->elements[0])->map(function ($url, $page) use ($wishlists) {
                    return [
                        'url' => $url,
                        'label' => $page,
                        'active' => $page === $wishlists->currentPage()
                    ];
                })
            ]
        ]);
    }

    public function orders()
    {
        $user = auth()->user();
        $stats = $this->getDashboardStats($user);

        $orders = Order::where('buyer_id', auth()->id())
            ->with(['items.product'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Dashboard/Orders', [
            'user' => $user,
            'stats' => $stats,
            'orders' => [
                'data' => $orders->items(),
                'first_page_url' => $orders->url(1),
                'last_page_url' => $orders->url($orders->lastPage()),
                'prev_page_url' => $orders->previousPageUrl(),
                'next_page_url' => $orders->nextPageUrl(),
                'links' => collect($orders->links()->elements[0])->map(function ($url, $page) use ($orders) {
                    return [
                        'url' => $url,
                        'label' => $page,
                        'active' => $page === $orders->currentPage()
                    ];
                })
            ]
        ]);
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
        $user = auth()->user();

        // Get seller statistics
        $totalSales = 0;
        $activeProducts = 0;
        $pendingOrders = 0;
        $products = collect(); // Initialize empty collection for products
        $categories = \App\Models\Category::all(); // Add categories for the form

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

            // Get paginated products
            $products = Product::where('seller_code', $user->seller_code)
                ->with(['category'])
                ->latest()
                ->paginate(10);
        }

        return view('dashboard.seller.products', compact(
            'totalSales',
            'activeProducts',
            'pendingOrders',
            'products',
            'categories'
        ));
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
        $user = auth()->user();
        $stats = $this->getDashboardStats($user);

        return Inertia::render('Dashboard/MeetupLocations', [
            'user' => $user,
            'stats' => $stats,
            'locations' => $user->meetupLocations()
                ->with('location')
                ->orderByDesc('is_default')
                ->get()
        ]);
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

    private function getDashboardStats($user)
    {
        $stats = [
            'totalOrders' => Order::where('buyer_id', $user->id)->count(),
            'activeOrders' => Order::where('buyer_id', $user->id)
                ->whereNotIn('status', ['Completed', 'Cancelled'])
                ->count(),
            'wishlistCount' => Wishlist::where('user_id', $user->id)->count(),
            'totalSales' => 0,
            'activeProducts' => 0,
            'pendingOrders' => 0
        ];

        if ($user->is_seller) {
            $stats['totalSales'] = OrderItem::where('seller_code', $user->seller_code)
                ->whereHas('order', function ($query) {
                    $query->where('status', 'Completed');
                })
                ->sum('subtotal');

            $stats['activeProducts'] = Product::where('seller_code', $user->seller_code)
                ->where('status', 'Active')
                ->count();

            $stats['pendingOrders'] = OrderItem::where('seller_code', $user->seller_code)
                ->whereHas('order', function ($query) {
                    $query->where('status', 'Pending');
                })->count();
        }

        return $stats;
    }
}
