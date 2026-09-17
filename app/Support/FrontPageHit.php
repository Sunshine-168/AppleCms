<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * 前台「页面」GET。统计明细和访问流水共用这一条线：
 * 后台、接口、静态文件、插件资源一律不算页面。
 */
final class FrontPageHit
{
    public static function isDocumentGet(Request $request): bool
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return false;
        }

        $path = '/'.ltrim($request->path(), '/');
        if ($path === '//' || $path === '') {
            $path = '/';
        }

        foreach ([
            '/admin',
            '/install',
            '/api/',
            '/api.php',
            '/css/',
            '/js/',
            '/static/',
            '/storage/',
            '/livewire/',
            '/plugin-assets/',
            '/vendor/',
            '/build/',
        ] as $prefix) {
            if ($path === rtrim($prefix, '/') || str_starts_with($path, $prefix)) {
                return false;
            }
        }

        if ($path === '/up') {
            return false;
        }

        if (preg_match('#\.(css|js|map|ico|png|jpe?g|gif|webp|svg|woff2?|ttf|eot|txt|xml|json)$#i', $path)) {
            $lower = strtolower($path);
            if (! str_ends_with($lower, 'sitemap.xml') && ! str_ends_with($lower, 'rss.xml')) {
                return false;
            }
        }

        return true;
    }
}
