<?php

namespace App\Services\Video;

use App\Models\Member\MemberPm;
use App\Models\Video\ActorModel;
use App\Models\Video\VideoArt;
use App\Models\Video\VideoCollectTask;
use App\Models\Video\VideoEpisodeModel;
use App\Models\Video\VideoGuestbook;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoPlayFail;
use App\Models\Video\VideoSourceModel;
use App\Models\Video\VideoStatModel;
use App\Models\Video\VideoTagModel;
use App\Models\Video\VideoTopicModel;
use App\Models\Video\VideoTypeModel;
use App\Models\Video\VideoVisitDay;
use App\Models\Video\VideoVisitItem;
use App\Models\Video\VideoWebsite;
use App\Services\Collect\CollectIngestService;
use App\Support\Utils\Result;
use App\Support\VideoMeta;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class SiteOpsService
{
    public function __construct(
        private readonly VideoSettingService $settings,
        private readonly CollectIngestService $ingest,
    ) {}

    /**
     * @return list<array{key:string,label:string,files:list<array{path:string,name:string,label:string}>}>
     */
    public function themeFiles(): array
    {
        $root = $this->themeRoot();
        if (! is_dir($root)) {
            return [];
        }
        $grouped = [];
        foreach (File::allFiles($root) as $file) {
            $rel = str_replace('\\', '/', $file->getRelativePathname());
            if (! str_ends_with(strtolower($rel), '.blade.php')) {
                continue;
            }
            $meta = $this->themeFileMeta($rel);
            $grouped[$meta['group']]['label'] = $meta['group_label'];
            $grouped[$meta['group']]['files'][] = [
                'path' => $rel,
                'name' => basename($rel),
                'label' => $meta['label'],
            ];
        }
        $order = [
            'layout' => 'ui.tpl_g_layout',
            'vod' => 'ui.tpl_g_vod',
            'member' => 'ui.rewrite_g_member',
            'partials' => 'ui.tpl_g_partials',
            'other' => 'ui.tpl_g_other',
        ];
        $out = [];
        foreach ($order as $key => $labelKey) {
            if (empty($grouped[$key]['files'])) {
                continue;
            }
            $files = $grouped[$key]['files'];
            usort($files, fn ($a, $b) => strcmp($a['label'], $b['label']));
            $out[] = [
                'key' => $key,
                'label' => $grouped[$key]['label'] ?? admin_t($labelKey),
                'files' => $files,
            ];
        }

        return $out;
    }

    /** @return array{slug:string,title:string} */
    public function themeInfo(): array
    {
        $slug = (string) config('video.theme', 'default');
        $title = $slug;
        $json = $this->themeRoot().DIRECTORY_SEPARATOR.'config.json';
        if (is_file($json)) {
            $data = json_decode((string) file_get_contents($json), true);
            if (is_array($data)) {
                $title = (string) ($data['title'] ?? $data['name'] ?? $slug);
            }
        }

        return ['slug' => $slug, 'title' => $title !== '' ? $title : $slug];
    }

    public function readThemeFile(string $rel): array
    {
        $path = $this->safeThemePath($rel);
        if ($path === null) {
            return Result::fail(admin_t('ui.tpl_bad_path'));
        }
        if (! is_file($path)) {
            return Result::fail(admin_t('ui.bak_file_missing'));
        }

        return Result::success([
            'path' => str_replace('\\', '/', $rel),
            'content' => (string) file_get_contents($path),
            'backup_at' => $this->latestBackupTime($rel),
            'label' => $this->themeFileMeta($rel)['label'],
        ]);
    }

    public function saveThemeFile(string $rel, string $content): array
    {
        $path = $this->safeThemePath($rel);
        if ($path === null) {
            return Result::fail(admin_t('ui.tpl_bad_path'));
        }
        if (! str_ends_with(strtolower($rel), '.blade.php')) {
            return Result::fail(admin_t('ui.tpl_blade_only'));
        }
        if (! is_file($path)) {
            return Result::fail(admin_t('ui.bak_file_missing'));
        }
        $this->backupThemeFile($rel);
        File::put($path, $content);
        try {
            Artisan::call('view:clear');
        } catch (\Throwable) {
        }

        return Result::success(['backup_at' => $this->latestBackupTime($rel)], admin_t('ui.tpl_saved_backup'));
    }

    public function backupThemeFile(string $rel): array
    {
        $path = $this->safeThemePath($rel);
        if ($path === null || ! is_file($path)) {
            return Result::fail(admin_t('ui.bak_file_missing'));
        }
        $stamp = date('YmdHis');
        $dest = storage_path('app/theme-backups/'.$stamp.'/'.str_replace('\\', '/', $rel));
        File::ensureDirectoryExists(dirname($dest));
        File::copy($path, $dest);

        return Result::success(['backup' => $dest, 'backup_at' => $this->latestBackupTime($rel)], admin_t('ui.tpl_backed_up'));
    }

    public function rollbackThemeFile(string $rel): array
    {
        $path = $this->safeThemePath($rel);
        if ($path === null) {
            return Result::fail(admin_t('ui.tpl_bad_path'));
        }
        $root = storage_path('app/theme-backups');
        if (! is_dir($root)) {
            return Result::fail(admin_t('ui.tpl_no_backup'));
        }
        $dirs = File::directories($root);
        rsort($dirs);
        $latest = null;
        foreach ($dirs as $dir) {
            $cand = $dir.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $rel);
            if (is_file($cand)) {
                $latest = $cand;
                break;
            }
        }
        if ($latest === null) {
            return Result::fail(admin_t('ui.tpl_no_file_backup'));
        }
        File::copy($latest, $path);
        try {
            Artisan::call('view:clear');
        } catch (\Throwable) {
        }

        return Result::success(['backup_at' => $this->latestBackupTime($rel)], admin_t('ui.tpl_rolled'));
    }

    public function runDueCollectTasks(): int
    {
        if (! Schema::hasTable('video_collect_tasks')) {
            return 0;
        }
        $ran = 0;
        foreach (VideoCollectTask::query()->where('status', 1)->get() as $task) {
            $cron = trim((string) $task->cron_expression);
            if ($cron === '' || ! $this->cronDue($cron, (int) $task->last_run_at)) {
                continue;
            }
            $result = $this->ingest->run((int) $task->collect_source_id, [
                'pages' => max(1, (int) $task->pages),
                'hours' => (int) $task->hours,
            ]);
            $task->last_run_at = time();
            $task->last_msg = mb_substr((string) ($result['msg'] ?? ''), 0, 250);
            $task->save();
            $ran++;
        }

        return $ran;
    }

    /**
     * @return array{
     *     site: array<string, mixed>,
     *     engines: list<array{id:string,label:string,token_key:string,hint:string,token:string,ready:bool}>,
     *     video_count: int,
     *     recent_count: int,
     *     site_url: string,
     *     host: string,
     *     local: bool,
     *     samples: list<string>,
     *     sitemap_url: string,
     *     sitemap_inc_url: string,
     *     robots_url: string,
     *     rss_url: string
     * }
     */
    public function pushPage(): array
    {
        $site = [];
        try {
            $site = $this->settings->site();
        } catch (\Throwable) {
            $site = [];
        }
        $count = 0;
        $recent = 0;
        $samples = [];
        try {
            if (Schema::hasTable('videos')) {
                $count = (int) VideoModel::query()->published()->count();
                $recent = (int) VideoModel::query()->published()->where('updated_at', '>=', time() - 86400 * 2)->count();
                $samples = VideoModel::query()->published()->orderByDesc('id')->limit(5)->get()
                    ->map(fn (VideoModel $v) => $this->absolutePushUrl($v))
                    ->filter()
                    ->values()
                    ->all();
            }
        } catch (\Throwable) {
            $count = 0;
            $recent = 0;
            $samples = [];
        }
        $siteUrl = rtrim((string) config('app.url'), '/');
        $host = strtolower((string) (parse_url($siteUrl, PHP_URL_HOST) ?: ''));
        $engines = [
            ['id' => 'baidu', 'label' => admin_t('ui.push_baidu'), 'token_key' => 'baidu_push_token', 'hint' => admin_t('ui.push_hint_baidu')],
            ['id' => 'shenma', 'label' => admin_t('ui.push_shenma'), 'token_key' => 'shenma_push_token', 'hint' => admin_t('ui.push_hint_shenma')],
            ['id' => 'bing', 'label' => admin_t('ui.push_bing'), 'token_key' => 'bing_push_token', 'hint' => admin_t('ui.push_hint_bing')],
        ];
        foreach ($engines as &$row) {
            $row['token'] = trim((string) ($site[$row['token_key']] ?? ''));
            $row['ready'] = $row['token'] !== '';
        }
        unset($row);

        return [
            'site' => $site,
            'engines' => $engines,
            'video_count' => $count,
            'recent_count' => $recent,
            'site_url' => $siteUrl,
            'host' => $host,
            'local' => $this->isLocalPushHost($host),
            'samples' => $samples,
            'sitemap_url' => url('/sitemap.xml'),
            'sitemap_inc_url' => url('/sitemap.xml?inc=1'),
            'robots_url' => url('/robots.txt'),
            'rss_url' => url('/rss.xml'),
        ];
    }

    public function seoPush(string $engine = 'baidu', int $limit = 50): array
    {
        $engine = in_array($engine, ['baidu', 'shenma', 'bing'], true) ? $engine : 'baidu';
        $label = match ($engine) {
            'shenma' => admin_t('ui.push_shenma'),
            'bing' => admin_t('ui.push_bing'),
            default => admin_t('ui.push_baidu'),
        };
        $tokenKey = match ($engine) {
            'shenma' => 'shenma_push_token',
            'bing' => 'bing_push_token',
            default => 'baidu_push_token',
        };
        $token = trim((string) $this->settings->get($tokenKey, ''));
        if ($token === '') {
            return Result::fail(admin_t('ui.push_need_token', ['label' => $label]));
        }
        $limit = max(1, min(100, $limit));
        $urls = [];
        try {
            if (Schema::hasTable('videos')) {
                $urls = VideoModel::query()->published()->orderByDesc('id')->limit($limit)->get()
                    ->map(fn (VideoModel $v) => $this->absolutePushUrl($v))
                    ->filter()
                    ->values()
                    ->all();
            }
        } catch (\Throwable) {
            $urls = [];
        }
        if ($urls === []) {
            return Result::fail(admin_t('ui.push_no_videos'));
        }
        $site = rtrim((string) config('app.url'), '/');
        $host = (string) (parse_url($site, PHP_URL_HOST) ?: '');
        try {
            $endpoint = match ($engine) {
                'shenma' => 'https://data.zhanzhang.sm.cn/push?site='.urlencode($host).'&user_name=&resource_name=daily&token='.urlencode($token),
                'bing' => 'https://ssl.bing.com/webmaster/api.svc/json/SubmitUrlbatch?apikey='.urlencode($token),
                default => 'http://data.zz.baidu.com/urls?site='.urlencode($host).'&token='.urlencode($token),
            };
            if ($engine === 'bing') {
                $res = Http::timeout(20)->asJson()->post($endpoint, ['siteUrl' => $site, 'urlList' => $urls]);
            } else {
                $res = Http::timeout(20)
                    ->withBody(implode("\n", $urls), 'text/plain')
                    ->post($endpoint);
            }
        } catch (ConnectionException $e) {
            return Result::fail(admin_t('ui.push_unreach', ['label' => $label, 'err' => $e->getMessage() !== '' ? $e->getMessage() : admin_t('auth.network')]));
        }

        return $this->readPushResponse($engine, $label, $urls, $res);
    }

    protected function absolutePushUrl(VideoModel $video): string
    {
        $loc = trim((string) $video->url);
        if ($loc === '') {
            return '';
        }
        if (str_starts_with($loc, 'http://') || str_starts_with($loc, 'https://')) {
            return $loc;
        }
        $site = rtrim((string) config('app.url'), '/');

        return $site.(str_starts_with($loc, '/') ? $loc : '/'.$loc);
    }

    protected function isLocalPushHost(string $host): bool
    {
        $host = strtolower($host);
        if ($host === '' || in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return true;
        }

        return str_ends_with($host, '.local') || str_ends_with($host, '.test');
    }

    /**
     * @param  list<string>  $urls
     * @param  \Illuminate\Http\Client\Response  $res
     * @return array{code:int,msg:string,data?:array<string,mixed>}
     */
    protected function readPushResponse(string $engine, string $label, array $urls, $res): array
    {
        $json = $res->json();
        $body = trim((string) $res->body());
        $err = '';
        if (is_array($json)) {
            if (isset($json['ErrorCode']) && (string) $json['ErrorCode'] !== '' && (string) $json['ErrorCode'] !== '0') {
                $err = (string) ($json['Message'] ?? $json['ErrorCode']);
            } elseif (array_key_exists('error', $json) && $json['error'] !== '' && $json['error'] !== 0 && $json['error'] !== '0' && $json['error'] !== null) {
                $err = (string) ($json['message'] ?? $json['msg'] ?? $json['error']);
            }
        }
        if ($err === '' && ! $res->successful()) {
            $err = is_array($json)
                ? (string) ($json['message'] ?? $json['Message'] ?? $json['msg'] ?? '')
                : '';
            if ($err === '') {
                $plain = trim(preg_replace('/\s+/', ' ', strip_tags($body)) ?? '');
                $err = $plain !== '' ? mb_substr($plain, 0, 180) : ('HTTP '.$res->status());
            }
        }
        if ($err !== '') {
            return Result::fail(admin_t('ui.push_reject', ['label' => $label, 'err' => $err]));
        }

        $n = count($urls);
        $remain = null;
        if (is_array($json)) {
            if (isset($json['success']) && is_numeric($json['success'])) {
                $n = (int) $json['success'];
            }
            if (array_key_exists('remain', $json) && $json['remain'] !== '' && $json['remain'] !== null) {
                $remain = $json['remain'];
            }
        }
        $msg = admin_t('ui.push_ok', ['label' => $label, 'n' => $n]);
        if ($remain !== null) {
            $msg = admin_t('ui.push_ok_remain', ['label' => $label, 'n' => $n, 'remain' => $remain]);
        }

        return Result::success([
            'count' => $n,
            'engine' => $engine,
            'remain' => $remain,
            'urls' => $urls,
        ], $msg);
    }

    public function baiduPush(int $limit = 50): array
    {
        return $this->seoPush('baidu', $limit);
    }

    public function makeHtml(string $scope = 'all'): array
    {
        return app(DiskHtmlService::class)->buildOnce($scope);
    }

    public function makeMap(string $scope = 'sitemap'): array
    {
        $ctrl = app(\App\Http\Controllers\Web\SeoController::class);
        $req = Request::create('/', 'GET');
        $written = [];
        if ($scope === 'rss') {
            File::put(public_path('rss.xml'), $ctrl->rssXml(''));
            $written[] = 'rss.xml';
        } else {
            File::put(public_path('sitemap.xml'), $ctrl->sitemapXml($req));
            $written[] = 'sitemap.xml';
        }

        return Result::success([
            'files' => $written,
            'count' => count($written),
        ], admin_t('ui.push_map_ok', ['files' => implode(', ', $written)]));
    }

    public function visitSummary(): array
    {
        if (! Schema::hasTable('video_visit_days')) {
            return ['days' => [], 'videos' => [], 'types' => [], 'today' => ['day' => (int) date('Ymd'), 'pv' => 0, 'uv' => 0]];
        }
        $today = (int) date('Ymd');
        $row = VideoVisitDay::query()->where('day', $today)->first();

        $topVideos = [];
        $topTypes = [];
        if (Schema::hasTable('video_visit_items')) {
            $today = (int) date('Ymd');
            $topVideos = VideoVisitItem::query()->where('day', $today)->where('video_id', '>', 0)
                ->orderByDesc('pv')->limit(20)->get()->toArray();
            $topTypes = VideoVisitItem::query()->where('day', $today)->where('type_id', '>', 0)->where('video_id', 0)
                ->orderByDesc('pv')->limit(20)->get()->toArray();
        }
        if ($topVideos === [] && Schema::hasTable('video_stats')) {
            $topVideos = VideoStatModel::query()->orderByDesc('hits')->limit(20)->get(['video_id', 'hits as pv'])->toArray();
        }

        return [
            'today' => [
                'day' => $today,
                'pv' => (int) ($row->pv ?? 0),
                'uv' => (int) ($row->uv ?? 0),
            ],
            'days' => VideoVisitDay::query()->orderByDesc('day')->limit(14)->get()->toArray(),
            'videos' => $topVideos,
            'types' => $topTypes,
        ];
    }

    public function disablePlayFailSource(int $failId): array
    {
        $fail = VideoPlayFail::query()->find($failId);
        if (! $fail) {
            return Result::fail(admin_t('ui.record_missing'));
        }
        $sid = (int) $fail->source_id;
        if ($sid < 1) {
            return Result::fail(admin_t('ui.no_linked_line'));
        }
        VideoSourceModel::query()->where('id', $sid)->update(['status' => 0, 'updated_at' => time()]);
        $fail->status = 1;
        $fail->save();

        return Result::success([], admin_t('ui.line_offlined_id', ['id' => $sid]));
    }

    public function addGuestbook(string $name, string $content, int $memberId = 0, string $ip = ''): array
    {
        $content = trim($content);
        if (mb_strlen($content) < 2) {
            return Result::fail(admin_t('ui.gbook_too_short'));
        }
        VideoGuestbook::query()->create([
            'member_id' => $memberId,
            'author_name' => mb_substr($name !== '' ? $name : admin_t('ui.guest'), 0, 80),
            'content' => mb_substr($content, 0, 2000),
            'reply' => '',
            'status' => (int) app(VideoSettingService::class)->get('gbook_audit', '0') === 1 ? 0 : 1,
            'ip' => $ip,
            'created_at' => time(),
        ]);

        return Result::success([], admin_t('ui.submitted'));
    }

    public function addPlayFail(int $videoId, int $sourceId, int $episodeId, string $url, string $content, string $ip): array
    {
        VideoPlayFail::query()->create([
            'video_id' => $videoId,
            'source_id' => $sourceId,
            'episode_id' => $episodeId,
            'url' => mb_substr($url, 0, 500),
            'content' => mb_substr($content !== '' ? $content : admin_t('ui.play_fail_default'), 0, 500),
            'ip' => $ip,
            'status' => 0,
            'created_at' => time(),
        ]);

        return Result::success([], admin_t('ui.recorded'));
    }

    public function memberInbox(int $memberId)
    {
        return MemberPm::query()->where('to_id', $memberId)->orderByDesc('id')->limit(50)->get();
    }

    public function hitVisit(string $ip, ?int $videoId = null, ?int $typeId = null): void
    {
        if (! Schema::hasTable('video_visit_days')) {
            return;
        }
        $day = (int) date('Ymd');
        $uvKey = 'vod.uv.'.$day.'.'.md5($ip);
        $isNew = ! Cache::has($uvKey);
        if ($isNew) {
            Cache::put($uvKey, 1, 86400);
        }
        $row = VideoVisitDay::query()->firstOrCreate(['day' => $day], ['pv' => 0, 'uv' => 0]);
        $row->pv = (int) $row->pv + 1;
        if ($isNew) {
            $row->uv = (int) $row->uv + 1;
        }
        $row->save();
        if (! Schema::hasTable('video_visit_items')) {
            return;
        }
        $videoId = (int) $videoId;
        $typeId = (int) $typeId;
        if ($videoId < 1 && $typeId < 1) {
            return;
        }
        $item = VideoVisitItem::query()->firstOrCreate(
            ['day' => $day, 'video_id' => $videoId, 'type_id' => $typeId],
            ['pv' => 0]
        );
        $item->pv = (int) $item->pv + 1;
        $item->save();
    }

    /** @return array<string, mixed> */
    public function rewriteRules(): array
    {
        $mode = 'laravel';
        $suffix = '.html';
        try {
            $mode = (string) $this->settings->get('rewrite_mode', config('video.rewrite.mode', 'laravel'));
            $suffix = (string) $this->settings->get('rewrite_suffix', config('video.rewrite.suffix', '.html'));
        } catch (\Throwable) {
            $mode = (string) config('video.rewrite.mode', 'laravel');
            $suffix = (string) config('video.rewrite.suffix', '.html');
        }
        if ($mode !== 'mac') {
            $mode = 'laravel';
        }
        $mac = $mode === 'mac';
        $suffix = $suffix === '' ? '.html' : $suffix;

        $nginx = <<<'NGINX'
# 网站根目录指到项目的 public 目录
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
location ~ \.php$ {
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    fastcgi_pass 127.0.0.1:9000;
    # Linux 套接字示例：fastcgi_pass unix:/run/php/php-fpm.sock;
}
NGINX;

        $apache = <<<'APACHE'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
APACHE;

        $routeGroups = $this->rewriteRouteGroups($mac, $suffix);
        $exampleIds = ['home', 'type', 'detail', 'play', 'search'];
        $examples = [];
        foreach ($routeGroups[0]['rows'] ?? [] as $row) {
            if (in_array((string) ($row['id'] ?? ''), $exampleIds, true)) {
                $examples[] = ['label' => $row['label'], 'path' => $row['path']];
            }
        }

        return [
            'mode' => $mode,
            'mode_label' => $mac ? admin_t('ui.rewrite_mac_mode') : admin_t('ui.rewrite_local_mode'),
            'mode_sample' => $mac ? '/index.php/vod/detail/id/123'.$suffix : '/vod/123',
            'suffix' => $suffix,
            'mac' => $mac,
            'route_groups' => $routeGroups,
            'examples' => $examples,
            'nginx' => $nginx,
            'apache' => $apache,
        ];
    }

    /** @return list<array{title:string, rows:list<array{id:string,label:string,path:string,note?:string}>}> */
    private function rewriteRouteGroups(bool $mac, string $suffix): array
    {
        $row = function (string $id, string $labelKey, string $path, ?string $noteKey = null): array {
            $label = str_contains($labelKey, '.') ? admin_t($labelKey) : $labelKey;
            $item = ['id' => $id, 'label' => $label, 'path' => $path];
            if ($noteKey !== null) {
                $item['note'] = admin_t($noteKey);
            }

            return $item;
        };
        $group = function (string $titleKey, array $rows): array {
            return ['title' => admin_t($titleKey), 'rows' => $rows];
        };
        $local = 'ui.rewrite_local_path';
        $needLogin = 'ui.rewrite_need_login';

        if ($mac) {
            return [
                $group('ui.rewrite_g_vod', [
                    $row('home', 'ui.rewrite_home', '/'),
                    $row('type', 'ui.rewrite_type', '/index.php/vod/type/id/1'.$suffix),
                    $row('detail', 'ui.rewrite_detail', '/index.php/vod/detail/id/1'.$suffix),
                    $row('play', 'ui.rewrite_play', '/index.php/vod/play/id/1/sid/1/nid/1'.$suffix),
                    $row('down', 'ui.rewrite_down', '/index.php/vod/down/id/1/sid/1/nid/1'.$suffix),
                    $row('player', 'ui.rewrite_player', '/player/1/1/1', $local),
                    $row('show', 'ui.rewrite_show', '/index.php/vod/show'.$suffix),
                    $row('search', 'ui.rewrite_search', '/index.php/vod/search'.$suffix),
                    $row('latest', 'ui.rewrite_latest', '/latest', $local),
                ]),
                $group('ui.rewrite_g_other', [
                    $row('tag', 'ui.rewrite_tag', '/index.php/vod/tag/id/1'.$suffix),
                    $row('actors', 'ui.rewrite_actors', '/actors', $local),
                    $row('actor', 'ui.rewrite_actor', '/index.php/vod/actor/id/1'.$suffix),
                    $row('topics', 'ui.rewrite_topics', '/topics', $local),
                    $row('topic', 'ui.rewrite_topic', '/index.php/vod/topic/id/1'.$suffix),
                    $row('arts', 'ui.rewrite_arts', '/index.php/vod/art'.$suffix),
                    $row('art', 'ui.rewrite_art', '/index.php/vod/art/id/1'.$suffix),
                    $row('roles', 'ui.rewrite_roles', '/index.php/vod/role'.$suffix),
                    $row('role', 'ui.rewrite_role', '/index.php/vod/role/id/1'.$suffix),
                    $row('plots', 'ui.rewrite_plots', '/index.php/vod/plot'.$suffix),
                    $row('plot', 'ui.rewrite_plot', '/index.php/vod/plot/id/1'.$suffix),
                    $row('sites', 'ui.rewrite_sites', '/index.php/vod/website'.$suffix),
                    $row('site', 'ui.rewrite_site', '/index.php/vod/website/id/1'.$suffix),
                    $row('gbook', 'ui.rewrite_gbook', '/gbook'),
                ]),
                $group('ui.rewrite_g_member', [
                    $row('login', 'ui.rewrite_login', '/member/login'),
                    $row('register', 'ui.rewrite_register', '/member/register'),
                    $row('center', 'ui.rewrite_center', '/member', $needLogin),
                ]),
                $group('ui.rewrite_g_seo', [
                    $row('sitemap', 'ui.rewrite_sitemap', '/sitemap.xml'),
                    $row('rss', 'RSS', '/rss.xml'),
                    $row('robots', 'robots', '/robots.txt'),
                ]),
            ];
        }

        return [
            $group('ui.rewrite_g_vod', [
                $row('home', 'ui.rewrite_home', '/'),
                $row('type', 'ui.rewrite_type', '/type/1'),
                $row('detail', 'ui.rewrite_detail', '/vod/1'),
                $row('play', 'ui.rewrite_play', '/play/1/1/1'),
                $row('down', 'ui.rewrite_down', '/down/1/1/1'),
                $row('player', 'ui.rewrite_player', '/player/1/1/1'),
                $row('show', 'ui.rewrite_show', '/show'),
                $row('search', 'ui.rewrite_search', '/search'),
                $row('latest', 'ui.rewrite_latest', '/latest'),
            ]),
            $group('ui.rewrite_g_other', [
                $row('tag', 'ui.rewrite_tag', '/tag/贺岁'),
                $row('actors', 'ui.rewrite_actors', '/actors'),
                $row('actor', 'ui.rewrite_actor', '/actor/1'),
                $row('topics', 'ui.rewrite_topics', '/topics'),
                $row('topic', 'ui.rewrite_topic', '/topic/1'),
                $row('arts', 'ui.rewrite_arts', '/arts'),
                $row('art', 'ui.rewrite_art', '/art/1'),
                $row('roles', 'ui.rewrite_roles', '/roles'),
                $row('role', 'ui.rewrite_role', '/role/1'),
                $row('plots', 'ui.rewrite_plots', '/plot'),
                $row('plot', 'ui.rewrite_plot', '/plot/1'),
                $row('sites', 'ui.rewrite_sites', '/website'),
                $row('site', 'ui.rewrite_site', '/website/1'),
                $row('gbook', 'ui.rewrite_gbook', '/gbook'),
            ]),
            $group('ui.rewrite_g_member', [
                $row('login', 'ui.rewrite_login', '/member/login'),
                $row('register', 'ui.rewrite_register', '/member/register'),
                $row('center', 'ui.rewrite_center', '/member', $needLogin),
            ]),
            $group('ui.rewrite_g_seo', [
                $row('sitemap', 'ui.rewrite_sitemap', '/sitemap.xml'),
                $row('rss', 'RSS', '/rss.xml'),
                $row('robots', 'robots', '/robots.txt'),
            ]),
        ];
    }

    public function replacePlayUrl(string $from, string $to, mixed $ids = [], string $value = ''): array
    {
        if ($from === '' && str_contains($value, '|')) {
            [$from, $to] = array_pad(explode('|', $value, 2), 2, '');
        }
        $from = (string) $from;
        $to = (string) $to;
        if ($from === '') {
            return Result::fail(admin_t('ui.replace_from_to'));
        }
        $idList = is_array($ids) ? array_values(array_filter(array_map('intval', $ids))) : VideoMeta::ids($ids);
        $q = VideoEpisodeModel::query();
        if ($idList !== []) {
            $q->whereIn('video_id', $idList);
        }
        $n = 0;
        $q->orderBy('id')->chunkById(200, function ($rows) use ($from, $to, &$n) {
            foreach ($rows as $ep) {
                $url = (string) $ep->url;
                $new = str_replace($from, $to, $url);
                if ($new === $url) {
                    continue;
                }
                $ep->url = $new;
                $ep->updated_at = time();
                $ep->save();
                $n++;
            }
        });

        return Result::success(['count' => $n], admin_t('ui.replaced_n', ['n' => $n]));
    }

    /** @return array{label:string,group:string,group_label:string} */
    private function themeFileMeta(string $rel): array
    {
        $map = [
            'layout.blade.php' => ['ui.tpl_f_chrome', 'layout'],
            'index/index.blade.php' => ['ui.rewrite_home', 'layout'],
            'partials/vod-card.blade.php' => ['ui.tpl_f_card', 'partials'],
            'partials/filters.blade.php' => ['ui.tpl_f_filters', 'partials'],
            'partials/paginate.blade.php' => ['ui.tpl_f_pager', 'partials'],
            'vod/type.blade.php' => ['ui.tpl_f_type_list', 'vod'],
            'vod/show.blade.php' => ['ui.tpl_f_show', 'vod'],
            'vod/detail.blade.php' => ['ui.tpl_f_vod_detail', 'vod'],
            'vod/play.blade.php' => ['ui.tpl_f_play', 'vod'],
            'vod/player.blade.php' => ['ui.tpl_f_player', 'vod'],
            'vod/down.blade.php' => ['ui.tpl_f_down', 'vod'],
            'vod/search.blade.php' => ['ui.rewrite_search', 'vod'],
            'vod/latest.blade.php' => ['ui.rewrite_latest', 'vod'],
            'vod/tag.blade.php' => ['ui.tpl_f_tag', 'vod'],
            'vod/topic.blade.php' => ['ui.tpl_f_topic', 'vod'],
            'vod/topics.blade.php' => ['ui.rewrite_topics', 'vod'],
            'vod/actor.blade.php' => ['ui.tpl_f_actor', 'vod'],
            'vod/actors.blade.php' => ['ui.rewrite_actors', 'vod'],
            'vod/art.blade.php' => ['ui.tpl_f_art', 'vod'],
            'vod/arts.blade.php' => ['ui.tpl_f_arts', 'vod'],
            'vod/gbook.blade.php' => ['ui.tpl_f_gbook', 'vod'],
            'vod/role.blade.php' => ['ui.tpl_f_role', 'vod'],
            'vod/roles.blade.php' => ['ui.tpl_f_roles', 'vod'],
            'vod/website.blade.php' => ['ui.tpl_f_site', 'vod'],
            'vod/websites.blade.php' => ['ui.tpl_f_sites', 'vod'],
            'vod/plot.blade.php' => ['ui.rewrite_plot', 'vod'],
            'member/login.blade.php' => ['ui.rewrite_login', 'member'],
            'member/register.blade.php' => ['ui.rewrite_register', 'member'],
            'member/center.blade.php' => ['ui.rewrite_center', 'member'],
            'member/list.blade.php' => ['ui.tpl_f_my_videos', 'member'],
            'member/inbox.blade.php' => ['ui.tpl_f_inbox', 'member'],
        ];
        if (isset($map[$rel])) {
            return [
                'label' => admin_t($map[$rel][0]),
                'group' => $map[$rel][1],
                'group_label' => $this->themeGroupLabel($map[$rel][1]),
            ];
        }
        $base = basename($rel, '.blade.php');
        if (str_starts_with($rel, 'member/')) {
            return ['label' => $base, 'group' => 'member', 'group_label' => $this->themeGroupLabel('member')];
        }
        if (str_starts_with($rel, 'partials/')) {
            return ['label' => $base, 'group' => 'partials', 'group_label' => $this->themeGroupLabel('partials')];
        }
        if (str_starts_with($rel, 'index/')) {
            return ['label' => $base, 'group' => 'layout', 'group_label' => $this->themeGroupLabel('layout')];
        }
        if (str_starts_with($rel, 'vod/')) {
            return ['label' => $base, 'group' => 'vod', 'group_label' => $this->themeGroupLabel('vod')];
        }

        return ['label' => $base, 'group' => 'other', 'group_label' => $this->themeGroupLabel('other')];
    }

    private function themeGroupLabel(string $group): string
    {
        return admin_t(match ($group) {
            'layout' => 'ui.tpl_g_layout',
            'vod' => 'ui.tpl_g_vod',
            'member' => 'ui.rewrite_g_member',
            'partials' => 'ui.tpl_g_partials',
            default => 'ui.tpl_g_other',
        });
    }

    private function latestBackupTime(string $rel): int
    {
        $root = storage_path('app/theme-backups');
        if (! is_dir($root)) {
            return 0;
        }
        $latest = 0;
        $relPath = str_replace('/', DIRECTORY_SEPARATOR, $rel);
        foreach (File::directories($root) as $dir) {
            $cand = $dir.DIRECTORY_SEPARATOR.$relPath;
            if (is_file($cand)) {
                $latest = max($latest, (int) filemtime($cand));
            }
        }

        return $latest;
    }

    private function themeRoot(): string
    {
        $theme = (string) config('video.theme', 'default');

        return resource_path('views/themes/'.$theme);
    }

    private function safeThemePath(string $rel): ?string
    {
        $rel = str_replace('\\', '/', $rel);
        if ($rel === '' || str_contains($rel, '..')) {
            return null;
        }
        $full = $this->themeRoot().DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $rel);
        $root = realpath($this->themeRoot()) ?: $this->themeRoot();
        $real = realpath(dirname($full)) ?: dirname($full);
        if (! str_starts_with($real, $root)) {
            return null;
        }

        return $full;
    }

    private function cronDue(string $cron, int $lastRunAt): bool
    {
        if ($lastRunAt > time() - 50) {
            return false;
        }
        try {
            return (new \Cron\CronExpression($cron))->isDue();
        } catch (\Throwable) {
            return $lastRunAt < 1 || (time() - $lastRunAt) >= 3600;
        }
    }
}
