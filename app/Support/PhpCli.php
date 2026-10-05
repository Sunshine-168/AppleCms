<?php

namespace App\Support;

class PhpCli
{
    public static function binary(): string
    {
        return self::resolve(
            PHP_BINARY !== '' ? PHP_BINARY : 'php',
            defined('PHP_BINDIR') ? (string) PHP_BINDIR : '',
            PHP_VERSION,
            PHP_OS_FAMILY
        );
    }

    public static function resolve(string $phpBinary, string $phpBindir = '', string $phpVersion = '', string $osFamily = 'Linux'): string
    {
        $binary = str_replace('\\', '/', trim($phpBinary));
        $bindir = str_replace('\\', '/', trim($phpBindir));
        $exe = $osFamily === 'Windows' ? 'php.exe' : 'php';
        $picked = '';
        foreach (self::candidates($binary, $bindir, $phpVersion, $exe) as $path) {
            if ($path === '') {
                continue;
            }
            if ($path === 'php' || $path === 'php.exe') {
                $picked = $picked !== '' ? $picked : $path;
                continue;
            }
            if (is_file($path)) {
                return $path;
            }
            if ($picked === '' && ! self::isWorker($path)) {
                $picked = $path;
            }
        }

        return $picked !== '' ? $picked : ($binary !== '' ? $binary : 'php');
    }

    public static function scheduleCronLine(): string
    {
        return '* * * * * '.self::quote(self::binary()).' '.self::quote(base_path('artisan')).' schedule:run >> /dev/null 2>&1';
    }

    public static function quote(string $path): string
    {
        if ($path === '' || ! preg_match('/[\s"\']/', $path)) {
            return $path;
        }

        return '"'.str_replace('"', '\\"', $path).'"';
    }

    /** @return list<string> */
    public static function candidates(string $phpBinary, string $phpBindir = '', string $phpVersion = '', string $exe = 'php'): array
    {
        $binary = str_replace('\\', '/', trim($phpBinary));
        $bindir = str_replace('\\', '/', trim($phpBindir));
        $out = [];
        $add = static function (string $path) use (&$out): void {
            $path = str_replace('\\', '/', $path);
            if ($path !== '' && ! in_array($path, $out, true)) {
                $out[] = $path;
            }
        };

        if ($binary !== '' && ! self::isWorker($binary)) {
            $add($binary);
        }
        if (self::isWorker($binary)) {
            $dir = dirname($binary);
            $root = dirname($dir);
            $add($root.'/bin/'.$exe);
            $add($dir.'/'.$exe);
        }
        if ($bindir !== '') {
            $add($bindir.'/'.$exe);
            if (str_ends_with($bindir, '/sbin') || str_ends_with($bindir, '/cgi')) {
                $add(dirname($bindir).'/bin/'.$exe);
            }
        }
        if (preg_match('/^(\d+)\.(\d+)/', $phpVersion, $m)) {
            $add('/www/server/php/'.$m[1].$m[2].'/bin/'.$exe);
        }
        if (is_dir('/www/server/php')) {
            foreach (scandir('/www/server/php') ?: [] as $name) {
                if ($name === '.' || $name === '..') {
                    continue;
                }
                $add('/www/server/php/'.$name.'/bin/'.$exe);
            }
        }
        if ($binary === '' || self::isWorker($binary)) {
            $add($exe);
        }

        return $out;
    }

    public static function isWorker(string $path): bool
    {
        $name = strtolower(basename(str_replace('\\', '/', $path)));

        return str_contains($name, 'php-fpm') || str_starts_with($name, 'php-cgi');
    }
}
