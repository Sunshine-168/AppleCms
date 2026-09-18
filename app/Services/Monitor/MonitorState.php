<?php

namespace App\Services\Monitor;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MonitorState
{
    public static function getNum(string $key, int $default = 0): int
    {
        if (! self::ready()) {
            return $default;
        }
        $row = DB::table('video_monitor_state')->where('state_key', $key)->first();
        if ($row === null) {
            return $default;
        }

        return (int) ($row->state_num ?? $default);
    }

    public static function getVal(string $key, string $default = ''): string
    {
        if (! self::ready()) {
            return $default;
        }
        $row = DB::table('video_monitor_state')->where('state_key', $key)->first();
        if ($row === null) {
            return $default;
        }

        return (string) ($row->state_val ?? $default);
    }

    public static function set(string $key, int $num, string $val = ''): void
    {
        if (! self::ready()) {
            return;
        }
        $now = time();
        DB::table('video_monitor_state')->updateOrInsert(
            ['state_key' => $key],
            [
                'state_num' => $num,
                'state_val' => $val,
                'updated_at' => $now,
            ]
        );
    }

    public static function due(string $key, int $intervalSec, int $now = 0): bool
    {
        if (! self::ready()) {
            return false;
        }
        $now = $now > 0 ? $now : time();
        $intervalSec = max(1, $intervalSec);
        self::ensureRow($key);
        $affected = DB::update(
            'UPDATE video_monitor_state SET state_num = ?, updated_at = ? WHERE state_key = ? AND state_num <= ?',
            [$now + $intervalSec, $now, $key, $now]
        );

        return (int) $affected === 1;
    }

    public static function purgeByPrefix(string $prefix): int
    {
        if (! self::ready() || $prefix === '') {
            return 0;
        }

        return (int) DB::table('video_monitor_state')->where('state_key', 'like', $prefix.'%')->delete();
    }

    private static function ensureRow(string $key): void
    {
        $exists = DB::table('video_monitor_state')->where('state_key', $key)->exists();
        if ($exists) {
            return;
        }
        DB::table('video_monitor_state')->insert([
            'state_key' => $key,
            'state_num' => 0,
            'state_val' => '',
            'updated_at' => 0,
        ]);
    }

    private static function ready(): bool
    {
        try {
            return Schema::hasTable('video_monitor_state');
        } catch (\Throwable) {
            return false;
        }
    }
}
