<?php

namespace App\Http\Controllers\Admin\Video;

use App\Http\Controllers\Controller;
use App\Services\Video\DiskHtmlService;
use App\Services\Video\HtmlCacheService;
use App\Services\Video\SiteOpsService;
use App\Services\Video\VideoSettingService;
use App\Support\Utils\Ajax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteOps extends Controller
{
    public function __construct(
        private readonly SiteOpsService $ops,
        private readonly HtmlCacheService $htmlCache,
        private readonly DiskHtmlService $diskHtml,
        private readonly VideoSettingService $settings,
    ) {}

    public function templates(): View
    {
        $codeEditor = false;
        try {
            $codeEditor = app(\App\Plugins\PluginManager::class)->isEnabled('code_editor');
        } catch (\Throwable) {
        }

        return view('admin.video.templates', [
            'groups' => $this->ops->themeFiles(),
            'theme' => $this->ops->themeInfo(),
            'codeEditor' => $codeEditor,
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
        $site = [];
        try {
            $site = $this->settings->site();
        } catch (\Throwable) {
        }
        $ttl = max(0, (int) ($site['html_cache_ttl'] ?? 3600));
        $ttlOptions = HtmlCacheService::ttlOptions();
        if (! array_key_exists($ttl, $ttlOptions)) {
            $ttlOptions[$ttl] = '当前 '.$this->ttlPlain($ttl);
        }
        $lastBust = $this->htmlCache->lastBust();

        return view('admin.video.make', [
            'enabled' => (bool) ($site['html_cache_enabled'] ?? false),
            'ttl' => $ttl,
            'ttlOptions' => $ttlOptions,
            'lastBust' => $lastBust,
            'lastBustLabel' => $this->htmlCache->bustLabel($lastBust),
            'diskEnabled' => $this->diskHtml->enabled(),
            'diskCount' => $this->diskHtml->fileCount(),
            'diskJob' => $this->diskHtml->publicJob(),
            'scopes' => DiskHtmlService::scopes(),
        ]);
    }

    public function makeCacheSave(Request $request): RedirectResponse
    {
        $on = $request->boolean('html_cache_enabled');
        $saved = $this->settings->saveOptions([
            'html_cache_enabled' => $on ? '1' : '0',
            'html_cache_ttl' => (string) max(0, (int) $request->input('html_cache_ttl', 3600)),
        ]);
        if (($saved['code'] ?? 1) !== 0) {
            return redirect('/admin/video/make')->with('error', (string) ($saved['msg'] ?? '没能保存'));
        }
        $this->htmlCache->forgetAll('settings');

        return redirect('/admin/video/make')->with('status', $on ? '已开启全页缓存' : '已关闭全页缓存');
    }

    public function makeCacheClear(): RedirectResponse
    {
        $this->htmlCache->forgetAll('admin:clear');

        return redirect('/admin/video/make')->with('status', '已清空。访客下一次打开会重新生成页面。');
    }

    public function makeCacheWarm(Request $request): RedirectResponse
    {
        if (! $this->htmlCache->enabled()) {
            return redirect('/admin/video/make')->with('error', '请先打开全页缓存并保存');
        }
        $result = $this->htmlCache->warm((int) $request->input('entries', 30));
        $msg = '已预热 '.$result['ok'].' 页';
        if ($result['fail'] > 0) {
            $msg .= '，'.$result['fail'].' 页没生成好';
        }

        return redirect('/admin/video/make')->with('status', $msg);
    }

    public function makeDiskSave(Request $request): RedirectResponse
    {
        $on = $request->boolean('disk_html_enabled');
        $saved = $this->settings->saveOptions([
            'disk_html_enabled' => $on ? '1' : '0',
        ]);
        if (($saved['code'] ?? 1) !== 0) {
            return redirect('/admin/video/make')->with('error', (string) ($saved['msg'] ?? '没能保存'));
        }

        return redirect('/admin/video/make')->with('status', $on ? '已打开磁盘静态页，可以开始生成' : '已关闭磁盘静态页');
    }

    public function makeStart(Request $request): JsonResponse
    {
        if (! $this->diskHtml->enabled()) {
            return Ajax::fail('请先打开磁盘静态页并保存');
        }
        $job = $this->diskHtml->startJob((string) $request->input('scope', 'all'));
        if (! empty($job['conflict'])) {
            return Ajax::message(1, (string) ($job['message'] ?? '请先停止当前任务'), $job);
        }

        return Ajax::message(0, (string) ($job['message'] ?? ''), $job);
    }

    public function makeStep(Request $request): JsonResponse
    {
        $job = $this->diskHtml->publicJob();
        $kind = (string) ($job['kind'] ?? 'build');
        if ($kind !== 'clear' && ! $this->diskHtml->enabled()) {
            return Ajax::fail('请先打开磁盘静态页并保存');
        }
        $chunk = (int) $request->input('chunk', $kind === 'clear' ? 40 : 6);
        $job = $this->diskHtml->stepJob($chunk);

        return Ajax::message(0, (string) ($job['message'] ?? ''), $job);
    }

    public function makeStatus(): JsonResponse
    {
        $job = $this->diskHtml->publicJob();

        return Ajax::message(0, (string) ($job['message'] ?? ''), $job);
    }

    public function makeCancel(): JsonResponse
    {
        $job = $this->diskHtml->cancelJob();

        return Ajax::message(0, (string) ($job['message'] ?? ''), $job);
    }

    public function makeClear(): JsonResponse
    {
        $job = $this->diskHtml->startClearJob();
        if (! empty($job['conflict'])) {
            return Ajax::message(1, (string) ($job['message'] ?? '请先停止当前任务'), $job);
        }

        return Ajax::message(0, (string) ($job['message'] ?? ''), $job);
    }

    public function makeRun(Request $request): JsonResponse
    {
        $data = $this->ops->makeHtml((string) $request->input('scope', 'all'));

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function makeMap(Request $request): JsonResponse
    {
        $data = $this->ops->makeMap((string) $request->input('scope', 'sitemap'));

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    protected function ttlPlain(int $seconds): string
    {
        if ($seconds <= 0) {
            return '改内容后马上换新';
        }
        if ($seconds % 86400 === 0) {
            return ($seconds / 86400).' 天';
        }
        if ($seconds % 3600 === 0) {
            return ($seconds / 3600).' 小时';
        }
        if ($seconds % 60 === 0) {
            return ($seconds / 60).' 分钟';
        }

        return $seconds.' 秒';
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

    public function collectDue(): JsonResponse
    {
        \Illuminate\Support\Facades\Artisan::call('video:collect-due');

        return Ajax::message(0, trim(\Illuminate\Support\Facades\Artisan::output()) ?: '已执行', []);
    }
}
