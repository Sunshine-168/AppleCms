<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Services\System\SysShortcutService;
use App\Support\Utils\Ajax;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 系统快捷
 */
class SysShortcut extends Controller
{
    protected SysShortcutService $sysShortcutService;

    public function __construct()
    {
        $this->sysShortcutService = new SysShortcutService();
    }

    /**
     * 显示系统快捷页
     */
    public function index(): View|Factory
    {
        return view('admin.system.shortcut.index');
    }

    /**
     * 获取系统快捷列表
     */
    public function getShortcutList(Request $request): JsonResponse
    {
        $data = $this->sysShortcutService->getShortcutList();
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }
}
