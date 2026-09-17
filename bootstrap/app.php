<?php

require_once __DIR__.'/../app/Support/helpers.php';

use App\Models\System\SysScheduleModel;
use App\Services\Admin\System\SysScheduleService;
use App\Support\ApiError;
use App\Support\Utils\Syslog;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('video:hits-reset')->dailyAt('00:05')->timezone(config('app.timezone', 'Asia/Shanghai'));
        $schedule->command('stats:prune')->dailyAt('03:20')->timezone(config('app.timezone', 'Asia/Shanghai'));
        $schedule->command('video:collect-due')->everyMinute()->withoutOverlapping()->timezone(config('app.timezone', 'Asia/Shanghai'));
        $schedule->command('video:publish-due')->everyMinute()->withoutOverlapping()->timezone(config('app.timezone', 'Asia/Shanghai'));

        try {
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
                    if (! \Cron\CronExpression::isValidExpression($cron)) {
                        $cron = '* * * * *';
                    }
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
        $middleware->validateCsrfTokens(except: [
            'api/admin/login',
            'pay/notify/*',
            'weixin',
        ]);
        $middleware->api(prepend: [
            \App\Http\Middleware\CheckInstalled::class,
        ]);
        $middleware->alias([
            'member.auth' => \App\Http\Middleware\MemberAuth::class,
            'vod.html' => \App\Http\Middleware\VideoHtmlCache::class,
        ]);
        $middleware->web(prepend: [
            \App\Http\Middleware\CheckInstalled::class,
        ]);
        $middleware->appendToGroup('web', \App\Http\Middleware\SiteClosed::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\VisitStat::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\RecordVisit::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\VideoAccessLog::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\SearchWordLog::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\VideoHtmlCache::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\SetAdminUiLocale::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (\Throwable $e) {
            Syslog::exception('system', $e);
        });
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (ApiError::shouldRender($request)) {
                return ApiError::json($e);
            }

            return null;
        });
    })
    ->create();
