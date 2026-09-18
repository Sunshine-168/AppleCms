<?php

namespace Plugins\Coupon;

use App\Support\Plugins\PluginBoot;
use Illuminate\Support\ServiceProvider;

class CouponServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_18_210000_create_plugin_coupon.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'coupon');
        if (! $this->app->routesAreCached()) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
    }
}
