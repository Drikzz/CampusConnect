<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SellerController;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

// Public routes should be at the top, before any middleware groups
Route::get('/', [ProductController::class, 'welcome'])->name('index');

// Route::inertia('/about', 'About', ['user' => 'About Us']);

// Update the products routes
Route::get('/products', [ProductController::class, 'index'])->name('products');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');

Route::middleware('guest')->group(function () {
    // This is the correct route we want to use
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware(['web'])->group(function () {
        Route::get('/register', [AuthController::class, 'showPersonalInfoForm'])->name('register.personal-info');
        Route::post('/register/step1', [AuthController::class, 'processPersonalInfo'])->name('register.step1');
        Route::get('/register/details', [AuthController::class, 'showDetailsForm'])->name('register.details');
        Route::post('/register/complete', [AuthController::class, 'completeRegistration'])->name('register.complete');
    });
});


Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Combined Dashboard and Seller routes
    Route::middleware('auth')->prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [DashboardController::class, 'profile'])->name('dashboard.profile');
        Route::get('/orders', [DashboardController::class, 'orders'])->name('dashboard.orders');
        Route::get('/wishlist', [DashboardController::class, 'wishlist'])->name('dashboard.wishlist');
        Route::get('/meetup-locations', [DashboardController::class, 'address'])->name('dashboard.address');
        Route::get('/reviews', [DashboardController::class, 'reviews'])->name('dashboard.reviews');

        // Profile update route
        Route::post('/profile/update', [DashboardController::class, 'updateProfile'])->name('profile.update');
        Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancelOrder'])->name('orders.cancel');
        Route::delete('/wishlist/{wishlist}', [DashboardController::class, 'removeFromWishlist'])->name('wishlist.remove');

        // Meetup locations CRUD
        Route::post('/meetup-locations', [DashboardController::class, 'storeMeetupLocation'])->name('meetup-locations.store');
        Route::put('/meetup-locations/{id}', [DashboardController::class, 'updateMeetupLocation'])->name('meetup-locations.update');
        Route::delete('/meetup-locations/{id}', [DashboardController::class, 'deleteMeetupLocation'])->name('meetup-locations.destroy');

        // Add new seller registration routes
        Route::get('/become-seller', [UserController::class, 'showBecomeSeller'])
            ->name('dashboard.become-seller');
        Route::post('/become-seller', [UserController::class, 'becomeSeller'])
            ->name('dashboard.seller.become');

        Route::post('/seller/terms', [DashboardController::class, 'acceptSellerTerms'])->name('dashboard.seller.terms.accept');

        // Remove or comment out these old routes
        // Route::get('/seller/terms', [DashboardController::class, 'showSellerTerms'])->name('dashboard.seller.terms');

        Route::post('/meetup-locations', [DashboardController::class, 'storeMeetupLocation'])->name('meetup-locations.store');
        Route::put('/meetup-locations/{id}', [DashboardController::class, 'updateMeetupLocation'])->name('meetup-locations.update');
        Route::delete('/meetup-locations/{id}', [DashboardController::class, 'deleteMeetupLocation'])->name('meetup-locations.destroy');

        // Product management routes
        Route::prefix('seller')->group(function () {
            // Seller dashboard routes
            Route::get('/', [SellerController::class, 'index'])->name('seller.index');
            Route::get('/products', [SellerController::class, 'products'])->name('seller.products');
            Route::get('/orders', [SellerController::class, 'orders'])->name('seller.orders');
            Route::get('/analytics', [SellerController::class, 'analytics'])->name('seller.analytics');
            Route::get('/orders/{order}', [SellerController::class, 'showOrder'])->name('seller.orders.show');
            Route::put('/orders/{order}/status', [SellerController::class, 'updateOrderStatus'])->name('seller.orders.update-status');
            Route::post('/orders/{order}/schedule-meetup', [SellerController::class, 'scheduleMeetup'])->name('seller.orders.schedule-meetup');

            // Product CRUD routes
            Route::post('/products', [SellerController::class, 'store'])->name('dashboard.seller.products.store');
            Route::get('/products/{id}/edit', [SellerController::class, 'edit'])->name('dashboard.seller.products.edit');
            Route::put('/products/{id}', [SellerController::class, 'update'])->name('dashboard.seller.products.update');
            Route::delete('/products/{id}', [SellerController::class, 'destroy'])->name('dashboard.seller.products.destroy');
            Route::post('/products/{product}/restore', [SellerController::class, 'restore'])->name('dashboard.seller.products.restore');
            Route::delete('/products/{product}/force-delete', [SellerController::class, 'forceDelete'])->name('dashboard.seller.products.force-delete');

            // Add meetup location routes inside seller prefix
            Route::post('/meetup-locations', [DashboardController::class, 'storeMeetupLocation'])
                ->name('meetup-locations.store');
            Route::put('/meetup-locations/{id}', [DashboardController::class, 'updateMeetupLocation'])
                ->name('meetup-locations.update');
            Route::delete('/meetup-locations/{id}', [DashboardController::class, 'deleteMeetupLocation'])
                ->name('meetup-locations.destroy');
        });
    });

    //checkout routes
    Route::get('/products/prod/{id}/summary', [CheckoutController::class, 'summary'])->name('summary');
    Route::post('/checkout/process', [CheckoutController::class, 'checkout'])->name('checkout.process');

    Route::post('/profile/revert', [DashboardController::class, 'revertProfileUpdate'])
        ->name('profile.revert');
});

