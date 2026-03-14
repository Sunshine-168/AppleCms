<?php
namespace App\Services\System;
use app\common\model\SysRoleModel;
use app\common\model\SysRolePermModel;
use app\common\utils\Result;
use function app\admin\service\v1\pageSize;

/**
 * 角色服务
 * 提供角色的增删改查与角色权限绑定管理
 */
class SysRoleService
{
    public SysRoleModel $sysRoleModel;
    public SysRolePermModel $sysRolePermModel;

    /**
     * 构造方法
     * 初始化角色模型与角色权限模型
     */
    public function __construct()
    {
        $this->sysRoleModel = new SysRoleModel();
        $this->sysRolePermModel = new SysRolePermModel();
    }

    /**
     * 角色列表
     * 支持按名称、标识、状态筛选，返回分页数据
     */
    public function getRoleLists(array $params): array
    {
        $where = [];
        if (!empty($params['name'])) $where[] = ['name', 'like', '%' . $params['name'] . '%'];
        if (!empty($params['code'])) $where[] = ['code', 'like', '%' . $params['code'] . '%'];
        if (isset($params['status']) && $params['status'] !== '') $where[] = ['status', '=', (int)$params['status']];
        $data = $this->sysRoleModel->paginates($where, '*', pageSize(), ['sort' => 'desc', 'id' => 'desc']);
        return Result::success($data);
    }

    /**
     * 新增角色
     * 创建角色基础信息：名称、标识、备注、状态、排序
     */
    public function addRole(array $data): array
    {
        $data['create_time'] = time();
        $data['update_time'] = time();
        $res = $this->sysRoleModel->inserts($data);
        if (!$res) return Result::fail('新增失败');
        return Result::success();
    }

    /**
     * 更新角色
     * 根据 `id` 更新角色基础信息
     */
    public function updateRole(int $id, array $data): array
    {
        $data['update_time'] = time();
        $res = $this->sysRoleModel->updateById($id, $data);
        if (!$res) return Result::fail('更新失败');
        return Result::success();
    }

    /**
     * 删除角色
     * 根据 `id` 删除角色
     */
    public function deleteRole(int $id): array
    {
        $res = $this->sysRoleModel->deleteById($id);
        if (!$res) return Result::fail('删除失败');
        return Result::success();
    }

    /**
     * 设置角色权限
     * 传入 `role_id` 与 `perm_ids[]` 批量绑定权限（先清空后重建）
     */
    public function setRolePerms(int $roleId, array $permIds): array
    {
        $this->sysRolePermModel->deleteByCondition(['role_id' => $roleId]);
        $rows = [];
        $time = time();
        foreach ($permIds as $pid) {
            $rows[] = ['role_id' => (int)$roleId, 'perm_id' => (int)$pid, 'create_time' => $time, 'update_time' => $time];
        }
        if (!empty($rows)) {
            $this->sysRolePermModel->insertsAll($rows);
        }
        return Result::success();
    }

    /**
     * 获取角色已绑定权限ID
     * 根据 `role_id` 返回该角色的 `perm_id` 数组
     */
    public function getRolePermIds(int $roleId): array
    {
        $ids = $this->sysRolePermModel->uniqueColumnByCondition(['role_id' => $roleId], 'perm_id');
        return Result::success($ids);
    }
}
