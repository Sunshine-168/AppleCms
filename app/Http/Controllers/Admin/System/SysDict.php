<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Services\Admin\System\SysDictService;
use App\Support\Utils\Ajax;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 系统设置控制器
 */
class SysDict extends Controller
{
    protected SysDictService $systemDictService;

    public function __construct()
    {
        $this->systemDictService = new SysDictService();
    }

    /**
     * 系统字典列表
     */
    public function index(): View|Factory
    {
        return view('admin.system.dict.index', $this->systemDictService->pageBoard());
    }

    /**
     * 获取系统字典列表
     */
    public function getSysLists(Request $request): JsonResponse
    {
        $dictType = (string) $request->input('dict_type', '');
        $q        = (string) $request->input('q', '');
        if ($q === '') {
            $q = (string) $request->input('dict_key', '');
        }
        if ($q === '') {
            $q = (string) $request->input('label', '');
        }
        $status = (string) $request->input('status', '');
        $limit  = (int) $request->input('limit', 20);

        $data = $this->systemDictService->getSysLists($dictType, $q, $status, $limit);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 添加系统字典
     */
    public function addSysSet(Request $request): JsonResponse
    {
        $dictType  = (string) $request->input('dict_type', '');
        $dictKey   = (string) $request->input('dict_key', '');
        $valueType = (int) $request->input('value_type', 0);
        $dictValue = $request->input('dict_value', null);
        $enumLimit = $request->input('enum_limit', null);
        $label     = (string) $request->input('label', '');
        $sort      = (int) $request->input('sort', 0);
        $status    = (int) $request->input('status', 0);
        $remark    = (string) $request->input('remark', '');

        $data = $this->systemDictService->addSysSet($dictType, $dictKey, $valueType, $dictValue, $enumLimit, $label, $sort, $status, $remark);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 更新系统字典
     */
    public function updateSysSet(Request $request): JsonResponse
    {
        $id        = (int) $request->input('id', 0);
        $dictType  = (string) $request->input('dict_type', '');
        $dictKey   = (string) $request->input('dict_key', '');
        $valueType = (int) $request->input('value_type', 0);
        $dictValue = $request->input('dict_value', null);
        $enumLimit = $request->input('enum_limit', null);
        $label     = (string) $request->input('label', '');
        $sort      = (int) $request->input('sort', 0);
        $status    = (int) $request->input('status', 0);
        $remark    = (string) $request->input('remark', '');

        $data = $this->systemDictService->updateSysSet($id, $dictType, $dictKey, $valueType, $dictValue, $enumLimit, $label, $sort, $status, $remark);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 删除系统字典
     */
    public function deleteSysSet(Request $request): JsonResponse
    {
        $id = (int) $request->input('id', 0);
        $data = $this->systemDictService->deleteSysSet($id);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function importFromSettings(Request $request): JsonResponse
    {
        $dictType = (string) $request->input('dict_type', '');
        $data = $this->systemDictService->importFromSettings($dictType);

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    /**
     * 更新系统字典状态
     */
    public function updateState(Request $request): JsonResponse
    {
        $id = (int) $request->input('id', 0);
        $status = (int) $request->input('status', 0);

        $data = $this->systemDictService->updateState($id, $status);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }
}
