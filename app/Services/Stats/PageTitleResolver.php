<?php

namespace App\Services\Stats;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PageTitleResolver
{
    /** @var array<string, string> */
    protected array $cache = [];

    /** @var array<string, bool> */
    protected array $ready = [];

    /** @var array<string, array<int|string, string>> */
    protected array $names = [];

    /**
     * @param  iterable<int, string>  $paths
     * @return array<string, string>
     */
    public function forPaths(iterable $paths): array
    {
        $list = [];
        foreach ($paths as $path) {
            $list[] = (string) $path;
        }
        $this->preload(array_values(array_unique($list)));
        $out = [];
        foreach ($list as $path) {
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

    /**
     * @param  list<string>  $paths
     */
    protected function preload(array $paths): void
    {
        $videoIds = [];
        $typeIds = [];
        $typeSlugs = [];
        $actorIds = [];
        $topicIds = [];
        $artIds = [];
        foreach ($paths as $path) {
            $path = $this->normalize($path);
            if (preg_match('#(?:^/vod/|/play/|/down/|/player/|/index\.php/vod/(?:detail|play|down)/id/)(\d+)#', $path, $m)) {
                $videoIds[] = (int) $m[1];
            } elseif (preg_match('#(?:^/type/|/index\.php/vod/type/id/)([^/]+)#', $path, $m)) {
                if (ctype_digit($m[1])) {
                    $typeIds[] = (int) $m[1];
                } else {
                    $typeSlugs[] = $m[1];
                }
            } elseif (preg_match('#(?:^/actor/|/index\.php/vod/actor/id/)(\d+)#', $path, $m)) {
                $actorIds[] = (int) $m[1];
            } elseif (preg_match('#(?:^/topic/|/index\.php/vod/topic/id/)(\d+)#', $path, $m)) {
                $topicIds[] = (int) $m[1];
            } elseif (preg_match('#(?:^/art/|/index\.php/vod/art/id/)(\d+)#', $path, $m)) {
                $artIds[] = (int) $m[1];
            }
        }
        $this->loadNames('videos', 'title', $videoIds);
        $this->loadNames('video_types', 'name', $typeIds);
        $this->loadSlugs('video_types', $typeSlugs);
        $this->loadNames('actors', 'name', $actorIds);
        $this->loadNames('video_topics', 'name', $topicIds);
        $this->loadNames('video_arts', 'title', $artIds);
    }

    /** @param  list<int>  $ids */
    protected function loadNames(string $table, string $column, array $ids): void
    {
        $ids = array_values(array_unique(array_filter($ids)));
        if ($ids === [] || ! $this->ready($table, $column)) {
            return;
        }
        try {
            foreach (DB::table($table)->whereIn('id', $ids)->pluck($column, 'id') as $id => $value) {
                $this->names[$table][(int) $id] = (string) $value;
            }
        } catch (\Throwable) {
        }
    }

    /** @param  list<string>  $slugs */
    protected function loadSlugs(string $table, array $slugs): void
    {
        $slugs = array_values(array_unique($slugs));
        if ($slugs === [] || ! $this->ready($table, 'name') || ! $this->ready($table, 'slug')) {
            return;
        }
        try {
            foreach (DB::table($table)->whereIn('slug', $slugs)->get(['slug', 'name']) as $row) {
                $this->names[$table.':slug'][(string) $row->slug] = (string) $row->name;
            }
        } catch (\Throwable) {
        }
    }

    protected function ready(string $table, string $column): bool
    {
        $key = $table.'.'.$column;
        if (array_key_exists($key, $this->ready)) {
            return $this->ready[$key];
        }
        try {
            $ok = Schema::hasTable($table) && Schema::hasColumn($table, $column);
        } catch (\Throwable) {
            $ok = false;
        }

        return $this->ready[$key] = $ok;
    }

    protected function named(string $table, int $id, string $column, string $fallback): string
    {
        if (isset($this->names[$table][$id])) {
            $value = trim((string) $this->names[$table][$id]);

            return $value !== '' ? $value : $fallback;
        }
        try {
            if (! $this->ready($table, $column)) {
                return $fallback;
            }
            $value = trim((string) DB::table($table)->where('id', $id)->value($column));
            $this->names[$table][$id] = $value;
            if ($value !== '') {
                return $value;
            }
        } catch (\Throwable) {
        }

        return $fallback;
    }

    protected function typeBySlug(string $slug): string
    {
        if (isset($this->names['video_types:slug'][$slug])) {
            $name = trim((string) $this->names['video_types:slug'][$slug]);
            if ($name !== '') {
                return $name;
            }
        }
        try {
            if (! $this->ready('video_types', 'name') || ! $this->ready('video_types', 'slug')) {
                return admin_t('ui.page_type_slug', ['slug' => $slug]);
            }
            $name = trim((string) DB::table('video_types')->where('slug', $slug)->value('name'));
            $this->names['video_types:slug'][$slug] = $name;
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
