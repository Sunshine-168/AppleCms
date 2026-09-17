<?php

namespace App\Http\Middleware;

use App\Support\FrontPageHit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class VideoAccessLog
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if (! FrontPageHit::isDocumentGet($request)) {
            return $response;
        }
        try {
            if (! Schema::hasTable('video_access_logs')) {
                return $response;
            }
            $ua = (string) $request->userAgent();
            [$isBot] = app(\App\Services\Stats\SpiderDetector::class)->detect($ua);
            \App\Models\Video\VideoAccessLog::query()->create([
                'ip' => (string) $request->ip(),
                'url' => mb_substr($request->fullUrl(), 0, 500),
                'ua' => mb_substr($ua, 0, 500),
                'is_bot' => $isBot ? 1 : 0,
                'created_at' => time(),
            ]);
        } catch (\Throwable) {
        }

        return $response;
    }
}
