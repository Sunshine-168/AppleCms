<?php

namespace App\Http\Middleware;

use App\Services\Video\VideoSettingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminIpAllow
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $raw = trim((string) app(VideoSettingService::class)->get('admin_ip_allow', ''));
        } catch (\Throwable) {
            return $next($request);
        }
        if ($raw === '') {
            return $next($request);
        }
        $ip = (string) $request->ip();
        foreach (preg_split('/[\s,]+/', $raw) ?: [] as $allow) {
            $allow = trim((string) $allow);
            if ($allow === '' || $allow === '*') {
                continue;
            }
            if ($allow === $ip) {
                return $next($request);
            }
        }

        abort(403, '当前 IP 不在后台白名单');
    }
}
