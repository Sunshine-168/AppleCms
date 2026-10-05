<?php

namespace App\Services\Collect;

use Illuminate\Support\Facades\Cache;

class CollectProgress
{
    public static function key(int $sourceId): string
    {
        return 'collect:progress:'.$sourceId;
    }

    /** @param  array<string, mixed>  $row */
    public static function start(int $sourceId, string $name): void
    {
        self::write($sourceId, [
            'running' => true,
            'done' => false,
            'ok' => 1,
            'name' => $name,
            'page' => 0,
            'pages' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'title' => '',
            'action' => '',
            'msg' => '正在连接资源站…',
        ]);
    }

    /** @param  array<string, mixed>  $patch */
    public static function tick(int $sourceId, array $patch): void
    {
        self::write($sourceId, array_merge(self::get($sourceId), $patch, [
            'running' => true,
            'done' => false,
        ]));
    }

    /** @param  array<string, mixed>  $patch */
    public static function finish(int $sourceId, array $patch = []): void
    {
        self::write($sourceId, array_merge(self::get($sourceId), $patch, [
            'running' => false,
            'done' => true,
        ]));
    }

    /** @return array<string, mixed> */
    public static function get(int $sourceId): array
    {
        if ($sourceId < 1) {
            return [];
        }
        $row = Cache::get(self::key($sourceId), []);

        return is_array($row) ? $row : [];
    }

    /** @param  array<string, mixed>  $row */
    private static function write(int $sourceId, array $row): void
    {
        if ($sourceId < 1) {
            return;
        }
        Cache::put(self::key($sourceId), $row, 600);
    }
}
