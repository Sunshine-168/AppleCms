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
            '/' => '首页',
            '/search' => '搜索',
            '/show' => '筛选',
            '/latest' => '最新',
            '/actors' => '演员库',
            '/topics' => '专题',
            '/website' => '网址导航',
            '/arts' => '资讯',
            '/roles' => '角色库',
            '/plot' => '剧情',
            '/gbook' => '留言',
            '/member' => '会员中心',
            '/member/login' => '会员登录',
            '/member/register' => '会员注册',
            '/member/favorites' => '收藏',
            '/member/history' => '观看历史',
            '/member/inbox' => '站内信',
            '/rss.xml' => 'RSS',
            '/sitemap.xml' => '站点地图',
        ];
        if (isset($static[$path])) {
            return $static[$path];
        }

        if (preg_match('#(?:^/vod/|/play/|/down/|/player/|/index\.php/vod/(?:detail|play|down)/id/)(\d+)#', $path, $m)) {
            return $this->named('videos', (int) $m[1], 'title', '影片 #'.$m[1]);
        }
        if (preg_match('#(?:^/type/|/index\.php/vod/type/id/)([^/]+)#', $path, $m)) {
            $key = $m[1];
            if (ctype_digit($key)) {
                return $this->named('video_types', (int) $key, 'name', '分类');
            }

            return $this->typeBySlug($key);
        }
        if (preg_match('#(?:^/actor/|/index\.php/vod/actor/id/)(\d+)#', $path, $m)) {
            return $this->named('actors', (int) $m[1], 'name', '演员');
        }
        if (preg_match('#(?:^/topic/|/index\.php/vod/topic/id/)(\d+)#', $path, $m)) {
            return $this->named('video_topics', (int) $m[1], 'name', '专题');
        }
        if (preg_match('#(?:^/art/|/index\.php/vod/art/id/)(\d+)#', $path, $m)) {
            return $this->named('video_arts', (int) $m[1], 'title', '文章');
        }
        if (preg_match('#(?:^/tag/|/index\.php/vod/tag/id/)([^/]+)#', $path, $m)) {
            return '标签 '.$m[1];
        }
        if (str_starts_with($path, '/member/')) {
            return '会员中心';
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
                return '分类 '.$slug;
            }
            $name = trim((string) DB::table('video_types')->where('slug', $slug)->value('name'));
            if ($name !== '') {
                return $name;
            }
        } catch (\Throwable) {
        }

        return '分类 '.$slug;
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
