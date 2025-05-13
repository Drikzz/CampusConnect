<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Order;
use App\Models\TradeTransaction;
use App\Observers\OrderObserver;
use App\Observers\TradeTransactionObserver;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers only once
        Order::observe(OrderObserver::class);
        TradeTransaction::observe(TradeTransactionObserver::class);

        // Make sure we're not accidentally registering TradeObserver too
        // TradeTransaction::observe(TradeObserver::class); // Commented out to prevent double registration

        Log::info('AppServiceProvider: Observers registered successfully', [
            'registered_observers' => [
                'Order' => OrderObserver::class,
                'TradeTransaction' => TradeTransactionObserver::class
            ]
        ]);
    }
}
