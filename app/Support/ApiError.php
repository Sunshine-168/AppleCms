<?php

namespace App\Support;

use App\Support\Utils\Ajax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ApiError
{
    public static function shouldRender(Request $request): bool
    {
        return $request->expectsJson()
            || $request->ajax()
            || $request->is('api/admin')
            || $request->is('api/admin/*');
    }

    public static function json(Throwable $e): JsonResponse
    {
        if ($e instanceof ValidationException) {
            $msg = collect($e->errors())->flatten()->filter()->first();
            $msg = is_string($msg) && $msg !== '' ? $msg : '请检查填写内容';

            return self::payload(1, $msg, ['errors' => $e->errors()], 422);
        }

        $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
        if ($status < 400) {
            $status = 500;
        }

        return self::payload($status >= 500 ? 1 : $status, self::message($e, $status), [], $status);
    }

    /** @param  array<string, mixed>  $data */
    private static function payload(int $code, string $msg, array $data, int $http): JsonResponse
    {
        $res = Ajax::message($code, $msg, $data);
        $json = $res->getData(true);
        $json['ok'] = false;
        $json['message'] = $msg;

        return response()->json($json, $http, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private static function message(Throwable $e, int $status): string
    {
        if ($status === 419) {
            return '页面已过期，请刷新后再试';
        }
        if ($status === 429) {
            return '操作太频繁，请稍后再试';
        }
        if ($status === 404) {
            return '页面不存在';
        }

        $raw = trim($e->getMessage());
        if ($status === 403) {
            return $raw !== '' ? $raw : '没有权限';
        }
        if ($status >= 500) {
            if (config('app.debug') && $raw !== '') {
                return $raw;
            }

            return '服务器出错了，请稍后再试';
        }

        return $raw !== '' ? $raw : '请求失败';
    }
}
