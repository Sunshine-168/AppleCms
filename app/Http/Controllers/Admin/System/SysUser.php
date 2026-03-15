<?php
namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Services\System\SysUserService;
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
        return view('admin.system.user.index');
    }

    /**
     * 获取系统用户列表
     * @param Request $request
     * @return JsonResponse
     */
    public function getSystemUserLists(Request $request): JsonResponse
    {
        $username = (string) $request->input('username', '');
        $limit    = (int) $request->input('limit', 10);
        $data     = $this->systemUserService->getSysUserLists($username, $limit);    
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

        $data = $this->systemUserService->addSysUser($username, $password, $email, $remark, $role);

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

        $data     = $this->systemUserService->updateSysUser($id, $username, $password, $email, $remark, $role);

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
        $vscode   = (string) $request->input('vscode', '');

        $data     = $this->systemUserService->login($username, $password, $vscode);

        return Ajax::message($data['code'], $data['msg'], $data['data']);

    }
    /**
     * 显示系统用户登录日志页
     * @return Factory|View
     */
    public function showSystemUserLoginLogs(): View|Factory
    {
        return view('admin.system.monitor.login_logs');
    }

    /**
     * 系统用户登录日志列表
     * @param Request $request
     * @return JsonResponse
     */
    public function getSystemUserLoginLists(Request $request): JsonResponse
    {
        $params = [
            'username'      => (string) $request->input('username', ''),
            'login_ip'      => (string) $request->input('login_ip', ''),
            'start_time'    => (string) $request->input('start_time', ''),
            'end_time'      => (string) $request->input('end_time', ''),
            'limit'         => (int) $request->input('limit', 10),
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
        return view('admin.system.monitor.operate_logs');
    }

    /**
     * 系统用户操作日志列表
     * @param Request $request
     * @return JsonResponse
     */
    public function getSystemOperateLogLists(Request $request): JsonResponse
    {
        $params = [
            'username'      => (string) $request->input('username', ''),
            'login_ip'      => (string) $request->input('login_ip', ''),
            'method'        => (string) $request->input('method', ''),
            'url'           => (string) $request->input('url', ''),
            'route'         => (string) $request->input('route', ''),
            'status'        => $request->input('status', ''),
            'start_time'    => (string) $request->input('start_time', ''),
            'end_time'      => (string) $request->input('end_time', ''),
            'limit'         => (int) $request->input('limit', 10),
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
        return view('admin.system.monitor.system_logs');
    }
    
    /**
     * 系统日志列表
     * @param Request $request
     * @return JsonResponse
     */
    public function getSystemSystemLogLists(Request $request): JsonResponse
    {
        $params = [
            'level'         => (string) $request->input('level', ''),
            'channel'       => (string) $request->input('channel', ''),
            'module'        => (string) $request->input('module', ''),
            'username'      => (string) $request->input('username', ''),
            'uid'           => $request->input('uid', ''),
            'request_id'    => (string) $request->input('request_id', ''),
            'method'        => (string) $request->input('method', ''),
            'url'           => (string) $request->input('url', ''),
            'ip'            => (string) $request->input('ip', ''),
            'start_time'    => (string) $request->input('start_time', ''),
            'end_time'      => (string) $request->input('end_time', ''),
            'limit'         => (int) $request->input('limit', 10),
        ];

        $data = $this->systemUserService->getSysSystemLogLists($params);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }
}
