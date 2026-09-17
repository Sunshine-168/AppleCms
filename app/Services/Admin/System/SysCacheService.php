<?php

namespace App\Services\Admin\System;

use App\Support\AdminOpLog;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SysCacheService
{
    /**
     * 获取缓存信息
     */
    public function getCacheInfo(): array
    {
        $defaultStore = config('cache.default');
        $prefix = config('cache.prefix');

        $storeConfig = config("cache.stores.$defaultStore", []);
        $driver = $storeConfig['driver'] ?? '';

        $data = [
            'default_store' => $defaultStore,
            'driver' => $driver,
            'prefix' => $prefix,
        ];

        if ($driver === 'database')
        {
            $table = config('cache.stores.database.table', 'cache');
            $count = null;

            if ($table && Schema::hasTable($table))
            {
                $count = DB::table($table)->count();
            }

            $data['database_table'] = $table;
            $data['database_count'] = $count;
        }

        return Result::success($data);
    }

    /**
     * 清空缓存
     */
    public function flushCache(): array
    {
        try {

            Cache::store()->flush();

            return AdminOpLog::ifOk(Result::success([], '缓存已清空'), 'flush', '清空了缓存', [
                'module' => '缓存',
                'target_type' => 'cache',
            ]);

        } catch (\Throwable $e) {

            return Result::fail($e->getMessage());

        }
    }

    /**
     * 执行 artisan 命令
     */
    public function runArtisan(string $command): array
    {
        $command = trim($command);

        if ($command === '')
        {
            return Result::fail('命令不能为空');
        }

        $allowed = [
            'cache:clear',
            'config:clear',
            'route:clear',
            'view:clear',
            'event:clear',
            'optimize:clear',
            'config:cache',
            'route:cache',
            'view:cache',
            'event:cache',
        ];

        if (!in_array($command, $allowed, true))
        {
            return Result::fail('不允许执行该命令');
        }

        try {

            $code = Artisan::call($command);

            if ($code !== 0)
            {
                return Result::fail(Artisan::output());
            }

            return AdminOpLog::ifOk(Result::success([
                'output' => Artisan::output()
            ], '执行成功'), 'run', '执行了缓存命令 '.$command, [
                'module' => '缓存',
                'target_type' => 'cache',
                'payload' => ['command' => $command],
            ]);

        } catch (\Throwable $e) {

            return Result::fail($e->getMessage());

        }
    }
}
