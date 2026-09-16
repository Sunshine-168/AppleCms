<?php

namespace App\Services\Admin\System;

use App\Models\System\SysRoleModel;
use App\Models\System\SysRolePermModel;
use App\Models\System\SysUserModel;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\Cache;

class SysRoleService
{
    public SysRoleModel $sysRoleModel;

    public SysRolePermModel $sysRolePermModel;

    public SysUserModel $sysUserModel;

    public function __construct()
    {
        $this->sysRoleModel = new SysRoleModel();
        $this->sysRolePermModel = new SysRolePermModel();
        $this->sysUserModel = new SysUserModel();
    }

    /**
     * @return array{queues:array<string,int>}
     */
    public function roleBoard(): array
    {
        $queues = ['all' => 0, 'used' => 0, 'empty' => 0, 'off' => 0];
        try {
            $queues['all'] = $this->sysRoleModel->countByCondition([]);
            $queues['off'] = $this->sysRoleModel->countByCondition([['status', '=', 0]]);
            $usedIds = $this->usedRoleIds();
            $queues['used'] = count($usedIds);
            $queues['empty'] = max(0, $queues['all'] - $queues['used']);
        } catch (\Throwable) {
        }

        return compact('queues');
    }

    /**
     * @param  array{q?:string,kind?:string,limit?:int}  $params
     */
    public function getRoleLists(array $params): array
    {
        $where = [];
        $q = trim((string) ($params['q'] ?? ''));
        $kind = (string) ($params['kind'] ?? '');
        $limit = (int) ($params['limit'] ?? 20);
        if ($limit < 1) {
            $limit = 20;
        }
        if ($q !== '') {
            $like = '%'.$q.'%';
            $where['or'] = [
                ['name', 'like', $like],
                ['code', 'like', $like],
                ['remark', 'like', $like],
            ];
        }
        $usedIds = $this->usedRoleIds();
        if ($kind === 'used') {
            if ($usedIds === []) {
                return Result::success(['total' => 0, 'data' => []]);
            }
            $where[] = ['id', 'in', $usedIds];
        } elseif ($kind === 'empty') {
            if ($usedIds !== []) {
                $where[] = ['id', 'not in', $usedIds];
            }
        } elseif ($kind === 'off') {
            $where[] = ['status', '=', 0];
        }

        $data = $this->sysRoleModel->paginates($where, '*', $limit, ['sort' => 'desc', 'id' => 'asc']);
        $permCounts = $this->permCounts();
        $userCounts = $this->userCounts();
        foreach ($data['data'] as &$item) {
            $id = (int) ($item['id'] ?? 0);
            $users = (int) ($userCounts[$id] ?? 0);
            $perms = (int) ($permCounts[$id] ?? 0);
            $item['users_count'] = $users;
            $item['perms_count'] = $perms;
            $item['can_delete'] = $users === 0;
            $item['in_use'] = $users > 0;
            $item['create_time'] = ! empty($item['create_time']) ? date('Y-m-d H:i:s', (int) $item['create_time']) : '';
            $item['update_time'] = ! empty($item['update_time']) ? date('Y-m-d H:i:s', (int) $item['update_time']) : '';
        }
        unset($item);

        return Result::success($data);
    }

    public function getRoleOptions(): array
    {
        $list = $this->sysRoleModel->selectByCondition([], ['id', 'name', 'status'], ['sort' => 'desc', 'id' => 'desc']);

        return Result::success($list);
    }

    public function addRole(string $name, string $code, string $remark = '', int $status = 1, int $sort = 0): array
    {
        $name = trim($name);
        $code = trim($code);
        $remark = trim($remark);
        if ($name === '') {
            return Result::fail('请填写名称');
        }
        if ($code === '') {
            $code = $this->uniqueCode($name);
        } elseif ($this->sysRoleModel->existsBy([['code', '=', $code]])) {
            return Result::fail('这个标识已经有了');
        }

        $time = time();
        $id = $this->sysRoleModel->insertsGetId([
            'name' => $name,
            'code' => $code,
            'remark' => $remark,
            'status' => $status === 0 ? 0 : 1,
            'sort' => (int) $sort,
            'create_time' => $time,
            'update_time' => $time,
        ]);
        if (! $id) {
            return Result::fail('没能添加');
        }

        return Result::success(['id' => (int) $id], '已添加，接着勾能进哪些菜单');
    }

