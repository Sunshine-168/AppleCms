<?php

namespace App\Http\Middleware;

use App\Models\System\SysPermModel;
use App\Models\System\SysRolePermModel;
use App\Models\System\SysUserModel;
use App\Support\Utils\Ajax;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;

class AdminPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        // 白名单
        if ($this->isWhitelisted($request)) {
            return $next($request);
        }

        // 获取用户ID
        $uid = (int) ($request->attributes->get('admin_uid') ?? session('admin_uid', 0));

        if ($uid < 1) {
            return $this->unauth($request);
        }

        // 用户信息（加缓存）
        $user = Cache::remember("admin_user_{$uid}", 300, function () use ($uid) {
            return (new SysUserModel())->findById($uid);
        });

        if (empty($user)) {
            return $this->unauth($request);
        }

        // 超级管理员放行
        if ($this->isSuperAdmin($uid, $user)) {
            return $next($request);
        }

        // 获取权限标识
        $perm = $this->getPermission($request);

        // 没写进菜单的地址仍放行（列表接口等）
        if (empty($perm) || empty($perm['id'])) {
            return $next($request);
        }

        $roleId = (int) ($user['role_id'] ?? 0);

        if ($roleId < 1) {
            return $this->forbidden($request);
        }

        // 角色权限缓存（重点优化）
        $permIds = Cache::remember("role_perm_{$roleId}", 300, function () use ($roleId) {
            return (new SysRolePermModel())
                ->uniqueColumnByCondition(['role_id' => $roleId], 'perm_id');
        });

        $hasPerm = in_array((int)$perm['id'], array_map('intval', $permIds), true);

        if ($hasPerm) {
            return $next($request);
        }

        return $this->forbidden($request);
    }

    /**
     * 白名单
     */
    private function isWhitelisted(Request $request): bool
    {
        return $request->is([
            'admin/login',
            'admin/captcha',
            'admin/logout',
            'admin/ui-locale',
            'admin/unlock',
            'admin/welcome',
            'admin/more',
            'admin/plugins',
        ]);
    }

    /**
     * 是否超级管理员
     */
    private function isSuperAdmin(int $uid, array $user): bool
    {
        return $uid === 1;
    }

    /**
     * 获取权限
     */
    private function getPermission(Request $request): array
    {
        $path = '/' . ltrim((string)$request->path(), '/');
        $method = strtoupper((string)$request->method());

        $routeName = $request->route() ? (string)$request->route()->getName() : '';

        // 优先 route
        if ($routeName !== '') {
            $perm = (new SysPermModel())->findByCondition([
                ['api', '=', 'route:' . $routeName],
                ['method', 'in', ['', $method]],
            ]);

            if (!empty($perm)) {
                return $perm;
            }
        }

        // fallback path
        return (new SysPermModel())->findByCondition([
            ['api', '=', $path],
            ['method', 'in', ['', $method]],
        ]) ?? [];
    }

    /**
     * 未登录
     */
    private function unauth(Request $request): Response
    {
        if ($request->expectsJson() || $request->ajax()) {
            return Ajax::message(1001, '登录失效,请重新登录', []);
        }
        return redirect('/admin/login');
    }

    /**
     * 无权限
     */
    private function forbidden(Request $request): Response
    {
        if ($request->expectsJson() || $request->ajax()) {
            return Ajax::message(1003, '没有权限做这项操作', []);
        }

        return redirect('/admin/welcome')->with('error', '没有权限做这项操作');
    }
}
