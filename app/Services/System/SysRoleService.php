<?php
namespace App\Services\System;
use App\Models\System\SysRoleModel;
use App\Models\System\SysRolePermModel;
use App\Support\Utils\Result;


/**
 * 角色服务类
 */
class SysRoleService
{
    public SysRoleModel $sysRoleModel;
    public SysRolePermModel $sysRolePermModel;

    public function __construct()
    {
        $this->sysRoleModel = new SysRoleModel();
        $this->sysRolePermModel = new SysRolePermModel();
    }
    /**
     * 获取角色列表
     * @param string $name 角色名称
     * @param string $code 角色标识
     * @param mixed $status 状态
     * @param int $limit 每页数量
     * @return array
     */
    public function getRoleLists(string $name, string $code, mixed $status, int $limit): array
    {
        $where = [];

        if ($limit < 1) 
        {
            $limit = 10;
        }

        if ($name = trim($name)) 
        {
            $where[] = ['name', '=', $name];
        }

        if ($code = trim($code)) 
        {
            $where[] = ['code', '=', $code];
        }
        if ($status !== '' && $status !== null) 
        {
            $where[] = ['status', '=', (int) $status];
        }

        $data = $this->sysRoleModel->paginates($where, '*', $limit, ['sort' => 'desc', 'id' => 'desc']);

        foreach ($data['data'] as &$item) 
        {
            $item['create_time'] = !empty($item['create_time']) ? date('Y-m-d H:i:s', (int) $item['create_time']) : '';
            $item['update_time'] = !empty($item['update_time']) ? date('Y-m-d H:i:s', (int) $item['update_time']) : '';
        }
        return Result::success($data);
    }

    /**
     * 添加角色
     * @param string $name 角色名称
     * @param string $code 角色标识
     * @param string $remark 备注
     * @param int $status 状态
     * @param int $sort 排序
     * @return array
     */
    public function addRole(string $name, string $code, string $remark = '', int $status = 1, int $sort = 0): array
    {
        $name = trim($name);
        $code = trim($code);
        $remark = trim($remark);

        if ($name === '' || $code === '') 
        {
            return Result::fail('参数错误');
        }

        if ($this->sysRoleModel->existsBy(['code' => $code])) 
        {
            return Result::fail('角色标识已存在');
        }

        $time = time();
        $res = $this->sysRoleModel->inserts([
            'name' => $name,
            'code' => $code,
            'remark' => $remark,
            'status' => (int) $status,
            'sort' => (int) $sort,
            'create_time' => $time,
            'update_time' => $time,
        ]);

        if (!$res) 
        {
            return Result::fail('新增失败');
        }

        return Result::success([], '新增成功');
    }
    /**
     * 更新角色
     * @param int $id 角色ID
     * @param string $name 角色名称
     * @param string $code 角色标识
     * @param string $remark 备注
     * @param int $status 状态
     * @param int $sort 排序
     * @return array
     */
    public function updateRole(int $id, string $name, string $code, string $remark = '', int $status = 1, int $sort = 0): array
    {
        if ($id < 1) 
        {
            return Result::fail('参数错误');
        }

        $name = trim($name);
        $code = trim($code);
        $remark = trim($remark);

        if ($name === '' || $code === '') 
        {
            return Result::fail('参数错误');
        }

        $exists = $this->sysRoleModel->where('code', $code)->where('id', '<>', $id)->exists();
        if ($exists) 
        {
            return Result::fail('角色标识已存在');
        }

        $res = $this->sysRoleModel->updateById($id, [
            'name' => $name,
            'code' => $code,
            'remark' => $remark,
            'status' => (int) $status,
            'sort' => (int) $sort,
            'update_time' => time(),
        ]);

        if (!$res) {
            return Result::fail('更新失败');
        }

        return Result::success([], '更新成功');
    }
    /**
     * 删除角色
     * @param int $id 角色ID
     * @return array
     */
    public function deleteRole(int $id): array
    {
        if ($id < 1) 
        {
            return Result::fail('参数错误');
        }

        $res = $this->sysRoleModel->deleteById($id);
        if (!$res) 
        {
            return Result::fail('删除失败');
        }

        $this->sysRolePermModel->deleteByCondition(['role_id' => $id]);

        return Result::success([], '删除成功');
    }
    /**
     * 设置角色权限
     * @param int $roleId 角色ID
     * @param array $permIds 权限ID列表
     * @return array
     */
    public function setRolePerms(int $roleId, array $permIds): array
    {
        if ($roleId < 1) 
        {
            return Result::fail('参数错误');
        }

        $this->sysRolePermModel->deleteByCondition(['role_id' => $roleId]);
        $rows = [];
        $time = time();
        foreach ($permIds as $pid) 
        {
            $rows[] = ['role_id' => (int)$roleId, 'perm_id' => (int)$pid, 'create_time' => $time, 'update_time' => $time];
        }
        if (!empty($rows)) 
        {
            $this->sysRolePermModel->insertsAll($rows);
        }
        return Result::success();
    }
    /**
     * 获取角色权限ID列表
     * @param int $roleId 角色ID
     * @return array
     */
    public function getRolePermIds(int $roleId): array
    {
        if ($roleId < 1) 
        {
            return Result::success([]);
        }

        $ids = $this->sysRolePermModel->uniqueColumnByCondition(['role_id' => $roleId], 'perm_id');
        return Result::success($ids);
    }
}
