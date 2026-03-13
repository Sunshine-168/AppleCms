<?php

namespace app\admin\controller;

use app\common\utils\Ajax;
use app\common\utils\ServiceFactory;
use think\facade\Request;
use think\response\Json;

/**
 * 系统设置控制器
 */
class SysDict
{
    protected mixed $systemDictService;

    public function __construct()
    {
        $this->systemDictService = ServiceFactory::make();
    }

    /**
     * 获取系统设置列表
     * @return Json
     */
    public function getSysLists(): Json
    {
        $params = Request::only([
            'dict_key',
            'dict_type',
            'label'
        ]);

        $data = $this->systemDictService->getSysLists($params);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 更新系统设置
     * @return Json
     */
    public function updateSysSet(): Json
    {
        $params = Request::only([
            'id'            => 0,
            'dict_type'     => '',
            'dict_key'      => '',
            'dict_value'    => '',
            'value_type'    => '',
            'sort'          => 0,
            'status'        => 1,
            'remark'        => '',
            'label'         => '',
            'enum_limit'    => ''
        ]);


        $data = $this->systemDictService->updateSysSet($params);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 添加系统设置
     * @return Json
     */
    public function addSysSet(): Json
    {
        $params = Request::only([
            'dict_type'     => '',
            'dict_key'      => '',
            'dict_value'    => '',
            'value_type'    => '',
            'sort'          => 0,
            'status'        => 1,
            'remark'        => '',
            'label'         => '',
            'enum_limit'    => ''
        ]);

        $data = $this->systemDictService->addSysSet($params);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 删除系统设置
     * @return Json
     */
    public function deleteSysSet(): Json
    {
        $id = Request::param('id', 0);

        $data = $this->systemDictService->deleteSysSet($id);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 更新状态设置（启用/禁用）
     * @return Json
     */
    public function updateState(): Json
    {
        $params = Request::only([
            'id'     => 0,
            'status' => 0
        ]);

        $data = $this->systemDictService->updateState($params);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }
}

