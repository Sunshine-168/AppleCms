<?php

namespace App\Services\Admin\System;

use App\Services\Video\HtmlCacheService;
use App\Support\AdminOpLog;
use App\Support\Utils\Result;
use App\Support\Utils\Usage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SysCacheService
{
    /**
     * @return array<string, array{commands: list<string>, msg: string, log: string}>
     */
    private function kinds(): array
    {
        return [
            'data' => [
                'commands' => ['cache:clear'],
                'msg' => admin_t('ui.cache_msg_data'),
                'log' => admin_t('ui.cache_msg_data'),
            ],
            'views' => [
                'commands' => ['view:clear'],
                'msg' => admin_t('ui.cache_msg_views'),
                'log' => admin_t('ui.cache_msg_views'),
            ],
            'config' => [
                'commands' => ['config:clear', 'route:clear', 'event:clear'],
                'msg' => admin_t('ui.cache_msg_config'),
                'log' => admin_t('ui.cache_msg_config'),
            ],
            'all' => [
                'commands' => ['optimize:clear'],
                'msg' => admin_t('ui.cache_msg_all'),
                'log' => admin_t('ui.cache_msg_all'),
            ],
            'pack-config' => [
                'commands' => ['config:cache'],
                'msg' => admin_t('ui.cache_msg_pack_config'),
                'log' => admin_t('ui.cache_msg_pack_config'),
            ],
            'pack-routes' => [
                'commands' => ['route:cache'],
                'msg' => admin_t('ui.cache_msg_pack_routes'),
                'log' => admin_t('ui.cache_msg_pack_routes'),
            ],
            'pack-views' => [
                'commands' => ['view:cache'],
                'msg' => admin_t('ui.cache_msg_pack_views'),
                'log' => admin_t('ui.cache_msg_pack_views'),
            ],
        ];
    }

    /**
     * 缓存工作台
     *
     * @return array<string, mixed>
     */
    public function pageBoard(): array
    {
        $defaultStore = (string) config('cache.default', '');
        $storeConfig = config("cache.stores.$defaultStore", []);
        $driver = (string) ($storeConfig['driver'] ?? $defaultStore);
        $driverMeta = $this->driverMeta($driver);
        $data = $this->dataCacheState($driver, $storeConfig);
        $views = $this->viewCacheState();
        $configPacked = is_file(base_path('bootstrap/cache/config.php'));
        $routesPacked = $this->routesPacked();
        $eventsPacked = is_file(base_path('bootstrap/cache/events.php'));
        $packed = $configPacked || $routesPacked || $eventsPacked;

        return [
            'driver' => $driver,
            'driver_label' => $driverMeta['label'],
            'driver_hint' => $driverMeta['hint'],
            'prefix' => (string) config('cache.prefix', ''),
            'data' => $data,
            'views' => $views,
            'config_packed' => $configPacked,
            'routes_packed' => $routesPacked,
            'events_packed' => $eventsPacked,
            'packed' => $packed,
            'config_detail' => $this->configDetail($configPacked, $routesPacked, $eventsPacked),
            'html_cache_on' => $this->htmlCacheOn(),
            'html_cache_url' => '/admin/video/make',
        ];
    }

    /**
     * 获取缓存信息
     */
    public function getCacheInfo(): array
    {
        return Result::success($this->pageBoard());
    }

    /**
     * 按用途清或打包
     */
    public function clear(string $kind): array
    {
        $kind = trim($kind);
        $kinds = $this->kinds();
        if (! isset($kinds[$kind])) {
            return Result::fail(admin_t('ui.cache_unknown_kind'));
        }

        $plan = $kinds[$kind];

        try {
            foreach ($plan['commands'] as $command) {
                $code = Artisan::call($command);
                if ($code !== 0) {
                    $out = trim((string) Artisan::output());

                    return Result::fail($out !== '' ? $out : admin_t('ui.cache_run_fail'));
                }
            }

            if ($kind === 'data' || $kind === 'all') {
                try {
                    Cache::flush();
                } catch (\Throwable) {
                }
            }

            return AdminOpLog::ifOk(Result::success($this->pageBoard(), $plan['msg']), 'flush', $plan['log'], [
                'module' => admin_t('page.cache'),
                'target_type' => 'cache',
                'payload' => ['kind' => $kind],
            ]);
        } catch (\Throwable $e) {
            $msg = trim($e->getMessage());

            return Result::fail($msg !== '' ? $msg : admin_t('ui.cache_run_fail'));
        }
    }

    /**
     * 清空数据缓存
     */
    public function flushCache(): array
    {
        return $this->clear('data');
    }

    /**
     * 执行 artisan 命令
     */
    public function runArtisan(string $command): array
    {
        $command = trim($command);
        if ($command === '') {
            return Result::fail(admin_t('ui.cache_unknown_kind'));
        }

        $fromCommand = [
            'cache:clear' => 'data',
            'view:clear' => 'views',
            'config:clear' => 'config',
            'route:clear' => 'config',
            'event:clear' => 'config',
            'optimize:clear' => 'all',
            'config:cache' => 'pack-config',
            'route:cache' => 'pack-routes',
            'view:cache' => 'pack-views',
        ];

        if (isset($fromCommand[$command])) {
            return $this->clear($fromCommand[$command]);
        }

        return Result::fail(admin_t('ui.cache_cmd_denied'));
    }

    /**
     * @return array{label: string, hint: string}
     */
    private function driverMeta(string $driver): array
    {
        return match ($driver) {
            'file' => ['label' => admin_t('ui.cache_driver_file'), 'hint' => admin_t('ui.cache_driver_file_h')],
            'redis' => ['label' => 'Redis', 'hint' => admin_t('ui.cache_driver_redis_h')],
            'database' => ['label' => admin_t('ui.cache_driver_db'), 'hint' => admin_t('ui.cache_driver_db_h')],
            'array' => ['label' => admin_t('ui.cache_driver_mem'), 'hint' => admin_t('ui.cache_driver_mem_h')],
            'memcached' => ['label' => 'Memcached', 'hint' => admin_t('ui.cache_driver_mc_h')],
            default => ['label' => $driver !== '' ? $driver : admin_t('ui.unknown'), 'hint' => ''],
        };
    }

    /**
     * @param  array<string, mixed>  $storeConfig
     * @return array<string, mixed>
     */
    private function dataCacheState(string $driver, array $storeConfig): array
    {
        $state = [
            'count' => null,
            'size_text' => '',
            'detail' => $this->driverMeta($driver)['label'],
            'table' => '',
        ];

        if ($driver === 'database') {
            $table = (string) ($storeConfig['table'] ?? config('cache.stores.database.table', 'cache'));
            $state['table'] = $table;
            $count = null;
            if ($table !== '' && Schema::hasTable($table)) {
                $count = (int) DB::table($table)->count();
            }
            $state['count'] = $count;
            $state['detail'] = $count === null
                ? admin_t('ui.cache_detail_db')
                : admin_t('ui.cache_detail_db_n', ['n' => $count]);

            return $state;
        }

        if ($driver === 'file') {
            $path = (string) ($storeConfig['path'] ?? storage_path('framework/cache/data'));
            $stats = $this->dirStats($path);
            $state['count'] = $stats['count'];
            $state['size_text'] = $stats['size_text'];
            $state['detail'] = $stats['count'] === 0
                ? admin_t('ui.cache_detail_file_empty')
                : admin_t('ui.cache_detail_file_n', ['size' => $stats['size_text'], 'n' => $stats['count']]);

            return $state;
        }

        if ($driver === 'array') {
            $state['detail'] = admin_t('ui.cache_detail_array');

            return $state;
        }

        if ($driver === 'redis') {
            $state['detail'] = admin_t('ui.cache_detail_redis');

            return $state;
        }

        return $state;
    }

    /**
     * @return array<string, mixed>
     */
    private function viewCacheState(): array
    {
        $stats = $this->dirStats(storage_path('framework/views'));
        $phpCount = $this->compiledViewCount();

        return [
            'count' => $phpCount,
            'size_text' => $stats['size_text'],
            'detail' => $phpCount === 0
                ? admin_t('ui.cache_views_empty')
                : ($stats['count'] > 0
                    ? admin_t('ui.cache_views_n_size', ['n' => $phpCount, 'size' => $stats['size_text']])
                    : admin_t('ui.cache_views_n', ['n' => $phpCount])),
        ];
    }

    private function compiledViewCount(): int
    {
        $dir = storage_path('framework/views');
        if (! is_dir($dir)) {
            return 0;
        }
        $files = glob($dir.DIRECTORY_SEPARATOR.'*.php') ?: [];

        return count($files);
    }

    private function configDetail(bool $configPacked, bool $routesPacked, bool $eventsPacked): string
    {
        $parts = [
            $configPacked ? admin_t('ui.cache_cfg_packed') : admin_t('ui.cache_cfg_fresh'),
            $routesPacked ? admin_t('ui.cache_rt_packed') : admin_t('ui.cache_rt_fresh'),
        ];
        if ($eventsPacked) {
            $parts[] = admin_t('ui.cache_ev_packed');
        }

        return implode(' · ', $parts);
    }

    private function routesPacked(): bool
    {
        foreach (glob(base_path('bootstrap/cache/routes*.php')) ?: [] as $file) {
            if (is_file($file)) {
                return true;
            }
        }

        return false;
    }

    private function htmlCacheOn(): bool
    {
        try {
            return app(HtmlCacheService::class)->enabled();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return array{bytes: int, count: int, size_text: string, capped: bool}
     */
    private function dirStats(string $path, int $maxFiles = 4000): array
    {
        $empty = ['bytes' => 0, 'count' => 0, 'size_text' => admin_t('ui.cache_empty_size'), 'capped' => false];
        if ($path === '' || ! is_dir($path)) {
            return $empty;
        }

        $bytes = 0;
        $count = 0;
        $capped = false;
        try {
            $it = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($it as $file) {
                if (! $file->isFile()) {
                    continue;
                }
                $name = $file->getFilename();
                if ($name === '.gitignore' || $name === '.gitkeep') {
                    continue;
                }
                $bytes += (int) $file->getSize();
                $count++;
                if ($count >= $maxFiles) {
                    $capped = true;
                    break;
                }
            }
        } catch (\Throwable) {
            return $empty;
        }

        if ($count === 0) {
            return $empty;
        }

        $text = $capped ? admin_t('ui.cache_about', ['size' => Usage::bytes($bytes)]) : Usage::bytes($bytes);

        return [
            'bytes' => $bytes,
            'count' => $count,
            'size_text' => $text,
            'capped' => $capped,
        ];
    }
}
