<?php

namespace App\Providers;

use App\Support\HttpSsl;
use App\Support\Plugins\PluginHost;
use App\Support\Plugins\PluginManager;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->ensureCompiledViewPath();
        $this->app->singleton(PluginHost::class);
        $this->app->singleton(PluginManager::class);
        $manager = $this->app->make(PluginManager::class);
        $manager->hydrate($this->app->make(PluginHost::class));
        foreach ($manager->providers() as $provider) {
            $this->app->register($provider);
        }
    }

    public function boot(): void
    {
        if (! is_file(storage_path('app/install.lock'))) {
            config([
                'session.driver' => 'file',
                'cache.default' => 'file',
                'queue.default' => 'sync',
            ]);
        }

        Paginator::defaultView('pagination.default');
        Paginator::defaultSimpleView('pagination.simple');
        Http::globalOptions([
            'verify' => HttpSsl::verify(),
        ]);
        try {
            app(\App\Services\Video\VideoSettingService::class)->applyRuntime();
        } catch (\Throwable) {
        }
    }

    /**
     * 新装时 storage/framework/views 经常还不存在。配置一旦把编译路径读成空，
     * 插件注册 Blade 指令就会在 package:discover 里中断。
     */
    private function ensureCompiledViewPath(): void
    {
        $dir = storage_path('framework/views');
        if (! is_dir($dir)) {
            try {
                mkdir($dir, 0775, true);
            } catch (\Throwable) {
            }
        }
        $compiled = config('view.compiled');
        if (! is_string($compiled) || $compiled === '') {
            config(['view.compiled' => $dir]);
        }
    }
}
