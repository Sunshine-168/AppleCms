<?php

namespace Plugins\Danmaku\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Utils\Ajax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Plugins\Danmaku\Services\DanmakuService;

class DanmakuController extends Controller
{
    public function __construct(private readonly DanmakuService $danmaku) {}

    public function index(Request $request, int $id): JsonResponse
    {
        $data = $this->danmaku->list($id, (int) $request->query('episode_id', 0));

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function store(Request $request, int $id): JsonResponse
    {
        $data = $this->danmaku->send(
            $id,
            $request->all(),
            Auth::guard('member')->user(),
            (string) $request->ip()
        );

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }
}
