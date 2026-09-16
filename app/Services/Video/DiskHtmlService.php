<?php

namespace App\Services\Video;

use App\Models\Video\ActorModel;
use App\Models\Video\VideoArt;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoTagModel;
use App\Models\Video\VideoTopicModel;
use App\Models\Video\VideoTypeModel;
use App\Models\Video\VideoWebsite;
use App\Support\Utils\Result;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

/** 整站磁盘 HTML：写入 public/html/{path}/index.html */
class DiskHtmlService
{
    public const JOB_TTL = 3600;

    public function __construct(private readonly VideoSettingService $settings) {}

    public function enabled(): bool
    {
        try {
            return (bool) ($this->settings->site()['disk_html_enabled'] ?? false);
        } catch (\Throwable) {
            return (bool) config('video.disk_html.enabled', false);
        }
    }

    /** @return array<string, string> */
    public static function scopes(): array
    {
        return [
            'all' => '全部',
            'index' => '首页',
            'type' => '分类',
            'detail' => '详情',
            'actor' => '演员',
            'topic' => '专题',
            'tag' => '标签',
            'art' => '文章',
            'website' => '网址',
        ];
    }

    public function htmlForRequest(Request $request): ?string
    {
        if (! $this->canServe($request)) {
            return null;
        }
        $path = '/'.ltrim($request->path(), '/');
        if ($path === '//') {
            $path = '/';
        }
        $file = $this->pathForUrl($this->rootPath(), $path);
        if (! is_file($file)) {
            return null;
        }
        $html = File::get($file);

        return is_string($html) && $html !== '' ? $html : null;
    }

    public function canServe(Request $request): bool
    {
        if (! $this->enabled()) {
            return false;
        }
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return false;
        }
        if ($request->ajax() || $request->expectsJson()) {
            return false;
        }
        try {
            if (auth('member')->check()) {
                return false;
            }
        } catch (\Throwable) {
        }
        $path = trim($request->path(), '/');
        $htmlCache = app(HtmlCacheService::class);
        if ($htmlCache->isInternal($request) || $htmlCache->isVolatileFrontPath($path)) {
            return false;
        }
        foreach ($request->query() as $key => $value) {
            $key = strtolower((string) $key);
            if (in_array($key, ['page', 'p'], true) || str_starts_with($key, 'utm_')) {
                continue;
            }

            return false;
        }

