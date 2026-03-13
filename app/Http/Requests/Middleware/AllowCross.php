<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 跨域请求中间件
 * Class AllowCross
 * @package app\common\middleware
 */
class AllowCross
{
    /**
     * 处理请求
     * @param  Request  $request
     * @param Closure $next
     * @return Response
     */
    public function handle( Request $request, Closure $next): Response
    {

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE, HEAD');

        if ($request->isMethod('OPTIONS')) 
        {
            return response('');
        }

        return $next($request);
    }
}
