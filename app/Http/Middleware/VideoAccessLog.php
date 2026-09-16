<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class VideoAccessLog
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $response;
        }
        $path = trim($request->path(), '/');
        foreach (['admin', 'install', 'css', 'js', 'static', 'storage'] as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return $response;
            }
        }
        try {
            if (! Schema::hasTable('video_access_logs')) {
                return $response;
            }
            $ua = (string) $request->userAgent();
            $isBot = preg_match('/bot|spider|crawl|slurp/i', $ua) ? 1 : 0;
            \App\Models\Video\VideoAccessLog::query()->create([
                'ip' => (string) $request->ip(),
                'url' => mb_substr($request->fullUrl(), 0, 500),
                'ua' => mb_substr($ua, 0, 500),
                'is_bot' => $isBot,
                'created_at' => time(),
            ]);
        } catch (\Throwable) {
        }

        return $response;
    }
}
