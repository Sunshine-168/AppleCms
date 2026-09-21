<?php

namespace App\Http\Controllers\Admin\Video;

use App\Http\Controllers\Controller;
use App\Services\Admin\System\SysSafetyScanService;
use App\Services\Video\DiskHtmlService;
use App\Services\Video\HtmlCacheService;
use App\Services\Video\SiteOpsService;
use App\Services\Video\VideoSettingService;
use App\Support\AdminOpLog;
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
        private readonly SysSafetyScanService $safetyScan,
    ) {}

    public function templates(Request $request): View
    {
        $desk = (string) $request->query('desk', 'look');
        if (! in_array($desk, ['look', 'files'], true)) {
            $desk = 'look';
        }
        if ($desk === 'look') {
            $tab = (string) $request->query('tab', 'base');
            $tabs = ['base', 'home', 'page', 'nav', 'other', 'seo', 'ads'];
            $hasPlayView = $this->settings->themeHasPlayView();
            if (! $hasPlayView) {
                $tabs = array_values(array_filter($tabs, fn (string $key) => $key !== 'page'));
            }
            if (! in_array($tab, $tabs, true)) {
                $tab = 'base';
            }

            return view('admin.video.theme', [
                'desk' => 'look',
                'site' => $this->settings->site(),
                'tab' => $tab,
                'hasPlayView' => $hasPlayView,
            ]);
        }

        $codeEditor = false;
        try {
            $codeEditor = app(\App\Support\Plugins\PluginManager::class)->isEnabled('code_editor');
        } catch (\Throwable) {
        }

        return view('admin.video.templates', [
            'desk' => 'files',
            'groups' => $this->ops->themeFiles(),
            'theme' => $this->ops->themeInfo(),
            'codeEditor' => $codeEditor,
        ]);
    }

    public function templateRead(Request $request): JsonResponse
    {
        try {
            $data = $this->ops->readThemeFile((string) $request->input('path', ''));

            return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
        } catch (\Throwable $e) {
            return Ajax::fail($e->getMessage() !== '' ? $e->getMessage() : admin_t('ui.read_fail'));
        }
    }

    public function templateSave(Request $request): JsonResponse
    {
        $path = (string) $request->input('path', '');
        $data = $this->ops->saveThemeFile($path, (string) $request->input('content', ''));
        if ((int) ($data['code'] ?? 1) === 0) {
            AdminOpLog::write('save', '保存了主题模板'.($path !== '' ? ' '.$path : ''), [
                'module' => '模板',
                'target_type' => 'templates',
                'payload' => ['path' => $path],
            ]);
        }

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
        return view('admin.video.push', $this->ops->pushPage());
    }

    public function pushRun(Request $request): JsonResponse
    {
        $engine = (string) $request->input('engine', 'baidu');
        $data = $this->ops->seoPush($engine, (int) $request->input('limit', 50));
        $label = match ($engine) {
            'shenma' => admin_t('ui.push_shenma'),
            'bing' => admin_t('ui.push_bing'),
            default => admin_t('ui.push_baidu'),
        };
        $n = (int) (($data['data']['count'] ?? 0));
        $data = AdminOpLog::ifOk($data, 'push', '向'.$label.'推了 '.$n.' 条', [
            'module' => '搜索推送',
            'target_type' => 'seo_push',
            'payload' => ['engine' => $engine, 'limit' => (int) $request->input('limit', 50)],
        ]);

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function make(Request $request): View
    {
        $desk = (string) $request->query('desk', 'opt');
        if (! in_array($desk, ['opt', 'index', 'map', 'cache'], true)) {
            $desk = 'opt';
        }
        $site = [];
        try {
            $site = $this->settings->site();
        } catch (\Throwable) {
        }
        $ttl = max(0, (int) ($site['html_cache_ttl'] ?? 3600));
        $ttlOptions = HtmlCacheService::ttlOptions();
        if (! array_key_exists($ttl, $ttlOptions)) {
            $ttlOptions[$ttl] = admin_t('ui.ttl_current', ['n' => $this->ttlPlain($ttl)]);
        }
        $lastBust = $this->htmlCache->lastBust();
        $catalog = $this->diskHtml->optCatalog();

        return view('admin.video.make', [
            'desk' => $desk,
            'enabled' => (bool) ($site['html_cache_enabled'] ?? false),
            'ttl' => $ttl,
            'ttlOptions' => $ttlOptions,
            'lastBust' => $lastBust,
            'lastBustLabel' => $this->htmlCache->bustLabel($lastBust),
            'diskEnabled' => $this->diskHtml->enabled(),
            'diskCount' => $this->diskHtml->fileCount(),
            'diskJob' => $this->diskHtml->publicJob(),
            'vodTypes' => $catalog['vodTypes'],
            'artTypes' => $catalog['artTypes'],
            'topics' => $catalog['topics'],
            'actors' => $catalog['actors'],
            'roles' => $catalog['roles'],
            'hasArts' => $catalog['hasArts'],
            'pluginMakes' => $catalog['plugins'] ?? [],
            'detailCap' => $catalog['detailCap'],
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
            return redirect($this->makeUrl('cache'))->with('error', (string) ($saved['msg'] ?? admin_t('ui.cant_save')));
        }
        $this->htmlCache->forgetAll('settings');
        AdminOpLog::write('save', $on ? '开启了全页缓存' : '关闭了全页缓存', [
            'module' => '缓存',
            'target_type' => 'cache',
        ]);

        return redirect($this->makeUrl('cache'))->with('status', $on ? admin_t('ui.html_cache_on') : admin_t('ui.html_cache_off'));
    }

    public function makeCacheClear(): RedirectResponse
    {
        $this->htmlCache->forgetAll('admin:clear');
        AdminOpLog::write('flush', '清空了全页缓存', [
            'module' => '缓存',
            'target_type' => 'cache',
        ]);

        return redirect($this->makeUrl('cache'))->with('status', admin_t('ui.html_cache_cleared'));
    }

    public function makeCacheWarm(Request $request): RedirectResponse
    {
        if (! $this->htmlCache->enabled()) {
            return redirect($this->makeUrl('cache'))->with('error', admin_t('ui.html_cache_need_on'));
        }
        $result = $this->htmlCache->warm((int) $request->input('entries', 30));
        $msg = $result['fail'] > 0
            ? admin_t('ui.html_cache_warmed_fail', ['ok' => $result['ok'], 'fail' => $result['fail']])
            : admin_t('ui.html_cache_warmed', ['ok' => $result['ok']]);

        return redirect($this->makeUrl('cache'))->with('status', $msg);
    }

    public function makeDiskSave(Request $request): RedirectResponse
    {
        $on = $request->boolean('disk_html_enabled');
        $saved = $this->settings->saveOptions([
            'disk_html_enabled' => $on ? '1' : '0',
        ]);
        $desk = (string) $request->input('desk', 'opt');
        if (($saved['code'] ?? 1) !== 0) {
            return redirect($this->makeUrl($desk))->with('error', (string) ($saved['msg'] ?? admin_t('ui.cant_save')));
        }

        return redirect($this->makeUrl($desk))->with('status', $on ? admin_t('ui.disk_html_on') : admin_t('ui.disk_html_off'));
    }

    public function makeStart(Request $request): JsonResponse
    {
        if (! $this->diskHtml->enabled()) {
            return Ajax::fail(admin_t('ui.disk_html_need_on'));
        }
        $job = $this->diskHtml->startJob((string) $request->input('scope', 'all'), [
            'ids' => $request->input('ids', []),
            'type_ids' => $request->input('type_ids', []),
            'when' => (string) $request->input('when', 'all'),
            'extra' => (string) $request->input('extra', ''),
        ]);
        if (! empty($job['conflict'])) {
            return Ajax::message(1, (string) ($job['message'] ?? admin_t('ui.job_stop_first')), $job);
        }

        return Ajax::message(0, (string) ($job['message'] ?? ''), $job);
    }

    public function makeStep(Request $request): JsonResponse
    {
        $job = $this->diskHtml->publicJob();
        $kind = (string) ($job['kind'] ?? 'build');
        if ($kind !== 'clear' && ! $this->diskHtml->enabled()) {
            return Ajax::fail(admin_t('ui.disk_html_need_on'));
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
            return Ajax::message(1, (string) ($job['message'] ?? admin_t('ui.job_stop_first')), $job);
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

    protected function makeUrl(string $desk = 'opt'): string
    {
        if (! in_array($desk, ['opt', 'index', 'map', 'cache'], true)) {
            $desk = 'opt';
        }

        return $desk === 'opt' ? '/admin/video/make' : '/admin/video/make?desk='.$desk;
    }

    protected function ttlPlain(int $seconds): string
    {
        if ($seconds <= 0) {
            return admin_t('ui.ttl_now');
        }
        if ($seconds % 86400 === 0) {
            return admin_t('ui.ttl_1d_n', ['n' => $seconds / 86400]);
        }
        if ($seconds % 3600 === 0) {
            return admin_t('ui.ttl_1h_n', ['n' => $seconds / 3600]);
        }
        if ($seconds % 60 === 0) {
            return admin_t('ui.ttl_1m_n', ['n' => $seconds / 60]);
        }

        return admin_t('ui.ttl_sec_n', ['n' => $seconds]);
    }

    public function disableFailSource(Request $request): JsonResponse
    {
        $data = $this->ops->disablePlayFailSource((int) $request->input('id', 0));

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function wizard(): View
    {
        return view('admin.video.wizard', [
            'catalog' => app(\App\Services\Admin\Video\ThemeTagWizardService::class)->catalog(),
        ]);
    }

    public function wizardSnippet(Request $request): JsonResponse
    {
        $data = app(\App\Services\Admin\Video\ThemeTagWizardService::class)
            ->snippet((string) $request->input('tag', ''), $request->all());

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function wizardTry(Request $request): JsonResponse
    {
        $data = app(\App\Services\Admin\Video\ThemeTagWizardService::class)
            ->tryTag((string) $request->input('tag', ''), $request->all());

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
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

        return Ajax::message(0, trim(\Illuminate\Support\Facades\Artisan::output()) ?: admin_t('ui.reset_ok'), []);
    }

    public function rewrite(): View
    {
        return view('admin.video.rewrite', $this->ops->rewriteRules());
    }

    public function safety(): View
    {
        return view('admin.video.safety', $this->safetyScan->pageBoard());
    }

    public function malwareScan(Request $request): JsonResponse
    {
        if ($request->boolean('cancel')) {
            $data = $this->safetyScan->cancelScan((string) $request->input('token', ''));

            return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
        }
        $token = trim((string) $request->input('token', ''));
        $data = $token !== ''
            ? $this->safetyScan->continueScan($token)
            : $this->safetyScan->beginScan($request->boolean('with_app'));

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

        return Ajax::message(0, trim(\Illuminate\Support\Facades\Artisan::output()) ?: admin_t('ui.ran_ok'), []);
    }
}
