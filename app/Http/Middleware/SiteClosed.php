<?php

namespace App\Http\Middleware;

use App\Services\Video\SiteFrontService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SiteClosed
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = trim($request->path(), '/');
        foreach (['admin', 'install', 'api', 'api.php', 'static', 'css', 'js', 'storage'] as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return $next($request);
            }
        }
        try {
            $tip = app(SiteFrontService::class)->siteClosed();
        } catch (\Throwable) {
            return $next($request);
        }
        if ($tip !== null) {
            return response($tip, 503);
        }

        return $next($request);
    }
}
