<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Utils\Syslog;

class RequestLog
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime   = microtime(true);
        $startMemory = memory_get_usage();

        $requestData = [
            'method'        => $request->method(),
            'url'           => $request->fullUrl(),
            'ip'            => $request->ip(),
            'user_agent'    => $request->userAgent(),
            'params'        => $this->filterSensitiveData($request->all()),
            'start_time'    => now()->toDateTimeString(),
        ];

        $response       = $next($request);

        $executionTime  = round((microtime(true) - $startTime) * 1000, 2);
        $memoryUsage    = round((memory_get_usage() - $startMemory) / 1024 / 1024, 2);

        $logData = array_merge($requestData, [
            'status_code'    => $response->getStatusCode(),
            'execution_time' => $executionTime . 'ms',
            'memory_usage'   => $memoryUsage . 'MB',
            'response_size'  => strlen((string) $response->getContent()) . ' bytes',
        ]);

        $type = $executionTime > 3000 ? 'warning' : 'info';
        $msg  = $executionTime > 3000 ? 'Slow Request' : 'Request';

        Syslog::log('request', $msg, $logData, $type);

        return $response;
    }

    private function filterSensitiveData(array $data): array
    {
        $sensitiveKeys = ['password', 'token', 'secret', 'key', 'auth'];

        foreach ($data as $key => $value) {
            foreach ($sensitiveKeys as $sensitiveKey) {
                if (stripos((string) $key, $sensitiveKey) !== false) {
                    $data[$key] = '***';
                    break;
                }
            }
        }

        return $data;
    }
}
