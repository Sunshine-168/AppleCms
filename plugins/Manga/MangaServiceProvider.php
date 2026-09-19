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
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_18_270000_expand_plugin_manga_reader.php');
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_18_280000_manga_collect_bind.php');
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_18_290000_create_plugin_manga_histories.php');
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_18_300000_manga_chapter_vip.php');
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_18_310000_manga_type_form_fields.php');
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_18_320000_create_plugin_manga_tags.php');
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_18_330000_create_plugin_manga_authors.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'manga');
        if (! $this->app->routesAreCached()) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
    }
}
