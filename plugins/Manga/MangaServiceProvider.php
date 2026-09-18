<?php

namespace Plugins\Manga;

use App\Support\Plugins\PluginBoot;
use Illuminate\Support\ServiceProvider;

class MangaServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_17_160000_create_plugin_manga.php');
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_18_120000_expand_plugin_manga.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'manga');
        if (! $this->app->routesAreCached()) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
    }
}
