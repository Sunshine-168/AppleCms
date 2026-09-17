<?php

namespace App\Support\Utils;

use Illuminate\Foundation\Application;

class ServerStats
{
    /** @return array<string, mixed> */
    public static function snapshot(): array
    {
        $memoryLimit = (string) ini_get('memory_limit');
        $phpUsed = memory_get_usage(true);

        return [
            'php' => PHP_VERSION,
            'laravel' => Application::VERSION,
            'app_name' => conf('name') ?: (string) config('app.name'),
            'app_version' => conf('version'),
            'os' => php_uname('s').' '.php_uname('r'),
            'sapi' => PHP_SAPI,
            'software' => (string) ($_SERVER['SERVER_SOFTWARE'] ?? ''),
            'host' => (string) ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? ''),
            'port' => (string) ($_SERVER['SERVER_PORT'] ?? ''),
            'upload' => (string) ini_get('upload_max_filesize'),
            'post' => (string) ini_get('post_max_size'),
            'memory_limit' => $memoryLimit,
            'now' => now()->format('Y-m-d H:i'),
            'timezone' => (string) config('app.timezone'),
            'php_memory' => [
                'used' => $phpUsed,
                'used_text' => self::bytes($phpUsed),
                'limit_text' => $memoryLimit,
            ],
            'load' => self::load(),
            'ram' => self::ram(),
            'disk' => self::disk(),
        ];
    }

    /** @return array{ok: bool, text: string} */
    private static function load(): array
    {
        $fail = ['ok' => false, 'text' => ''];

        if (function_exists('sys_getloadavg')) {
            $avg = @sys_getloadavg();
            if (is_array($avg) && isset($avg[0], $avg[1], $avg[2])) {
                return [
                    'ok' => true,
                    'text' => self::loadText((float) $avg[0], (float) $avg[1], (float) $avg[2]),
                ];
            }
        }

        $raw = self::readProc('/proc/loadavg');
        if ($raw === '') {
            return $fail;
        }
        $parts = preg_split('/\s+/', $raw) ?: [];
        if (count($parts) < 3) {
            return $fail;
        }

        return [
            'ok' => true,
            'text' => self::loadText((float) $parts[0], (float) $parts[1], (float) $parts[2]),
        ];
    }

    /** @return array{ok: bool, percent: float, used_text: string, total_text: string} */
    private static function ram(): array
    {
        $fromProc = self::ramFromProc();
        if ($fromProc['ok']) {
            return $fromProc;
        }
        if (PHP_OS_FAMILY === 'Windows') {
            return self::ramFromCom();
        }

        return self::ramFail();
    }

    /** @return array{ok: bool, percent: float, used_text: string, total_text: string} */
    private static function ramFromProc(): array
    {
        $raw = self::readProc('/proc/meminfo');
        if ($raw === '') {
            return self::ramFail();
        }

        $totalKb = null;
        $availableKb = null;
        foreach (preg_split("/\r\n|\n|\r/", $raw) ?: [] as $line) {
            if (preg_match('/^MemTotal:\s+(\d+)\s*kB/i', $line, $m)) {
                $totalKb = (int) $m[1];
            } elseif (preg_match('/^MemAvailable:\s+(\d+)\s*kB/i', $line, $m)) {
                $availableKb = (int) $m[1];
            }
        }
        if ($totalKb === null || $availableKb === null || $totalKb <= 0) {
            return self::ramFail();
        }

        $total = $totalKb * 1024;
        $used = max(0, ($totalKb - $availableKb) * 1024);

        return self::ramOk($used, $total);
    }

    /** @return array{ok: bool, percent: float, used_text: string, total_text: string} */
    private static function ramFromCom(): array
    {
        if (! class_exists('COM', false)) {
            return self::ramFail();
        }
        try {
            $wmi = new \COM('winmgmts://./root/cimv2');
            $rows = $wmi->ExecQuery('SELECT TotalVisibleMemorySize, FreePhysicalMemory FROM Win32_OperatingSystem');
            foreach ($rows as $row) {
                $totalKb = (int) $row->TotalVisibleMemorySize;
                $freeKb = (int) $row->FreePhysicalMemory;
                if ($totalKb <= 0) {
                    return self::ramFail();
                }
                $total = $totalKb * 1024;
                $used = max(0, ($totalKb - $freeKb) * 1024);

                return self::ramOk($used, $total);
            }
        } catch (\Throwable) {
            return self::ramFail();
        }

        return self::ramFail();
    }

    /**
     * @return array{
     *     ok: bool,
     *     path: string,
     *     total: int,
     *     free: int,
     *     used: int,
     *     percent: float,
     *     total_text: string,
     *     free_text: string,
     *     used_text: string,
     *     tone: string
     * }
     */
    private static function disk(): array
    {
        $path = realpath(base_path()) ?: base_path();
        $totalRaw = @disk_total_space($path);
        $freeRaw = @disk_free_space($path);
        if (! is_numeric($totalRaw) || ! is_numeric($freeRaw) || (float) $totalRaw <= 0) {
            return [
                'ok' => false,
                'path' => $path,
                'total' => 0,
                'free' => 0,
                'used' => 0,
                'percent' => 0.0,
                'total_text' => '',
                'free_text' => '',
                'used_text' => '',
                'tone' => 'ok',
            ];
        }

        $total = (int) $totalRaw;
        $free = max(0, (int) $freeRaw);
        $used = max(0, $total - $free);
        $percent = $total > 0 ? ($used / $total) * 100 : 0.0;
        $tone = $percent < 70 ? 'ok' : ($percent < 90 ? 'warn' : 'danger');

        return [
            'ok' => true,
            'path' => $path,
            'total' => $total,
            'free' => $free,
            'used' => $used,
            'percent' => $percent,
            'total_text' => self::bytes($total),
            'free_text' => self::bytes($free),
            'used_text' => self::bytes($used),
            'tone' => $tone,
        ];
    }

    /** @return array{ok: bool, percent: float, used_text: string, total_text: string} */
    private static function ramOk(int $used, int $total): array
    {
        $percent = $total > 0 ? ($used / $total) * 100 : 0.0;

        return [
            'ok' => true,
            'percent' => $percent,
            'used_text' => self::bytes($used),
            'total_text' => self::bytes($total),
        ];
    }

    /** @return array{ok: bool, percent: float, used_text: string, total_text: string} */
    private static function ramFail(): array
    {
        return [
            'ok' => false,
            'percent' => 0.0,
            'used_text' => '',
            'total_text' => '',
        ];
    }

    private static function loadText(float $one, float $five, float $fifteen): string
    {
        return number_format($one, 2, '.', '').' / '.number_format($five, 2, '.', '').' / '.number_format($fifteen, 2, '.', '');
    }

    private static function readProc(string $path): string
    {
        if (! is_readable($path)) {
            return '';
        }
        $raw = @file_get_contents($path);

        return is_string($raw) ? trim($raw) : '';
    }

    private static function bytes(int $bytes): string
    {
        return Usage::bytes($bytes);
    }
}
