<?php
namespace App\Services\System;
use App\Models\System\SysPermModel;
use App\Support\Utils\Result;

/**
 * 权限服务
 * 负责权限点的增删改查与菜单树构建
 */
class SysPermService
{
    public SysPermModel $sysPermModel;

    /**
     * 构造方法
     * 初始化权限模型
     */
    public function __construct()
    {
        $this->sysPermModel = new SysPermModel();
    }

    /**
     * 权限列表
     * 支持按名称、标识、类型、父级、API、HTTP 方法筛选
     * 返回分页数据
     */
    public function getPermLists(array $params): array
    {
        $where = [];
        $limit = (int) ($params['limit'] ?? 10);
        if ($limit < 1)
        {
            $limit = 10;
        }

        if (!empty($params['name']))
        {
            $where[] = ['name', '=', trim((string) $params['name'])];
        }

        if (!empty($params['code']))
        {
            $where[] = ['code', '=', trim((string) $params['code'])];
        }

        if (isset($params['type']) && $params['type'] !== '')
        {
            $where[] = ['type', '=', (int) $params['type']];
        }

        if (isset($params['pid']) && $params['pid'] !== '')
        {
            $where[] = ['pid', '=', (int) $params['pid']];
        }

        if (!empty($params['api']))
        {
            $where[] = ['api', '=', trim((string) $params['api'])];
        }

        if (!empty($params['method']))
        {
            $where[] = ['method', '=', strtoupper(trim((string) $params['method']))];
        }

        $data = $this->sysPermModel->paginates($where, '*', $limit, ['sort' => 'desc', 'id' => 'desc']);
        return Result::success($data);
    }

    /**
     * 菜单/权限列表
     * 支持按名称、标识、类型、父级、API、HTTP 方法筛选
     * 返回分页数据
     */
    public function getMenuFlatList(array $params): array
    {
        $where = [];

        if (!empty($params['name']))
        {
            $where[] = ['name', '=', trim((string) $params['name'])];
        }

        if (!empty($params['code']))
        {
            $where[] = ['code', '=', trim((string) $params['code'])];
        }

        if (!empty($params['api']))
        {
            $where[] = ['api', '=', trim((string) $params['api'])];
        }
        
        if (!empty($params['method']))
        {
            $where[] = ['method', '=', strtoupper(trim((string) $params['method']))];
        }

        if (isset($params['type']) && $params['type'] !== '')
        {
            $where[] = ['type', '=', (int) $params['type']];
        }

        $list = $this->sysPermModel->selectByCondition($where, '*', ['sort' => 'desc', 'id' => 'asc']);
        if (!is_array($list))
        {
            $list = [];
        }

        $tree = $this->buildTree($list);
        $this->sortTree($tree);
        $rows = $this->flattenTree($tree, 0);

        $parentNameMap = [];
        foreach ($list as $item)
        {
            if (isset($item['id']))
            {
                $parentNameMap[(int) $item['id']] = (string) ($item['name'] ?? '');
            }
        }
        foreach ($rows as &$row)
        {
            $pid = (int) ($row['pid'] ?? 0);
            $row['parent_name'] = $pid > 0 ? ($parentNameMap[$pid] ?? '') : '';
            $row['create_time'] = !empty($row['create_time']) ? date('Y-m-d H:i:s', (int) $row['create_time']) : '';
            $row['update_time'] = !empty($row['update_time']) ? date('Y-m-d H:i:s', (int) $row['update_time']) : '';
        }
        unset($row);

        return Result::success([
            'total' => count($rows),
            'data' => $rows,
        ]);
    }

    /**
     * 菜单/权限父级选项
     * 支持按类型筛选（1：菜单；2：按钮；3：接口）
     * 返回 ID 与名称键值对数组
     */
    public function getParentOptions(int $type = 1): array
    {
        $list = $this->sysPermModel->selectByCondition([['type', '=', $type]], '*', ['sort' => 'desc', 'id' => 'asc']);
        if (!is_array($list))
        {
            $list = [];
        }

        $tree = $this->buildTree($list);
        $this->sortTree($tree);
        $rows = $this->flattenTree($tree, 0);

        $options = [
            ['id' => 0, 'name' => '顶级'],
        ];
        foreach ($rows as $row)
        {
            $level = (int) ($row['level'] ?? 0);
            $prefix = str_repeat('— ', $level);
            $options[] = [
                'id' => (int) ($row['id'] ?? 0),
                'name' => $prefix . (string) ($row['name'] ?? ''),
            ];
        }

        return Result::success($options);
    }

