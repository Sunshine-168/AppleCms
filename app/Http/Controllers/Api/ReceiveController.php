<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Collect\CollectIngestService;
use App\Services\Video\VideoSettingService;
use App\Support\Utils\Ajax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReceiveController extends Controller
{
    public function vod(Request $request, CollectIngestService $ingest, VideoSettingService $settings): JsonResponse
    {
        $key = trim((string) $settings->get('inbound_key', ''));
        if ($key === '' || ! hash_equals($key, (string) $request->input('key', $request->header('X-Inbound-Key', '')))) {
            return Ajax::message(1, '入库密钥无效', []);
        }
        $item = $request->input('data', $request->all());
        if (isset($item['data']) && is_array($item['data'])) {
            $item = $item['data'];
        }
        if (! is_array($item) || trim((string) ($item['vod_name'] ?? '')) === '') {
            return Ajax::message(1, '缺少 vod_name', []);
        }
        $result = $ingest->ingestRemote($item);

        return Ajax::message(
            ($result['action'] ?? '') === 'skipped' ? 1 : 0,
            (string) ($result['msg'] ?? 'ok'),
            $result
        );
    }
}
