<?php

namespace App\Services\Monitor;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MonitorStore
{
    public const TYPE_GAUGE = 1;

    public const TYPE_COUNTER = 2;

    /**
     * @param  list<array{k:string,t?:int,v:float|int}>  $rows
     */
    public static function upsertMany(int $statMin, array $rows): int
    {
        if ($rows === [] || ! self::readyMin()) {
            return 0;
        }
        $now = time();
        $payload = [];
        foreach ($rows as $row) {
            $key = mb_substr(trim((string) ($row['k'] ?? '')), 0, 64);
            if ($key === '') {
                continue;
            }
            $type = (int) ($row['t'] ?? self::TYPE_GAUGE);
            if ($type !== self::TYPE_COUNTER) {
                $type = self::TYPE_GAUGE;
            }
            $payload[] = [
                'metric_key' => $key,
                'stat_min' => $statMin,
                'metric_type' => $type,
                'metric_value' => (float) ($row['v'] ?? 0),
                'updated_at' => $now,
            ];
        }
        if ($payload === []) {
            return 0;
        }
        DB::table('video_monitor_min')->upsert(
            $payload,
            ['metric_key', 'stat_min'],
            ['metric_type', 'metric_value', 'updated_at']
        );

        return count($payload);
    }

    /**
     * @param  list<string>  $metricKeys
     * @return array{series: array<string, array<int, float>>, granularity: string}
     */
    public static function fetchSeries(array $metricKeys, int $fromTs, int $toTs, string $granularity = 'auto'): array
    {
        $metricKeys = array_values(array_unique(array_filter($metricKeys, static fn ($k) => is_string($k) && $k !== '')));
        if ($metricKeys === []) {
            return ['series' => [], 'granularity' => 'min'];
        }
        if ($granularity === 'auto') {
            $granularity = self::pickGranularity($fromTs);
        }
        if ($granularity !== 'hour') {
            $granularity = 'min';
        }
        $series = [];
        foreach ($metricKeys as $k) {
            $series[$k] = [];
        }
        if ($granularity === 'hour') {
            if (! self::readyHour()) {
                return ['series' => $series, 'granularity' => 'hour'];
            }
            $rows = DB::table('video_monitor_hour')
                ->whereIn('metric_key', $metricKeys)
                ->whereBetween('stat_hour', [$fromTs, $toTs])
                ->orderBy('stat_hour')
                ->get(['metric_key', 'stat_hour', 'val_avg']);
            foreach ($rows as $row) {
                $series[(string) $row->metric_key][(int) $row->stat_hour] = (float) $row->val_avg;
            }

            return ['series' => $series, 'granularity' => 'hour'];
        }
        if (! self::readyMin()) {
            return ['series' => $series, 'granularity' => 'min'];
        }
        $rows = DB::table('video_monitor_min')
            ->whereIn('metric_key', $metricKeys)
            ->whereBetween('stat_min', [$fromTs, $toTs])
            ->orderBy('stat_min')
            ->get(['metric_key', 'stat_min', 'metric_value']);
        foreach ($rows as $row) {
            $series[(string) $row->metric_key][(int) $row->stat_min] = (float) $row->metric_value;
        }

        return ['series' => $series, 'granularity' => 'min'];
    }

    /**
     * @param  array<int, float|int>  $series
     */
    public static function aggregate(array $series, string $agg): ?float
    {
        if ($series === []) {
            return null;
        }
        if ($agg === 'last') {
            ksort($series);
            $last = end($series);

            return $last === false ? null : (float) $last;
        }
        $vals = array_values($series);

        return match ($agg) {
            'max' => (float) max($vals),
            'min' => (float) min($vals),
            'sum' => (float) array_sum($vals),
            default => (float) (array_sum($vals) / count($vals)),
        };
    }

    public static function rollupHour(int $hourStart): int
    {
        if (! self::readyMin() || ! self::readyHour()) {
            return 0;
        }
        $hourStart = (int) $hourStart;
        $hourEnd = $hourStart + 3600;
        $now = time();
        $rows = DB::table('video_monitor_min')
            ->selectRaw('metric_key, AVG(metric_value) as val_avg, MAX(metric_value) as val_max, MIN(metric_value) as val_min, SUM(metric_value) as val_sum, COUNT(*) as sample_cnt')
            ->where('stat_min', '>=', $hourStart)
            ->where('stat_min', '<', $hourEnd)
            ->groupBy('metric_key')
            ->get();
        if ($rows->isEmpty()) {
            return 0;
        }
        $payload = [];
        foreach ($rows as $row) {
            $payload[] = [
                'metric_key' => (string) $row->metric_key,
                'stat_hour' => $hourStart,
                'val_avg' => (float) $row->val_avg,
                'val_max' => (float) $row->val_max,
                'val_min' => (float) $row->val_min,
                'val_sum' => (float) $row->val_sum,
                'sample_cnt' => (int) $row->sample_cnt,
                'updated_at' => $now,
            ];
        }
        DB::table('video_monitor_hour')->upsert(
            $payload,
            ['metric_key', 'stat_hour'],
            ['val_avg', 'val_max', 'val_min', 'val_sum', 'sample_cnt', 'updated_at']
        );

        return count($payload);
    }

    /** @return array{min:int,hour:int} */
    public static function purge(int $minRetainDays, int $hourRetainDays): array
    {
        $minRetainDays = max(1, min(14, $minRetainDays));
        $hourRetainDays = max(7, min(730, $hourRetainDays));
        $deletedMin = 0;
        $deletedHour = 0;
        if (self::readyMin()) {
            $deletedMin = (int) DB::table('video_monitor_min')
                ->where('stat_min', '<', time() - ($minRetainDays * 86400))
                ->delete();
        }
        if (self::readyHour()) {
            $deletedHour = (int) DB::table('video_monitor_hour')
                ->where('stat_hour', '<', time() - ($hourRetainDays * 86400))
                ->delete();
        }

        return ['min' => $deletedMin, 'hour' => $deletedHour];
    }

    private static function pickGranularity(int $fromTs): string
    {
        $days = max(1, (int) MonitorService::option('monitor_retain_min_days', 3));
        $cut = time() - ($days * 86400) + 43200;

        return $fromTs >= $cut ? 'min' : 'hour';
    }

    private static function readyMin(): bool
    {
        try {
            return Schema::hasTable('video_monitor_min');
        } catch (\Throwable) {
            return false;
        }
    }

    private static function readyHour(): bool
    {
        try {
            return Schema::hasTable('video_monitor_hour');
        } catch (\Throwable) {
            return false;
        }
    }
}
