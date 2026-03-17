<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Services\System\SysShortcutService;
use App\Support\Utils\Ajax;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SysShortcut extends Controller
{
    protected SysShortcutService $sysShortcutService;

    public function __construct()
    {
        $this->sysShortcutService = new SysShortcutService();
    }

    public function index(): View|Factory
    {
        return view('admin.system.shortcut.index');
    }

    public function getShortcutList(Request $request): JsonResponse
    {
        $data = $this->sysShortcutService->getShortcutList();
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }
}
