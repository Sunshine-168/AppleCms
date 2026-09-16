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
