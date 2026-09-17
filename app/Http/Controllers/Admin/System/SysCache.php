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
     */
    public function showCache(): View|Factory
    {
        return view('admin.system.tools.cache', $this->systemCacheService->pageBoard());
    }

    /**
     * 获取缓存信息
     */
    public function getCacheInfo(): JsonResponse
    {
        $res = $this->systemCacheService->getCacheInfo();
        return Ajax::message($res['code'], $res['msg'], $res['data']);
    }

    /**
     * 按用途清或打包
     */
    public function clearCache(Request $request): JsonResponse
    {
        $kind = (string) $request->input('kind', 'data');
        $res = $this->systemCacheService->clear($kind);
        return Ajax::message($res['code'], $res['msg'], $res['data']);
    }

    /**
     * 刷新缓存（数据缓存）
     */
    public function flushCache(): JsonResponse
    {
        $res = $this->systemCacheService->flushCache();
        return Ajax::message($res['code'], $res['msg'], $res['data']);
    }

    /**
     * 执行缓存命令
     */
    public function runCacheCommand(Request $request): JsonResponse
    {
        $kind = trim((string) $request->input('kind', ''));
        if ($kind !== '') {
            $res = $this->systemCacheService->clear($kind);
            return Ajax::message($res['code'], $res['msg'], $res['data']);
        }

        $command = (string) $request->input('command', '');
        $res = $this->systemCacheService->runArtisan($command);
        return Ajax::message($res['code'], $res['msg'], $res['data']);
    }
}
