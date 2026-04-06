<?php
namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Services\System\SysScheduleService;
use App\Support\Utils\Ajax;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 定时任务控制器
 * @package App\Http\Controllers\Admin\System
 */
class SysSchedule extends Controller
{
    protected SysScheduleService $systemScheduleService;

    public function __construct()
    {
        $this->systemScheduleService = new SysScheduleService();
    }

    /**
     * 显示定时任务页
     * @return View|Factory
     */
    public function showSchedule(): View|Factory
    {
        return view('admin.system.tools.schedule');
    }

    /**
     * 获取定时任务列表
     * @param Request $request
     * @return JsonResponse
     */
    public function getScheduleList(Request $request): JsonResponse
    {
        $name   = (string) $request->input('name', '');
        $type   = (string) $request->input('type', '');
        $status = $request->input('status', '');
        $limit  = (int) $request->input('limit', 10);

        $data = $this->systemScheduleService->getScheduleLists($name, $type, $status, $limit);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 保存定时任务
     * @param Request $request
     * @return JsonResponse
     */
    public function saveSchedule(Request $request): JsonResponse
    {
        $id                  = (int) $request->input('id', 0);
        $name                = (string) $request->input('name', '');
        $code                = (string) $request->input('code', '');
        $type                = (string) $request->input('type', 'artisan');
        $command             = (string) $request->input('command', '');
        $params              = (string) $request->input('params', '');
        $cronExpression      = (string) $request->input('cron_expression', '* * * * *');
        $timezone            = (string) $request->input('timezone', 'Asia/Shanghai');
        $status              = (int) $request->input('status', 1);
        $withoutOverlapping  = (int) $request->input('without_overlapping', 1);
        $onOneServer         = (int) $request->input('on_one_server', 0);
        $runInMaintenance    = (int) $request->input('run_in_maintenance', 0);
        $timeout             = (int) $request->input('timeout', 0);
        $maxAttempts         = (int) $request->input('max_attempts', 1);
        $remark              = (string) $request->input('remark', '');
        $sort                = (int) $request->input('sort', 0);

        $res = $this->systemScheduleService->saveSchedule(
            $id,
            $name,
            $code,
            $type,
            $command,
            $params,
            $cronExpression,
            $timezone,
            $status,
            $withoutOverlapping,
            $onOneServer,
            $runInMaintenance,
            $timeout,
            $maxAttempts,
            $remark,
            $sort
        );

        return Ajax::message($res['code'], $res['msg'], $res['data']);
    }

    /**
     * 删除定时任务
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteSchedule(Request $request): JsonResponse
    {
        $id = (int) $request->input('id', 0);
        $res = $this->systemScheduleService->deleteSchedule($id);
        return Ajax::message($res['code'], $res['msg'], $res['data']);
    }

    /**
     * 更新定时任务状态
     * @param Request $request
     * @return JsonResponse
     */
    public function updateScheduleStatus(Request $request): JsonResponse
    {
        $id = (int) $request->input('id', 0);
        $status = (int) $request->input('status', 0);
        $res = $this->systemScheduleService->updateScheduleStatus($id, $status);
        return Ajax::message($res['code'], $res['msg'], $res['data']);
    }

    /**
     * 立即执行定时任务
     * @param Request $request
     * @return JsonResponse
     */
    public function runScheduleOnce(Request $request): JsonResponse
    {
        $id = (int) $request->input('id', 0);
        $res = $this->systemScheduleService->runScheduleOnce($id);
        return Ajax::message($res['code'], $res['msg'], $res['data']);
    }
}

