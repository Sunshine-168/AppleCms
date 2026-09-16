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
        $order = ['layout' => '首页与布局', 'vod' => '影片', 'member' => '会员', 'partials' => '公共块', 'other' => '其他'];
        $out = [];
        foreach ($order as $key => $label) {
            if (empty($grouped[$key]['files'])) {
                continue;
            }
            $files = $grouped[$key]['files'];
            usort($files, fn ($a, $b) => strcmp($a['label'], $b['label']));
            $out[] = [
                'key' => $key,
                'label' => $grouped[$key]['label'] ?? $label,
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
            return Result::fail('非法路径');
        }
        if (! is_file($path)) {
            return Result::fail('文件不存在');
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
            return Result::fail('非法路径');
        }
        if (! str_ends_with(strtolower($rel), '.blade.php')) {
            return Result::fail('只能编辑 blade 模板');
        }
        if (! is_file($path)) {
            return Result::fail('文件不存在');
        }
        $this->backupThemeFile($rel);
        File::put($path, $content);
        try {
            Artisan::call('view:clear');
        } catch (\Throwable) {
        }

        return Result::success(['backup_at' => $this->latestBackupTime($rel)], '已保存，并做了备份');
    }

    public function backupThemeFile(string $rel): array
    {
        $path = $this->safeThemePath($rel);
        if ($path === null || ! is_file($path)) {
            return Result::fail('文件不存在');
        }
        $stamp = date('YmdHis');
        $dest = storage_path('app/theme-backups/'.$stamp.'/'.str_replace('\\', '/', $rel));
        File::ensureDirectoryExists(dirname($dest));
        File::copy($path, $dest);

        return Result::success(['backup' => $dest, 'backup_at' => $this->latestBackupTime($rel)], '已备份');
    }

    public function rollbackThemeFile(string $rel): array
    {
        $path = $this->safeThemePath($rel);
        if ($path === null) {
            return Result::fail('非法路径');
        }
        $root = storage_path('app/theme-backups');
        if (! is_dir($root)) {
            return Result::fail('没有备份');
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
            return Result::fail('没有该文件的备份');
        }
        File::copy($latest, $path);
        try {
            Artisan::call('view:clear');
        } catch (\Throwable) {
        }

        return Result::success(['backup_at' => $this->latestBackupTime($rel)], '已回到上次备份');
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

    public function seoPush(string $engine = 'baidu', int $limit = 50): array
    {
        $engine = $engine === '' ? 'baidu' : $engine;
        $tokenKey = match ($engine) {
            'shenma' => 'shenma_push_token',
            'bing' => 'bing_push_token',
            default => 'baidu_push_token',
        };
        $token = trim((string) $this->settings->get($tokenKey, ''));
        if ($token === '') {
            return Result::fail('请先填写 '.$engine.' 推送 token');
        }
        $site = rtrim((string) config('app.url'), '/');
        $urls = VideoModel::query()->published()->orderByDesc('id')->limit(max(1, min(200, $limit)))->get()
            ->map(fn (VideoModel $v) => $site.$v->url)
            ->all();
        if ($urls === []) {
            return Result::fail('没有可推送的影片');
        }
        try {
            $host = parse_url($site, PHP_URL_HOST) ?: '';
            $endpoint = match ($engine) {
                'shenma' => 'https://data.zhanzhang.sm.cn/push?site='.urlencode((string) $host).'&user_name=&resource_name=daily&token='.urlencode($token),
                'bing' => 'https://ssl.bing.com/webmaster/api.svc/json/SubmitUrlbatch?apikey='.urlencode($token),
                default => 'http://data.zz.baidu.com/urls?site='.urlencode((string) $host).'&token='.urlencode($token),
            };
            if ($engine === 'bing') {
                $res = Http::timeout(20)->post($endpoint, ['siteUrl' => $site, 'urlList' => $urls]);
            } else {
                $res = Http::timeout(20)
                    ->withBody(implode("\n", $urls), 'text/plain')
                    ->post($endpoint);
            }
        } catch (ConnectionException $e) {
            return Result::fail($e->getMessage());
        }

        return Result::success([
            'count' => count($urls),
            'engine' => $engine,
            'response' => $res->json() ?: $res->body(),
        ], $engine.' 已提交 '.count($urls).' 条');
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
        ], '已生成 public/'.implode(', ', $written));
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
            return Result::fail('记录不存在');
        }
        $sid = (int) $fail->source_id;
        if ($sid < 1) {
            return Result::fail('没有关联线路');
        }
        VideoSourceModel::query()->where('id', $sid)->update(['status' => 0, 'updated_at' => time()]);
        $fail->status = 1;
        $fail->save();

        return Result::success([], '已下线线路 #'.$sid);
    }

    public function addGuestbook(string $name, string $content, int $memberId = 0, string $ip = ''): array
    {
        $content = trim($content);
        if (mb_strlen($content) < 2) {
            return Result::fail('留言太短');
        }
        VideoGuestbook::query()->create([
            'member_id' => $memberId,
            'author_name' => mb_substr($name !== '' ? $name : '游客', 0, 80),
            'content' => mb_substr($content, 0, 2000),
            'reply' => '',
            'status' => (int) app(VideoSettingService::class)->get('gbook_audit', '0') === 1 ? 0 : 1,
            'ip' => $ip,
            'created_at' => time(),
        ]);

        return Result::success([], '已提交');
    }

    public function addPlayFail(int $videoId, int $sourceId, int $episodeId, string $url, string $content, string $ip): array
    {
        VideoPlayFail::query()->create([
            'video_id' => $videoId,
            'source_id' => $sourceId,
            'episode_id' => $episodeId,
            'url' => mb_substr($url, 0, 500),
            'content' => mb_substr($content !== '' ? $content : '播放失败', 0, 500),
            'ip' => $ip,
            'status' => 0,
            'created_at' => time(),
        ]);

        return Result::success([], '已记录');
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

    /** @return array{nginx:string,apache:string,mode:string,suffix:string} */
    public function rewriteRules(): array
    {
        $mode = (string) $this->settings->get('rewrite_mode', config('video.rewrite.mode', 'laravel'));
        $suffix = (string) $this->settings->get('rewrite_suffix', config('video.rewrite.suffix', '.html'));
        $nginx = <<<'NGINX'
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
location ~ \.php$ {
    fastcgi_pass unix:/run/php/php-fpm.sock;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    include fastcgi_params;
}
NGINX;
        if ($mode === 'mac') {
            $nginx .= <<<NGINX

# MacCMS style: /index.php/vod/detail/id/123{$suffix}
location /index.php/ {
    try_files \$uri /index.php?\$query_string;
}
NGINX;
        }
        $apache = <<<'APACHE'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^index\.php$ - [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . /index.php [L]
</IfModule>
APACHE;

        return [
            'nginx' => $nginx,
            'apache' => $apache,
            'mode' => $mode,
            'suffix' => $suffix,
        ];
    }

    public function malwareScan(): array
    {
        $needles = [
            'eval'.'(',
            'assert'.'(',
            'base64_decode'.'(',
            'system'.'(',
            'passthru'.'(',
            'shell_exec'.'(',
        ];
        $hits = [];
        foreach ([app_path(), public_path()] as $root) {
            if (! is_dir($root)) {
                continue;
            }
            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
            foreach ($it as $file) {
                if (! $file->isFile() || strtolower($file->getExtension()) !== 'php') {
                    continue;
                }
                $path = $file->getPathname();
                $rel = str_replace('\\', '/', substr($path, strlen(base_path()) + 1));
                $content = @file_get_contents($path);
                if ($content === false || $content === '') {
                    continue;
                }
                $lines = preg_split("/\r\n|\n|\r/", $content) ?: [];
                foreach ($lines as $num => $line) {
                    foreach ($needles as $needle) {
                        if (str_contains($line, $needle)) {
                            $hits[] = $rel.':'.($num + 1);
                            break;
                        }
                    }
                }
            }
        }

        return Result::success([
            'hits' => $hits,
            'count' => count($hits),
        ], $hits === [] ? '未发现可疑调用' : '发现 '.count($hits).' 处');
    }

    public function replacePlayUrl(string $from, string $to, mixed $ids = [], string $value = ''): array
    {
        if ($from === '' && str_contains($value, '|')) {
            [$from, $to] = array_pad(explode('|', $value, 2), 2, '');
        }
        $from = (string) $from;
        $to = (string) $to;
        if ($from === '') {
            return Result::fail('格式为 from|to');
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

        return Result::success(['count' => $n], '已替换 '.$n.' 条');
    }

    /** @return array{label:string,group:string,group_label:string} */
    private function themeFileMeta(string $rel): array
    {
        $map = [
            'layout.blade.php' => ['整站头尾', 'layout', '首页与布局'],
            'index/index.blade.php' => ['首页', 'layout', '首页与布局'],
            'partials/vod-card.blade.php' => ['影片卡片', 'partials', '公共块'],
            'partials/filters.blade.php' => ['筛选条', 'partials', '公共块'],
            'partials/paginate.blade.php' => ['分页', 'partials', '公共块'],
            'vod/type.blade.php' => ['分类列表', 'vod', '影片'],
            'vod/show.blade.php' => ['筛选结果', 'vod', '影片'],
            'vod/detail.blade.php' => ['影片详情', 'vod', '影片'],
            'vod/play.blade.php' => ['播放页', 'vod', '影片'],
            'vod/player.blade.php' => ['播放器', 'vod', '影片'],
            'vod/down.blade.php' => ['下载页', 'vod', '影片'],
            'vod/search.blade.php' => ['搜索', 'vod', '影片'],
            'vod/latest.blade.php' => ['最近更新', 'vod', '影片'],
            'vod/tag.blade.php' => ['标签页', 'vod', '影片'],
            'vod/topic.blade.php' => ['专题详情', 'vod', '影片'],
            'vod/topics.blade.php' => ['专题列表', 'vod', '影片'],
            'vod/actor.blade.php' => ['演员详情', 'vod', '影片'],
            'vod/actors.blade.php' => ['演员列表', 'vod', '影片'],
            'vod/art.blade.php' => ['文章详情', 'vod', '影片'],
            'vod/arts.blade.php' => ['文章列表', 'vod', '影片'],
            'vod/gbook.blade.php' => ['留言本', 'vod', '影片'],
            'vod/role.blade.php' => ['角色详情', 'vod', '影片'],
            'vod/roles.blade.php' => ['角色列表', 'vod', '影片'],
            'vod/website.blade.php' => ['网址详情', 'vod', '影片'],
            'vod/websites.blade.php' => ['网址列表', 'vod', '影片'],
            'vod/plot.blade.php' => ['剧情', 'vod', '影片'],
            'member/login.blade.php' => ['登录', 'member', '会员'],
            'member/register.blade.php' => ['注册', 'member', '会员'],
            'member/center.blade.php' => ['会员中心', 'member', '会员'],
            'member/list.blade.php' => ['我的影片', 'member', '会员'],
            'member/inbox.blade.php' => ['站内信', 'member', '会员'],
        ];
        if (isset($map[$rel])) {
            return ['label' => $map[$rel][0], 'group' => $map[$rel][1], 'group_label' => $map[$rel][2]];
        }
        $base = basename($rel, '.blade.php');
        if (str_starts_with($rel, 'member/')) {
            return ['label' => $base, 'group' => 'member', 'group_label' => '会员'];
        }
        if (str_starts_with($rel, 'partials/')) {
            return ['label' => $base, 'group' => 'partials', 'group_label' => '公共块'];
        }
        if (str_starts_with($rel, 'index/')) {
            return ['label' => $base, 'group' => 'layout', 'group_label' => '首页与布局'];
        }
        if (str_starts_with($rel, 'vod/')) {
            return ['label' => $base, 'group' => 'vod', 'group_label' => '影片'];
        }

        return ['label' => $base, 'group' => 'other', 'group_label' => '其他'];
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
