<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminUsersController;
use App\Http\Controllers\AdminProductsController;
use App\Http\Controllers\AdminCategoriesTagsController;
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
        Route::post('/register/details', [AuthController::class, 'processAccountInfo'])->name('register.account-info');
        Route::get('/register/profile-picture', [AuthController::class, 'showProfilePicturePage'])->name('register.profile-picture');
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

            // Fix: Update the wishlist route definition to avoid conflicts
            Route::get('/wishlist', [WishlistController::class, 'index'])->name('dashboard.wishlist');

            Route::get('/meetup-locations', [DashboardController::class, 'address'])->name('dashboard.address');
            Route::get('/reviews', [DashboardController::class, 'reviews'])->name('dashboard.reviews');

            // Profile and wishlist routes
            Route::post('/profile/update', [DashboardController::class, 'updateProfile'])->name('profile.update');

            // Fix: Consolidate wishlist routes under proper prefix
            Route::prefix('wishlist')->group(function () {
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
                Route::get('/orders', [SellerController::class, 'orders'])->name('seller.orders'); // edit this colleen
                Route::get('/analytics', [SellerController::class, 'analytics'])->name('seller.analytics');
                Route::get('/reviews', [SellerController::class, 'reviews'])->name('seller.reviews');

                // Order management routes // edit this colleen
                Route::get('/orders/{order}', [SellerController::class, 'showOrder'])->name('seller.orders.show'); // edit this colleen
                Route::put('/orders/{order}/status', [SellerController::class, 'updateOrderStatus'])->name('seller.orders.update-status'); // edit this colleen
                Route::post('/orders/{order}/schedule-meetup', [SellerController::class, 'scheduleMeetup'])->name('seller.orders.schedule-meetup'); // edit this colleen

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
                Route::prefix('wallet')->group(function () {
                    Route::get('/', [SellerWalletController::class, 'index'])->name('seller.wallet.index');
                    Route::post('/setup', [SellerWalletController::class, 'setup'])->name('seller.wallet.setup');
                    Route::post('/refill', [SellerWalletController::class, 'refill'])->name('seller.wallet.refill');
                    Route::post('/withdraw', [SellerWalletController::class, 'withdraw'])->name('seller.wallet.withdraw'); // Add this missing route
                    Route::get('/status', [SellerWalletController::class, 'getWalletStatus'])->name('seller.wallet.status');
                    // Route::get('/wallet/receipt/{id}', [SellerWalletController::class, 'downloadReceipt'])->name('seller.wallet.receipt');
                });
            });
        });

        // Add or update the receipt download route
        Route::get('/dashboard/seller/wallet/receipt/{id}', [SellerWalletController::class, 'downloadReceipt'])
            ->middleware(['auth', 'verified'])
            ->name('seller.wallet.receipt');

        // Update wishlist routes
        Route::prefix('dashboard/wishlist')->group(function () {
            Route::get('/', [WishlistController::class, 'index'])->name('dashboard.wishlist');
            Route::post('/', [WishlistController::class, 'toggle'])->name('wishlist.toggle'); // Keep this one as the main toggle route
            Route::get('/check/{product_id}', [WishlistController::class, 'checkStatus'])->name('wishlist.check');
            Route::delete('/{id}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
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

Route::middleware('auth', 'admin')->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/wallet-requests', [AdminController::class, 'walletRequests'])->name('wallet-requests');
        Route::post('/wallet-requests/{id}/approve', [AdminController::class, 'approveWalletRequest'])->name('wallet-requests.approve');
        Route::post('/wallet-requests/{id}/reject', [AdminController::class, 'rejectWalletRequest'])->name('wallet-requests.reject');

        // Ensure this route is properly defined
        Route::post('/wallet-requests/{id}/complete-withdrawal', [AdminController::class, 'markWithdrawalCompleted'])
            ->name('wallet-requests.complete-withdrawal');

        
        // User Management Routes
        Route::get('/users', [AdminUsersController::class, 'users'])->name('users');
        Route::post('/users/{id}/toggle-seller', [AdminUsersController::class, 'toggleSellerStatus'])->name('users.toggle-seller');
        Route::post('/users/{id}/toggle-status', [AdminUsersController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::put('/users/{id}', [AdminUsersController::class, 'update'])->name('admin.users.update');
        Route::delete('/users/{id}', [AdminUsersController::class, 'destroy'])->name('users.delete');
        Route::delete('/users/bulk-delete', [AdminUsersController::class, 'bulkDelete'])->name('users.bulk-delete');

        
        // Product Management Routes
        Route::get('/products', [AdminProductsController::class, 'index'])->name('products');
        Route::post('/products', [AdminProductsController::class, 'store'])->name('products.store');
        Route::put('/products/{id}', [AdminProductsController::class, 'update'])->name('products.update');
        Route::patch('/products/{id}', [AdminProductsController::class, 'update'])->name('products.update'); // Add PATCH as alternative
        Route::delete('/products/{id}', [AdminProductsController::class, 'destroy'])->name('products.delete');
        Route::delete('/products/bulk-delete', [AdminProductsController::class, 'bulkDelete'])->name('products.bulk-delete');
        Route::post('/products/{id}/toggle-status', [AdminProductsController::class, 'toggleStatus'])->name('products.toggle-status');
        
        // Categories & Tags Management Routes
        Route::get('/categories-tags', [AdminCategoriesTagsController::class, 'index'])->name('admin.categories-tags');
        
        // Category Routes
        Route::post('/categories', [AdminCategoriesTagsController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{id}', [AdminCategoriesTagsController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{id}', [AdminCategoriesTagsController::class, 'destroyCategory'])->name('categories.delete');
        Route::delete('/categories/bulk-delete', [AdminCategoriesTagsController::class, 'bulkDeleteCategories'])->name('categories.bulk-delete');
        
        // Tag Routes
        Route::post('/tags', [AdminCategoriesTagsController::class, 'storeTag'])->name('tags.store');
        Route::put('/tags/{id}', [AdminCategoriesTagsController::class, 'updateTag'])->name('tags.update');
        Route::delete('/tags/{id}', [AdminCategoriesTagsController::class, 'destroyTag'])->name('tags.delete');
        Route::delete('/tags/bulk-delete', [AdminCategoriesTagsController::class, 'bulkDeleteTags'])->name('tags.bulk-delete');
        
        Route::get('/orders', [AdminController::class, 'transactions'])->name('orders');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');

        // Wallet Management Routes
        Route::get('/wallet', [AdminController::class, 'walletRequests'])->name('wallet'); // Use same controller method
        Route::post('/wallet/fees', [AdminController::class, 'updatePlatformFees'])
            ->name('wallet-requests.update-fees');
        Route::post('/wallet/adjust', [AdminController::class, 'adjustWalletBalance'])
            ->name('wallet-requests.adjust-balance');
        Route::post('/wallet/refunds/{id}/approve', [AdminController::class, 'approveRefund'])
            ->name('wallet-requests.approve-refund');
        Route::post('/wallet/refunds/{id}/reject', [AdminController::class, 'rejectRefund'])
            ->name('wallet-requests.reject-refund');
    });
});

Route::fallback(function () {
    return Inertia::render('404');
});
