<?php

namespace App\Http\Middleware;

use App\Services\Monitor\MonitorBucket;
use App\Services\Monitor\MonitorService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MonitorRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->set('_monitor_t0', microtime(true));

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        try {
            if ($this->skip($request)) {
                return;
            }
            $t0 = (float) $request->attributes->get('_monitor_t0', microtime(true));
            $ms = (int) round((microtime(true) - $t0) * 1000);
            if ($ms < 0) {
                $ms = 0;
            }
            $code = (int) $response->getStatusCode();
            if ($code <= 0) {
                $code = 200;
            }
            $cls = $code >= 500 ? '5xx' : ($code >= 400 ? '4xx' : ($code >= 300 ? '3xx' : '2xx'));
            $delta = [
                'http.req' => 1,
                'http.'.$cls => 1,
            ];
            $slow = MonitorService::slowMs();
            if ($slow > 0 && $ms >= $slow) {
                $delta['http.slow'] = 1;
            }
            MonitorBucket::add($delta);
        } catch (\Throwable) {
        }
    }

    private function skip(Request $request): bool
    {
        $path = '/'.ltrim($request->path(), '/');
        if ($path === '//' || $path === '') {
            $path = '/';
        }
        if ($path === '/admin/system/runtime/series' || $path === '/admin/system/runtime/live') {
            return true;
        }
        if (str_starts_with($path, '/admin/system/runtime/series') || str_starts_with($path, '/admin/system/runtime/live')) {
            return true;
        }
        foreach ([
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
                return true;
            }
        }
        if ($path === '/up') {
            return true;
        }
        if (preg_match('#\.(css|js|map|ico|png|jpe?g|gif|webp|svg|woff2?|ttf|eot|txt|xml|json)$#i', $path)) {
            $lower = strtolower($path);
            if (! str_ends_with($lower, 'sitemap.xml') && ! str_ends_with($lower, 'rss.xml')) {
                return true;
            }
        }

        return false;
    }
}
