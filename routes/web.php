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

// Public routes should be at the top, before any middleware groups
Route::get('/', [ProductController::class, 'welcome'])->name('index');
Route::get('/products', [ProductController::class, 'index'])->name('products');
Route::get('/products/{id}', [ProductController::class, 'product_details'])->name('prod.details');
Route::get('/trade', [ProductController::class, 'trade'])->name('trade');

// Authentication routes
Route::view('/login', 'auth.login')->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Combined Dashboard and Seller routes
    Route::middleware('auth')->prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [DashboardController::class, 'profile'])->name('dashboard.profile');
        Route::get('/orders', [DashboardController::class, 'orders'])->name('dashboard.orders');
        Route::get('/wishlist', [DashboardController::class, 'wishlist'])->name('dashboard.wishlist');
        Route::get('/address', [DashboardController::class, 'address'])->name('dashboard.address');
        Route::get('/reviews', [DashboardController::class, 'reviews'])->name('dashboard.reviews');

        // Profile update route
        Route::post('/profile/update', [DashboardController::class, 'updateProfile'])->name('profile.update');
        Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancelOrder'])->name('orders.cancel');
        Route::delete('/wishlist/{wishlist}', [DashboardController::class, 'removeFromWishlist'])->name('wishlist.remove');

        // Add new seller registration routes
        Route::get('/become-seller', [DashboardController::class, 'showSellerRegistration'])->name('dashboard.become-seller');
        Route::get('/seller/terms', [DashboardController::class, 'showSellerTerms'])->name('dashboard.seller.terms');
        Route::post('/seller/terms', [DashboardController::class, 'acceptSellerTerms'])->name('dashboard.seller.terms.accept');

        Route::post('/meetup-locations', [DashboardController::class, 'storeMeetupLocation'])->name('meetup-locations.store');
        Route::put('/meetup-locations/{id}', [DashboardController::class, 'updateMeetupLocation'])->name('meetup-locations.update');
        Route::delete('/meetup-locations/{id}', [DashboardController::class, 'deleteMeetupLocation'])->name('meetup-locations.delete');
    });

    //checkout routes
    Route::get('/products/prod/{id}/summary', [CheckoutController::class, 'summary'])->name('summary');
    Route::post('/checkout/process', [CheckoutController::class, 'checkout'])->name('checkout.process');
});

// Seller routes should be last
Route::middleware(['auth', 'seller'])->prefix('dashboard/seller')->name('dashboard.seller.')->group(function () {
    // Dashboard & Analytics
    Route::get('/', [SellerController::class, 'index'])->name('dashboard');
    Route::get('/analytics', [SellerController::class, 'analytics'])->name('analytics');

    // Products Management (these won't conflict with public routes now)
    Route::get('/manage-products', [SellerController::class, 'products'])->name('products.index');
    Route::post('/manage-products', [SellerController::class, 'store'])->name('products.store');
    Route::get('/manage-products/{product}/edit', [SellerController::class, 'edit'])->name('products.edit');
    Route::put('/manage-products/{product}', [SellerController::class, 'update'])->name('products.update');
    Route::delete('/manage-products/{product}', [SellerController::class, 'destroy'])->name('products.destroy');

    // Orders Management
    Route::get('/orders', [SellerController::class, 'orders'])->name('orders');
    Route::get('/orders/{order}', [SellerController::class, 'showOrder'])->name('orders.show');
    Route::put('/orders/{order}/status', [SellerController::class, 'updateOrderStatus'])->name('orders.update-status');
    Route::post('/orders/{order}/complete', [SellerController::class, 'completeOrder'])->name('orders.complete');

    // Categories
    Route::get('/categories', [SellerController::class, 'categories'])->name('categories');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showPersonalInfoForm'])->name('register.personal-info');
    Route::post('/register/step1', [AuthController::class, 'processPersonalInfo'])->name('register.step1');
    Route::get('/register/details', [AuthController::class, 'showDetailsForm'])->name('register.details');
    Route::post('/register/complete', [AuthController::class, 'completeRegistration'])->name('register.complete');
});

// Admin Authentication Routes
// Route::get('/admin/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
// Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.submit');

// Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

// Admin Dashboard Route
// Route::get('/admin/dashboard', function () {
//     return view('admin.admin-dashboard');
// })->name('admin.dashboard');

Route::get('/admin/dashboard2', [AdminController::class, 'dashboard2'])->name('admin-dashboard2');
Route::get('/admin/userManagement', [AdminController::class, 'userManagement'])->name('admin-userManagement');
Route::get('/admin/userManagement/create', [AdminController::class, 'create'])->name('admin-userManagement.create');
Route::post('/admin/userManagement', [AdminController::class, 'store'])->name('admin-userManagement.store');
Route::get('/admin/userManagement/{user}/edit', [AdminController::class, 'edit'])->name('admin-userManagement.edit');
Route::put('/admin/userManagement/{user}', [AdminController::class, 'update'])->name('admin-userManagement.update');
Route::delete('/admin/userManagement/{user}', [AdminController::class, 'destroy'])->name('admin-userManagement.destroy');
Route::get('/admin/userManagement/{user}', [AdminController::class, 'show'])->name('admin-userManagement.show');

// Route::get('/admin/sales', function () {
//     return view('admin.admin-sales');
// })->name('admin.sales');

// Route::get('/admin/transactions', function () {
//     return view('admin.admin-transactions');
// })->name('admin.transactions');

Route::get('/admin/transactions', [AdminController::class, 'transactions'])->name('admin.transactions');

 Route::get('/admin/users', function () {
    return view('admin.admin-userManagement');
 })->name('admin.users');

// Route::get('/admin/reports', function () {
//     return view('admin.admin-reportManagement');
// })->name('admin.reports');


// Route::get('/admin/funds', function () {
//     return view('admin.admin-fundManagement');
// })->name('admin.funds');

Route::get('/admin/product-management', [AdminController::class, 'productManagement'])->name('admin-productManagement');

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
