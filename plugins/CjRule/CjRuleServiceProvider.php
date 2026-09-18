<?php

namespace Plugins\CjRule;

use App\Support\Plugins\PluginBoot;
use Illuminate\Support\ServiceProvider;

class CjRuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        PluginBoot::migrateFile(__DIR__.'/database/migrations/2026_09_18_220000_expand_video_cj_rules.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'cj_rule');
        if (! $this->app->routesAreCached()) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
    }
}
