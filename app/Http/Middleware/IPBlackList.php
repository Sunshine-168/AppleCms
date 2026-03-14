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
class IPBlackList
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
        $denyIps = config('ip_access.blacklist', []);

        if (in_array($ip, $denyIps, true)) {
            return response()->json([
                'code' => StatusEnum::FAILED->value,
                'msg' => 'ip address in black list [' . $ip . ']',
                'data' => (object) [],
            ]);
        }

        return $next($request);
    }
}
