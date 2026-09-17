<?php

namespace App\Http\Middleware;

use App\Services\Video\SiteOpsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VisitStat
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $response;
        }
        $path = trim($request->path(), '/');
        foreach (['admin', 'install', 'member', 'api', 'api.php', 'player', 'css', 'js', 'static', 'storage', 'plugin-assets', 'livewire'] as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return $response;
            }
        }
        try {
            $videoId = null;
            $typeId = null;
            if (preg_match('#(?:^|/)vod/(\d+)#', $path, $m) || preg_match('#(?:^|/)play/(\d+)#', $path, $m)) {
                $videoId = (int) $m[1];
            }
            if (preg_match('#(?:^|/)type/(\d+)#', $path, $m)) {
                $typeId = (int) $m[1];
            }
            app(SiteOpsService::class)->hitVisit((string) $request->ip(), $videoId, $typeId);
        } catch (\Throwable) {
        }

        return $response;
    }
}
