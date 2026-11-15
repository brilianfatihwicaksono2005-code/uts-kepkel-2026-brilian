<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\Redis;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CollectorRegistry::class, function () {
            $adapter = new Redis([
                'host' => env('REDIS_HOST', 'redis'),
                'port' => (int) env('REDIS_PORT', 6379),
                'password' => env('REDIS_PASSWORD') ?: null,
                'timeout' => 0.5,
                'read_timeout' => 2,
            ]);

            return new CollectorRegistry($adapter);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
