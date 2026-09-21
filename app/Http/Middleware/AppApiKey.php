<?php

namespace App\Http\Middleware;

use App\Services\Video\VideoSettingService;
use App\Support\AppApi;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AppApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $settings = app(VideoSettingService::class);
        $closed = trim((string) $settings->get('site_closed', '0'));
        if ($closed === '1') {
            $tip = (string) $settings->get('site_close_tip', '站点维护中');

            return AppApi::fail($tip !== '' ? $tip : '站点维护中');
        }

        $need = trim((string) $settings->get('app_key', ''));
        if ($need === '') {
            $need = trim((string) $settings->get('provide_key', ''));
        }
        if ($need === '') {
            return $next($request);
        }
        $given = (string) $request->query('key', $request->header('X-App-Key', $request->header('X-Provide-Key', $request->input('key', ''))));
        if ($given === '' || ! hash_equals($need, $given)) {
            return response()->json(['code' => 1, 'msg' => '密钥无效', 'data' => []], 403);
        }

        return $next($request);
    }
}
