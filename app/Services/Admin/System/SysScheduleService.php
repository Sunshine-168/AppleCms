<?php


namespace App\Services\Admin\System;

use App\Models\System\SysScheduleModel;
use App\Support\Utils\Result;
use Cron\CronExpression;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class SysScheduleService
{

    public SysScheduleModel $sysScheduleModel;

    public function __construct()
    {
        $this->sysScheduleModel = new SysScheduleModel();
    }

    /**
     * 获取任务列表
     */
    public function getScheduleLists(string $name, string $type, mixed $status, int $limit): array
    {

        if ($limit < 1)
        {
            $limit = 10;
        }

        $where = [];

        if ($name = trim($name))
        {
            $where[] = ['name', '=', $name];
        }

        if ($type = trim($type))
        {
            $where[] = ['type', '=', $type];
        }

        if ($status !== '' && $status !== null)
        {
            $where[] = ['status', '=', (int)$status];
        }

        $data = $this->sysScheduleModel->paginates(
            $where,
            '*',
            $limit,
            ['sort' => 'desc', 'id' => 'desc']
        );

        foreach ($data['data'] as &$item)
        {

            $item['last_run_time'] = !empty($item['last_run_time'])
                ? date('Y-m-d H:i:s', $item['last_run_time'])
                : '';

            $item['next_run_time'] = !empty($item['next_run_time'])
                ? date('Y-m-d H:i:s', $item['next_run_time'])
                : '';
        }

        return Result::success($data);
    }


    /**
     * 保存任务
     */
    public function saveSchedule(
        int $id,
        string $name,
        string $code,
        string $type,
        string $command,
        string $params,
        string $cronExpression,
        string $timezone,
        int $status,
        int $withoutOverlapping,
        int $onOneServer,
        int $runInMaintenance,
        int $timeout,
        int $maxAttempts,
        string $remark,
        int $sort
    ): array {

        $name = trim($name);
        if ($name === '')
        {
            return Result::fail('请输入任务名称');
        }

        if (!in_array($type, ['artisan', 'shell', 'http'], true))
        {
            return Result::fail('任务类型不正确');
        }

        $command = trim($command);
        if ($command === '')
        {
            return Result::fail('请输入执行内容');
        }

        if (mb_strlen($command) > 2000)
        {
            return Result::fail('执行内容过长');
        }

        $cronExpression = trim($cronExpression);

        try {
            if (! CronExpression::isValidExpression($cronExpression)) {
                return Result::fail('cron表达式格式错误');
            }
        } catch (\Throwable) {
            return Result::fail('cron表达式格式错误');
        }

        if ($timezone === '' || !in_array($timezone, timezone_identifiers_list()))
        {
            $timezone = 'Asia/Shanghai';
        }

        $timeout = max(0, min($timeout, 3600));
        $maxAttempts = max(1, min($maxAttempts, 50));
        $sort = max(0, (int)$sort);

        $time = time();

        $nextRunTime = 0;

        try {

            $cron = new CronExpression($cronExpression);

            $nextRunTime = $cron->getNextRunDate()->getTimestamp();

        } catch (\Throwable) {
        }

        $update = [

            'name' => $name,
            'code' => trim($code),
            'type' => $type,
            'command' => $command,
            'params' => (string)$params,
            'cron_expression' => $cronExpression,
            'timezone' => $timezone,
            'status' => $status ? 1 : 0,
            'without_overlapping' => $withoutOverlapping ? 1 : 0,
            'on_one_server' => $onOneServer ? 1 : 0,
            'run_in_maintenance' => $runInMaintenance ? 1 : 0,
            'timeout' => $timeout,
            'max_attempts' => $maxAttempts,
            'next_run_time' => $nextRunTime,
            'remark' => trim($remark),
            'sort' => $sort,
            'update_time' => $time,
            'update_at' => date('Y-m-d H:i:s', $time),

        ];

        $existsWhere = [['name', '=', $name]];

        if ($id > 0) {
            $existsWhere[] = ['id', '<>', $id];
        }

        if ($this->sysScheduleModel->existsBy($existsWhere))
        {
            return Result::fail('任务名称已存在');
        }

        if ($id > 0)
        {

            $ok = $this->sysScheduleModel->updateById($id, $update);

            if (!$ok) {
                return Result::fail('保存失败');
            }

            return Result::success(['id' => $id], '保存成功');
        }

        $insert = $update;

        $insert['create_time'] = $time;
        $insert['create_at'] = date('Y-m-d H:i:s', $time);

        $newId = $this->sysScheduleModel->insertsGetId($insert);

        if (!$newId) {
            return Result::fail('保存失败');
        }

        return Result::success(['id' => $newId], '保存成功');
    }


    /**
     * 删除任务
     */
    public function deleteSchedule(int $id): array
    {

        if ($id <= 0)
        {
            return Result::fail('参数错误');
        }

        $row = $this->sysScheduleModel->findById($id);

        if (!$row)
        {
            return Result::fail('任务不存在');
        }

        $ok = $this->sysScheduleModel->deleteById($id);

        return $ok
            ? Result::success([], '删除成功')
            : Result::fail('删除失败');
    }


    /**
     * 修改状态
     */
    public function updateScheduleStatus(int $id, int $status): array
    {

        $ok = $this->sysScheduleModel->updateById($id, [

            'status' => $status ? 1 : 0,
            'update_time' => time(),
            'update_at' => date('Y-m-d H:i:s')

        ]);

        return $ok
            ? Result::success([], '更新成功')
            : Result::fail('更新失败');
    }


    /**
     * 立即执行任务
     */
    public function runScheduleOnce(int $id): array
    {

        $task = $this->sysScheduleModel->findById($id);

        if (!$task) {
            return Result::fail('任务不存在');
        }

        $lock = null;

        if ($task['without_overlapping'])
        {

            $lock = Cache::lock('schedule_lock_' . $id, 600);

            if (!$lock->get()) {
                return Result::fail('任务正在执行中');
            }
        }

        $status = 2;
        $error = '';
        $output = '';
        $start = time();

        try {

            if ($task['type'] === 'artisan')
            {

                $output = $this->runArtisanCommand($task['command']);

            } elseif ($task['type'] === 'shell')
            {

                $output = $this->runShellCommand($task['command'], $task['timeout']);

            } elseif ($task['type'] === 'http')
            {

                $output = $this->runHttpRequest($task['command'], $task['timeout']);

            }

            $status = 1;

        } catch (\Throwable $e) {

            $error = $e->getMessage();

            Log::error('schedule run error', [
                'task' => $task['name'],
                'error' => $error
            ]);
        }

        if ($lock) {
            $lock->release();
        }

        $nextRun = 0;

        try {

            $cron = new CronExpression($task['cron_expression']);

            $nextRun = $cron->getNextRunDate()->getTimestamp();

        } catch (\Throwable) {
        }

        $this->sysScheduleModel->updateById($id, [

            'last_run_time' => $start,
            'last_status' => $status,
            'last_error' => $error,
            'next_run_time' => $nextRun,
            'update_time' => time(),
            'update_at' => date('Y-m-d H:i:s')

        ]);

        return $status === 1
            ? Result::success(['output' => $output], '执行成功')
            : Result::fail($error ?: '执行失败');
    }



    /**
     * 执行Artisan命令
     * @param string $command
     * @return string
     */
    private function runArtisanCommand(string $command): string
    {

        $code = Artisan::call($command);

        if ($code !== 0) {
            throw new \RuntimeException(Artisan::output());
        }

        return Artisan::output();
    }

    /**
     * 执行Shell命令
     * @param string $command
     * @param int $timeout
     * @return string
     */
    private function runShellCommand(string $command, int $timeout): string
    {

        $process = Process::fromShellCommandline($command);

        $process->setTimeout($timeout > 0 ? $timeout : null);

        $process->run();

        if (!$process->isSuccessful()) {

            throw new \RuntimeException(
                $process->getErrorOutput() ?: $process->getOutput()
            );
        }

        return $process->getOutput();
    }

    /**
     * 执行HTTP请求
     * @param string $url
     * @param int $timeout
     * @return string
     */
    private function runHttpRequest(string $url, int $timeout): string
    {

        $resp = Http::timeout($timeout ?: 30)
            ->acceptJson()
            ->get($url);

        if (!$resp->successful()) {
            throw new \RuntimeException('HTTP错误: ' . $resp->status());
        }

        return $resp->body();
    }

}

