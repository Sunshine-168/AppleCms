<?php
namespace App\Http\Controllers\Admin\System;
use app\common\utils\Ajax;
use app\common\utils\ServiceFactory;
use think\facade\Request;
use think\response\Json;

/**
 * 角色管理控制器
 * 提供角色的增删改查，以及角色与权限的绑定
 */
class SysRole
{
    protected mixed $service;
    public function __construct()
    {
        $this->service = ServiceFactory::make();
    }
    /**
     * 角色列表
     * 支持按名称、标识、状态筛选，返回分页数据
     */
    public function getRoleLists(): Json
    {
        $params = Request::only(['name','code','status']);
        $data = $this->service->getRoleLists($params);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }
    /**
     * 新增角色
     * 创建角色基础信息：名称、标识、备注、状态、排序
     */
    public function addRole(): Json
    {
        $params = Request::only(['name','code','remark','status','sort']);
        $data = $this->service->addRole($params);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }
    /**
     * 更新角色
     * 根据 `id` 更新角色基础信息
     */
    public function updateRole(): Json
    {
        $id = (int)Request::param('id', 0);
        $params = Request::only(['name','code','remark','status','sort']);
        $data = $this->service->updateRole($id, $params);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }
    /**
     * 删除角色
     * 根据 `id` 删除角色；注意会影响用户的角色绑定与权限
     */
    public function deleteRole(): Json
    {
        $id = (int)Request::param('id', 0);
        $data = $this->service->deleteRole($id);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }
    /**
     * 设置角色权限
     * 传入 `role_id` 与 `perm_ids[]` 批量绑定权限（先清空后重建）
     */
    public function setRolePerms(): Json
    {
        $roleId = (int)Request::param('role_id', 0);
        $permIds = Request::param('perm_ids', []);
        if (!is_array($permIds)) $permIds = [];
        $data = $this->service->setRolePerms($roleId, $permIds);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }
    /**
     * 获取角色已绑定权限ID
     * 根据 `role_id` 返回该角色的 `perm_id` 数组
     */
    public function getRolePermIds(): Json
    {
        $roleId = (int)Request::param('role_id', 0);
        $data = $this->service->getRolePermIds($roleId);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }
}
