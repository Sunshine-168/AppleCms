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
        $this->ensureAppKey();
        $this->ensureCompiledViewPath();
        $this->ensureWritableLog();
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
                'session.driver' => $this->writableDir(storage_path('framework/sessions')) ? 'file' : 'cookie',
                'cache.default' => $this->writableDir(storage_path('framework/cache/data')) ? 'file' : 'array',
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
     * 加密中间件在安装页之前就会用到密钥。空的 APP_KEY 会让 /install 直接 500。
     */
    private function ensureAppKey(): void
    {
        if ((string) config('app.key') !== '') {
            return;
        }
        if ($this->app->environment('testing')) {
            config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);

            return;
        }
        $stored = storage_path('app/app.key');
        if (is_file($stored)) {
            $saved = trim((string) file_get_contents($stored));
            if ($saved !== '') {
                config(['app.key' => $saved]);

                return;
            }
        }
        $key = 'base64:'.base64_encode(random_bytes(32));
        $env = base_path('.env');
        if (! is_file($env) && is_file(base_path('.env.example'))) {
            @copy(base_path('.env.example'), $env);
        }
        if (is_file($env) && is_writable($env)) {
            $content = (string) file_get_contents($env);
            if (preg_match('/^APP_KEY=.*$/m', $content)) {
                $content = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY='.$key, $content, 1) ?? $content;
            } else {
                $content = rtrim($content)."\nAPP_KEY=".$key."\n";
            }
            if (file_put_contents($env, $content) !== false) {
                config(['app.key' => $key]);

                return;
            }
        }
        $dir = dirname($stored);
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        if (is_dir($dir) && is_writable($dir)) {
            @file_put_contents($stored, $key);
        }
        config(['app.key' => $key]);
    }

    /**
     * 日志目录不可写时，写 laravel.log 会失败，再把这个失败套进原来的异常里。
     */
    private function ensureWritableLog(): void
    {
        if ($this->writableDir(storage_path('logs'))) {
            return;
        }
        config([
            'logging.default' => 'errorlog',
            'logging.channels.stack.ignore_exceptions' => true,
        ]);
    }

    private function writableDir(string $dir): bool
    {
        if (! is_dir($dir)) {
            try {
                mkdir($dir, 0775, true);
            } catch (\Throwable) {
            }
        }

        return is_dir($dir) && is_writable($dir);
    }

    /**
     * 新装时 storage/framework/views 经常还不存在。配置一旦把编译路径读成空，
     * 插件注册 Blade 指令就会在打开安装页时中断。
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
        if (! is_dir($dir) || ! is_writable($dir)) {
            $dir = rtrim(sys_get_temp_dir(), '\\/').DIRECTORY_SEPARATOR.'laravideo-views';
            if (! is_dir($dir)) {
                try {
                    mkdir($dir, 0775, true);
                } catch (\Throwable) {
                }
            }
        }
        $compiled = config('view.compiled');
        if (! is_string($compiled) || $compiled === '' || ! is_dir($compiled) || ! is_writable($compiled)) {
            config(['view.compiled' => $dir]);
        }
    }
}
