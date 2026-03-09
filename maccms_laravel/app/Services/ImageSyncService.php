<?php

namespace App\Services;

use App\Models\Annex;
use Illuminate\Support\Facades\File;

class ImageSyncService
{
    public function syncImage(?string $url, string $flag = 'vod'): array
    {
        $url = trim((string) $url);
        if ($url === '') {
            return ['success' => false, 'original' => '', 'path' => '', 'message' => '图片地址为空'];
        }

        $cleanUrl = str_replace('#err', '', $url);
        if (!str_starts_with(strtolower($cleanUrl), 'http')) {
            return ['success' => false, 'original' => $url, 'path' => $cleanUrl, 'message' => '非远程图片，跳过'];
        }

        $content = function_exists('mac_curl_get') ? mac_curl_get($cleanUrl) : @file_get_contents($cleanUrl);
        if (empty($content) || strlen($content) < 10) {
            return ['success' => false, 'original' => $url, 'path' => $cleanUrl . '#err', 'message' => '下载失败'];
        }

        $imageInfo = @getimagesizefromstring($content);
        $extension = $this->detectExtension($cleanUrl, $imageInfo);
        if ($extension === null) {
            return ['success' => false, 'original' => $url, 'path' => $cleanUrl . '#err', 'message' => '文件不是有效图片'];
        }

        $relativePath = $this->buildRelativePath($flag, $extension);
        $fullPath = public_path($relativePath);
        File::ensureDirectoryExists(dirname($fullPath));

        if (@file_put_contents($fullPath, $content) === false) {
            return ['success' => false, 'original' => $url, 'path' => $cleanUrl . '#err', 'message' => '保存文件失败'];
        }

        $this->recordAnnex($relativePath, 'image', filesize($fullPath));

        return [
            'success' => true,
            'original' => $url,
            'path' => str_replace('\\', '/', $relativePath),
            'message' => '下载成功',
        ];
    }

    public function syncContentImages(?string $content, string $flag = 'vod'): array
    {
        $content = (string) $content;
        if ($content === '') {
            return ['content' => $content, 'processed' => 0, 'success' => 0, 'failed' => 0, 'items' => []];
        }

        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
        $urls = $matches[1] ?? [];
        $items = [];
        $processed = 0;
        $success = 0;
        $failed = 0;

        foreach (array_unique($urls) as $url) {
            if (!str_starts_with(strtolower((string) $url), 'http')) {
                continue;
            }

            $processed++;
            $result = $this->syncImage($url, $flag);
            $items[] = $result;

            if ($result['success']) {
                $success++;
                $content = str_replace($url, $result['path'], $content);
            } else {
                $failed++;
                $content = str_replace($url, $result['path'], $content);
            }
        }

        return compact('content', 'processed', 'success', 'failed', 'items');
    }

    public function recordAnnex(string $file, string $type = 'image', int $size = 0): void
    {
        $file = ltrim(str_replace('\\', '/', $file), '/');
        if ($file === '') {
            return;
        }

        Annex::query()->updateOrCreate(
            ['annex_file' => $file],
            [
                'annex_type' => $type,
                'annex_size' => $size,
                'annex_time' => time(),
            ]
        );
    }

    protected function detectExtension(string $url, $imageInfo): ?string
    {
        $mimeMap = [
            IMAGETYPE_GIF => 'gif',
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_WEBP => 'webp',
        ];

        if (is_array($imageInfo) && isset($imageInfo[2], $mimeMap[$imageInfo[2]])) {
            return $mimeMap[$imageInfo[2]];
        }

        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true) ? ($extension === 'jpeg' ? 'jpg' : $extension) : null;
    }

    protected function buildRelativePath(string $flag, string $extension): string
    {
        $flag = preg_replace('/[^A-Za-z0-9_-]/', '', $flag) ?: 'vod';
        $ymd = date('Ymd');
        $baseDir = public_path('upload/' . $flag);
        File::ensureDirectoryExists($baseDir);

        $directory = $ymd;
        for ($i = 1; $i <= 100; $i++) {
            $candidate = $i === 1 ? $ymd : $ymd . '-' . $i;
            $candidatePath = $baseDir . DIRECTORY_SEPARATOR . $candidate;
            if (!is_dir($candidatePath)) {
                $directory = $candidate;
                break;
            }

            $fileCount = count(File::files($candidatePath));
            if ($fileCount <= 999) {
                $directory = $candidate;
                break;
            }
        }

        return 'upload/' . $flag . '/' . $directory . '/' . md5(uniqid((string) mt_rand(), true)) . '.' . $extension;
    }
}
