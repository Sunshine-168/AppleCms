<?php

namespace Plugins\Chatroom\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Utils\Ajax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Plugins\Chatroom\Services\ChatroomService;

class ChatroomController extends Controller
{
    public function __construct(private readonly ChatroomService $chat) {}

    public function index(Request $request, int $id): JsonResponse
    {
        $data = $this->chat->list($id, (int) $request->query('after_id', 0));

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function store(Request $request, int $id): JsonResponse
    {
        $data = $this->chat->send(
            $id,
            $request->all(),
            Auth::guard('member')->user(),
            (string) $request->ip()
        );

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function report(Request $request): JsonResponse
    {
        $data = $this->chat->report(
            (int) $request->input('id', 0),
            Auth::guard('member')->user()
        );

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }
}
