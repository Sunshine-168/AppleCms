<?php

namespace App\Providers;

use App\Plugins\PluginManager;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        foreach ((new PluginManager)->providers() as $provider) {
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
        try {
            app(\App\Services\Video\VideoSettingService::class)->applyRuntime();
        } catch (\Throwable) {
        }
    }
}
