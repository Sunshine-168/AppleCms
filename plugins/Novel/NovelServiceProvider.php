<?php

namespace Plugins\Novel;

use App\Support\Plugins\PluginBoot;
use Illuminate\Support\ServiceProvider;

class NovelServiceProvider extends ServiceProvider
{
    /** 启动小说插件。 */
    public function boot(): void
    {
        $file = __DIR__.'/database/migrations/2026_09_19_100000_create_plugin_novel.php';
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        PluginBoot::migrateFile($file);
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_19_101000_expand_plugin_novel_meta.php');
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_19_102000_expand_plugin_novel_tags_authors_comments.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'novel');
        if (! $this->app->routesAreCached()) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
    }
}