// Admin Authentication Routes
// Route::get('/admin/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
// Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.submit');

// Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

// Admin Dashboard Route
// Route::get('/admin/dashboard', function () {
//     return view('admin.admin-dashboard');
// })->name('admin.dashboard');

// Route::get('/admin/dashboard2', [AdminController::class, 'dashboard2'])->name('admin-dashboard2');
// Route::get('/admin/userManagement', [AdminController::class, 'userManagement'])->name('admin-userManagement');
// Route::get('/admin/userManagement/create', [AdminController::class, 'create'])->name('admin-userManagement.create');
// Route::post('/admin/userManagement', [AdminController::class, 'store'])->name('admin-userManagement.store');
// Route::get('/admin/userManagement/{user}/edit', [AdminController::class, 'edit'])->name('admin-userManagement.edit');
// Route::put('/admin/userManagement/{user}', [AdminController::class, 'update'])->name('admin-userManagement.update');
// Route::delete('/admin/userManagement/{user}', [AdminController::class, 'destroy'])->name('admin-userManagement.destroy');
// Route::get('/admin/userManagement/{user}', [AdminController::class, 'show'])->name('admin-userManagement.show');

// Route::get('/admin/sales', function () {
//     return view('admin.admin-sales');
// })->name('admin.sales');

// Route::get('/admin/transactions', function () {
//     return view('admin.admin-transactions');
// })->name('admin.transactions');

// Route::get('/admin/transactions', [AdminController::class, 'transactions'])->name('admin.transactions');

// Route::get('/admin/users', function () {
//     return view('admin.admin-userManagement');
// })->name('admin.users');

// Route::get('/admin/reports', function () {
//     return view('admin.admin-reportManagement');
// })->name('admin.reports');


// Route::get('/admin/funds', function () {
//     return view('admin.admin-fundManagement');
// })->name('admin.funds');

// Route::get('/admin/product-management', [AdminController::class, 'productManagement'])->name('admin-productManagement');

// PLS DON'T DELETE THIS CODE FOR A WHILE
// Protected Admin Routes
// Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
//     // Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
//     // Route::get('/sales', [AdminController::class, 'sales'])->name('admin.sales');
//     // Route::get('/transactions', [AdminController::class, 'transactions'])->name('admin.transactions');
//     // Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
//     // Route::get('/reports', [AdminController::class, 'reports'])->name('admin.reports');
//     Route::get('/products', [AdminController::class, 'products'])->name('admin.products');
//     Route::get('/funds', [AdminController::class, 'funds'])->name('admin.funds');
// });

// Route::view('/admin/sales', 'admin.admin-sales')->name('adminsales');

// Route::view('/admin/products', 'admin.admin-productManagement')->name('admin-product-management');
// Route::view('/admin/userManagement', 'admin.admin-userManagement')->name('admin-userManagement');
// Route::view('/admin/funds', 'admin.admin-fundManagement')->name('admin-funds');

// Route::view('/Adminside-userprofile', 'admin.adminside-userprofile  ')->name('admin-userManagement');
// Route::view('/Admin-transactions', 'admin.admin-transactions')->name('admin-transactions');

// Route::view('/Admin-user-approve', 'admin.admin-user-approved')->name('admin-user-approved');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard/Profile', [
            'user' => Auth::user(),
            'stats' => [
                'totalOrders' => 0, // Add your stats logic here
                'wishlistCount' => 0,
                'activeOrders' => 0,
            ]
        ]);
    })->name('dashboard');

    Route::get('/dashboard/meetup-locations', function () {
        return Inertia::render('Dashboard/MeetupLocations', [
            'user' => Auth::user(),
            'stats' => [
                'totalOrders' => 0, // Add your stats logic here
                'wishlistCount' => 0,
                'activeOrders' => 0,
            ],
            'locations' => [] // Add your locations data here
        ]);
    })->name('dashboard.address');
});

Route::fallback(function () {
    return Inertia::render('404');
});
