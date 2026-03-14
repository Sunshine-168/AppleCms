<?php
namespace App\Http\Controllers\Admin\System;
use app\common\utils\Ajax;
use app\common\utils\ServiceFactory;
use think\facade\Request;
use think\response\Json;

/**
 * 权限管理控制器
 * 负责权限点的增删改查与菜单树获取
 */
class SysPerm
{
    protected mixed $service;

    /**
     * 构造方法
     * 初始化 Service 实例（自动按版本路由到 v1）
     */
    public function __construct()
    {
        $this->service = ServiceFactory::make();
    }

    /**
     * 权限列表
     * 支持按名称、标识、类型、父级、API、HTTP 方法筛选
     * 返回分页数据
     */
    public function getPermLists(): Json
    {
        $params = Request::only(['name','code','type','pid','api','method']);
        $data = $this->service->getPermLists($params);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 新增权限
     * 允许创建菜单/按钮/接口型权限；接口型需填写 `api` 与 `method`
     */
    public function addPerm(): Json
    {
        $params = Request::only(['name','code','api','method','pid','type','icon','sort']);
        $data = $this->service->addPerm($params);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 更新权限
     * 根据 `id` 更新权限基本信息；变更接口型方法会自动转为大写
     */
    public function updatePerm(): Json
    {
        $id = (int)Request::param('id', 0);
        $params = Request::only(['name','code','api','method','pid','type','icon','sort']);
        $data = $this->service->updatePerm($id, $params);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 删除权限
     * 根据 `id` 删除权限点；需注意可能影响已绑定的角色权限
     */
    public function deletePerm(): Json
    {
        $id = (int)Request::param('id', 0);
        $data = $this->service->deletePerm($id);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 菜单树
     * 返回仅包含 `type=1`（菜单型）的树形结构，用于前端渲染菜单
     */
    public function getMenuTree(): Json
    {
        $data = $this->service->getMenuTree();
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 权限树（三级树形结构）
     * 返回包含一级模块、二级菜单、三级按钮的完整树形结构
     * 用于权限管理列表展示
     */
    public function getPermTree(): Json
    {
        $data = $this->service->getPermTree();
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }
}
