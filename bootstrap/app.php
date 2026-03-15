<?php

use App\Models\System\SysScheduleModel;
use App\Services\System\SysScheduleService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Schema;
use App\Support\Utils\Syslog;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        try {
            // 表不存在则直接返回
            if (!Schema::hasTable('sys_schedule')) 
            {
                return;
            }

            $sysScheduleModel = new SysScheduleModel();
            $tasks = $sysScheduleModel->where('status', 1)
                ->orderByDesc('sort')
                ->orderByDesc('id')
                ->get()
                ->toArray();

            // 使用 IOC 注入 Service
            $service = app(SysScheduleService::class);

            foreach ($tasks as $task) 
            {
                $id = (int) ($task['id'] ?? 0);
                if ($id <= 0) 
                {
                    continue;
                }

                $cron = trim((string) ($task['cron_expression'] ?? '* * * * *'));

                // Cron 表达式合法性校验
                try {
                    \Cron\CronExpression::factory($cron);
                } catch (\Throwable) {
                    $cron = '* * * * *';
                }

                $timezone = trim((string) ($task['timezone'] ?? ''));

                if (!in_array($timezone, timezone_identifiers_list())) 
                {
                    $timezone = config('app.timezone', 'Asia/Shanghai');
                }

                $withoutOverlapping = (int) ($task['without_overlapping'] ?? 0) === 1;
                $onOneServer = (int) ($task['on_one_server'] ?? 0) === 1;
                $runInMaintenance = (int) ($task['run_in_maintenance'] ?? 0) === 1;

                $event = $schedule->call(function () use ($service, $id) {
                    $service->runScheduleOnce($id);
                })->name('sys_schedule:' . $id)
                  ->cron($cron)
                  ->timezone($timezone);

                if ($withoutOverlapping) 
                {
                    $event->withoutOverlapping();
                }

                if ($onOneServer) 
                {
                    $event->onOneServer();
                }

                if ($runInMaintenance) 
                {
                    $event->evenInMaintenanceMode();
                }
            }
        } catch (\Throwable $e) {
            Syslog::exception('system', $e, 'sys_schedule scheduler');
        }
    })
    ->withMiddleware(function (Middleware $middleware): void {
        // 自定义中间件
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (\Throwable $e) {
            Syslog::exception('system', $e);
        });
    })
    ->create();