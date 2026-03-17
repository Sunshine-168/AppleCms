<?php

namespace App\Http\Middleware;

use App\Models\System\SysUserModel;
use App\Support\Utils\Ajax;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 管理员认证中间件
 */ 
class AdminAuth
{  
    /**
     * 处理请求
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isWhitelisted($request)) {
            return $next($request);
        }

        if ($this->isAuthed($request)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return Ajax::message(1001, '登录失效,请重新登录', []);
        }

        return redirect('/admin/login');
    }

    /**
     * 判断是否为白名单路由
     *
     * @param Request $request
     * @return bool
     */
    private function isWhitelisted(Request $request): bool
    {
        return $request->is('admin/login')
            || $request->is('admin/captcha')
            || $request->is('admin/logout');
    }

    /**
     * 判断是否已认证
     *
     * @param Request $request
     * @return bool
     */
    private function isAuthed(Request $request): bool
    {
        $uid = (int) session('admin_uid', 0);
        $username = (string) session('admin_username', '');

        if ($uid > 0 && $username !== '') {
            return true;
        }

        $auth = (string) $request->header('Authorization', '');
        $bearer = '';
        if ($auth !== '' && str_starts_with($auth, 'Bearer ')) {
            $bearer = trim(substr($auth, 7));
        }

        $token = (string) (
            $request->header('token')
            ?: $request->header('X-Token')
            ?: $request->header('x-token')
            ?: $bearer
            ?: $request->input('token', '')
            ?: $request->input('access_token', '')
        );

        if ($token === '') {
            return false;
        }

        $user = (new SysUserModel())->findByCondition([['token', '=', $token]]);
        if (empty($user)) {
            return false;
        }

        session([
            'admin_uid' => (int) ($user['id'] ?? 0),
            'admin_username' => (string) ($user['username'] ?? ''),
        ]);

        return session('admin_uid', 0) > 0 && (string) session('admin_username', '') !== '';
    }
}

