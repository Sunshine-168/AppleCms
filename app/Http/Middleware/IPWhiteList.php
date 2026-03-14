<?php


namespace App\Http\Middleware;

use App\Enums\System\StatusEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Utils\IpAddress;


/**
 * 检测ip
 * Class IPBlackList
 * @package app\common\middleware
 */
class IPWhiteList
{
    /**
     * 处理请求
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = IpAddress::realIP();
        $allowIps = config('ip_access.whitelist', []);

        if (!empty($allowIps) && !in_array($ip, $allowIps, true)) {
            return response()->json([
                'code' => StatusEnum::FAILED->value,
                'msg' => 'ip address not in white list [' . $ip . ']',
                'data' => (object) [],
            ]);
        }

        return $next($request);
    }
}
