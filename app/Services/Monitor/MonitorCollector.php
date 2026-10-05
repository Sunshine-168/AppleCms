<?php

namespace App\Services\Monitor;

use App\Support\Utils\ServerStats;

class MonitorCollector
{
    /**
     * @return array{metrics: list<array{k:string,t:int,v:float}>, skipped: array<string, string>}
     */
    public static function collect(): array
    {
        $metrics = [];
        $skipped = [];

        if (! MonitorBucket::writable()) {
            $skipped['runtime'] = 'storage/app/monitor 当前进程写不进去，请求计数会静默丢失';
        }

        self::collectLoad($metrics, $skipped);
        self::collectCpu($metrics, $skipped);
        self::collectMemory($metrics, $skipped);
        self::collectDisk($metrics, $skipped);
        self::collectPhpMemory($metrics, $skipped);

        return ['metrics' => $metrics, 'skipped' => $skipped];
    }

    /**
     * @param  list<array{k:string,t:int,v:float}>  $metrics
     * @param  array<string, string>  $skipped
     */
    private static function collectLoad(array &$metrics, array &$skipped): void
    {
        try {
            if (! function_exists('sys_getloadavg')) {
                $skipped['sys.load1'] = '没有 sys_getloadavg';
                return;
            }
            $load = @sys_getloadavg();
            if (! is_array($load) || count($load) < 3) {
                $skipped['sys.load1'] = PHP_OS_FAMILY === 'Windows'
                    ? 'Windows 上采不到负载'
                    : 'sys_getloadavg 没有返回三个数字';
                return;
            }
            $metrics[] = ['k' => 'sys.load1', 't' => MonitorStore::TYPE_GAUGE, 'v' => round((float) $load[0], 2)];
            $metrics[] = ['k' => 'sys.load5', 't' => MonitorStore::TYPE_GAUGE, 'v' => round((float) $load[1], 2)];
            $metrics[] = ['k' => 'sys.load15', 't' => MonitorStore::TYPE_GAUGE, 'v' => round((float) $load[2], 2)];
        } catch (\Throwable $e) {
            $skipped['sys.load1'] = $e->getMessage();
        }
    }

    /**
     * Linux /proc/stat. Two snapshots in one tick so the first sample already has a percentage.
     *
     * @param  list<array{k:string,t:int,v:float}>  $metrics
     * @param  array<string, string>  $skipped
     */
    private static function collectCpu(array &$metrics, array &$skipped): void
    {
        try {
            if (PHP_OS_FAMILY === 'Windows' || ! is_readable('/proc/stat')) {
                $skipped['sys.cpu.pct'] = PHP_OS_FAMILY === 'Windows'
                    ? 'Windows 上不读 CPU，曲线会空'
                    : '没有可读的 /proc/stat';
                return;
            }
            $cur = self::readProcCpu();
            if ($cur === null) {
                $skipped['sys.cpu.pct'] = '/proc/stat 解析失败';
                return;
            }
            $prev = self::storedProcCpu();
            if ($prev === null || self::cpuPercent($prev, $cur) === null) {
                if (function_exists('usleep')) {
                    usleep(300000);
                }
                $again = self::readProcCpu();
                if ($again !== null) {
                    $prev = $cur;
                    $cur = $again;
                }
            }
            MonitorState::set('cpu.prev', time(), json_encode($cur, JSON_UNESCAPED_UNICODE) ?: '');
            $pct = self::cpuPercent($prev, $cur);
            if ($pct === null) {
                $skipped['sys.cpu.pct'] = $prev === null
                    ? '第一次采样，下一分钟才有 CPU 百分比'
                    : 'CPU 计数没有前进';

                return;
            }
            $metrics[] = [
                'k' => 'sys.cpu.pct',
                't' => MonitorStore::TYPE_GAUGE,
                'v' => $pct,
            ];
        } catch (\Throwable $e) {
            $skipped['sys.cpu.pct'] = $e->getMessage();
        }
    }

    /** @return array{total:int,idle:int,mode:string}|null */
    private static function readProcCpu(): ?array
    {
        $raw = @file_get_contents('/proc/stat');
        if (! is_string($raw) || $raw === '') {
            return null;
        }
        $parsed = self::parseProcStat($raw);
        if ($parsed === null) {
            return null;
        }
        $parsed['mode'] = 'jiffies';

        return $parsed;
    }

    /** @return array{total:int,idle:int,mode?:string}|null */
    private static function storedProcCpu(): ?array
    {
        $raw = MonitorState::getVal('cpu.prev', '');
        if ($raw === '') {
            return null;
        }
        $prev = json_decode($raw, true);

        return is_array($prev) ? $prev : null;
    }

