<?php

namespace App\Http\Controllers\Api;

use App\Services\Video\VideoSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppController extends ProvideController
{
    public function vod(Request $request, VideoSettingService $settings): JsonResponse
    {
        $need = trim((string) $settings->get('app_key', ''));
        if ($need === '') {
            $need = trim((string) $settings->get('provide_key', ''));
        }
        if ($need !== '') {
            $given = (string) $request->query('key', $request->header('X-Provide-Key', $request->input('key', '')));
            if ($given === '' || ! hash_equals($need, $given)) {
                return response()->json(['code' => 0, 'msg' => '密钥无效'], 403);
            }
        }

        return response()->json($this->vodPayload($request));
    }
}
