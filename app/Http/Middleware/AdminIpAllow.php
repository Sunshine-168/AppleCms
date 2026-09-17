<?php

namespace App\Http\Middleware;

use App\Services\Video\VideoSettingService;
use App\Support\AdminIpAllowlist;
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
        $rules = AdminIpAllowlist::parse($raw);
        if ($rules === []) {
            return $next($request);
        }
        $ip = (string) $request->ip();
        if (AdminIpAllowlist::allows($ip, $rules)) {
            return $next($request);
        }

        abort(403, '当前 IP '.$ip.' 不在后台白名单');
    }
}
