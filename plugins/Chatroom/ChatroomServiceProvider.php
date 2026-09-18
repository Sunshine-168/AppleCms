<?php

namespace Plugins\Chatroom;

use App\Support\Plugins\PluginBoot;
use Illuminate\Support\ServiceProvider;

class ChatroomServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_17_160000_create_plugin_chatroom.php');
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_18_230000_expand_plugin_chatroom.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'chatroom');
        if (! $this->app->routesAreCached()) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
    }
}
