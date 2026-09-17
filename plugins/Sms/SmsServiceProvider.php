<?php

namespace Plugins\Sms;

use App\Support\Plugins\PluginBoot;
use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_17_180000_create_plugin_sms_codes.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'sms');
        if (! $this->app->routesAreCached()) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
    }
}
