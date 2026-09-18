<?php

namespace Plugins\PublishPage;

use App\Support\Plugins\PluginBoot;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Support\ServiceProvider;
use Plugins\PublishPage\Http\Middleware\PublishGate;

class PublishPageServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_18_181000_create_plugin_publish_page.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'publishpage');
        if (! $this->app->routesAreCached()) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
        EncryptCookies::except(\Plugins\PublishPage\Services\PublishService::COOKIE);
        $this->app->make(HttpKernel::class);
        $this->app->make('router')->pushMiddlewareToGroup('web', PublishGate::class);
    }
}