    /**
     * @param  array<string, mixed>|null  $prev
     * @param  array{total:int,idle:int,mode?:string}  $cur
     */
    public static function cpuPercent(?array $prev, array $cur): ?float
    {
        if (! is_array($prev) || ($prev['mode'] ?? '') !== 'jiffies') {
            return null;
        }
        $totalDelta = (int) $cur['total'] - (int) ($prev['total'] ?? 0);
        $idleDelta = (int) $cur['idle'] - (int) ($prev['idle'] ?? 0);
        if ($totalDelta <= 0) {
            return null;
        }

        return round(min(100.0, max(0.0, 100.0 * (1.0 - ($idleDelta / $totalDelta)))), 2);
    }

    /**
     * @param  list<array{k:string,t:int,v:float}>  $metrics
     * @param  array<string, string>  $skipped
     */
    private static function collectMemory(array &$metrics, array &$skipped): void
    {
        try {
            $ram = ServerStats::snapshot()['ram'] ?? [];
            if (empty($ram['ok'])) {
                $skipped['sys.mem.used_pct'] = PHP_OS_FAMILY === 'Windows'
                    ? 'Windows 上没有可用的内存读数'
                    : '读不到 /proc/meminfo';
                return;
            }
            $metrics[] = [
                'k' => 'sys.mem.used_pct',
                't' => MonitorStore::TYPE_GAUGE,
                'v' => round((float) ($ram['percent'] ?? 0), 2),
            ];
        } catch (\Throwable $e) {
            $skipped['sys.mem.used_pct'] = $e->getMessage();
        }
    }

    /**
     * @param  list<array{k:string,t:int,v:float}>  $metrics
     * @param  array<string, string>  $skipped
     */
    private static function collectDisk(array &$metrics, array &$skipped): void
    {
        try {
            $path = realpath(base_path()) ?: base_path();
            $total = @disk_total_space($path);
            $free = @disk_free_space($path);
            if (! is_numeric($total) || ! is_numeric($free) || (float) $total <= 0) {
                $skipped['sys.disk.used_pct'] = '读不到站点目录所在盘';
                return;
            }
            $usedPct = 100.0 * (((float) $total - max(0.0, (float) $free)) / (float) $total);
            $metrics[] = [
                'k' => 'sys.disk.used_pct',
                't' => MonitorStore::TYPE_GAUGE,
                'v' => round(min(100.0, max(0.0, $usedPct)), 2),
            ];
        } catch (\Throwable $e) {
            $skipped['sys.disk.used_pct'] = $e->getMessage();
        }
    }

    /**
     * @param  list<array{k:string,t:int,v:float}>  $metrics
     * @param  array<string, string>  $skipped
     */
    private static function collectPhpMemory(array &$metrics, array &$skipped): void
    {
        try {
            $limit = self::memoryLimitBytes();
            if ($limit === null || $limit <= 0) {
                $skipped['php.memory_pct'] = 'memory_limit 无上限，算不出占比';
                return;
            }
            $used = memory_get_usage(true);
            $metrics[] = [
                'k' => 'php.memory_pct',
                't' => MonitorStore::TYPE_GAUGE,
                'v' => round(min(100.0, max(0.0, 100.0 * $used / $limit)), 2),
            ];
        } catch (\Throwable $e) {
            $skipped['php.memory_pct'] = $e->getMessage();
        }
    }

    /** @return array{total:int,idle:int}|null */
    public static function parseProcStat(string $content): ?array
    {
        foreach (preg_split("/\r\n|\n|\r/", $content) ?: [] as $line) {
            if (! str_starts_with($line, 'cpu ')) {
                continue;
            }
            $parts = preg_split('/\s+/', trim($line)) ?: [];
            array_shift($parts);
            if (count($parts) < 5) {
                return null;
            }
            $total = 0;
            foreach ($parts as $p) {
                if (is_numeric($p)) {
                    $total += (int) $p;
                }
            }
            $idle = (int) $parts[3] + (int) $parts[4];
            if ($total <= 0) {
                return null;
            }

            return ['total' => $total, 'idle' => $idle];
        }

        return null;
    }

    private static function memoryLimitBytes(): ?int
    {
        $raw = trim((string) ini_get('memory_limit'));
        if ($raw === '' || $raw === '-1') {
            return null;
        }
        if (preg_match('/^(\d+)\s*([KMG])B?$/i', $raw, $m)) {
            $n = (int) $m[1];
            return match (strtoupper($m[2])) {
                'K' => $n * 1024,
                'M' => $n * 1048576,
                'G' => $n * 1073741824,
                default => $n,
            };
        }
        if (is_numeric($raw)) {
            return (int) $raw;
        }

        return null;
    }
}
