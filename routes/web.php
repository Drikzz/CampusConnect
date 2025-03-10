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
use App\Http\Controllers\SellerWalletController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\ProductTradeController;


// Public routes should be at the top, before any middleware groups
Route::get('/', [ProductController::class, 'welcome'])->name('index');

// Route::inertia('/about', 'About', ['user' => 'About Us']);   

// Update the products routes
Route::get('/products', [ProductController::class, 'index'])->name('products');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
Route::get('/trade', [ProductController::class, 'trade'])->name('products.trade');
Route::get('/products/trade', [ProductTradeController::class, 'index'])->name('products.trade');

// Trade routes
Route::get('/products/trade', [ProductTradeController::class, 'index'])->name('product.trade.index');
Route::post('/products/trade/submit', [ProductTradeController::class, 'submitTradeOffer'])->name('product.trade.submit')->middleware('auth');

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

    // Email Verification Routes
    Route::get('/email/verify', [AuthController::class, 'verifyNotice'])->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->middleware(['signed'])->name('verification.verify');

    Route::post('/email/verification-notification', [AuthController::class, 'verifyHandler'])->middleware('throttle:6,1')->name('verification.send');

    Route::get('/wishlist/after-login', [WishlistController::class, 'handleAfterLogin'])
        ->name('wishlist.after-login');

    // Protect these routes with verified middleware
    Route::middleware(['verified'])->group(function () {
        // Combined Dashboard and Seller routes
        Route::prefix('dashboard')->group(function () {
            // Main dashboard routes
            Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
            Route::get('/profile', [DashboardController::class, 'profile'])->name('dashboard.profile');
            Route::get('/orders', [DashboardController::class, 'orders'])->name('dashboard.orders');
            Route::get('/trades', [DashboardController::class, 'trades'])->name('dashboard.trades');

            // Fix: Update the wishlist route definition to avoid conflicts
            Route::get('/wishlist', [WishlistController::class, 'index'])->name('dashboard.wishlist');

            Route::get('/meetup-locations', [DashboardController::class, 'address'])->name('dashboard.address');
            Route::get('/reviews', [DashboardController::class, 'reviews'])->name('dashboard.reviews');

            // Profile and wishlist routes
            Route::post('/profile/update', [DashboardController::class, 'updateProfile'])->name('profile.update');

            // Fix: Consolidate wishlist routes under proper prefix
            Route::prefix('wishlist')->group(function () {
                Route::post('/', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
                Route::get('/check/{product_id}', [WishlistController::class, 'checkStatus'])->name('wishlist.check');
                Route::delete('/{id}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
            });

            Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
            Route::get('/orders/{order}/details', [OrderController::class, 'show'])->name('orders.details');
            Route::patch('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
            Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

            // Seller registration routes
            Route::get('/become-seller', [UserController::class, 'showBecomeSeller'])->name('dashboard.become-seller');
            Route::post('/become-seller', [UserController::class, 'becomeSeller'])->name('dashboard.seller.become');
            Route::post('/seller/terms', [DashboardController::class, 'acceptSellerTerms'])->name('dashboard.seller.terms.accept');

            // Seller specific routes
            Route::prefix('seller')->middleware('seller')->group(function () {
                // Dashboard and main routes
                Route::get('/', [SellerController::class, 'index'])->name('seller.index');
                Route::get('/products', [SellerController::class, 'products'])->name('seller.products');
                Route::get('/orders', [SellerController::class, 'orders'])->name('seller.orders');
                Route::get('/analytics', [SellerController::class, 'analytics'])->name('seller.analytics');
                Route::get('/reviews', [SellerController::class, 'reviews'])->name('seller.reviews');

                // Order management routes
                Route::get('/orders/{order}', [SellerController::class, 'showOrder'])->name('seller.orders.show');
                Route::put('/orders/{order}/status', [SellerController::class, 'updateOrderStatus'])->name('seller.orders.update-status');
                Route::post('/orders/{order}/schedule-meetup', [SellerController::class, 'scheduleMeetup'])->name('seller.orders.schedule-meetup');

                // Product management routes
                Route::post('/products', [SellerController::class, 'store'])->name('seller.products.store');
                Route::get('/products/{id}/edit', [SellerController::class, 'edit'])->name('seller.products.edit');
                Route::post('/products/{id}', [SellerController::class, 'update'])->name('seller.products.update');
                // Route::put('/products/{id}', [SellerController::class, 'update']);  // Add this as fallback
                Route::delete('/products/{id}', [SellerController::class, 'destroy'])->name('seller.products.destroy');
                Route::post('/products/{product}/restore', [SellerController::class, 'restore'])->name('seller.products.restore');
                Route::delete('/products/{product}/force-delete', [SellerController::class, 'forceDelete'])->name('seller.products.force-delete');

                // Add bulk action routes
                Route::delete('/products/bulk-delete', [SellerController::class, 'bulkDelete'])
                    ->name('seller.products.bulk-delete');
                Route::post('/products/bulk-restore', [SellerController::class, 'bulkRestore'])
                    ->name('seller.products.bulk-restore');
                Route::delete('/products/bulk-force-delete', [SellerController::class, 'bulkForceDelete'])
                    ->name('seller.products.bulk-force-delete');

                // Meetup location routes
                Route::get('/meetup-locations', [SellerController::class, 'meetupLocations'])->name('seller.meetup-locations');
                Route::post('/meetup-locations', [SellerController::class, 'storeMeetupLocation'])->name('seller.meetup-locations.store');
                Route::put('/meetup-locations/{id}', [SellerController::class, 'updateMeetupLocation'])->name('seller.meetup-locations.update');
                Route::delete('/meetup-locations/{id}', [SellerController::class, 'deleteMeetupLocation'])->name('seller.meetup-locations.destroy');

                //wallet routes
                Route::get('/wallet', [SellerWalletController::class, 'index'])->name('seller.wallet.index');
                Route::post('/wallet/activate', [SellerWalletController::class, 'activate'])->name('seller.wallet.activate');
            });
        });

        // Update wishlist routes
        Route::prefix('dashboard/wishlist')->group(function () {
            Route::get('/', [WishlistController::class, 'index'])->name('dashboard.wishlist');
            Route::post('/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
            Route::get('/check/{product_id}', [WishlistController::class, 'checkStatus'])->name('wishlist.check');
            Route::delete('/{id}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
        });

        // Trade related routes
        Route::prefix('trades')->group(function () {
            Route::patch('/{trade}/cancel', [ProductTradeController::class, 'cancelTrade'])->name('trades.cancel');
        });

        //checkout routes - update to add consistent naming
        Route::get('/products/prod/{id}/summary', [CheckoutController::class, 'summary'])->name('summary');
        // Add a new route with checkout.show name for backward compatibility
        Route::get('/checkout/{id}', [CheckoutController::class, 'summary'])->name('checkout.show');
        Route::post('/checkout/process', [CheckoutController::class, 'checkout'])->name('checkout.process');

        Route::post('/profile/revert', [DashboardController::class, 'revertProfileUpdate'])
            ->name('profile.revert');
    });
});

// Route::prefix('tags')->group(function () {
//     Route::get('/', [TagController::class, 'index'])->name('tags.index');
//     Route::post('/', [TagController::class, 'store'])->name('tags.store');
//     Route::delete('/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');

//     // Add this route temporarily for debugging
//     Route::get('/debug/order/{order}', function (App\Models\Order $order) {
//         return response()->json([
//             'order' => $order->load(['items.product', 'meetup_location', 'buyer', 'seller']),
//         ]);
//     })->middleware('auth');
// });

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

Route::fallback(function () {
    return Inertia::render('404');
});
