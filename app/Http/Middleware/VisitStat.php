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
        foreach (['admin', 'install', 'member', 'api', 'api.php', 'player', 'css', 'js', 'static', 'storage'] as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return $response;
            }
        }
        try {
            app(SiteOpsService::class)->hitVisit((string) $request->ip());
        } catch (\Throwable) {
        }

        return $response;
    }
}
