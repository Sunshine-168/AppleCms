<?php
namespace App\Http\Controllers\Admin\System;
use App\Http\Controllers\Controller;
use App\Services\Admin\System\SysRoleService;
use App\Support\Utils\Ajax;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SysRole extends Controller
{
    protected SysRoleService $systemRoleService;

    public function __construct()
    {
        $this->systemRoleService = new SysRoleService();
    }

    /**
     * 显示角色列表页面
     * @return View|Factory
     */
    public function index(): View|Factory
    {
        return view('admin.system.role.index');
    }

    /**
     * 获取角色列表
     * @param Request $request
     * @return JsonResponse
     */
    public function getRoleLists(Request $request): JsonResponse
    {
        $name = (string) $request->input('name', '');
        $code = (string) $request->input('code', '');
        $status = $request->input('status', '');
        $limit = (int) $request->input('limit', 10);

        $res = $this->systemRoleService->getRoleLists($name, $code, $status, $limit);
        return Ajax::message($res['code'], $res['msg'], $res['data']);
    }

    /**
     * 获取角色下拉选项
     * @return JsonResponse
     */
    public function getRoleOptions(): JsonResponse
    {
        $res = $this->systemRoleService->getRoleOptions();
        return Ajax::message($res['code'], $res['msg'], $res['data']);
    }

    /**
     * 添加角色
     * @param Request $request
     * @return JsonResponse
     */
    public function addRole(Request $request): JsonResponse
    {
        $name = (string) $request->input('name', '');
        $code = (string) $request->input('code', '');
        $remark = (string) $request->input('remark', '');
        $status = (int) $request->input('status', 1);
        $sort = (int) $request->input('sort', 0);

        $res = $this->systemRoleService->addRole($name, $code, $remark, $status, $sort);
        return Ajax::message($res['code'], $res['msg'], $res['data']);
    }

    /**
     * 更新角色
     * @param Request $request
     * @return JsonResponse
     */
    public function updateRole(Request $request): JsonResponse
    {
        $id = (int) $request->input('id', 0);
        $name = (string) $request->input('name', '');
        $code = (string) $request->input('code', '');
        $remark = (string) $request->input('remark', '');
        $status = (int) $request->input('status', 1);
        $sort = (int) $request->input('sort', 0);

        $res = $this->systemRoleService->updateRole($id, $name, $code, $remark, $status, $sort);
        return Ajax::message($res['code'], $res['msg'], $res['data']);
    }

    /**
     * 删除角色
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteRole(Request $request): JsonResponse
    {
        $id = (int) $request->input('id', 0);
        $res = $this->systemRoleService->deleteRole($id);
        return Ajax::message($res['code'], $res['msg'], $res['data']);
    }

    /**
     * 设置角色权限
     * @param Request $request
     * @return JsonResponse
     */
    public function setRolePerms(Request $request): JsonResponse
    {
        $roleId = (int) $request->input('role_id', 0);
        $permIds = $request->input('perm_ids', []);
        if (!is_array($permIds)) {
            $permIds = [];
        }

        $res = $this->systemRoleService->setRolePerms($roleId, $permIds);
        return Ajax::message($res['code'], $res['msg'], $res['data']);
    }

    /**
     * 获取角色权限ID列表
     * @param Request $request
     * @return JsonResponse
     */
    public function getRolePermIds(Request $request): JsonResponse
    {
        $roleId = (int) $request->input('role_id', 0);
        $res = $this->systemRoleService->getRolePermIds($roleId);
        return Ajax::message($res['code'], $res['msg'], $res['data']);
    }
}
