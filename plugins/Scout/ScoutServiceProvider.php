<?php

namespace Plugins\Scout;

use Illuminate\Support\ServiceProvider;
use Plugins\Scout\Console\ScoutSyncCommand;

class ScoutServiceProvider extends ServiceProvider
{
    /**
     * 注册全文搜索插件：配置合并、管理页、同步命令。
     */
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/scout_plugin.php', 'scout_plugin');
        $this->applyRuntimeConfig();
        $this->loadViewsFrom(__DIR__.'/resources/views', 'scout');
        if (! $this->app->routesAreCached()) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
        if ($this->app->runningInConsole()) {
            $this->commands([ScoutSyncCommand::class]);
        }
    }

    /**
     * 把站点设置里的驱动/主机写进运行时 Scout 配置。
     */
    private function applyRuntimeConfig(): void
    {
        try {
            $svc = $this->app->make(\Plugins\Scout\Services\ScoutSearchService::class);
            $svc->applyConfig();
        } catch (\Throwable) {
        }
    }
}
