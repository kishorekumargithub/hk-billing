<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        RateLimiter::for('usage', function (Request $request) {
            $merchantId = $request->attributes->get('merchant')?->id ?? $request->ip();
            return Limit::perMinute(config('billing.usage_rate_limit', 120))->by("usage:$merchantId");
        });
    }
}