    public function updateRole(int $id, string $name, string $code, string $remark = '', int $status = 1, int $sort = 0): array
    {
        if ($id < 1) {
            return Result::fail('角色不存在');
        }
        $row = $this->sysRoleModel->findById($id);
        if (! $row) {
            return Result::fail('角色不存在');
        }
        $name = trim($name);
        $code = trim($code);
        $remark = trim($remark);
        if ($name === '') {
            return Result::fail('请填写名称');
        }
        if ($code === '') {
            $code = (string) ($row['code'] ?? $this->uniqueCode($name, $id));
        }
        $dup = $this->sysRoleModel->findByCondition([['code', '=', $code]]);
        if ($dup && (int) ($dup['id'] ?? 0) !== $id) {
            return Result::fail('这个标识已经有了');
        }

        $res = $this->sysRoleModel->updateById($id, [
            'name' => $name,
            'code' => $code,
            'remark' => $remark,
            'status' => $status === 0 ? 0 : 1,
            'sort' => (int) $sort,
            'update_time' => time(),
        ]);
        if (! $res) {
            return Result::fail('没能保存');
        }

        return Result::success([], '已保存');
    }

    public function deleteRole(int $id): array
    {
        if ($id < 1) {
            return Result::fail('角色不存在');
        }
        $users = $this->sysUserModel->countByCondition([['role_id', '=', $id]]);
        if ($users > 0) {
            return Result::fail('有人在用这个角色，先换人再删');
        }

        $res = $this->sysRoleModel->deleteById($id);
        if (! $res) {
            return Result::fail('没能删除');
        }
        $this->sysRolePermModel->deleteByCondition(['role_id' => $id]);
        Cache::forget('role_perm_'.$id);

        return Result::success([], '已删除');
    }

    public function setRolePerms(int $roleId, array $permIds): array
    {
        if ($roleId < 1) {
            return Result::fail('角色不存在');
        }

        $this->sysRolePermModel->deleteByCondition(['role_id' => $roleId]);
        $rows = [];
        $time = time();
        foreach ($permIds as $pid) {
            $pid = (int) $pid;
            if ($pid < 1) {
                continue;
            }
            $rows[] = ['role_id' => $roleId, 'perm_id' => $pid, 'create_time' => $time, 'update_time' => $time];
        }
        if ($rows !== []) {
            $this->sysRolePermModel->insertsAll($rows);
        }
        Cache::forget('role_perm_'.$roleId);

        return Result::success([], '已保存权限');
    }

    public function getRolePermIds(int $roleId): array
    {
        if ($roleId < 1) {
            return Result::success([]);
        }

        $ids = $this->sysRolePermModel->uniqueColumnByCondition(['role_id' => $roleId], 'perm_id');

        return Result::success($ids);
    }

    /** @return list<int> */
    protected function usedRoleIds(): array
    {
        $ids = $this->sysUserModel->uniqueColumnByCondition([['role_id', '>', 0]], 'role_id');
        $out = [];
        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $out[] = $id;
            }
        }

        return array_values(array_unique($out));
    }

    /** @return array<int,int> */
    protected function userCounts(): array
    {
        $rows = $this->sysUserModel->selectByCondition([['role_id', '>', 0]], ['id', 'role_id']);
        $out = [];
        foreach ($rows as $row) {
            $id = (int) ($row['role_id'] ?? 0);
            if ($id > 0) {
                $out[$id] = ($out[$id] ?? 0) + 1;
            }
        }

        return $out;
    }

    /** @return array<int,int> */
    protected function permCounts(): array
    {
        $rows = $this->sysRolePermModel->selectByCondition([], ['role_id', 'perm_id']);
        $out = [];
        foreach ($rows as $row) {
            $id = (int) ($row['role_id'] ?? 0);
            if ($id > 0) {
                $out[$id] = ($out[$id] ?? 0) + 1;
            }
        }

        return $out;
    }

    protected function uniqueCode(string $name, int $exceptId = 0): string
    {
        $base = strtolower(trim((string) preg_replace('/[^a-zA-Z0-9]+/', '_', $name)));
        $base = trim($base, '_');
        if ($base === '' || is_numeric($base)) {
            $base = 'role';
        }
        $code = $base;
        $n = 2;
        while (true) {
            $dup = $this->sysRoleModel->findByCondition([['code', '=', $code]]);
            if (! $dup || (int) ($dup['id'] ?? 0) === $exceptId) {
                return $code;
            }
            $code = $base.'_'.$n;
            $n++;
        }
    }
}
