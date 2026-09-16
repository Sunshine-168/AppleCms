<?php

namespace App\Services\Video;

use App\Models\Member\MemberPm;
use App\Models\Video\VideoCollectTask;
use App\Models\Video\VideoGuestbook;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoPlayFail;
use App\Models\Video\VideoVisitDay;
use App\Services\Collect\CollectIngestService;
use App\Support\Utils\Result;
use Illuminate\Http\Client\ConnectionException;
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

    /** @return list<array{path:string,name:string}> */
    public function themeFiles(): array
    {
        $root = $this->themeRoot();
        if (! is_dir($root)) {
            return [];
        }
        $out = [];
        foreach (File::allFiles($root) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $rel = str_replace('\\', '/', $file->getRelativePathname());
            $out[] = ['path' => $rel, 'name' => $rel];
        }
        usort($out, fn ($a, $b) => strcmp($a['path'], $b['path']));

        return $out;
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
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        return Result::success([], '已保存');
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

    public function baiduPush(int $limit = 50): array
    {
        $token = trim((string) $this->settings->get('baidu_push_token', ''));
        if ($token === '') {
            return Result::fail('请先填写百度推送 token');
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
            $res = Http::timeout(20)
                ->withBody(implode("\n", $urls), 'text/plain')
                ->post('http://data.zz.baidu.com/urls?site='.urlencode((string) $host).'&token='.urlencode($token));
        } catch (ConnectionException $e) {
            return Result::fail($e->getMessage());
        }

        return Result::success([
            'count' => count($urls),
            'response' => $res->json() ?: $res->body(),
        ], '已提交 '.count($urls).' 条');
    }

    public function makeHtml(): array
    {
        Artisan::call('view:clear');
        Cache::flush();
        $dir = storage_path('app/html');
        File::ensureDirectoryExists($dir);
        File::put($dir.'/.keep', (string) time());

        return Result::success(['dir' => $dir], '已清理模板与页面缓存，访客将重新生成 HTML');
    }

    public function visitSummary(): array
    {
        if (! Schema::hasTable('video_visit_days')) {
            return ['days' => [], 'today' => ['day' => (int) date('Ymd'), 'pv' => 0, 'uv' => 0]];
        }
        $today = (int) date('Ymd');
        $row = VideoVisitDay::query()->where('day', $today)->first();

        return [
            'today' => [
                'day' => $today,
                'pv' => (int) ($row->pv ?? 0),
                'uv' => (int) ($row->uv ?? 0),
            ],
            'days' => VideoVisitDay::query()->orderByDesc('day')->limit(14)->get()->toArray(),
        ];
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
            'status' => 1,
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

    public function hitVisit(string $ip): void
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
