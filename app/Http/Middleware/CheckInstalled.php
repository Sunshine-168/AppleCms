<?php

namespace App\Http\Middleware;

use App\Services\Video\VideoInstallService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        // 安装过程把会话固定成文件。锁写出后完成页若改回数据库会话，就读不到结果。
        if (
            ($request->is('install') || $request->is('install/*'))
            && config('session.driver') !== 'array'
        ) {
            $sessionDir = storage_path('framework/sessions');
            config([
                'session.driver' => (is_dir($sessionDir) && is_writable($sessionDir)) ? 'file' : 'cookie',
            ]);
        }

        if (
            $request->is('install')
            || $request->is('install/*')
            || $request->is('up')
            || $request->is('css/*')
            || $request->is('js/*')
            || $request->is('static/*')
            || $request->is('storage/*')
            || $request->is('favicon.ico')
            || $request->is('robots.txt')
        ) {
            return $next($request);
        }

        try {
            if (app(VideoInstallService::class)->alreadyInstalled()) {
                return $next($request);
            }
        } catch (\Throwable) {
            return redirect('/install');
        }

        return redirect('/install');
    }
}
