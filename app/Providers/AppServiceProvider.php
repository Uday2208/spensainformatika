<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;

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
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('username').'|'.$request->ip());
        });

        if (!app()->runningInConsole()) {
            \Illuminate\Support\Facades\View::composer('*', function ($view) {
                try {
                    $settings = Cache::remember('app_settings', 3600, function () {
                        return \App\Models\Setting::pluck('value', 'key')->toArray();
                    });
                    $view->with('app_settings', $settings);
                } catch (\Exception $e) {
                    $view->with('app_settings', []);
                }
            });
        }
    }
}
