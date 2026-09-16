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

    public function templateBackup(Request $request): JsonResponse
    {
        $data = $this->ops->backupThemeFile((string) $request->input('path', ''));

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function templateRollback(Request $request): JsonResponse
    {
        $data = $this->ops->rollbackThemeFile((string) $request->input('path', ''));

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
        $data = $this->ops->seoPush((string) $request->input('engine', 'baidu'), (int) $request->input('limit', 50));

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function make(): View
    {
        return view('admin.video.make');
    }

    public function makeRun(Request $request): JsonResponse
    {
        $data = $this->ops->makeHtml((string) $request->input('scope', 'all'));

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function disableFailSource(Request $request): JsonResponse
    {
        $data = $this->ops->disablePlayFailSource((int) $request->input('id', 0));

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function wizard(): View
    {
        return view('admin.video.wizard');
    }

    public function hitsReset(Request $request): JsonResponse
    {
        $opts = [];
        if ($request->boolean('week')) {
            $opts['--week'] = true;
        }
        if ($request->boolean('month')) {
            $opts['--month'] = true;
        }
        \Illuminate\Support\Facades\Artisan::call('video:hits-reset', $opts);

        return Ajax::message(0, trim(\Illuminate\Support\Facades\Artisan::output()) ?: '已重置', []);
    }

    public function rewrite(): View
    {
        return view('admin.video.rewrite', $this->ops->rewriteRules());
    }

    public function safety(): View
    {
        return view('admin.video.safety');
    }

    public function malwareScan(): JsonResponse
    {
        $data = $this->ops->malwareScan();

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function batchReplaceUrl(Request $request): JsonResponse
    {
        $data = $this->ops->replacePlayUrl(
            (string) $request->input('from', ''),
            (string) $request->input('to', ''),
            $request->input('ids', []),
            (string) $request->input('value', '')
        );

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }
}
