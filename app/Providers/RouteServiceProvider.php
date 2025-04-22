<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
                
            // Log routes for debugging in local environment
            if (app()->environment('local')) {
                $this->logRoutes();
            }
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
    
    /**
     * Log all registered routes to help debug routing issues
     */
    protected function logRoutes(): void
    {
        $this->app->booted(function () {
            $routes = Route::getRoutes();
            $routeList = [];
            
            foreach ($routes as $route) {
                // Focus on admin category and tag routes
                if (str_contains($route->uri, 'admin/categor') || str_contains($route->uri, 'admin/tag')) {
                    $routeList[] = [
                        'methods' => implode('|', $route->methods),
                        'uri' => $route->uri,
                        'name' => $route->getName(),
                        'action' => $route->getActionName(),
                    ];
                }
            }
            
            Log::info('Admin Category/Tag Routes:', $routeList);
        });
    }
}