<?php
namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Utils\Result;


/**
 * 登入中间件
 * Class LoginMiddleware
 * @package app\home\middleware
 */
class LoginMiddleware
{

    /**
     * 处理请求
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next):Response
    {

        $reqUri    = $request->path();

        //白名单接口
        if(Str::contains($reqUri,  '/login/login'))
        {
            return $next($request);
        }

        //非名单接口
        if (empty((new User())->userByHeader('id')))
        {
            return response()->json(Result::info(444,[],'登录失效,请重新登录'));
        }

        return $next($request);

    }

}
