<?php

namespace App\Services\Monitor;

use App\Models\Stat\StatHit;
use App\Models\Video\VideoAccessLog;
use Illuminate\Support\Facades\Schema;

class MonitorAccess
{
    /** @var list<string> */
    private const SCAN = [
        '.env', '.git/', 'wp-login', 'wp-admin', 'wp-content',
        'phpmyadmin', 'pma/', 'adminer', 'xmlrpc.php',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public static function lastDay(int $cc = 120): array
    {
        $cc = max(1, $cc);
        $since = time() - 86400;
        if (self::hasHits()) {
            return self::fromHits($since, $cc);
        }
        if (self::hasAccessLogs()) {
            return self::fromAccessLogs($since, $cc);
        }

        return [];
    }

    public static function isScanPath(string $path): bool
    {
        $path = strtolower($path);
        if ($path === '') {
            return false;
        }
        foreach (self::SCAN as $n) {
            if (str_contains($path, $n)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function fromHits(int $since, int $cc): array
    {
        $from = date('Y-m-d H:i:s', $since);
        $rows = StatHit::query()
            ->where('created_at', '>=', $from)
            ->orderByDesc('id')
            ->limit(8000)
            ->get(['ip', 'path', 'user_agent', 'status_code']);
        $map = [];
        foreach ($rows as $row) {
            $ip = trim((string) $row->ip);
            if ($ip === '') {
                continue;
            }
            if (! isset($map[$ip])) {
                $map[$ip] = [
                    'ip' => $ip,
                    'hits' => 0,
                    'e4' => 0,
                    'e5' => 0,
                    'scan' => 0,
                    'ua' => (string) ($row->user_agent ?? ''),
                    'path' => (string) ($row->path ?? ''),
                ];
            }
            $map[$ip]['hits']++;
            $code = (int) ($row->status_code ?? 0);
            if ($code >= 500) {
                $map[$ip]['e5']++;
            } elseif ($code >= 400) {
                $map[$ip]['e4']++;
            }
            $path = (string) ($row->path ?? '');
            if (self::isScanPath($path)) {
                $map[$ip]['scan']++;
                $map[$ip]['path'] = $path;
            }
        }

        return self::present($map, $cc);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function fromAccessLogs(int $since, int $cc): array
    {
        $rows = VideoAccessLog::query()
            ->where('created_at', '>=', $since)
            ->orderByDesc('id')
            ->limit(8000)
            ->get(['ip', 'url', 'ua']);
        $map = [];
        foreach ($rows as $row) {
            $ip = trim((string) $row->ip);
            if ($ip === '') {
                continue;
            }
            if (! isset($map[$ip])) {
                $map[$ip] = [
                    'ip' => $ip,
                    'hits' => 0,
                    'e4' => 0,
                    'e5' => 0,
                    'scan' => 0,
                    'ua' => (string) ($row->ua ?? ''),
                    'path' => (string) ($row->url ?? ''),
                ];
            }
            $map[$ip]['hits']++;
            $path = (string) ($row->url ?? '');
            if (self::isScanPath($path)) {
                $map[$ip]['scan']++;
                $map[$ip]['path'] = $path;
            }
        }

        return self::present($map, $cc);
    }

    /**
     * @param  array<string, array<string, mixed>>  $map
     * @return list<array<string, mixed>>
     */
    private static function present(array $map, int $cc): array
    {
        $out = [];
        foreach ($map as $row) {
            $high = ((int) $row['hits'] >= $cc) || ((int) $row['scan'] > 0);
            $row['high'] = $high;
            $row['flag'] = $high ? (((int) $row['scan'] > 0) ? admin_t('ui.flag_scan') : admin_t('ui.flag_high')) : '';
            $out[] = $row;
        }
        usort($out, static function (array $a, array $b): int {
            if ((int) $a['high'] !== (int) $b['high']) {
                return (int) $b['high'] <=> (int) $a['high'];
            }

            return (int) $b['hits'] <=> (int) $a['hits'];
        });

        return array_slice($out, 0, 80);
    }

    private static function hasHits(): bool
    {
        try {
            return Schema::hasTable('stat_hits');
        } catch (\Throwable) {
            return false;
        }
    }

    private static function hasAccessLogs(): bool
    {
        try {
            return Schema::hasTable('video_access_logs');
        } catch (\Throwable) {
            return false;
        }
    }
}
