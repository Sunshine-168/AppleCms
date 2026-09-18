<?php

namespace Plugins\CjRule\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Utils\Ajax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Plugins\CjRule\Services\CjRuleAdmin;
use Plugins\CjRule\Services\CjRuleService;

class CjRuleController extends Controller
{
    public function __construct(
        private readonly CjRuleService $cj,
        private readonly CjRuleAdmin $admin,
    ) {}

    public function tryRun(Request $request): JsonResponse
    {
        $data = $this->cj->preview($request->all());

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function import(Request $request): JsonResponse
    {
        $data = $this->cj->import((int) $request->input('id', 0));

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function toggle(Request $request): JsonResponse
    {
        $data = $this->admin->toggle((int) $request->input('id', 0));

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }
}
