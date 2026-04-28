<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Services\Admin\System\SysCacheService;
use App\Support\Utils\Ajax;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 系统缓存管理
 * Class SysCache
 * @package App\Http\Controllers\Admin\System
 */
class SysCache extends Controller
{
    protected SysCacheService $systemCacheService;

    public function __construct()
    {
        $this->systemCacheService = new SysCacheService();
    }

    /**
     * 显示缓存管理页面
     * @return View|Factory
     */
    public function showCache(): View|Factory
    {
        return view('admin.system.tools.cache');
    }

    /**
     * 获取缓存信息
     * @return JsonResponse
     */
    public function getCacheInfo(): JsonResponse
    {
        $res = $this->systemCacheService->getCacheInfo();
        return Ajax::message($res['code'], $res['msg'], $res['data']);
    }

    /**
     * 刷新缓存
     * @return JsonResponse
     */
    public function flushCache(): JsonResponse
    {
        $res = $this->systemCacheService->flushCache();
        return Ajax::message($res['code'], $res['msg'], $res['data']);
    }

    /**
     * 执行缓存命令
     * @param Request $request
     * @return JsonResponse
     */
    public function runCacheCommand(Request $request): JsonResponse
    {
        $command = (string) $request->input('command', '');
        $res = $this->systemCacheService->runArtisan($command);
        return Ajax::message($res['code'], $res['msg'], $res['data']);
    }
}

