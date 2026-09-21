<?php
namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Services\Admin\System\SysUserService;
use App\Support\Utils\Ajax;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


/**
 * 系统用户
 */
class SysUser extends Controller
{

    protected SysUserService $systemUserService;

    public function __construct()
    {
        $this->systemUserService = new SysUserService();
    }

    /**
     * 显示系统用户列表
     * @return View|Factory
     */
    public function index(): View|Factory
    {
        $board = $this->systemUserService->adminBoard();

        return view('admin.system.user.index', [
            'roles' => $board['roles'] ?? [],
            'queues' => $board['queues'] ?? [],
            'currentId' => (int) session('admin_uid', 0),
        ]);
    }

    /**
     * 获取系统用户列表
     * @param Request $request
     * @return JsonResponse
     */
    public function getSystemUserLists(Request $request): JsonResponse
    {
        $data = $this->systemUserService->getSysUserLists([
            'q' => (string) ($request->input('q', '') !== '' ? $request->input('q') : $request->input('username', '')),
            'kind' => (string) $request->input('kind', ''),
            'role_id' => (string) $request->input('role_id', ''),
            'limit' => (int) $request->input('limit', 20),
        ]);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 获取系统用户详情
     * @param Request $request
     * @return JsonResponse
     */
    public function getSystemUserInfo(Request $request): JsonResponse
    {
        $id   = (int) $request->input('id', 0);
        $data = $this->systemUserService->getSysUserInfo($id);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 获取用户已绑定角色ID
     * 传入参数：user_id（用户ID）
     * 返回数据：该用户绑定的 role_id 数组
     */
    public function getUserRoleIds(Request $request): JsonResponse
    {
        $userId = (int) $request->input('user_id', 0);

        $data   = $this->systemUserService->getUserRoleIds($userId);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 设置用户角色
     * 传入参数：user_id（用户ID）、role_ids（角色ID数组）
     * 处理逻辑：先清空用户已有角色，再批量重建绑定关系
     */
    public function setUserRoles(Request $request): JsonResponse
    {
        $userId  = (int) $request->input('user_id', 0);

        $roleIds = $request->input('role_ids', []);

        if (!is_array($roleIds))
        {
            $roleIds = [];
        }

        $data     = $this->systemUserService->setUserRoles($userId, $roleIds);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 添加用户
     * @param Request $request
     * @return JsonResponse
     */
    public function addSystemUser(Request $request): JsonResponse
    {
        $username = (string) $request->input('username', '');
        $password = (string) $request->input('password', '');
        $email    = (string) $request->input('email', '');
        $remark   = (string) $request->input('remark', '');
        $role     = (int) $request->input('role', 1);
        $roleId   = (int) $request->input('role_id', 0);

        $data = $this->systemUserService->addSysUser($username, $password, $email, $remark, $role, $roleId);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 更新用户
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSystemUser(Request $request): JsonResponse
    {
        $id       = (int) $request->input('id', 0);
        $username = (string) $request->input('username', '');
        $password = (string) $request->input('password', '');
        $email    = (string) $request->input('email', '');
        $remark   = (string) $request->input('remark', '');
        $role     = $request->has('role') ? (int) $request->input('role', 1) : null;
        $roleId   = $request->has('role_id') ? (int) $request->input('role_id', 0) : null;

        $data     = $this->systemUserService->updateSysUser($id, $username, $password, $email, $remark, $role, $roleId);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 删除用户
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteSystemUser(Request $request): JsonResponse
    {
        $id   = (int) $request->input('id', 0);

        $data = $this->systemUserService->deleteSysUser($id);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 显示登录页
     * @return Factory|\Illuminate\View\View
     */
    public function showLogin(): Factory|View
    {
        return view('admin.system.user.login');
    }

    /**
     * 登入
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $username = (string) $request->input('username', '');
        $password = (string) $request->input('password', '');
        $vscode   = (string) ($request->input('captcha', '') !== '' ? $request->input('captcha') : $request->input('vscode', ''));

        try {
            $data = $this->systemUserService->login($username, $password, $vscode);
        } catch (\Throwable $e) {
            $msg = config('app.debug') && trim($e->getMessage()) !== '' ? $e->getMessage() : admin_t('auth.fail');

            return Ajax::message(1, $msg);
        }

        return Ajax::message((int) $data['code'], (string) ($data['msg'] ?? admin_t('auth.fail')), is_array($data['data'] ?? null) ? $data['data'] : []);

    }

    /**
     * 退出登录
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $uid = (int) session('admin_uid', 0);
        $token = (string) ($request->header('token') ?: $request->input('token', ''));

        $request->session()->forget(['admin_uid', 'admin_username']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $res = $this->systemUserService->logout($uid, $token);
        return Ajax::message($res['code'], $res['msg'], $res['data']);
    }

    /**
     * 显示修改密码页
     * @return View|Factory
     */
    public function showChangePassword(): View|Factory
    {
        return view('admin.system.user.password');
    }

    /**
     * 修改密码
     * @param Request $request
     * @return JsonResponse
     */
    public function changePassword(Request $request): JsonResponse
    {
        $uid = (int) session('admin_uid', 0);
        $currentPassword = (string) $request->input('current_password', '');
        $newPassword = (string) $request->input('new_password', '');
        $confirmPassword = (string) $request->input('confirm_password', '');

        $data = $this->systemUserService->changePassword($uid, $currentPassword, $newPassword, $confirmPassword);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }
    /**
     * 显示系统用户登录日志页
     * @return Factory|View
     */
    public function showSystemUserLoginLogs(): View|Factory
    {
        $now = now();

        return view('admin.system.monitor.login_logs', [
            'currentName' => (string) session('admin_username', ''),
            'today' => $now->toDateString(),
            'yesterday' => $now->copy()->subDay()->toDateString(),
            'weekFrom' => $now->copy()->subDays(6)->toDateString(),
            'monthFrom' => $now->copy()->subDays(29)->toDateString(),
        ]);
    }

    /**
     * 系统用户登录日志列表
     * @param Request $request
     * @return JsonResponse
     */
    public function getSystemUserLoginLists(Request $request): JsonResponse
    {
        $q = (string) $request->input('q', '');
        $username = (string) $request->input('username', '');
        $params = [
            'q' => $q !== '' ? $q : $username,
            'username' => $username,
            'login_ip' => (string) ($request->input('login_ip', '') ?: $request->input('ip', '')),
            'mine' => $request->boolean('mine'),
            'start_time' => (string) ($request->input('start_time', '') ?: $request->input('from', '')),
            'end_time' => (string) ($request->input('end_time', '') ?: $request->input('to', '')),
            'limit' => (int) $request->input('limit', 20),
        ];

        $data = $this->systemUserService->getSysUserLoginLists($params);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 显示系统用户操作日志页
     * @return Factory|View
     */
    public function showSystemOperateLogs(): View|Factory
    {
        $now = now();

        return view('admin.system.monitor.operate_logs', [
            'today' => $now->toDateString(),
            'yesterday' => $now->copy()->subDay()->toDateString(),
            'weekFrom' => $now->copy()->subDays(6)->toDateString(),
            'monthFrom' => $now->copy()->subDays(29)->toDateString(),
        ]);
    }

    /**
     * 系统用户操作日志列表
     * @param Request $request
     * @return JsonResponse
     */
    public function getSystemOperateLogLists(Request $request): JsonResponse
    {
        $q = (string) $request->input('q', '');
        $username = (string) $request->input('username', '');
        $params = [
            'q' => $q !== '' ? $q : $username,
            'username' => $username,
            'login_ip' => (string) ($request->input('login_ip', '') ?: $request->input('ip', '')),
            'mine' => $request->boolean('mine'),
            'start_time' => (string) ($request->input('start_time', '') ?: $request->input('from', '')),
            'end_time' => (string) ($request->input('end_time', '') ?: $request->input('to', '')),
            'limit' => (int) $request->input('limit', 20),
        ];

        $data = $this->systemUserService->getSysOperateLogLists($params);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 显示系统日志页
     * @return Factory|View
     */
    public function showSystemSystemLogs(): View|Factory
    {
        $now = now();

        return view('admin.system.monitor.system_logs', [
            'today' => $now->toDateString(),
            'yesterday' => $now->copy()->subDay()->toDateString(),
            'weekFrom' => $now->copy()->subDays(6)->toDateString(),
            'monthFrom' => $now->copy()->subDays(29)->toDateString(),
        ]);
    }

    /**
     * 系统日志列表
     * @param Request $request
     * @return JsonResponse
     */
    public function getSystemSystemLogLists(Request $request): JsonResponse
    {
        $params = [
            'q' => (string) $request->input('q', ''),
            'level' => (string) $request->input('level', ''),
            'area' => (string) $request->input('area', ''),
            'ip' => (string) ($request->input('ip', '') ?: $request->input('login_ip', '')),
            'start_time' => (string) ($request->input('start_time', '') ?: $request->input('from', '')),
            'end_time' => (string) ($request->input('end_time', '') ?: $request->input('to', '')),
            'limit' => (int) $request->input('limit', 20),
        ];

        $data = $this->systemUserService->getSysSystemLogLists($params);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }
}