        return true;
    }

    public function rootPath(): string
    {
        $override = config('video.disk_html.root');
        if (is_string($override) && $override !== '') {
            return $override;
        }
        $rel = trim((string) config('video.disk_html.path', 'html'), '/\\');

        return public_path($rel);
    }

    public function jobKey(): string
    {
        return 'vod.disk_html.job';
    }

    /**
     * @return array{status:string,kind:string,done:int,total:int,ok:int,fail:int,percent:int,current:string,message:string,file_count:int,errors:list<string>}
     */
    public function publicJob(): array
    {
        return $this->toPublic($this->rawJob());
    }

    public function clear(): int
    {
        $files = $this->listHtmlFiles();
        foreach ($files as $file) {
            File::delete($file);
        }
        $this->pruneEmptyDirs($this->rootPath());

        return count($files);
    }

    public function fileCount(): int
    {
        return count($this->listHtmlFiles());
    }

    /** @return list<string> */
    public function listHtmlFiles(): array
    {
        $root = $this->rootPath();
        if (! is_dir($root)) {
            return [];
        }
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $item) {
            if ($item->isFile() && strtolower($item->getExtension()) === 'html') {
                $files[] = $item->getPathname();
            }
        }

        return $files;
    }

    /**
     * @return array{code:int,msg:string,data:array<string,mixed>}
     */
    public function buildOnce(string $scope = 'all'): array
    {
        File::ensureDirectoryExists($this->rootPath());
        $urls = $this->collectUrls($scope);
        $ok = 0;
        $fail = 0;
        $files = [];
        foreach ($urls as $urlPath) {
            $result = $this->capture($urlPath);
            if ($result['file']) {
                $files[] = 'html/'.$this->relativeHtmlPath($result['file']);
                $ok++;
            } else {
                $fail++;
            }
        }

        $msg = '已写出 '.$ok.' 个静态文件到 public/html';
        if ($fail > 0) {
            $msg .= '，'.$fail.' 页没写上';
        }

        return Result::success([
            'dir' => $this->rootPath(),
            'files' => $files,
            'count' => $ok,
            'fail' => $fail,
        ], $msg);
    }

    /**
     * @return array{status:string,kind:string,done:int,total:int,ok:int,fail:int,percent:int,current:string,message:string,file_count:int,errors:list<string>,conflict?:bool}
     */
    public function startJob(string $scope = 'all'): array
    {
        $busy = $this->busyPublic('build');
        if ($busy !== null) {
            return $busy;
        }

        $urls = $this->collectUrls($scope);
        $job = $this->emptyJob();
        $job['kind'] = 'build';
        $job['status'] = $urls === [] ? 'done' : 'running';
        $job['urls'] = $urls;
        $job['total'] = count($urls);
        $job['current'] = $urls[0] ?? '';
        $job['started_at'] = now()->toIso8601String();
        if ($urls === []) {
            $job['finished_at'] = now()->toIso8601String();
            $job['message'] = '没有可生成的页面';
        } else {
            $job['message'] = '准备写 '.$job['total'].' 个页面';
        }
        $this->putJob($job);

        return $this->toPublic($job);
    }

    /**
     * @return array{status:string,kind:string,done:int,total:int,ok:int,fail:int,percent:int,current:string,message:string,file_count:int,errors:list<string>,conflict?:bool}
     */
    public function startClearJob(): array
    {
        $busy = $this->busyPublic('clear');
        if ($busy !== null) {
            return $busy;
        }

        $files = $this->listHtmlFiles();
        $job = $this->emptyJob();
        $job['kind'] = 'clear';
        $job['status'] = $files === [] ? 'done' : 'running';
        $job['files'] = $files;
        $job['total'] = count($files);
        $job['current'] = $files === [] ? '' : $this->relativeHtmlPath($files[0]);
        $job['started_at'] = now()->toIso8601String();
        if ($files === []) {
            $job['finished_at'] = now()->toIso8601String();
            $job['message'] = '没有可删的静态文件';
        } else {
            $job['message'] = '准备删 '.$job['total'].' 个文件';
        }
        $this->putJob($job);

        return $this->toPublic($job);
    }

    /**
     * @return array{status:string,kind:string,done:int,total:int,ok:int,fail:int,percent:int,current:string,message:string,file_count:int,errors:list<string>}
     */
    public function stepJob(int $chunk = 6): array
    {
        $job = $this->rawJob();
        if (($job['status'] ?? '') !== 'running') {
            return $this->toPublic($job);
        }

        if (($job['kind'] ?? 'build') === 'clear') {
            return $this->stepClearJob($job, $chunk);
        }

        File::ensureDirectoryExists($this->rootPath());
        $chunk = max(1, min(20, $chunk));
        $urls = $job['urls'] ?? [];
        $end = min((int) $job['index'] + $chunk, (int) $job['total']);
        for ($i = (int) $job['index']; $i < $end; $i++) {
            $url = (string) ($urls[$i] ?? '');
            $job['current'] = $url;
            $job['message'] = $url === '' ? '正在写…' : '正在写 '.$url;
            $result = $url === '' ? ['file' => null, 'error' => '空地址'] : $this->capture($url);
            if ($result['file']) {
                $job['ok']++;
            } else {
                $job['fail']++;
                $job['errors'][] = $result['error'] ?? $url;
            }
            $job['index'] = $i + 1;
        }

        if ((int) $job['index'] >= (int) $job['total']) {
            $job['status'] = 'done';
            $job['current'] = '';
            $job['finished_at'] = now()->toIso8601String();
            $job['message'] = '写好了 '.$job['ok'].' 个文件';
            if ((int) $job['fail'] > 0) {
                $shown = array_slice($job['errors'] ?? [], 0, 5);
                $job['message'] .= '，'.$job['fail'].' 页没写上';
                if ($shown !== []) {
                    $job['message'] .= '：'.implode('、', $shown);
                }
            }
            unset($job['urls']);
        }

        $this->putJob($job);

        return $this->toPublic($job);
    }

    /**
     * @return array{status:string,kind:string,done:int,total:int,ok:int,fail:int,percent:int,current:string,message:string,file_count:int,errors:list<string>}
     */
    public function cancelJob(): array
    {
        $job = $this->rawJob();
        if (($job['status'] ?? '') === 'running') {
            $kind = (string) ($job['kind'] ?? 'build');
            $job['status'] = 'cancelled';
            $job['finished_at'] = now()->toIso8601String();
            if ($kind === 'clear') {
                $this->pruneEmptyDirs($this->rootPath());
                $job['message'] = '已停止。已经删掉 '.$job['ok'].' 个文件';
                unset($job['files']);
            } else {
                $job['message'] = '已停止。已经写好 '.$job['ok'].' 个文件';
                unset($job['urls']);
            }
            $this->putJob($job);
        }

        return $this->toPublic($job);
    }

    /** @return list<string> */
    public function collectUrls(string $scope = 'all'): array
    {
        $scope = array_key_exists($scope, self::scopes()) ? $scope : 'all';
        $urls = [];
        if (in_array($scope, ['all', 'index'], true)) {
            $this->pushUrl($urls, '/');
            $this->pushUrl($urls, '/latest');
            $this->pushUrl($urls, '/actors');
            $this->pushUrl($urls, '/topics');
            $this->pushUrl($urls, '/arts');
            $this->pushUrl($urls, '/website');
        }
        $this->collectGroup($urls, in_array($scope, ['all', 'type'], true) && Schema::hasTable('video_types'), function () use (&$urls) {
            VideoTypeModel::query()->active()->orderBy('id')->limit(200)->each(function (VideoTypeModel $type) use (&$urls) {
                $this->pushUrl($urls, $this->pathOf(vod_url('type', ['id' => $type->id]), '/type/'.$type->id));
            });
        });
        $this->collectGroup($urls, in_array($scope, ['all', 'detail'], true) && Schema::hasTable('videos'), function () use (&$urls) {
            VideoModel::query()->published()->orderByDesc('id')->limit(500)->each(function (VideoModel $video) use (&$urls) {
                $this->pushUrl($urls, $this->pathOf(vod_url('detail', ['id' => $video->id]), '/vod/'.$video->id));
            });
        });
        $this->collectGroup($urls, in_array($scope, ['all', 'actor'], true) && Schema::hasTable('actors'), function () use (&$urls) {
            $q = ActorModel::query()->orderByDesc('id')->limit(200);
            if (Schema::hasColumn('actors', 'status')) {
                $q->where('status', 1);
            }
            $q->each(function (ActorModel $actor) use (&$urls) {
                $this->pushUrl($urls, $this->pathOf(vod_url('actor', ['id' => $actor->id]), '/actor/'.$actor->id));
            });
        });
        $this->collectGroup($urls, in_array($scope, ['all', 'topic'], true) && Schema::hasTable('video_topics'), function () use (&$urls) {
            $q = VideoTopicModel::query()->orderByDesc('id')->limit(200);
            if (Schema::hasColumn('video_topics', 'status')) {
                $q->where('status', 1);
            }
            $q->each(function (VideoTopicModel $topic) use (&$urls) {
                $this->pushUrl($urls, $this->pathOf((string) $topic->url, '/topic/'.$topic->id));
            });
        });
        $this->collectGroup($urls, in_array($scope, ['all', 'tag'], true) && Schema::hasTable('video_tags'), function () use (&$urls) {
            $q = VideoTagModel::query()->orderByDesc('id')->limit(200);
            if (Schema::hasColumn('video_tags', 'status')) {
                $q->where('status', 1);
            }
            $q->each(function (VideoTagModel $tag) use (&$urls) {
                $this->pushUrl($urls, $this->pathOf((string) $tag->url, '/tag/'.$tag->id));
            });
        });
        $this->collectGroup($urls, in_array($scope, ['all', 'art'], true) && Schema::hasTable('video_arts'), function () use (&$urls) {
            $q = VideoArt::query()->orderByDesc('id')->limit(200);
            if (Schema::hasColumn('video_arts', 'status')) {
                $q->where('status', 1);
            }
            $q->each(function (VideoArt $art) use (&$urls) {
                $this->pushUrl($urls, $this->pathOf((string) $art->url, '/art/'.$art->id));
            });
        });
        $this->collectGroup($urls, in_array($scope, ['all', 'website'], true) && Schema::hasTable('video_websites'), function () use (&$urls) {
            $q = VideoWebsite::query()->orderByDesc('id')->limit(200);
            if (Schema::hasColumn('video_websites', 'status')) {
                $q->where('status', 1);
            }
            $q->each(function (VideoWebsite $website) use (&$urls) {
                $this->pushUrl($urls, $this->pathOf(vod_url('website', ['id' => $website->id]), '/website/'.$website->id));
            });
        });

        $out = [];
        foreach ($urls as $url) {
            $norm = $this->normalizeUrl((string) $url);
            if ($norm !== null) {
                $out[] = $norm;
            }
        }

        return array_values(array_unique($out));
    }

    /** @param  list<string>  $urls */
    protected function collectGroup(array &$urls, bool $run, callable $fn): void
    {
        if (! $run) {
            return;
        }
        try {
            $fn();
        } catch (\Throwable) {
        }
    }

    /** @return array{file:?string,error:?string} */
    protected function capture(string $urlPath): array
    {
        $urlPath = $this->normalizeUrl($urlPath);
        if ($urlPath === null) {
            return ['file' => null, 'error' => '地址无效'];
        }

        try {
            /** @var HttpKernel $kernel */
            $kernel = app(HttpKernel::class);
            $request = Request::create($urlPath, 'GET', [], [], [], [
                'HTTP_USER_AGENT' => 'Vod-DiskHtml/1.0',
                'HTTP_ACCEPT' => 'text/html',
                'HTTP_X_VOD_INTERNAL' => 'disk-html',
            ]);
            $response = $kernel->handle($request);
            $status = $response->getStatusCode();
            $html = $response->getContent();
            $kernel->terminate($request, $response);
            if ($status !== 200 || ! is_string($html) || $html === '') {
                return ['file' => null, 'error' => $urlPath.'（HTTP '.$status.'）'];
            }
            $file = $this->pathForUrl($this->rootPath(), $urlPath);
            File::ensureDirectoryExists(dirname($file));
            File::put($file, $html);

            return ['file' => $file, 'error' => null];
        } catch (\Throwable $e) {
            return ['file' => null, 'error' => $urlPath.'（'.$e->getMessage().'）'];
        }
    }

    protected function pathForUrl(string $root, string $urlPath): string
    {
        $path = trim($urlPath, '/');
        if ($path === '') {
            return $root.DIRECTORY_SEPARATOR.'index.html';
        }
        $segments = explode('/', $path);

        return $root.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $segments).DIRECTORY_SEPARATOR.'index.html';
    }

    /** @param list<string> $urls */
    protected function pushUrl(array &$urls, string $url): void
    {
        if ($url === '' || $url === '#') {
            return;
        }
        $urls[] = $url;
    }

    protected function pathOf(string $url, string $fallback): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (! is_string($path) || $path === '') {
            return $fallback;
        }

        return $path;
    }

    protected function normalizeUrl(string $urlPath): ?string
    {
        $urlPath = trim($urlPath);
        if ($urlPath === '' || $urlPath === '#') {
            return null;
        }
        if (str_starts_with($urlPath, 'http://') || str_starts_with($urlPath, 'https://')) {
            $path = parse_url($urlPath, PHP_URL_PATH);
            $urlPath = is_string($path) && $path !== '' ? $path : '/';
        }
        $urlPath = '/'.ltrim($urlPath, '/');
        if ($urlPath === '//') {
            return '/';
        }

        return $urlPath;
    }

    /**
     * @param  array<string, mixed>  $job
     * @return array{status:string,kind:string,done:int,total:int,ok:int,fail:int,percent:int,current:string,message:string,file_count:int,errors:list<string>}
     */
    protected function stepClearJob(array $job, int $chunk): array
    {
        $chunk = max(1, min(80, $chunk));
        $files = $job['files'] ?? [];
        $end = min((int) $job['index'] + $chunk, (int) $job['total']);
        for ($i = (int) $job['index']; $i < $end; $i++) {
            $file = (string) ($files[$i] ?? '');
            $job['current'] = $file === '' ? '' : $this->relativeHtmlPath($file);
            $job['message'] = $file === '' ? '正在删…' : '正在删 '.$job['current'];
            if ($file === '' || ! is_file($file) || File::delete($file)) {
                $job['ok']++;
            } else {
                $job['fail']++;
                $job['errors'][] = $job['current'];
            }
            $job['index'] = $i + 1;
        }

        if ((int) $job['index'] >= (int) $job['total']) {
            $this->pruneEmptyDirs($this->rootPath());
            $job['status'] = 'done';
            $job['current'] = '';
            $job['finished_at'] = now()->toIso8601String();
            $job['message'] = '已删掉 '.$job['ok'].' 个文件';
            if ((int) $job['fail'] > 0) {
                $job['message'] .= '，'.$job['fail'].' 个没删掉';
            }
            unset($job['files']);
        }

        $this->putJob($job);

        return $this->toPublic($job);
    }

    protected function pruneEmptyDirs(string $root): void
    {
        if (! is_dir($root)) {
            return;
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            }
        }
    }

    protected function relativeHtmlPath(string $file): string
    {
        $root = rtrim(str_replace('\\', '/', $this->rootPath()), '/');
        $norm = str_replace('\\', '/', $file);
        if (str_starts_with($norm, $root.'/')) {
            return substr($norm, strlen($root) + 1);
        }

        return basename($file);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function busyPublic(string $wantKind): ?array
    {
        $current = $this->rawJob();
        if (($current['status'] ?? '') !== 'running') {
            return null;
        }
        $pub = $this->toPublic($current);
        if ((string) ($current['kind'] ?? 'build') !== $wantKind) {
            $pub['conflict'] = true;
            $pub['message'] = ($current['kind'] ?? 'build') === 'clear'
                ? '正在删除，请先停止'
                : '正在生成，请先停止';
        }

        return $pub;
    }

    /** @return array<string, mixed> */
    protected function emptyJob(): array
    {
        return [
            'status' => 'idle',
            'kind' => 'build',
            'urls' => [],
            'files' => [],
            'index' => 0,
            'total' => 0,
            'ok' => 0,
            'fail' => 0,
            'errors' => [],
            'current' => '',
            'message' => '',
            'started_at' => null,
            'finished_at' => null,
        ];
    }

    /** @return array<string, mixed> */
    protected function rawJob(): array
    {
        $job = Cache::get($this->jobKey());

        return is_array($job) ? array_merge($this->emptyJob(), $job) : $this->emptyJob();
    }

    /** @param array<string, mixed> $job */
    protected function putJob(array $job): void
    {
        Cache::put($this->jobKey(), $job, self::JOB_TTL);
    }

    /**
     * @param  array<string, mixed>  $job
     * @return array{status:string,kind:string,done:int,total:int,ok:int,fail:int,percent:int,current:string,message:string,file_count:int,errors:list<string>}
     */
    protected function toPublic(array $job): array
    {
        $total = (int) ($job['total'] ?? 0);
        $done = (int) ($job['index'] ?? 0);
        $kind = (string) ($job['kind'] ?? 'build');
        $status = (string) ($job['status'] ?? 'idle');
        $fileCount = ($status === 'running' && $kind === 'clear')
            ? max(0, $total - $done)
            : $this->fileCount();

        $out = [
            'status' => $status,
            'kind' => $kind,
            'done' => $done,
            'total' => $total,
            'ok' => (int) ($job['ok'] ?? 0),
            'fail' => (int) ($job['fail'] ?? 0),
            'percent' => $total > 0 ? (int) floor($done * 100 / $total) : 0,
            'current' => (string) ($job['current'] ?? ''),
            'message' => (string) ($job['message'] ?? ''),
            'file_count' => $fileCount,
            'errors' => array_values(array_slice($job['errors'] ?? [], 0, 10)),
        ];
        if (! empty($job['conflict'])) {
            $out['conflict'] = true;
        }

        return $out;
    }
}
