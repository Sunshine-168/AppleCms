<?php

namespace App\Http\Controllers\Admin\Video;

use App\Http\Controllers\Controller;
use App\Services\Video\SiteOpsService;
use App\Support\Utils\Ajax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteOps extends Controller
{
    public function __construct(private readonly SiteOpsService $ops) {}

    public function templates(): View
    {
        return view('admin.video.templates', [
            'files' => $this->ops->themeFiles(),
        ]);
    }

    public function templateRead(Request $request): JsonResponse
    {
        $data = $this->ops->readThemeFile((string) $request->input('path', ''));

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function templateSave(Request $request): JsonResponse
    {
        $data = $this->ops->saveThemeFile((string) $request->input('path', ''), (string) $request->input('content', ''));

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function visits(): View
    {
        return view('admin.video.visits', $this->ops->visitSummary());
    }

    public function push(): View
    {
        return view('admin.video.push');
    }

    public function pushRun(Request $request): JsonResponse
    {
        $data = $this->ops->baiduPush((int) $request->input('limit', 50));

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function make(): View
    {
        return view('admin.video.make');
    }

    public function makeRun(): JsonResponse
    {
        $data = $this->ops->makeHtml();

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }
}
