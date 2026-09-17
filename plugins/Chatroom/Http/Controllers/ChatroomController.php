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

    public function index(int $id): JsonResponse
    {
        $data = $this->chat->list($id);

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
}
