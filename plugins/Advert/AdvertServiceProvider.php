<?php

namespace Plugins\Advert;

use App\Support\Plugins\PluginBoot;
use Illuminate\Support\ServiceProvider;

class AdvertServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_18_182000_create_plugin_advert.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'advert');
        if (! $this->app->routesAreCached()) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
    }
}
