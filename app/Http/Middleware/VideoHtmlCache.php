<?php

namespace App\Http\Middleware;

use App\Services\Video\DiskHtmlService;
use App\Services\Video\HtmlCacheService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VideoHtmlCache
{
    public function __construct(
        private readonly HtmlCacheService $htmlCache,
        private readonly DiskHtmlService $diskHtml,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (! $this->htmlCache->isInternal($request)) {
                $disk = $this->diskHtml->htmlForRequest($request);
                if (is_string($disk) && $disk !== '') {
                    return response($disk, 200, [
                        'Content-Type' => 'text/html; charset=UTF-8',
                        'X-Vod-Disk-Html' => 'HIT',
                    ]);
                }
                $cached = $this->htmlCache->get($request);
                if (is_string($cached) && $cached !== '') {
                    return response($cached, 200, [
                        'Content-Type' => 'text/html; charset=UTF-8',
                        'X-Vod-Html-Cache' => 'HIT',
                    ]);
                }
            }
        } catch (\Throwable) {
            return $next($request);
        }

        /** @var Response $response */
        $response = $next($request);
        try {
            if (
                $response->getStatusCode() === 200
                && str_contains((string) $response->headers->get('Content-Type', 'text/html'), 'text/html')
            ) {
                $content = $response->getContent();
                if (is_string($content) && $content !== '') {
                    $this->htmlCache->put($request, $content);
                    if ($this->htmlCache->isCacheable($request)) {
                        $response->headers->set('X-Vod-Html-Cache', 'MISS');
                    }
                }
            }
        } catch (\Throwable) {
        }

        return $response;
    }
}
