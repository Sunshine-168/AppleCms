<?php
namespace App\Http\Middleware\Index;

use Closure;
use Illuminate\Http\Request;

class HtmlspecialcharsMiddleware
{

    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('post'))
        {
            $postData = $request->post();

            // 对所有POST参数进行htmlspecialchars处理
            $escapedPostData = array_map(function($value) {
                return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }, $postData);

            // 将处理后的数据重新设置到请求对象中（可选）
            // 注意：这不会改变原始的$_POST超全局变量，只是修改了ThinkPHP的请求对象
            $request->merge($escapedPostData);

            // 或者，你也可以选择不修改请求对象，而是在中间件之后从请求对象中获取已处理的数据
            // $escapedPostData将在$next($request)之后可用，但你需要以某种方式将其传递给控制器
        }

        if ($request->isMethod('get'))
        {
            $getData = $request->query();

            // 对所有GET参数进行htmlspecialchars处理
            $escapedPostData = array_map(function($value) {
                return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }, $getData);

            // 将处理后的数据重新设置到请求对象中（可选）
            // 注意：这不会改变原始的$_GET超全局变量，只是修改了ThinkPHP的请求对象
            $request->merge($escapedPostData);

            // 或者，你也可以选择不修改请求对象，而是在中间件之后从请求对象中获取已处理的数据
            // $escapedPostData将在$next($request)之后可用，但你需要以某种方式将其传递给控制器
        }

        return $next($request);
    }
}