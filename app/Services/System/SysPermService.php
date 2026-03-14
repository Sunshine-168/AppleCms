<?php
namespace App\Services\System;
use app\common\model\SysPermModel;
use app\common\utils\Result;
use function app\admin\service\v1\pageSize;

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
        if (!empty($params['name'])) $where[] = ['name', 'like', '%' . $params['name'] . '%'];
        if (!empty($params['code'])) $where[] = ['code', 'like', '%' . $params['code'] . '%'];
        if (isset($params['type']) && $params['type'] !== '') $where[] = ['type', '=', (int)$params['type']];
        if (isset($params['pid'])) $where[] = ['pid', '=', (int)$params['pid']];
        if (!empty($params['api'])) $where[] = ['api', 'like', '%' . $params['api'] . '%'];
        if (!empty($params['method'])) $where[] = ['method', '=', strtoupper($params['method'])];
        $data = $this->sysPermModel->paginates($where, '*', pageSize(), ['sort' => 'desc', 'id' => 'desc']);
        return Result::success($data);
    }

    /**
     * 新增权限
     * 支持菜单/按钮/接口型权限；接口型需填写 `api` 与 `method`
     */
    public function addPerm(array $data): array
    {
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['method'] = strtoupper($data['method'] ?? '');
        $res = $this->sysPermModel->inserts($data);
        if (!$res) return Result::fail('新增失败');
        return Result::success();
    }

    /**
     * 更新权限
     * 根据 `id` 更新权限基本信息；`method` 自动转为大写
     */
    public function updatePerm(int $id, array $data): array
    {
        $data['update_time'] = time();
        if (isset($data['method'])) $data['method'] = strtoupper($data['method']);
        $res = $this->sysPermModel->updateById($id, $data);
        if (!$res) return Result::fail('更新失败');
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
        $map = [];
        foreach ($list as $item) {
            $item['children'] = [];
            $map[$item['id']] = $item;
        }
        $root = [];
        foreach ($map as $id => &$node) {  // 使用引用传递
            $pid = (int)$node['pid'];
            if ($pid > 0 && isset($map[$pid])) {
                $map[$pid]['children'][] = &$node;  // 使用引用传递
            } else {
                $root[] = &$node;  // 使用引用传递
            }
        }
        unset($node); // 解除引用，避免后续误操作
        return Result::success($root);
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

        // 使用引用的方式构建树形结构
        // 先建立 id => node 的映射，并初始化 children
        $map = [];
        foreach ($list as $item) {
            $item['children'] = [];
            $map[$item['id']] = $item;
        }

        // 构建树形结构（关键：使用引用传递）
        $root = [];
        foreach ($map as $id => &$node) {  // 注意这里的 &$node 使用引用
            $pid = (int)$node['pid'];
            if ($pid > 0 && isset($map[$pid])) {
                // 有父节点，添加到父节点的 children 中
                $map[$pid]['children'][] = &$node;  // 使用引用传递
            } else {
                // 没有父节点或父节点不存在，作为根节点
                $root[] = &$node;  // 使用引用传递
            }
        }
        unset($node); // 解除引用

        return Result::success($root);
    }
}
