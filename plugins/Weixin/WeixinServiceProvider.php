<?php

namespace Plugins\Weixin;

use Illuminate\Support\ServiceProvider;

class WeixinServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! $this->app->routesAreCached()) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
    }
}