    /**
     * 新增权限
     * 支持菜单/按钮/接口型权限；接口型需填写 `api` 与 `method`
     */
    public function addPerm(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $code = trim((string) ($data['code'] ?? ''));
        $api = trim((string) ($data['api'] ?? ''));
        $method = strtoupper(trim((string) ($data['method'] ?? '')));
        $type = (int) ($data['type'] ?? 1);
        $pid = (int) ($data['pid'] ?? 0);
        $sort = (int) ($data['sort'] ?? 0);
        $icon = trim((string) ($data['icon'] ?? ''));

        if ($name === '')
        {
            return Result::fail('名称不能为空');
        }

        if ($api !== '' && $code === '')
        {
            $code = $this->codeFromApi($api);
        }

        if ($code === '')
        {
            return Result::fail('标识不能为空');
        }

        $exists = $this->sysPermModel->existsBy([['code', '=', $code]]);
        if ($exists)
        {
            return Result::fail('标识已存在');
        }

        $now = time();
        $insert = [
            'name' => $name,
            'code' => $code,
            'api' => $api,
            'method' => $method,
            'pid' => $pid,
            'type' => $type,
            'icon' => $icon,
            'sort' => $sort,
            'create_time' => $now,
            'update_time' => $now,
        ];

        $res = $this->sysPermModel->inserts($insert);
        if (!$res)
        {
            return Result::fail('新增失败');
        }
        return Result::success();
    }

    /**
     * 更新权限
     * 根据 `id` 更新权限基本信息；`method` 自动转为大写
     */
    public function updatePerm(int $id, array $data): array
    {
        if ($id < 1)
        {
            return Result::fail('缺少ID');
        }

        $old = $this->sysPermModel->findById($id);
        if (empty($old))
        {
            return Result::fail('记录不存在');
        }

        $name = trim((string) ($data['name'] ?? ($old['name'] ?? '')));
        $code = trim((string) ($data['code'] ?? ($old['code'] ?? '')));
        $api = trim((string) ($data['api'] ?? ($old['api'] ?? '')));
        $method = strtoupper(trim((string) ($data['method'] ?? ($old['method'] ?? ''))));
        $type = (int) ($data['type'] ?? ($old['type'] ?? 1));
        $pid = (int) ($data['pid'] ?? ($old['pid'] ?? 0));
        $sort = (int) ($data['sort'] ?? ($old['sort'] ?? 0));
        $icon = trim((string) ($data['icon'] ?? ($old['icon'] ?? '')));

        if ($name === '')
        {
            return Result::fail('名称不能为空');
        }

        if ($api !== '' && $code === '')
        {
            $code = $this->codeFromApi($api);
        }

        if ($code === '')
        {
            return Result::fail('标识不能为空');
        }

        $exists = $this->sysPermModel->existsBy([
            ['id', '<>', $id],
            ['code', '=', $code],
        ]);
        if ($exists)
        {
            return Result::fail('标识已存在');
        }

        $update = [
            'name' => $name,
            'code' => $code,
            'api' => $api,
            'method' => $method,
            'pid' => $pid,
            'type' => $type,
            'icon' => $icon,
            'sort' => $sort,
            'update_time' => time(),
        ];

        $res = $this->sysPermModel->updateById($id, $update);
        if (!$res)
        {
            return Result::fail('更新失败');
        }
        return Result::success();
    }

    /**
     * 删除权限
     * 根据 `id` 删除权限点
     */
    public function deletePerm(int $id): array
    {
        $res = $this->sysPermModel->deleteById($id);
        if (!$res) return Result::fail('删除失败');
        return Result::success();
    }
        
    /**
     * 级联删除权限
     * 根据 `id` 级联删除权限点及其所有子项（菜单/按钮/接口）
     */
    public function deletePermCascade(int $id): array
    {
        if ($id < 1)
        {
            return Result::fail('缺少ID');
        }

        $list = $this->sysPermModel->selectByCondition([], ['id', 'pid'], ['id' => 'asc']);
        if (!is_array($list))
        {
            $list = [];
        }

        $childrenMap = [];
        foreach ($list as $row)
        {
            $pid = (int) ($row['pid'] ?? 0);
            $cid = (int) ($row['id'] ?? 0);
            if ($cid > 0)
            {
                $childrenMap[$pid][] = $cid;
            }
        }

        $toDelete = [];
        $stack = [$id];
        while (!empty($stack))
        {
            $cur = array_pop($stack);
            if (isset($toDelete[$cur]))
            {
                continue;
            }
            $toDelete[$cur] = true;
            foreach (($childrenMap[$cur] ?? []) as $childId)
            {
                $stack[] = $childId;
            }
        }

        $ids = array_keys($toDelete);
        if (empty($ids))
        {
            return Result::success();
        }

        $ok = $this->sysPermModel->deleteByCondition([['id', 'in', $ids]]);
        if (!$ok)
        {
            return Result::fail('删除失败');
        }

        return Result::success();
    }

