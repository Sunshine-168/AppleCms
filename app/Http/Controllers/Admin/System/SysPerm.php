<?php

namespace App\Http\Controllers\Admin\System;
use App\Http\Controllers\Controller;
use App\Services\Admin\System\SysPermService;
use App\Support\Utils\Ajax;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 菜单/权限控制器
 */
class SysPerm extends Controller
{
    protected SysPermService $sysPermService;

    public function __construct()
    {
        $this->sysPermService = new SysPermService();
    }
    /**
     * 菜单/权限列表页
     * @return View|Factory
     */
    public function index(): View|Factory
    {
        $this->sysPermService->syncFromWorkspaces();

        return view('admin.system.menu.index');
    }

    /**
     * 获取菜单/权限列表
     * @param Request $request
     * @return JsonResponse
     */
    public function getMenuLists(Request $request): JsonResponse
    {
        $params = $request->only(['name', 'code', 'api', 'type', 'page', 'limit']);
        $data = $this->sysPermService->getMenuFlatList($params);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 获取菜单/权限父级选项
     * @param Request $request
     * @return JsonResponse
     */
    public function getMenuParentOptions(Request $request): JsonResponse
    {
        $type = (int) $request->input('type', 1);
        if (!in_array($type, [1, 2, 3], true))
        {
            $type = 1;
        }
        $data = $this->sysPermService->getParentOptions($type);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 添加菜单/权限
     * @param Request $request
     * @return JsonResponse
     */
    public function addMenu(Request $request): JsonResponse
    {
        $params = $request->only(['name', 'code', 'api', 'method', 'pid', 'type', 'icon', 'sort']);
        $data = $this->sysPermService->addPerm($params);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 更新菜单/权限
     * @param Request $request
     * @return JsonResponse
     */
    public function updateMenu(Request $request): JsonResponse
    {
        $id = (int) $request->input('id', 0);
        $params = $request->only(['name', 'code', 'api', 'method', 'pid', 'type', 'icon', 'sort']);
        $data = $this->sysPermService->updatePerm($id, $params);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 删除菜单/权限
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteMenu(Request $request): JsonResponse
    {
        $id = (int) $request->input('id', 0);
        $data = $this->sysPermService->deletePermCascade($id);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 把当前侧栏/全部功能写成权限点
     */
    public function syncMenus(): JsonResponse
    {
        $data = $this->sysPermService->syncFromWorkspaces();
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 获取权限树（角色授权用）
     * @return JsonResponse
     */
    public function getPermTree(): JsonResponse
    {
        $data = $this->sysPermService->getPermTree();
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }
}
