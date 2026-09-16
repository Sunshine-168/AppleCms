<?php

namespace App\Services\Video;

use App\Models\Video\ActorModel;
use App\Models\Video\CollectSourceModel;
use App\Models\Video\VideoArt;
use App\Models\Video\VideoCollectTemp;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoPlayerModel;
use App\Models\Video\VideoSourceModel;
use App\Services\Admin\Video\VideoService;
use App\Services\Collect\CollectIngestService;
use App\Services\Collect\MacResourceClient;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class SiteToolsService
{
    public function __construct(
        private readonly ImageLocalizeService $images,
        private readonly MacResourceClient $client,
        private readonly CollectIngestService $ingest,
    ) {}

    /** @return array{code:int,msg:string,data:array} */
    public function recycleList(array $params): array
    {
        $params['trash'] = '1';
        if (! isset($params['limit'])) {
            $params['limit'] = 20;
        }

        return app(VideoService::class)->getVideoLists($params);
    }

    public function recycleCount(): int
    {
        try {
            if (! Schema::hasColumn('videos', 'deleted_at')) {
                return 0;
            }

            return (int) VideoModel::query()->withoutGlobalScope('alive')->where('deleted_at', '>', 0)->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    public function restore(mixed $ids): array
    {
        $ids = $this->ids($ids);
        if ($ids === []) {
            return Result::fail('请先勾选影片');
        }
        if (! Schema::hasColumn('videos', 'deleted_at')) {
            return Result::fail('未启用回收站');
        }
        $n = VideoModel::query()->withoutGlobalScope('alive')->whereIn('id', $ids)->where('deleted_at', '>', 0)->update([
            'deleted_at' => 0,
            'updated_at' => time(),
        ]);

        return $n > 0 ? Result::success(['count' => $n], '已还原 '.$n.' 部') : Result::fail('记录不在回收站');
    }

    /** @return list<int> */
    public function recycleIds(): array
    {
        try {
            if (! Schema::hasColumn('videos', 'deleted_at')) {
                return [];
            }

            return VideoModel::query()->withoutGlobalScope('alive')->where('deleted_at', '>', 0)->pluck('id')->map(fn ($id) => (int) $id)->all();
        } catch (\Throwable) {
            return [];
        }
    }

    public function scanImages(int $limit = 80): array
    {
        $limit = max(1, min(200, $limit));
        $rows = VideoModel::query()->where('cover', '!=', '')->orderByDesc('id')->limit($limit)->get(['id', 'title', 'cover']);
        $remote = [];
        $broken = [];
        foreach ($rows as $row) {
            $cover = (string) $row->cover;
            if ($this->images->isRemote($cover)) {
                $remote[] = ['id' => $row->id, 'title' => $row->title, 'cover' => $cover];
            }
            if (! $this->images->probe($cover)) {
                $broken[] = ['id' => $row->id, 'title' => $row->title, 'cover' => $cover];
            }
        }

        return Result::success([
            'checked' => $rows->count(),
            'remote' => $remote,
            'broken' => $broken,
        ], '扫描完成');
    }

    public function localizeImages(int $limit = 40): array
    {
        $limit = max(1, min(100, $limit));
        $rows = VideoModel::query()->orderByDesc('id')->limit($limit * 4)->get(['id', 'cover']);
        $done = 0;
        foreach ($rows as $row) {
            if ($done >= $limit) {
                break;
            }
            $cover = (string) $row->cover;
            if (! $this->images->isRemote($cover)) {
                continue;
            }
            $local = $this->images->download($cover, (string) $row->id);
            if ($local !== $cover) {
                $row->cover = $local;
                $row->updated_at = time();
                $row->save();
                $done++;
            }
        }

        return Result::success(['count' => $done], '已本地化 '.$done.' 张封面');
    }

    public function quality(int $limit = 50): array
    {
        $emptyUrl = VideoModel::query()->whereDoesntHave('episodes')->count();
        $emptyPic = VideoModel::query()->where(function ($q) {
            $q->whereNull('cover')->orWhere('cover', '');
        })->count();
        $emptyContent = VideoModel::query()->where(function ($q) {
            $q->whereNull('description')->orWhere('description', '');
        })->count();
        $noActor = VideoModel::query()->whereDoesntHave('actors')->count();
        $dupTitles = VideoModel::query()->select('title')->groupBy('title')->havingRaw('COUNT(*) > 1')->pluck('title');
        $repeat = $dupTitles->count() === 0 ? 0 : VideoModel::query()->whereIn('title', $dupTitles)->count();
        $missingRows = [];
        if (Schema::hasColumn('videos', 'total') && Schema::hasTable('video_episodes')) {
            $missingRows = VideoModel::query()
                ->where('total', '>', 0)
                ->whereRaw('(select count(*) from video_episodes where video_episodes.video_id = videos.id) < videos.total')
                ->orderByDesc('id')
                ->limit($limit)
                ->get(['id', 'title', 'total'])
                ->map(fn ($row) => ['id' => $row->id, 'title' => $row->title, 'total' => $row->total])
                ->all();
        }

        return Result::success([
            'empty_url' => $emptyUrl,
            'empty_pic' => $emptyPic,
            'empty_content' => $emptyContent,
            'no_actor' => $noActor,
            'repeat' => $repeat,
            'missing_ep' => count($missingRows),
            'missing_ep_rows' => $missingRows,
        ], '体检完成');
    }

    public function replacePlayer(string $from, string $to, string $mode = 'rename'): array
    {
        $from = trim($from);
        $to = trim($to);
        if ($from === '') {
            return Result::fail('请填写原播放器标识');
        }
        $q = VideoSourceModel::query()->where(function ($w) use ($from) {
            $w->where('player', $from)->orWhere('name', $from);
        });
        $count = $q->count();
        if ($mode === 'disable') {
            $q->update(['status' => 0, 'updated_at' => time()]);

            return Result::success(['count' => $count], '已下线 '.$count.' 条线路');
        }
        if ($to === '') {
            return Result::fail('请填写目标播放器标识');
        }
        VideoSourceModel::query()->where(function ($w) use ($from) {
            $w->where('player', $from)->orWhere('name', $from);
        })->update(['player' => $to, 'updated_at' => time()]);
        if (VideoPlayerModel::query()->where('code', $to)->doesntExist()) {
            VideoPlayerModel::query()->create([
                'code' => $to,
                'name' => $to,
                'parse' => '',
                'sort' => 0,
                'status' => 1,
            ]);
        }

        return Result::success(['count' => $count], '已替换 '.$count.' 条线路');
    }

    public function scanAnnex(): array
    {
        $used = [];
        foreach (VideoModel::query()->where('cover', '!=', '')->pluck('cover') as $cover) {
            $this->collectLocalPath((string) $cover, $used);
        }
        if (Schema::hasTable('video_arts')) {
            foreach (VideoArt::query()->where('cover', '!=', '')->pluck('cover') as $cover) {
                $this->collectLocalPath((string) $cover, $used);
            }
        }
        if (Schema::hasTable('actors')) {
            foreach (ActorModel::query()->where('avatar', '!=', '')->pluck('avatar') as $cover) {
                $this->collectLocalPath((string) $cover, $used);
            }
        }
        $dir = public_path('uploads/vod');
        $orphan = [];
        if (is_dir($dir)) {
            foreach (File::files($dir) as $file) {
                $rel = '/uploads/vod/'.$file->getFilename();
                if (! isset($used[$rel])) {
                    $orphan[] = $rel;
                }
            }
        }

        return Result::success([
            'used' => count($used),
            'orphan' => array_slice($orphan, 0, 200),
            'orphan_n' => count($orphan),
        ], '附件扫描完成');
    }

    public function deleteOrphans(): array
    {
        $scan = $this->scanAnnex();
        $n = 0;
        foreach ((array) ($scan['data']['orphan'] ?? []) as $rel) {
            $path = public_path(ltrim((string) $rel, '/'));
            if (is_file($path) && @unlink($path)) {
                $n++;
            }
        }

        return Result::success(['count' => $n], '已删除 '.$n.' 个未引用文件');
    }

    public function probeHub(string $apiUrl): array
    {
        $parsed = $this->client->fetch($apiUrl, ['ac' => 'list', 'pg' => 1]);
        if (! ($parsed['ok'] ?? false)) {
            return Result::fail((string) ($parsed['msg'] ?? '探测失败'));
        }

        return Result::success([
            'types' => $parsed['types'] ?? [],
            'page' => $parsed['page'] ?? [],
            'sample' => array_slice($parsed['list'] ?? [], 0, 8),
        ], '探测成功');
    }

    public function promoteTemp(int $id): array
    {
        $row = VideoCollectTemp::query()->find($id);
        if (! $row) {
            return Result::fail('临时记录不存在');
        }
        if ((int) $row->status === 1) {
            return Result::fail('已转入过');
        }
        $payload = json_decode((string) $row->payload, true);
        if (! is_array($payload)) {
            return Result::fail('临时数据损坏');
        }
        $source = CollectSourceModel::query()->find((int) $row->collect_source_id);
        $res = $source
            ? $this->ingest->ingestDirect($source, $payload)
            : $this->ingest->ingestRemote($payload, false);
        $ok = in_array((string) ($res['action'] ?? ''), ['created', 'updated'], true);
        $row->status = $ok ? 1 : 0;
        $row->msg = (string) ($res['msg'] ?? $res['action'] ?? 'ok');
        $row->save();
        if (! $ok) {
            return Result::fail((string) ($res['msg'] ?? '转入失败'), is_array($res) ? $res : []);
        }

        return Result::success($res, '已转入正式库');
    }

    /** @param  mixed  $ids */
    private function ids(mixed $ids): array
    {
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        if (! is_array($ids)) {
            $ids = [$ids];
        }

        return array_values(array_unique(array_filter(array_map('intval', $ids))));
    }

    /** @param  array<string, true>  $used */
    private function collectLocalPath(string $url, array &$used): void
    {
        if ($this->images->isRemote($url)) {
            return;
        }
        $path = parse_url($url, PHP_URL_PATH) ?: $url;
        $path = '/'.ltrim((string) $path, '/');
        $used[$path] = true;
    }
}
