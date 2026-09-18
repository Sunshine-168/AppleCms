<?php

namespace Plugins\FriendLink;

use App\Support\Plugins\PluginBoot;
use Illuminate\Support\ServiceProvider;

class FriendLinkServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_18_180000_create_plugin_friend_link.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'friendlink');
        if (! $this->app->routesAreCached()) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
    }
}
