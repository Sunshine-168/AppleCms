<?php

namespace App\Services\Video;

use App\Models\Video\VideoDomain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DomainBindService
{
    public static function normalizeHost(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return '';
        }
        $raw = preg_replace('#^https?://#i', '', $raw) ?? $raw;
        $raw = preg_replace('#/.*$#', '', $raw) ?? $raw;
        $raw = strtolower(trim($raw, ". \t"));
        $raw = preg_replace('/:(80|443)$/', '', $raw) ?? $raw;
        if ($raw === 'localhost' || filter_var($raw, FILTER_VALIDATE_IP)) {
            return $raw;
        }
        $raw = preg_replace('/^www\./', '', $raw) ?? $raw;
        if ($raw === '' || ! preg_match('/^[a-z0-9](?:[a-z0-9.-]*[a-z0-9])?$/', $raw)) {
            return '';
        }

        return $raw;
    }

    /** @return list<string> */
    public static function hostCandidates(string $host): array
    {
        $host = self::normalizeHost($host);
        if ($host === '') {
            return [];
        }
        $bare = preg_replace('/^www\./', '', $host) ?: $host;
        $out = [$host, $bare];
        if (! str_starts_with($host, 'www.') && ! filter_var($bare, FILTER_VALIDATE_IP) && $bare !== 'localhost') {
            $out[] = 'www.'.$bare;
        }

        return array_values(array_unique($out));
    }

    public static function currentHost(?Request $request = null): string
    {
        $request ??= request();

        return self::normalizeHost((string) $request->getHost());
    }

    public static function findActive(?string $host = null): ?VideoDomain
    {
        try {
            if (! Schema::hasTable('video_domains')) {
                return null;
            }
        } catch (\Throwable) {
            return null;
        }
        $host = $host === null ? self::currentHost() : self::normalizeHost($host);
        $candidates = self::hostCandidates($host);
        if ($candidates === []) {
            return null;
        }
        try {
            return VideoDomain::query()
                ->where('status', 1)
                ->whereIn('host', $candidates)
                ->orderByDesc('id')
                ->first();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $site
     * @return array<string, mixed>
     */
    public static function apply(array $site, ?VideoDomain $bind): array
    {
        if (! $bind) {
            return $site;
        }
        $name = trim((string) ($bind->site_name ?? ''));
        if ($name !== '') {
            $site['title'] = $name;
            $site['site_title'] = $name;
        }
        $keyword = trim((string) ($bind->site_keyword ?? ''));
        if ($keyword !== '') {
            $site['keyword'] = $keyword;
            $site['site_keyword'] = $keyword;
        }
        $description = trim((string) ($bind->site_description ?? ''));
        if ($description !== '') {
            $site['description'] = $description;
            $site['site_description'] = $description;
        }
        $theme = trim((string) ($bind->theme ?? ''));
        if ($theme !== '' && self::themeExists($theme)) {
            $site['theme'] = $theme;
        }

        return $site;
    }

    public static function themeExists(string $theme): bool
    {
        $theme = trim($theme);
        if ($theme === '' || str_contains($theme, '/') || str_contains($theme, '\\') || str_contains($theme, '..')) {
            return false;
        }
        $dir = resource_path('views/themes/'.$theme);

        return is_dir($dir);
    }

    /** @return list<array{name: string, title: string}> */
    public static function themeOptions(): array
    {
        $root = resource_path('views/themes');
        if (! is_dir($root)) {
            return [];
        }
        $out = [];
        foreach (scandir($root) ?: [] as $name) {
            if ($name === '.' || $name === '..' || ! is_dir($root.DIRECTORY_SEPARATOR.$name)) {
                continue;
            }
            if (! self::themeExists($name)) {
                continue;
            }
            $out[] = [
                'name' => $name,
                'title' => self::themeTitle($name),
            ];
        }
        usort($out, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return $out;
    }

    public static function themeTitle(string $theme): string
    {
        $file = resource_path('views/themes/'.$theme.'/config.json');
        if (is_file($file)) {
            $json = json_decode((string) file_get_contents($file), true);
            $title = trim((string) ($json['title'] ?? ''));
            if ($title !== '') {
                return $title;
            }
        }

        return $theme;
    }
}
