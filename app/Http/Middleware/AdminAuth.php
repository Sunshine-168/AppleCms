<?php

namespace App\Http\Middleware;

use App\Models\System\SysUserModel;
use App\Support\Utils\Ajax;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 管理员认证中间件
 */
class AdminAuth
{
    /**
     * 处理请求
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 白名单
        if ($this->isWhitelisted($request)) 
        {
            return $next($request);
        }

        // 已登录
        if ($this->isAuthed($request)) 
        {
            return $next($request);
        }

        // ajax请求
        if ($request->expectsJson() || $request->ajax()) 
        {
            return Ajax::message(1001, '登录失效,请重新登录', []);
        }

        // 页面请求
        return redirect('/admin/login');
    }

    /**
     * 白名单
     */
    private function isWhitelisted(Request $request): bool
    {
        return $request->is([
            'admin/login',
            'admin/captcha',
            'admin/logout'
        ]);
    }

    /**
     * 是否登录
     */
    private function isAuthed(Request $request): bool
    {
        // session判断
        $uid = (int)session('admin_uid', 0);
        $username = (string)session('admin_username', '');

        if ($uid > 0 && $username !== '') 
        {
            return true;
        }

        // 获取token
        $token = $this->getToken($request);

        if ($token === '') 
        {
            return false;
        }

        // 查询用户
        $user = (new SysUserModel())->findByCondition([
            ['token', '=', $token]
        ]);

        if (empty($user)) {
            return false;
        }

        // 写入session
        session([
            'admin_uid' => (int)$user['id'],
            'admin_username' => (string)$user['username'],
        ]);

        return true;
    }

    /**
     * 获取token
     */
    private function getToken(Request $request): string
    {
        $auth = (string)$request->header('Authorization', '');

        if ($auth !== '' && str_starts_with($auth, 'Bearer ')) 
        {
            return trim(substr($auth, 7));
        }

        return (string)(
            $request->header('token')
            ?: $request->header('X-Token')
            ?: $request->header('x-token')
            ?: $request->input('token', '')
            ?: $request->input('access_token', '')
        );
    }
}