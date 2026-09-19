<?php

namespace App\Services\Video;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class ImageLocalizeService
{
    public function download(string $url, string $basename, string $subdir = 'vod'): string
    {
        $url = trim($url);
        if ($url === '' || (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://'))) {
            return $url;
        }
        $subdir = preg_replace('/[^a-z0-9_-]/i', '', $subdir) ?: 'vod';
        try {
            $resp = Http::timeout(15)->withHeaders(['User-Agent' => 'LaraVideo/1.0'])->get($url);
            if (! $resp->successful() || $resp->body() === '') {
                return $url;
            }
            $path = (string) (parse_url($url, PHP_URL_PATH) ?: '');
            $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
            $ext = preg_replace('/[^a-z0-9]/', '', $ext) ?: 'jpg';
            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                $ext = 'jpg';
            }
            $dir = public_path('uploads/'.$subdir);
            File::ensureDirectoryExists($dir);
            $file = $dir.DIRECTORY_SEPARATOR.$basename.'.'.$ext;
            File::put($file, $resp->body());
            $this->watermark($file);

            return '/uploads/'.$subdir.'/'.$basename.'.'.$ext;
        } catch (\Throwable) {
            return $url;
        }
    }

    public function isRemote(string $url): bool
    {
        $url = trim($url);

        return str_starts_with($url, 'http://') || str_starts_with($url, 'https://');
    }

    public function probe(string $url): bool
    {
        $url = trim($url);
        if ($url === '') {
            return false;
        }
        if (! $this->isRemote($url)) {
            $local = public_path(ltrim(parse_url($url, PHP_URL_PATH) ?: $url, '/'));

            return is_file($local) && filesize($local) > 32;
        }
        try {
            $resp = Http::timeout(8)->withHeaders(['User-Agent' => 'LaraVideo/1.0'])->head($url);
            if ($resp->successful()) {
                return true;
            }
            $get = Http::timeout(8)->withHeaders(['User-Agent' => 'LaraVideo/1.0'])->get($url);

            return $get->successful() && strlen($get->body()) > 32;
        } catch (\Throwable) {
            return false;
        }
    }

    public function watermark(string $file): void
    {
        $text = trim((string) app(VideoSettingService::class)->get('watermark_text', ''));
        if ($text === '' || ! is_file($file) || ! function_exists('imagecreatefromstring')) {
            return;
        }
        $bin = @file_get_contents($file);
        if ($bin === false || $bin === '') {
            return;
        }
        $im = @imagecreatefromstring($bin);
        if (! $im) {
            return;
        }
        $w = imagesx($im);
        $h = imagesy($im);
        $color = imagecolorallocatealpha($im, 255, 255, 255, 40);
        imagestring($im, 5, max(8, $w - 8 - strlen($text) * 9), max(8, $h - 24), $text, $color);
        $ext = strtolower((string) pathinfo($file, PATHINFO_EXTENSION));
        match ($ext) {
            'png' => imagepng($im, $file),
            'gif' => imagegif($im, $file),
            'webp' => function_exists('imagewebp') ? imagewebp($im, $file) : imagejpeg($im, $file, 88),
            default => imagejpeg($im, $file, 88),
        };
        imagedestroy($im);
    }
}
