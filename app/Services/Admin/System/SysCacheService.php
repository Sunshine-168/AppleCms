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
    /** @var array<string, array{commands: list<string>, msg: string, log: string}> */
    private const KINDS = [
        'data' => [
            'commands' => ['cache:clear'],
            'msg' => '已清掉数据缓存。设置和查询会重新算。',
            'log' => '清了数据缓存',
        ],
        'views' => [
            'commands' => ['view:clear'],
            'msg' => '已清掉编译模板。下次打开页面会重新编译。',
            'log' => '清了模板缓存',
        ],
        'config' => [
            'commands' => ['config:clear', 'route:clear', 'event:clear'],
            'msg' => '已解开配置和路由打包。改过的配置会重新读。',
            'log' => '解开了配置和路由打包',
        ],
        'all' => [
            'commands' => ['optimize:clear'],
            'msg' => '已全部清掉。前台会稍慢一会儿。',
            'log' => '清空了全部缓存',
        ],
        'pack-config' => [
            'commands' => ['config:cache'],
            'msg' => '已打包配置。之后改配置文件不会马上生效，要先解开。',
            'log' => '打包了配置',
        ],
        'pack-routes' => [
            'commands' => ['route:cache'],
            'msg' => '已打包路由。改路由文件后要先解开再打包。',
            'log' => '打包了路由',
        ],
        'pack-views' => [
            'commands' => ['view:cache'],
            'msg' => '已预编译模板。改模板后要先清掉再预编译。',
            'log' => '预编译了模板',
        ],
    ];

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
        if (! isset(self::KINDS[$kind])) {
            return Result::fail('不知道要清哪一项');
        }

        $plan = self::KINDS[$kind];

        try {
            foreach ($plan['commands'] as $command) {
                $code = Artisan::call($command);
                if ($code !== 0) {
                    $out = trim((string) Artisan::output());

                    return Result::fail($out !== '' ? $out : '没能执行');
                }
            }

            if ($kind === 'data' || $kind === 'all') {
                try {
                    Cache::flush();
                } catch (\Throwable) {
                }
            }

            return AdminOpLog::ifOk(Result::success($this->pageBoard(), $plan['msg']), 'flush', $plan['log'], [
                'module' => '缓存',
                'target_type' => 'cache',
                'payload' => ['kind' => $kind],
            ]);
        } catch (\Throwable $e) {
            $msg = trim($e->getMessage());

            return Result::fail($msg !== '' ? $msg : '没能执行');
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
            return Result::fail('不知道要执行哪一项');
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

        return Result::fail('不允许执行该命令');
    }

    /**
     * @return array{label: string, hint: string}
     */
    private function driverMeta(string $driver): array
    {
        return match ($driver) {
            'file' => ['label' => '文件', 'hint' => '缓存在服务器磁盘，单机够用'],
            'redis' => ['label' => 'Redis', 'hint' => '缓存在 Redis，多机可共用'],
            'database' => ['label' => '数据库', 'hint' => '缓存在数据表里'],
            'array' => ['label' => '内存', 'hint' => '只在这次请求里，关掉就没了'],
            'memcached' => ['label' => 'Memcached', 'hint' => '缓存在 Memcached'],
            default => ['label' => $driver !== '' ? $driver : '未知', 'hint' => ''],
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
                ? '数据库'
                : ('数据库 · '.$count.' 条');

            return $state;
        }

        if ($driver === 'file') {
            $path = (string) ($storeConfig['path'] ?? storage_path('framework/cache/data'));
            $stats = $this->dirStats($path);
            $state['count'] = $stats['count'];
            $state['size_text'] = $stats['size_text'];
            $state['detail'] = $stats['count'] === 0
                ? '文件 · 空'
                : ('文件 · '.$stats['size_text'].($stats['count'] > 0 ? ' · '.$stats['count'].' 个文件' : ''));

            return $state;
        }

        if ($driver === 'array') {
            $state['detail'] = '内存，不持久';

            return $state;
        }

        if ($driver === 'redis') {
            $state['detail'] = '在 Redis 里，条数不在这里数';

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
                ? '还没有编译文件'
                : ('已编译 '.$phpCount.' 个'.($stats['count'] > 0 ? ' · '.$stats['size_text'] : '')),
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
            $configPacked ? '配置已打包' : '配置每次读最新',
            $routesPacked ? '路由已打包' : '路由每次读最新',
        ];
        if ($eventsPacked) {
            $parts[] = '事件已打包';
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
        $empty = ['bytes' => 0, 'count' => 0, 'size_text' => '空', 'capped' => false];
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

        $text = ($capped ? '约 ' : '').Usage::bytes($bytes);

        return [
            'bytes' => $bytes,
            'count' => $count,
            'size_text' => $text,
            'capped' => $capped,
        ];
    }
}
