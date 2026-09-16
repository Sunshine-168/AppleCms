<?php

namespace App\Http\Middleware;

use App\Services\Stats\VisitRecorder;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** 前台访问埋点（terminate 阶段写入，不拖慢响应） */
class RecordVisit
{
    public function __construct(private readonly VisitRecorder $recorder)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $this->recorder->record($request, $response);
    }
}
