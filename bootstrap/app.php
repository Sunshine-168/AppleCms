<?php

require_once __DIR__.'/../app/Support/helpers.php';
require_once __DIR__.'/../app/Support/PcntlScheduleStub.php';

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

foreach ([
    dirname(__DIR__).'/storage/framework/views',
    dirname(__DIR__).'/storage/framework/cache/data',
    dirname(__DIR__).'/storage/framework/sessions',
    dirname(__DIR__).'/storage/logs',
    dirname(__DIR__).'/storage/app',
    dirname(__DIR__).'/bootstrap/cache',
] as $runtimeDir) {
    if (! is_dir($runtimeDir)) {
        @mkdir($runtimeDir, 0775, true);
    }
}

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
        $schedule->command('video:collect-due')->everyMinute()->withoutOverlapping(10)->timezone(config('app.timezone', 'Asia/Shanghai'));
        $schedule->command('video:publish-due')->everyMinute()->withoutOverlapping(10)->timezone(config('app.timezone', 'Asia/Shanghai'));
        $schedule->command('monitor:tick')->everyMinute()->withoutOverlapping(5)->timezone(config('app.timezone', 'Asia/Shanghai'));

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
            'member.api' => \App\Http\Middleware\MemberApiAuth::class,
            'vod.html' => \App\Http\Middleware\VideoHtmlCache::class,
        ]);
        $middleware->web(prepend: [
            \App\Http\Middleware\CheckInstalled::class,
        ]);
        $middleware->appendToGroup('web', \App\Http\Middleware\SiteClosed::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\VisitStat::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\RecordVisit::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\VideoAccessLog::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\MonitorRequest::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\SearchWordLog::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\VideoHtmlCache::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\SetAdminUiLocale::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (\Throwable $e) {
            try {
                Syslog::exception('system', $e);
            } catch (\Throwable) {
            }
        });
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (ApiError::shouldRender($request)) {
                return ApiError::json($e);
            }
            if ($request->is('install') || $request->is('install/*')) {
                $raw = trim($e->getMessage());
                $blocked = $raw === ''
                    || str_contains($raw, 'Permission denied')
                    || str_contains($raw, 'failed to open stream')
                    || str_contains($raw, 'could not be opened');
                if ($blocked) {
                    $title = '目录还不能写';
                    $lead = '安装要把会话和日志写到 storage。当前运行网站的用户还没有写入权限，所以先停在这里。';
                    $hint = '在宝塔的文件管理里打开网站目录，把 storage 和 bootstrap/cache 的所有者改为 www，并允许写入。改完后刷新这个页面。';
                } else {
                    $title = '安装暂时没能继续';
                    $plain = preg_replace('/The exception occurred while attempting to log:\s*/', '', $raw) ?? $raw;
                    $plain = preg_replace('/Context:\s*\{"exception":\{\}\}\s*/', '', $plain) ?? $plain;
                    $plain = trim(preg_replace('/\s+/', ' ', $plain) ?? $plain);
                    if (mb_strlen($plain) > 180) {
                        $plain = mb_substr($plain, 0, 180).'…';
                    }
                    $lead = $plain !== '' ? $plain : '请稍后再试。';
                    $hint = '刷新页面重试。若仍停在这里，把 storage 和 bootstrap/cache 交给网站运行用户（宝塔里一般是 www）。';
                }
                $body = '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
                    .'<title>'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'</title>'
                    .'<link rel="stylesheet" href="'.e(asset('css/install.css')).'">'
                    .'</head><body><div class="wrap"><div class="brand"><span class="logo">苹</span> 苹果v12 安装</div>'
                    .'<div class="card" style="display:block;padding:28px 32px"><h1 style="margin:0 0 8px;font-size:22px">'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'</h1>'
                    .'<p class="lead">'.htmlspecialchars($lead, ENT_QUOTES, 'UTF-8').'</p>'
                    .'<p class="hint">'.htmlspecialchars($hint, ENT_QUOTES, 'UTF-8').'</p>'
                    .'<div class="actions"><a class="btn" href="'.e(url('/install')).'">刷新重试</a></div>'
                    .'</div></div></body></html>';

                return response($body, 500, ['Content-Type' => 'text/html; charset=UTF-8']);
            }

            return null;
        });
    })
    ->create();
