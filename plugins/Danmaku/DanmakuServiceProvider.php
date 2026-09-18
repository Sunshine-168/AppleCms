<?php

namespace Plugins\Danmaku;

use App\Support\Plugins\PluginBoot;
use Illuminate\Support\ServiceProvider;

class DanmakuServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_16_230000_create_video_danmaku.php');
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_18_231000_expand_video_danmaku.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'danmaku');
        if (! $this->app->routesAreCached()) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
    }
}
