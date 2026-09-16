<?php

namespace App\Http\Middleware;

use App\Services\Video\VideoSettingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class VideoHtmlCache
{
    public function __construct(private readonly VideoSettingService $settings) {}

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $site = $this->settings->site();
        } catch (\Throwable) {
            return $next($request);
        }
        if (! ($site['html_cache_enabled'] ?? false)) {
            return $next($request);
        }
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }
        if ($request->ajax() || $request->expectsJson()) {
            return $next($request);
        }
        try {
            if (auth('member')->check()) {
                return $next($request);
            }
        } catch (\Throwable) {
        }
        $path = trim($request->path(), '/');
        foreach (['admin', 'member', 'api', 'api.php', 'player', 'install'] as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return $next($request);
            }
        }
        if (in_array($path, ['sitemap.xml', 'rss.xml', 'robots.txt'], true)) {
            return $next($request);
        }

        $ttl = max(60, (int) ($site['html_cache_ttl'] ?? 3600));
        $key = 'vod.html.'.md5($request->fullUrl());
        $html = Cache::get($key);
        if (is_string($html) && $html !== '') {
            return response($html, 200, [
                'Content-Type' => 'text/html; charset=UTF-8',
                'X-Vod-Html-Cache' => 'HIT',
            ]);
        }

        /** @var Response $response */
        $response = $next($request);
        if (
            $response->getStatusCode() === 200
            && str_contains((string) $response->headers->get('Content-Type', 'text/html'), 'text/html')
        ) {
            $content = $response->getContent();
            if (is_string($content) && $content !== '') {
                Cache::put($key, $content, $ttl);
                $response->headers->set('X-Vod-Html-Cache', 'MISS');
            }
        }

        return $response;
    }
}
