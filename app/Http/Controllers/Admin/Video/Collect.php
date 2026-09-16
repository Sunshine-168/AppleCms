<?php

namespace App\Http\Controllers\Admin\Video;

use App\Http\Controllers\Controller;
use App\Services\Collect\CollectIngestService;
use App\Support\Utils\Ajax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Collect extends Controller
{
    public function __construct(private readonly CollectIngestService $ingest) {}

    public function classes(Request $request): JsonResponse
    {
        $data = $this->ingest->fetchClasses((int) $request->input('id', 0));

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function bind(Request $request): JsonResponse
    {
        $bind = $request->input('bind', []);
        if (is_string($bind)) {
            $bind = json_decode($bind, true) ?: [];
        }
        $data = $this->ingest->saveBind((int) $request->input('id', 0), is_array($bind) ? $bind : []);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function run(Request $request): JsonResponse
    {
        $data = $this->ingest->run((int) $request->input('id', 0), [
            'page' => $request->input('page', 1),
            'pages' => $request->input('pages', 1),
            'hours' => $request->input('hours', $request->input('h', 0)),
            'ids' => $request->input('ids', ''),
            't' => $request->input('t', ''),
        ]);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function resume(Request $request): JsonResponse
    {
        $data = $this->ingest->resume((int) $request->input('id', 0), [
            'pages' => $request->input('pages', 1),
            'hours' => $request->input('hours', 24),
        ]);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function retry(Request $request): JsonResponse
    {
        $data = $this->ingest->retry((int) $request->input('id', 0));

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function suggestBind(Request $request): JsonResponse
    {
        $data = $this->ingest->suggestBind((int) $request->input('id', 0));

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }
}
