<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Services\System\SysDatabaseService;
use App\Support\Utils\Ajax;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 数据库字典
 */
class SysDatabase extends Controller
{
    protected SysDatabaseService $systemDatabaseService;

    public function __construct()
    {
        $this->systemDatabaseService = new SysDatabaseService();
    }
    /**
     * 显示数据库字典页
     * @return View|Factory
     */
    public function showDatabaseDict(): View|Factory
    {
        return view('admin.system.database.dict');
    }

    /**
     * 获取数据库表列表
     * @return JsonResponse
     */
    public function getDatabaseTables(): JsonResponse
    {
        $data = $this->systemDatabaseService->getDatabaseTables();
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 获取数据库表字段列表
     * @param Request $request
     * @return JsonResponse
     */
    public function getDatabaseColumns(Request $request): JsonResponse
    {
        $table = (string) $request->input('table', '');
        $data  = $this->systemDatabaseService->getDatabaseColumns($table);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }
}
