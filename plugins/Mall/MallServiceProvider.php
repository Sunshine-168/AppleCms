<?php

namespace Plugins\Mall;

use App\Support\Plugins\PluginBoot;
use Illuminate\Support\ServiceProvider;

class MallServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_17_160000_create_plugin_mall.php');
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_18_140000_expand_plugin_mall.php');
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_18_190000_strengthen_plugin_mall.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'mall');
        if (! $this->app->routesAreCached()) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
    }
}
