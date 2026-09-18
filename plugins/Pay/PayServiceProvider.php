<?php

namespace Plugins\Pay;

use App\Support\Plugins\PluginBoot;
use Illuminate\Support\ServiceProvider;

class PayServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_18_200000_create_plugin_pay_channels.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'pay');
        if (! $this->app->routesAreCached()) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
    }
}
