<?php

namespace Plugins\PublishPage\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Plugins\PublishPage\Services\PublishService;
use Symfony\Component\HttpFoundation\Response;

class PublishGate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET')) {
            return $next($request);
        }
        if ($request->is('admin', 'admin/*', 'install', 'install/*', 'sitehome', 'publish', 'publish/*')) {
            return $next($request);
        }
        $path = '/'.ltrim($request->path(), '/');
        if ($path === '//') {
            $path = '/';
        }
        $query = (string) $request->getQueryString();
        if ($path !== '/' || $query !== '') {
            return $next($request);
        }
        if (! is_file(storage_path('app/install.lock'))) {
            return $next($request);
        }
        try {
            $svc = app(PublishService::class);
            if (! $svc->enabled()) {
                return $next($request);
            }
        } catch (\Throwable) {
            return $next($request);
        }
        if ($svc->hasEnteredCookie($request)) {
            return $next($request);
        }
        $html = view('publishpage::publish', $svc->pageData())->render();

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-store, private, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }
}
