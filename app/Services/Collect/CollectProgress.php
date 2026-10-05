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
            'lines' => [],
        ]);
    }

    /**
     * @param  array<string, mixed>  $patch
     * @param  array{action?:string,title?:string,msg?:string,page?:int}|null  $line
     */
    public static function tick(int $sourceId, array $patch, ?array $line = null): void
    {
        self::write($sourceId, self::merge($sourceId, $patch, [
            'running' => true,
            'done' => false,
        ], $line));
    }

    /**
     * @param  array<string, mixed>  $patch
     * @param  array{action?:string,title?:string,msg?:string,page?:int}|null  $line
     */
    public static function finish(int $sourceId, array $patch = [], ?array $line = null): void
    {
        self::write($sourceId, self::merge($sourceId, $patch, [
            'running' => false,
            'done' => true,
        ], $line));
    }

    /**
     * @param  array<string, mixed>  $patch
     * @param  array<string, mixed>  $flags
     * @param  array{action?:string,title?:string,msg?:string,page?:int}|null  $line
     * @return array<string, mixed>
     */
    private static function merge(int $sourceId, array $patch, array $flags, ?array $line): array
    {
        $cur = self::get($sourceId);
        unset($patch['lines']);
        $row = array_merge($cur, $patch, $flags);
        if ($line) {
            $lines = is_array($row['lines'] ?? null) ? $row['lines'] : [];
            $lines[] = [
                'action' => (string) ($line['action'] ?? ''),
                'title' => (string) ($line['title'] ?? ''),
                'msg' => (string) ($line['msg'] ?? ''),
                'page' => (int) ($line['page'] ?? 0),
            ];
            $row['lines'] = array_slice($lines, -200);
        }

        return $row;
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