    /**
     * 菜单树（包含按钮权限）
     * 返回包含菜单和按钮的完整树形结构
     * 一级模块(type=1) -> 二级菜单(type=1) -> 三级按钮(type=2)
     * 按 id 升序排序（id 越小越靠前）
     */
    public function getMenuTree(): array
    {
        // 查询所有菜单型(type=1)和按钮型(type=2)权限，按 id 升序排序
        $list = $this->sysPermModel->selectByCondition(
            [['type', 'in', [1, 2]]],
            '*',
            ['id' => 'asc']
        );
        if (!is_array($list))
        {
            $list = [];
        }

        $tree = $this->buildTree($list);
        $this->sortTree($tree);
        return Result::success($tree);
    }

    /**
     * 权限树（三级树形结构）
     * 返回包含一级模块、二级菜单、三级按钮的完整树形结构
     * 用于权限管理列表展示
     */
    public function getPermTree(): array
    {
        // 获取所有权限数据
        $list = $this->sysPermModel->selectByCondition([], '*', ['sort' => 'desc', 'id' => 'asc']);

        // 如果没有数据，直接返回
        if (empty($list)) {
            return Result::success([]);
        }

        if (!is_array($list))
        {
            $list = [];
        }

        $tree = $this->buildTree($list);
        $this->sortTree($tree);
        $applyTitle = function(array &$nodes) use (&$applyTitle): void {
            foreach ($nodes as &$n)
            {
                if (!isset($n['title']) || $n['title'] === '')
                {
                    $n['title'] = (string) ($n['name'] ?? '');
                }
                if (!empty($n['children']) && is_array($n['children']))
                {
                    $applyTitle($n['children']);
                }
            }
            unset($n);
        };
        $applyTitle($tree);
        return Result::success($tree);
    }
    /**
     * 从 API 路径生成权限标识
     * 支持路由格式（route:path）和 HTTP 路径格式（/path）
     */
    protected function codeFromApi(string $api): string
    {
        $api = trim($api);
        if ($api === '')
        {
            return '';
        }

        if (str_starts_with($api, 'route:'))
        {
            return $api;
        }

        if (str_starts_with($api, 'http://') || str_starts_with($api, 'https://'))
        {
            $path = (string) (parse_url($api, PHP_URL_PATH) ?: '');
            $path = trim($path);
            if ($path === '')
            {
                return '';
            }
            $api = $path;
        }

        $api = '/' . ltrim($api, '/');
        $api = preg_replace('#/+#', '/', $api);
        $api = trim((string) $api, '/');
        if ($api === '')
        {
            return '';
        }

        return str_replace('/', '.', $api);
    }

    /**
     * 构建权限树
     * 将权限列表转换为树形结构，按 pid 关联
     */
    protected function buildTree(array $list): array
    {
        $map = [];
        foreach ($list as $item)
        {
            if (!isset($item['id']))
            {
                continue;
            }
            $item['children'] = [];
            $map[(int) $item['id']] = $item;
        }

        $root = [];
        foreach ($map as $id => $node)
        {
            $pid = (int) ($node['pid'] ?? 0);
            if ($pid > 0 && isset($map[$pid]))
            {
                $map[$pid]['children'][] = $id;
            }
            else
            {
                $root[] = $id;
            }
        }

        $build = function(array $ids) use (&$build, &$map): array {
            $nodes = [];
            foreach ($ids as $id)
            {
                if (!isset($map[$id]))
                {
                    continue;
                }
                $n = $map[$id];
                $childIds = $n['children'] ?? [];
                $n['children'] = $build(is_array($childIds) ? $childIds : []);
                $nodes[] = $n;
            }
            return $nodes;
        };

        return $build($root);
    }
    
    /**
     * 递归排序权限树
     * 按 sort 降序排序，相同 sort 按 id 升序排序
     */
    protected function sortTree(array &$nodes): void
    {
        usort($nodes, function($a, $b) {
            $sa = (int) ($a['sort'] ?? 0);
            $sb = (int) ($b['sort'] ?? 0);
            if ($sa === $sb)
            {
                return ((int) ($a['id'] ?? 0)) <=> ((int) ($b['id'] ?? 0));
            }
            return $sb <=> $sa;
        });

        foreach ($nodes as &$n)
        {
            if (!empty($n['children']) && is_array($n['children']))
            {
                $this->sortTree($n['children']);
            }
        }
        unset($n);
    }
    
    /**
     * 递归展开权限树
     * 将树形结构展开为一维数组，包含层级信息
     */
    protected function flattenTree(array $nodes, int $level = 0): array
    {
        $rows = [];
        foreach ($nodes as $n)
        {
            $children = $n['children'] ?? [];
            $n['children'] = [];
            $n['level'] = $level;
            $rows[] = $n;
            if (!empty($children) && is_array($children))
            {
                $rows = array_merge($rows, $this->flattenTree($children, $level + 1));
            }
        }
        return $rows;
    }
}
