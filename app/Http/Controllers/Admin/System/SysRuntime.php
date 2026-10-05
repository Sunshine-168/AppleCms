<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Services\Monitor\MonitorService;
use App\Support\Utils\Ajax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SysRuntime extends Controller
{
    public function __construct(private readonly MonitorService $monitor) {}

    public function index(Request $request): View
    {
        $desk = strtolower(trim((string) $request->query('desk', 'perf')));
        if (! in_array($desk, ['perf', 'live', 'settings', 'rules', 'events', 'access'], true)) {
            $desk = 'perf';
        }

        return view('admin.system.runtime', $this->monitor->pageBoard($desk, $request->query()));
    }

    public function series(Request $request): JsonResponse
    {
        $range = (string) $request->query('range', '1h');

        return Ajax::success($this->monitor->seriesPayload($range));
    }

    public function live(): JsonResponse
    {
        return Ajax::success($this->monitor->livePayload());
    }

    public function tick(Request $request): JsonResponse|RedirectResponse
    {
        $res = app(\App\Services\Monitor\MonitorTick::class)->run(0, true);
        $msg = (string) ($res['msg'] ?? '');
        if ($msg === '' || $msg === '已采集') {
            $msg = admin_t('ui.tick_ok');
        }
        if ($request->expectsJson() || $request->ajax()) {
            return Ajax::success(['ok' => true], $msg);
        }

        return redirect('/admin/system/runtime')->with('status', $msg);
    }

    public function saveSettings(Request $request): JsonResponse
    {
        $res = $this->monitor->saveSettings($request->all());

        return Ajax::message((int) $res['code'], (string) $res['msg'], is_array($res['data'] ?? null) ? $res['data'] : []);
    }

    public function saveRule(Request $request): JsonResponse
    {
        $res = $this->monitor->saveRule($request->all());

        return Ajax::message((int) $res['code'], (string) $res['msg'], is_array($res['data'] ?? null) ? $res['data'] : []);
    }

    public function updateRuleStatus(Request $request): JsonResponse
    {
        $res = $this->monitor->updateRuleStatus((int) $request->input('id', 0), (int) $request->input('status', 0));

        return Ajax::message((int) $res['code'], (string) $res['msg'], is_array($res['data'] ?? null) ? $res['data'] : []);
    }

    public function testRule(Request $request): JsonResponse
    {
        $res = $this->monitor->testRule((int) $request->input('id', 0));

        return Ajax::message((int) $res['code'], (string) $res['msg'], is_array($res['data'] ?? null) ? $res['data'] : []);
    }

    public function ackEvent(Request $request): JsonResponse
    {
        $res = $this->monitor->ackEvent((int) $request->input('id', 0));

        return Ajax::message((int) $res['code'], (string) $res['msg'], is_array($res['data'] ?? null) ? $res['data'] : []);
    }
}
