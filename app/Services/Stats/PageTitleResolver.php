<?php

namespace App\Services\Stats;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PageTitleResolver
{
    /** @var array<string, string> */
    protected array $cache = [];

    /**
     * @param  iterable<int, string>  $paths
     * @return array<string, string>
     */
    public function forPaths(iterable $paths): array
    {
        $out = [];
        foreach ($paths as $path) {
            $path = (string) $path;
            $out[$path] = $this->title($path);
        }

        return $out;
    }

    public function title(string $path): string
    {
        $norm = $this->normalize($path);
        if (isset($this->cache[$norm])) {
            return $this->cache[$norm];
        }

        return $this->cache[$norm] = $this->resolve($norm);
    }

    protected function resolve(string $path): string
    {
        $static = [
            '/' => admin_t('ui.rewrite_home'),
            '/search' => admin_t('ui.page_search'),
            '/show' => admin_t('ui.filter'),
            '/latest' => admin_t('ui.page_latest'),
            '/actors' => admin_t('ui.actors_lib'),
            '/topics' => admin_t('nav.topics'),
            '/website' => admin_t('item.websites'),
            '/arts' => admin_t('ui.page_arts_news'),
            '/roles' => admin_t('item.roles'),
            '/plot' => admin_t('ui.page_plot'),
            '/gbook' => admin_t('ui.page_gbook'),
            '/member' => admin_t('ui.page_member'),
            '/member/login' => admin_t('ui.page_member_login'),
            '/member/register' => admin_t('ui.page_member_register'),
            '/member/favorites' => admin_t('item.favorites'),
            '/member/history' => admin_t('ui.page_history'),
            '/member/inbox' => admin_t('ui.page_inbox'),
            '/rss.xml' => 'RSS',
            '/sitemap.xml' => admin_t('ui.page_sitemap'),
            '/manga' => admin_t('item.manga'),
            '/live' => admin_t('live.title'),
        ];
        if (isset($static[$path])) {
            return $static[$path];
        }

        if (preg_match('#(?:^/vod/|/play/|/down/|/player/|/index\.php/vod/(?:detail|play|down)/id/)(\d+)#', $path, $m)) {
            return $this->named('videos', (int) $m[1], 'title', admin_t('ui.page_vod_n', ['id' => $m[1]]));
        }
        if (preg_match('#(?:^/type/|/index\.php/vod/type/id/)([^/]+)#', $path, $m)) {
            $key = $m[1];
            if (ctype_digit($key)) {
                return $this->named('video_types', (int) $key, 'name', admin_t('ui.page_type'));
            }

            return $this->typeBySlug($key);
        }
        if (preg_match('#(?:^/actor/|/index\.php/vod/actor/id/)(\d+)#', $path, $m)) {
            return $this->named('actors', (int) $m[1], 'name', admin_t('ui.page_actor'));
        }
        if (preg_match('#(?:^/topic/|/index\.php/vod/topic/id/)(\d+)#', $path, $m)) {
            return $this->named('video_topics', (int) $m[1], 'name', admin_t('ui.page_topic'));
        }
        if (preg_match('#(?:^/art/|/index\.php/vod/art/id/)(\d+)#', $path, $m)) {
            return $this->named('video_arts', (int) $m[1], 'title', admin_t('ui.page_art'));
        }
        if (preg_match('#(?:^/tag/|/index\.php/vod/tag/id/)([^/]+)#', $path, $m)) {
            return admin_t('ui.page_tag', ['name' => $m[1]]);
        }
        if (str_starts_with($path, '/member/')) {
            return admin_t('ui.page_member');
        }

        return $path;
    }

    protected function named(string $table, int $id, string $column, string $fallback): string
    {
        try {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                return $fallback;
            }
            $value = trim((string) DB::table($table)->where('id', $id)->value($column));
            if ($value !== '') {
                return $value;
            }
        } catch (\Throwable) {
        }

        return $fallback;
    }

    protected function typeBySlug(string $slug): string
    {
        try {
            if (! Schema::hasTable('video_types')) {
                return admin_t('ui.page_type_slug', ['slug' => $slug]);
            }
            $name = trim((string) DB::table('video_types')->where('slug', $slug)->value('name'));
            if ($name !== '') {
                return $name;
            }
        } catch (\Throwable) {
        }

        return admin_t('ui.page_type_slug', ['slug' => $slug]);
    }

    public function normalize(string $path): string
    {
        $parsed = parse_url($path, PHP_URL_PATH);
        $path = is_string($parsed) && $parsed !== '' ? $parsed : $path;
        $path = '/'.ltrim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        return $path === '' ? '/' : $path;
    }
}
