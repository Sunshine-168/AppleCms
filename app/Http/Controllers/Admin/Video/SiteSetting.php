<?php

namespace App\Http\Controllers\Admin\Video;

use App\Http\Controllers\Controller;
use App\Services\Video\VideoSettingService;
use App\Support\Utils\Ajax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteSetting extends Controller
{
    public function __construct(private readonly VideoSettingService $settings) {}

    public function index(): View
    {
        return view('admin.video.settings', [
            'site' => $this->settings->site(),
        ]);
    }

    public function save(Request $request): JsonResponse
    {
        $data = $this->settings->save($request->all());

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }
}
