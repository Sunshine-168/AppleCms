<?php

namespace Plugins\Live;

use App\Support\Plugins\PluginBoot;
use Illuminate\Support\ServiceProvider;

class LiveServiceProvider extends ServiceProvider
{
    /** 启动直播频道插件。 */
    public function boot(): void
    {
        $file = __DIR__.'/database/migrations/2026_09_19_120000_create_plugin_live.php';
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        PluginBoot::migrateFile($file);
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_19_150000_add_recommend_to_plugin_live_channels.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'live');
        if (! $this->app->routesAreCached()) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
        PluginBoot::cmsLoops([
            'live' => \Plugins\Live\Tags\LiveTag::class,
            'liveCate' => \Plugins\Live\Tags\LiveCateTag::class,
        ]);
    }
}
