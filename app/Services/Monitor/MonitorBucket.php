<?php

namespace App\Services\Monitor;

/**
 * 请求级指标的分钟桶。只走 storage/app/monitor 下的 flock 分片，不接 Redis。
 */
class MonitorBucket
{
    public const SHARDS = 64;

    /**
     * @param  array<string, int>  $delta
     */
    public static function add(array $delta): void
    {
        if ($delta === []) {
            return;
        }
        try {
            self::addFile(self::currentMinute(), $delta);
        } catch (\Throwable) {
        }
    }

    /**
     * @return array<int, array<string, int>>
     */
    public static function drainClosed(int $now = 0, int $maxMinutes = 30): array
    {
        $now = $now > 0 ? $now : time();
        $maxMinutes = min(120, max(1, $maxMinutes));
        $closed = (int) (floor($now / 60) * 60);
        try {
            return self::drainFile($closed, $maxMinutes, true);
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * 当前分钟仍在写入，只读不删，覆盖写到分钟表，方便当场看见。
     *
     * @return array<int, array<string, int>>
     */
    public static function peekOpen(int $now = 0): array
    {
        $now = $now > 0 ? $now : time();
        $current = (int) (floor($now / 60) * 60);
        try {
            return self::drainFile($current + 60, 1, false);
        } catch (\Throwable) {
            return [];
        }
    }

    public static function gc(int $maxAgeSec = 3600): int
    {
        try {
            $cut = time() - max(600, $maxAgeSec);
            $n = 0;
            $dir = self::dir();
            if (! is_dir($dir)) {
                return 0;
            }
            $files = @glob($dir.DIRECTORY_SEPARATOR.'*') ?: [];
            foreach ($files as $f) {
                if (@filemtime($f) < $cut) {
                    @unlink($f);
                    $n++;
                }
            }

            return $n;
        } catch (\Throwable) {
            return 0;
        }
    }

    public static function writable(): bool
    {
        try {
            $dir = self::dir();
            if (! is_dir($dir)) {
                return is_writable(storage_path('app'));
            }

            return is_writable($dir);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @param  array<string, int>  $delta
     */
    private static function addFile(int $minute, array $delta): void
    {
        $dir = self::dir();
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $path = $dir.DIRECTORY_SEPARATOR.$minute.'_'.self::shard().'.json';
        $fp = @fopen($path, 'c+');
        if ($fp === false) {
            return;
        }
        if (! flock($fp, LOCK_EX)) {
            fclose($fp);

            return;
        }
        $size = (int) filesize($path);
        $data = [];
        if ($size > 0) {
            rewind($fp);
            $raw = fread($fp, $size);
            if (is_string($raw) && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $data = $decoded;
                }
            }
        }
        foreach ($delta as $field => $n) {
            $n = (int) $n;
            if ($n === 0) {
                continue;
            }
            $field = (string) $field;
            $data[$field] = (int) ($data[$field] ?? 0) + $n;
        }
        $json = json_encode($data);
        if ($json !== false) {
            rewind($fp);
            ftruncate($fp, 0);
            fwrite($fp, $json);
            fflush($fp);
        }
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * @return array<int, array<string, int>>
     */
    private static function drainFile(int $closed, int $maxMinutes, bool $remove): array
    {
        $dir = self::dir();
        if (! is_dir($dir)) {
            return [];
        }
        $files = @glob($dir.DIRECTORY_SEPARATOR.'*.json') ?: [];
        if ($files === []) {
            return [];
        }
        $oldest = $closed - ($maxMinutes * 60);
        $out = [];
        foreach ($files as $path) {
            $base = basename($path, '.json');
            $parts = explode('_', $base);
            if (count($parts) !== 2) {
                continue;
            }
            $minute = (int) $parts[0];
            if ($minute >= $closed) {
                continue;
            }
            if ($minute < $oldest) {
                if ($remove) {
                    @unlink($path);
                }
                continue;
            }
            if ($remove) {
                $tmp = $path.'.flushing';
                if (! @rename($path, $tmp)) {
                    continue;
                }
                $raw = @file_get_contents($tmp);
                @unlink($tmp);
            } else {
                $raw = @file_get_contents($path);
            }
            if (! is_string($raw) || $raw === '') {
                continue;
            }
            $data = json_decode($raw, true);
            if (! is_array($data)) {
                continue;
            }
            if (! isset($out[$minute])) {
                $out[$minute] = [];
            }
            foreach ($data as $field => $v) {
                $field = (string) $field;
                $out[$minute][$field] = (int) ($out[$minute][$field] ?? 0) + (int) $v;
            }
        }

        return $out;
    }

    private static function dir(): string
    {
        return storage_path('app'.DIRECTORY_SEPARATOR.'monitor');
    }

    private static function shard(): int
    {
        $pid = function_exists('getmypid') ? getmypid() : 0;
        if ($pid === false || $pid === 0) {
            $pid = 1;
        }

        return (crc32((string) $pid) & 0x7fffffff) % self::SHARDS;
    }

    private static function currentMinute(): int
    {
        return (int) (floor(time() / 60) * 60);
    }
}
