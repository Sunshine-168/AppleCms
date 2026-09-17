<?php

namespace App\Services\Stats;

use App\Models\Stat\StatHit;
use App\Support\FrontPageHit;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VisitRecorder
{
    public function __construct(private readonly SpiderDetector $spiders) {}

    public function shouldRecord(Request $request, Response $response): bool
    {
        if (! FrontPageHit::isDocumentGet($request)) {
            return false;
        }

        return $response->getStatusCode() < 500;
    }

    public function record(Request $request, Response $response): void
    {
        if (! $this->shouldRecord($request, $response)) {
            return;
        }

        $ua = (string) $request->userAgent();
        [$isSpider, $spiderName] = $this->spiders->detect($ua);
        $ip = (string) $request->ip();
        $path = '/'.ltrim($request->path(), '/');
        if ($path === '//') {
            $path = '/';
        }

        try {
            StatHit::query()->create([
                'path' => mb_substr($path, 0, 500),
                'query' => mb_substr((string) $request->getQueryString(), 0, 500) ?: null,
                'ip' => mb_substr($ip, 0, 45) ?: null,
                'visitor_hash' => hash('sha256', $ip.'|'.mb_substr($ua, 0, 200).'|'.now()->toDateString()),
                'user_agent' => mb_substr($ua, 0, 500) ?: null,
                'referer' => mb_substr((string) $request->headers->get('referer'), 0, 500) ?: null,
                'locale' => app()->getLocale(),
                'is_spider' => $isSpider,
                'spider_name' => $spiderName,
                'status_code' => $response->getStatusCode(),
                'created_at' => now(),
            ]);
        } catch (\Throwable) {
            // 统计失败不影响前台
        }
    }
}
