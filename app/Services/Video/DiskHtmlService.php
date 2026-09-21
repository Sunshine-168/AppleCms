<?php

namespace App\Services\Video;

use App\Models\Video\ActorModel;
use App\Models\Video\VideoArt;
use App\Models\Video\VideoArtTag;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoRole;
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

    public const DETAIL_CAP = 2000;

    public const LIST_CAP = 200;

    public const PICK_CAP = 300;

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
            'art_type' => '文章分类',
            'art_tag' => '文章标签',
            'role' => '角色',
            'website' => '网址',
            'vod_day' => '当天视频',
            'art_day' => '当天文章',
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
    /**
     * @param  array<string, mixed>  $filter
     * @return array{code:int,msg:string,data:array<string,mixed>}
     */
    public function buildOnce(string $scope = 'all', array $filter = []): array
    {
        File::ensureDirectoryExists($this->rootPath());
        $urls = $this->collectUrls($scope, $filter);
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

        $msg = admin_t('ui.disk_wrote', ['ok' => $ok]);
        if ($fail > 0) {
            $msg .= admin_t('ui.disk_fail_n', ['fail' => $fail]);
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
    /**
     * @param  array<string, mixed>  $filter  ids[], type_ids[], when=all|today|missing, extra='index'|''
     * @return array{status:string,kind:string,done:int,total:int,ok:int,fail:int,percent:int,current:string,message:string,file_count:int,errors:list<string>,conflict?:bool}
     */
    public function startJob(string $scope = 'all', array $filter = []): array
    {
        $busy = $this->busyPublic('build');
        if ($busy !== null) {
            return $busy;
        }

        $urls = $this->collectUrls($scope, $filter);
        $job = $this->emptyJob();
        $job['kind'] = 'build';
        $job['status'] = $urls === [] ? 'done' : 'running';
        $job['urls'] = $urls;
        $job['total'] = count($urls);
        $job['current'] = $urls[0] ?? '';
        $job['started_at'] = now()->toIso8601String();
        if ($urls === []) {
            $job['finished_at'] = now()->toIso8601String();
            $job['message'] = admin_t('ui.disk_no_pages');
        } else {
            $job['message'] = admin_t('ui.disk_ready_write', ['n' => $job['total']]);
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
            $job['message'] = admin_t('ui.disk_no_files');
        } else {
            $job['message'] = admin_t('ui.disk_ready_del', ['n' => $job['total']]);
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
            $job['message'] = $url === '' ? admin_t('ui.disk_writing') : admin_t('ui.disk_writing_url', ['url' => $url]);
            $result = $url === '' ? ['file' => null, 'error' => admin_t('ui.disk_empty_url')] : $this->capture($url);
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
            $job['message'] = admin_t('ui.disk_wrote_ok', ['ok' => $job['ok']]);
            if ((int) $job['fail'] > 0) {
                $shown = array_slice($job['errors'] ?? [], 0, 5);
                $job['message'] .= admin_t('ui.disk_fail_n', ['fail' => $job['fail']]);
                if ($shown !== []) {
                    $job['message'] .= admin_t('ui.disk_fail_list', ['list' => implode('、', $shown)]);
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
                $job['message'] = admin_t('ui.disk_stopped_del', ['ok' => $job['ok']]);
                unset($job['files']);
            } else {
                $job['message'] = admin_t('ui.disk_stopped_write', ['ok' => $job['ok']]);
                unset($job['urls']);
            }
            $this->putJob($job);
        }

        return $this->toPublic($job);
    }

    /**
     * @param  array<string, mixed>  $filter
     * @return list<string>
     */
    public function collectUrls(string $scope = 'all', array $filter = []): array
    {
        $filter = $this->normalizeFilter($filter);
        $scope = array_key_exists($scope, self::scopes()) ? $scope : 'all';
        $urls = [];

        if ($filter['extra'] === 'index') {
            $this->collectIndexOnly($urls, $scope);

            return $this->uniqueUrls($urls);
        }

        $all = $scope === 'all';
        $vodDay = $scope === 'vod_day';
        $artDay = $scope === 'art_day';
        $todayFilter = array_merge($filter, ['when' => 'today', 'ids' => [], 'type_ids' => []]);

        if ($all || $scope === 'index') {
            $this->pushIndexUrls($urls);
        }
        if ($all || $scope === 'type' || $vodDay) {
            $this->collectVodTypeUrls($urls, $vodDay ? $todayFilter : $filter);
        }
        if ($all || $scope === 'detail' || $vodDay) {
            $this->collectVodDetailUrls($urls, $vodDay ? $todayFilter : $filter);
        }
        if ($all || $scope === 'art_type' || $artDay) {
            $this->collectArtTypeUrls($urls, $artDay ? $todayFilter : $filter);
        }
        if ($all || $scope === 'art' || $artDay) {
            $this->collectArtDetailUrls($urls, $artDay ? $todayFilter : $filter);
        }
        if ($all || $scope === 'art' || $scope === 'art_tag' || $artDay) {
            $this->collectArtTagUrls($urls, $artDay && $scope !== 'art_tag' ? $todayFilter : $filter);
        }
        if ($all || $scope === 'actor') {
            $this->collectActorUrls($urls, $filter);
        }
        if ($all || $scope === 'topic') {
            $this->collectTopicUrls($urls, $filter);
        }
        if ($all || $scope === 'role') {
            $this->collectRoleUrls($urls, $filter);
        }
        if ($all || $scope === 'tag') {
            $this->collectTagUrls($urls, $filter);
        }
        if ($all || $scope === 'website') {
            $this->collectWebsiteUrls($urls, $filter);
        }

        $out = $this->uniqueUrls($urls);
        if ($filter['when'] === 'missing') {
            $out = $this->filterMissing($out);
        }

        return $out;
    }

    /**
     * @return array{
     *     vodTypes: list<array{id:int,name:string,parent_id:int,depth:int}>,
     *     artTypes: list<array{id:int,name:string,parent_id:int,depth:int}>,
     *     topics: list<array{id:int,name:string}>,
     *     actors: list<array{id:int,name:string}>,
     *     roles: list<array{id:int,name:string}>,
     *     hasArts: bool,
     *     detailCap: int,
     *     listCap: int
     * }
     */
    public function optCatalog(): array
    {
        $hasArts = false;
        try {
            $hasArts = Schema::hasTable('video_arts');
        } catch (\Throwable) {
            $hasArts = false;
        }

        return [
            'vodTypes' => $this->typeCatalog(1),
            'artTypes' => $hasArts ? $this->typeCatalog(2) : [],
            'topics' => $this->pickNameRows('video_topics', VideoTopicModel::class, self::PICK_CAP),
            'actors' => $this->pickNameRows('actors', ActorModel::class, self::PICK_CAP),
            'roles' => $this->pickNameRows('video_roles', VideoRole::class, self::PICK_CAP),
            'hasArts' => $hasArts,
            'detailCap' => self::DETAIL_CAP,
            'listCap' => self::LIST_CAP,
        ];
    }

    /**
     * @param  array<string, mixed>  $filter
     * @return array{ids: list<int>, type_ids: list<int>, when: string, extra: string}
     */
    protected function normalizeFilter(array $filter): array
    {
        $when = strtolower((string) ($filter['when'] ?? 'all'));
        if (! in_array($when, ['all', 'today', 'missing'], true)) {
            $when = 'all';
        }

        return [
            'ids' => $this->intIds($filter['ids'] ?? [], self::PICK_CAP),
            'type_ids' => $this->intIds($filter['type_ids'] ?? [], self::PICK_CAP),
            'when' => $when,
            'extra' => trim((string) ($filter['extra'] ?? '')),
        ];
    }

    /** @return list<int> */
    protected function intIds(mixed $raw, int $cap): array
    {
        if (is_string($raw) || is_numeric($raw)) {
            $raw = preg_split('/[,\s]+/', trim((string) $raw)) ?: [];
        }
        if (! is_array($raw)) {
            return [];
        }
        $out = [];
        foreach ($raw as $item) {
            if (is_array($item)) {
                continue;
            }
            $n = (int) $item;
            if ($n <= 0) {
                continue;
            }
            $out[] = $n;
            if (count($out) >= $cap) {
                break;
            }
        }

        return array_values(array_unique($out));
    }

    /** @param list<string> $urls */
    protected function collectIndexOnly(array &$urls, string $scope): void
    {
        if ($scope === 'topic') {
            $this->pushUrl($urls, '/topics');

            return;
        }
        if ($scope === 'actor') {
            $this->pushUrl($urls, '/actors');

            return;
        }
        if ($scope === 'role') {
            $this->pushUrl($urls, '/roles');

            return;
        }
        if (in_array($scope, ['art', 'art_type', 'art_tag'], true)) {
            $this->pushUrl($urls, '/arts');

            return;
        }
        $this->pushIndexUrls($urls);
    }

    /** @param list<string> $urls */
    protected function pushIndexUrls(array &$urls): void
    {
        $this->pushUrl($urls, '/');
        $this->pushUrl($urls, '/latest');
        $this->pushUrl($urls, '/actors');
        $this->pushUrl($urls, '/topics');
        $this->pushUrl($urls, '/arts');
        $this->pushUrl($urls, '/website');
    }

    /**
     * @param  list<string>  $urls
     * @param  array{ids: list<int>, type_ids: list<int>, when: string, extra: string}  $filter
     */
    protected function collectVodTypeUrls(array &$urls, array $filter): void
    {
        $this->collectGroup($urls, Schema::hasTable('video_types'), function () use (&$urls, $filter) {
            $q = VideoTypeModel::query()->active()->orderBy('id');
            $this->constrainMid($q, 1);
            if ($filter['ids'] !== []) {
                $q->whereIn('id', $filter['ids']);
            }
            $this->constrainWhen($q, 'video_types', $filter['when']);
            $q->limit(self::LIST_CAP)->each(function (VideoTypeModel $type) use (&$urls) {
                $this->pushUrl($urls, $this->pathOf(vod_url('type', ['id' => $type->id]), '/type/'.$type->id));
            });
        });
    }

    /**
     * @param  list<string>  $urls
     * @param  array{ids: list<int>, type_ids: list<int>, when: string, extra: string}  $filter
     */
    protected function collectArtTypeUrls(array &$urls, array $filter): void
    {
        $this->collectGroup($urls, Schema::hasTable('video_types') && Schema::hasTable('video_arts'), function () use (&$urls, $filter) {
            $q = VideoTypeModel::query()->active()->orderBy('id');
            $this->constrainMid($q, 2);
            if ($filter['ids'] !== []) {
                $q->whereIn('id', $filter['ids']);
            }
            $this->constrainWhen($q, 'video_types', $filter['when']);
            $q->limit(self::LIST_CAP)->each(function (VideoTypeModel $type) use (&$urls) {
                if ($type->kind() === 'link') {
                    return;
                }
                $this->pushUrl($urls, '/art/type/'.$type->id);
            });
        });
    }

    /**
     * @param  list<string>  $urls
     * @param  array{ids: list<int>, type_ids: list<int>, when: string, extra: string}  $filter
     */
    protected function collectVodDetailUrls(array &$urls, array $filter): void
    {
        $this->collectGroup($urls, Schema::hasTable('videos'), function () use (&$urls, $filter) {
            $q = VideoModel::query()->published()->orderByDesc('id');
            if ($filter['type_ids'] !== []) {
                $q->whereIn('type_id', $filter['type_ids']);
            }
            $this->constrainWhen($q, 'videos', $filter['when']);
            $q->limit(self::DETAIL_CAP)->each(function (VideoModel $video) use (&$urls) {
                $this->pushUrl($urls, $this->pathOf(vod_url('detail', ['id' => $video->id]), '/vod/'.$video->id));
            });
        });
    }

    /**
     * @param  list<string>  $urls
     * @param  array{ids: list<int>, type_ids: list<int>, when: string, extra: string}  $filter
     */
    protected function collectArtDetailUrls(array &$urls, array $filter): void
    {
        $this->collectGroup($urls, Schema::hasTable('video_arts'), function () use (&$urls, $filter) {
            $q = VideoArt::query()->listed()->orderByDesc('id');
            if ($filter['type_ids'] !== [] && Schema::hasColumn('video_arts', 'type_id')) {
                $q->whereIn('type_id', $filter['type_ids']);
            }
            $this->constrainWhen($q, 'video_arts', $filter['when']);
            $q->limit(self::DETAIL_CAP)->each(function (VideoArt $art) use (&$urls) {
                $this->pushUrl($urls, $this->pathOf((string) $art->url, '/art/'.$art->id));
            });
        });
    }

    /**
     * @param  list<string>  $urls
     * @param  array{ids: list<int>, type_ids: list<int>, when: string, extra: string}  $filter
     */
    protected function collectArtTagUrls(array &$urls, array $filter): void
    {
        $this->collectGroup($urls, Schema::hasTable('video_art_tags'), function () use (&$urls, $filter) {
            $q = VideoArtTag::query()->where('status', 1)->orderByDesc('id')->limit(self::LIST_CAP);
            $this->constrainWhen($q, 'video_art_tags', $filter['when']);
            $q->each(function (VideoArtTag $tag) use (&$urls) {
                $slug = trim((string) $tag->slug);
                $this->pushUrl($urls, $this->pathOf((string) $tag->url, '/art/tag/'.($slug !== '' ? $slug : $tag->id)));
            });
        });
    }

    /**
     * @param  list<string>  $urls
     * @param  array{ids: list<int>, type_ids: list<int>, when: string, extra: string}  $filter
     */
    protected function collectActorUrls(array &$urls, array $filter): void
    {
        $this->collectGroup($urls, Schema::hasTable('actors'), function () use (&$urls, $filter) {
            $q = ActorModel::query()->orderByDesc('id');
            if (Schema::hasColumn('actors', 'status')) {
                $q->where('status', 1);
            }
            if ($filter['ids'] !== []) {
                $q->whereIn('id', $filter['ids']);
            }
            $this->constrainWhen($q, 'actors', $filter['when']);
            $cap = $filter['ids'] !== [] ? self::PICK_CAP : self::LIST_CAP;
            $q->limit($cap)->each(function (ActorModel $actor) use (&$urls) {
                $this->pushUrl($urls, $this->pathOf(vod_url('actor', ['id' => $actor->id]), '/actor/'.$actor->id));
            });
        });
    }

    /**
     * @param  list<string>  $urls
     * @param  array{ids: list<int>, type_ids: list<int>, when: string, extra: string}  $filter
     */
    protected function collectTopicUrls(array &$urls, array $filter): void
    {
        $this->collectGroup($urls, Schema::hasTable('video_topics'), function () use (&$urls, $filter) {
            $q = VideoTopicModel::query()->orderByDesc('id');
            if (Schema::hasColumn('video_topics', 'status')) {
                $q->where('status', 1);
            }
            if ($filter['ids'] !== []) {
                $q->whereIn('id', $filter['ids']);
            }
            $this->constrainWhen($q, 'video_topics', $filter['when']);
            $cap = $filter['ids'] !== [] ? self::PICK_CAP : self::LIST_CAP;
            $q->limit($cap)->each(function (VideoTopicModel $topic) use (&$urls) {
                $this->pushUrl($urls, $this->pathOf((string) $topic->url, '/topic/'.$topic->id));
            });
        });
    }

    /**
     * @param  list<string>  $urls
     * @param  array{ids: list<int>, type_ids: list<int>, when: string, extra: string}  $filter
     */
    protected function collectRoleUrls(array &$urls, array $filter): void
    {
        $this->collectGroup($urls, Schema::hasTable('video_roles'), function () use (&$urls, $filter) {
            $q = VideoRole::query()->orderByDesc('id');
            if (Schema::hasColumn('video_roles', 'status')) {
                $q->where('status', 1);
            }
            if ($filter['ids'] !== []) {
                $q->whereIn('id', $filter['ids']);
            }
            $this->constrainWhen($q, 'video_roles', $filter['when']);
            $cap = $filter['ids'] !== [] ? self::PICK_CAP : self::LIST_CAP;
            $q->limit($cap)->each(function (VideoRole $role) use (&$urls) {
                $this->pushUrl($urls, $this->pathOf((string) $role->url, '/role/'.$role->id));
            });
        });
    }

    /**
     * @param  list<string>  $urls
     * @param  array{ids: list<int>, type_ids: list<int>, when: string, extra: string}  $filter
     */
    protected function collectTagUrls(array &$urls, array $filter): void
    {
        $this->collectGroup($urls, Schema::hasTable('video_tags'), function () use (&$urls, $filter) {
            $q = VideoTagModel::query()->orderByDesc('id')->limit(self::LIST_CAP);
            if (Schema::hasColumn('video_tags', 'status')) {
                $q->where('status', 1);
            }
            $this->constrainWhen($q, 'video_tags', $filter['when']);
            $q->each(function (VideoTagModel $tag) use (&$urls) {
                $this->pushUrl($urls, $this->pathOf((string) $tag->url, '/tag/'.$tag->id));
            });
        });
    }

    /**
     * @param  list<string>  $urls
     * @param  array{ids: list<int>, type_ids: list<int>, when: string, extra: string}  $filter
     */
    protected function collectWebsiteUrls(array &$urls, array $filter): void
    {
        $this->collectGroup($urls, Schema::hasTable('video_websites'), function () use (&$urls, $filter) {
            $q = VideoWebsite::query()->orderByDesc('id')->limit(self::LIST_CAP);
            if (Schema::hasColumn('video_websites', 'status')) {
                $q->where('status', 1);
            }
            $this->constrainWhen($q, 'video_websites', $filter['when']);
            $q->each(function (VideoWebsite $website) use (&$urls) {
                $this->pushUrl($urls, $this->pathOf(vod_url('website', ['id' => $website->id]), '/website/'.$website->id));
            });
        });
    }

    protected function constrainMid(\Illuminate\Database\Eloquent\Builder $query, int $mid): void
    {
        if (! Schema::hasColumn('video_types', 'mid')) {
            if ($mid !== 1) {
                $query->whereRaw('0 = 1');
            }

            return;
        }
        if ($mid === 1) {
            $query->where(function ($inner) {
                $inner->where('mid', 1)->orWhere('mid', 0)->orWhereNull('mid');
            });

            return;
        }
        $query->where('mid', $mid);
    }

    protected function constrainWhen(\Illuminate\Database\Eloquent\Builder $query, string $table, string $when): void
    {
        if ($when !== 'today') {
            return;
        }
        if (! Schema::hasColumn($table, 'updated_at')) {
            $query->whereRaw('0 = 1');

            return;
        }
        $query->where('updated_at', '>=', $this->todayStart());
    }

    protected function todayStart(): int
    {
        return (int) strtotime('today');
    }

    /** @param list<string> $urls @return list<string> */
    protected function uniqueUrls(array $urls): array
    {
        $out = [];
        foreach ($urls as $url) {
            $norm = $this->normalizeUrl((string) $url);
            if ($norm !== null) {
                $out[] = $norm;
            }
        }

        return array_values(array_unique($out));
    }

    /** @param list<string> $urls @return list<string> */
    protected function filterMissing(array $urls): array
    {
        $root = $this->rootPath();
        $out = [];
        foreach ($urls as $url) {
            $norm = $this->normalizeUrl((string) $url);
            if ($norm === null) {
                continue;
            }
            if (! is_file($this->pathForUrl($root, $norm))) {
                $out[] = $norm;
            }
        }

        return $out;
    }

    /**
     * @return list<array{id:int,name:string,parent_id:int,depth:int}>
     */
    protected function typeCatalog(int $mid): array
    {
        try {
            if (! Schema::hasTable('video_types')) {
                return [];
            }
            if ($mid !== 1 && ! Schema::hasColumn('video_types', 'mid')) {
                return [];
            }
            $q = VideoTypeModel::query()->active()->orderByDesc('sort')->orderBy('id');
            $this->constrainMid($q, $mid);
            $rows = $q->get(['id', 'name', 'parent_id'])->map(function (VideoTypeModel $row) {
                return [
                    'id' => (int) $row->id,
                    'name' => (string) $row->name,
                    'parent_id' => (int) ($row->parent_id ?? 0),
                ];
            })->all();

            return $this->treeTypes($rows);
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @param  list<array{id:int,name:string,parent_id:int}>  $rows
     * @return list<array{id:int,name:string,parent_id:int,depth:int}>
     */
    protected function treeTypes(array $rows): array
    {
        $byParent = [];
        foreach ($rows as $row) {
            $byParent[(int) $row['parent_id']][] = $row;
        }
        $out = [];
        $walk = function (int $pid, int $depth) use (&$walk, &$out, $byParent): void {
            foreach ($byParent[$pid] ?? [] as $row) {
                $row['depth'] = $depth;
                $out[] = $row;
                $walk((int) $row['id'], $depth + 1);
            }
        };
        $walk(0, 0);
        $seen = [];
        foreach ($out as $row) {
            $seen[(int) $row['id']] = true;
        }
        foreach ($rows as $row) {
            if (! isset($seen[(int) $row['id']])) {
                $row['depth'] = 0;
                $out[] = $row;
            }
        }

        return $out;
    }

    /**
     * @param  class-string  $class
     * @return list<array{id:int,name:string}>
     */
    protected function pickNameRows(string $table, string $class, int $limit): array
    {
        try {
            if (! Schema::hasTable($table)) {
                return [];
            }
            $q = $class::query()->orderByDesc('id')->limit($limit);
            if (Schema::hasColumn($table, 'status')) {
                $q->where('status', 1);
            }
            $out = [];
            foreach ($q->get(['id', 'name']) as $row) {
                $out[] = ['id' => (int) $row->id, 'name' => (string) $row->name];
            }

            return $out;
        } catch (\Throwable) {
            return [];
        }
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
            return ['file' => null, 'error' => admin_t('ui.disk_bad_url')];
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
            $job['message'] = $file === '' ? admin_t('ui.disk_deleting') : admin_t('ui.disk_deleting_path', ['path' => $job['current']]);
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
            $job['message'] = admin_t('ui.disk_deleted_ok', ['ok' => $job['ok']]);
            if ((int) $job['fail'] > 0) {
                $job['message'] .= admin_t('ui.disk_del_fail_n', ['fail' => $job['fail']]);
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
                ? admin_t('ui.disk_busy_clear')
                : admin_t('ui.disk_busy_build');
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
