<?php

namespace App\Http\Middleware;

use App\Utils\RouteWhitelist;
use App\Utils\UserContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PublicUriMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        [$module, $path] = $this->resolveRouteInfo($request);

        if ($module !== '' && RouteWhitelist::check($path, $module)) {
            return $next($request);
        }

        if (empty(UserContext::id())) {
            return response()->json([
                'code' => 401,
                'msg' => '未登录或登录已过期',
                'data' => [],
            ]);
        }

        return $next($request);
    }

    private function resolveRouteInfo(Request $request): array
    {
        $segments = $request->segments();
        if (($segments[0] ?? '') === 'api')
        {
            array_shift($segments);
        }

        $module = $segments[0] ?? '';

        if (isset($segments[1]) && preg_match('/^v\d+$/i', $segments[1]))
        {
            $pathSegments = array_slice($segments, 2);
        } else
        {
            $pathSegments = array_slice($segments, 1);
        }

        return [$module, implode('/', $pathSegments)];
    }
}
