<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

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
        // URL::forceScheme('https');  // Force HTTPS in Laravel Testing for authorize.net

        Paginator::useBootstrapFive();

        //  RateLimiter apply for specific time
        RateLimiter::for('admin-login', function (Request $request) {
            $key = strtolower($request->input('email')) . '|' . $request->ip();

            return Limit::none();
        });

        // RateLimiter::for('admin-login', function (Request $request) {
        //     return Limit::perMinute(3)                                     // Allow only 3 requests per minute per user/IP
        //         ->by($request->input('email') ?: $request->ip()) 
        //         ->response(function () {
        //             return redirect()->route('admin.login')->with('error', 'Too many login attempts. Please try again after 1 minute.');
        //         });
            
        // });
        
    }
}
