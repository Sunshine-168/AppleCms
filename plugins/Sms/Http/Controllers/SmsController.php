<?php

namespace Plugins\Sms\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Utils\Ajax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Plugins\Sms\Services\SmsService;

class SmsController extends Controller
{
    public function __construct(private readonly SmsService $sms) {}

    public function send(Request $request): JsonResponse
    {
        $data = $this->sms->send(
            (string) $request->input('phone', ''),
            (string) $request->input('scene', 'register') ?: 'register'
        );

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }
}
