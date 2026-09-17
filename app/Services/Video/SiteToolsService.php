<?php

namespace App\Services\Video;

use App\Models\Video\ActorModel;
use App\Models\Video\CollectSourceModel;
use App\Models\Video\VideoArt;
use App\Models\Video\VideoCollectTemp;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoPlayerModel;
use App\Models\Video\VideoSourceModel;
use App\Models\Video\VideoUnion;
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

    public function imagesPage(): array
    {
        $watermark = '';
        $picLocal = false;
        try {
            $settings = app(VideoSettingService::class);
            $picLocal = (int) $settings->get('collect_pic_local', '0') === 1;
            $watermark = trim((string) $settings->get('watermark_text', ''));
        } catch (\Throwable) {
        }

        return array_merge($this->imageStock(), [
            'title' => admin_t('page.tool_images'),
            'pic_local' => $picLocal,
            'watermark' => $watermark,
        ]);
    }

    public function scanImages(int $limit = 80): array
    {
        $limit = max(1, min(200, $limit));
        $rows = VideoModel::query()->where('cover', '!=', '')->orderByDesc('id')->limit($limit)->get(['id', 'title', 'cover']);
        $remote = [];
        $missing = [];
        foreach ($rows as $row) {
            $cover = trim((string) $row->cover);
            $ok = $this->images->probe($cover);
            $item = $this->imageItem($row, $cover, $ok);
            if ($this->images->isRemote($cover)) {
                $remote[] = $item;
            } elseif (! $ok) {
                $missing[] = $item;
            }
        }
        $broken = 0;
        foreach ($remote as $item) {
            if (! ($item['ok'] ?? false)) {
                $broken++;
            }
        }
        $broken += count($missing);
        $checked = $rows->count();

        return Result::success(array_merge($this->imageStock(), [
            'checked' => $checked,
            'remote' => array_slice($remote, 0, 60),
            'missing' => array_slice($missing, 0, 40),
            'broken_n' => $broken,
            'limit' => $limit,
        ]), $this->scanImagesMsg($checked, count($remote), $broken));
    }

    public function localizeImages(int $limit = 40): array
    {
        $limit = max(1, min(100, $limit));
        $rows = VideoModel::query()->orderByDesc('id')->limit($limit * 4)->get(['id', 'cover']);
        $tried = 0;
        $done = 0;
        foreach ($rows as $row) {
            if ($done >= $limit) {
                break;
            }
            $cover = (string) $row->cover;
            if (! $this->images->isRemote($cover)) {
                continue;
            }
            $tried++;
            $local = $this->images->download($cover, (string) $row->id);
            if ($local !== $cover) {
                $row->cover = $local;
                $row->updated_at = time();
                $row->save();
                $done++;
            }
        }
        $stock = $this->imageStock();

        return Result::success(array_merge($stock, [
            'count' => $done,
            'tried' => $tried,
            'failed' => max(0, $tried - $done),
        ]), $this->localizeImagesMsg($tried, $done, (int) $stock['remote_n']));
    }

    public function quality(int $limit = 50): array
    {
        unset($limit);
        $counts = app(VideoService::class)->qualityCounts();
        $problems = (int) ($counts['empty_url'] ?? 0)
            + (int) ($counts['empty_pic'] ?? 0)
            + (int) ($counts['empty_content'] ?? 0)
            + (int) ($counts['no_actor'] ?? 0)
            + (int) ($counts['repeat'] ?? 0)
            + (int) ($counts['missing_ep'] ?? 0);
        $all = (int) ($counts['all'] ?? 0);
        if ($all < 1) {
            return Result::success($counts, '片库还是空的');
        }
        if ($problems < 1) {
            return Result::success($counts, '这几项都齐了');
        }

        return Result::success($counts, '有缺项，点卡片看片子。不会改数据。');
    }

    public function qualityPage(): array
    {
        $counts = app(VideoService::class)->qualityCounts();
        $order = ['empty_url', 'empty_pic', 'empty_content', 'no_actor', 'repeat', 'missing_ep'];
        $focus = '';
        foreach ($order as $key) {
            if ((int) ($counts[$key] ?? 0) > 0) {
                $focus = $key;
                break;
            }
        }
        if ($focus === '') {
            $focus = 'empty_url';
        }

        return [
            'title' => admin_t('page.tool_quality'),
            'counts' => $counts,
            'focus' => $focus,
        ];
    }

    public function playersPage(): array
    {
        $stock = $this->playerStock();

        return array_merge($stock, [
            'title' => admin_t('page.tool_players'),
        ]);
    }

    public function replacePlayer(string $from, string $to, string $mode = 'rename'): array
    {
        $from = trim($from);
        $to = trim($to);
        if ($from === '') {
            return Result::fail('请填写原标识，或点下面线路上的标识');
        }
        if (! Schema::hasTable('video_sources')) {
            return Result::fail('还没有线路');
        }
        $q = $this->sourcePlayerQuery($from);
        $count = $q->count();
        if ($count < 1) {
            return Result::fail('没有线路用「'.$from.'」这个标识', $this->playerStock());
        }
        if ($mode === 'disable') {
            $q->update(['status' => 0, 'updated_at' => time()]);

            return Result::success(array_merge($this->playerStock(), [
                'count' => $count,
                'from' => $from,
                'to' => '',
                'mode' => 'disable',
                'created' => false,
            ]), '已下线 '.$count.' 条线路（标识 '.$from.'），前台不播这些线');
        }
        if ($to === '') {
            return Result::fail('请填写要换成的播放器标识');
        }
        if ($from === $to) {
            return Result::fail('原标识和目标一样，不用换');
        }
        $this->sourcePlayerQuery($from)->update(['player' => $to, 'updated_at' => time()]);
        $created = false;
        if (Schema::hasTable('video_players') && VideoPlayerModel::query()->where('code', $to)->doesntExist()) {
            $row = [
                'code' => $to,
                'name' => $to,
                'parse' => '',
                'sort' => 0,
                'status' => 1,
            ];
            if (Schema::hasColumn('video_players', 'engine')) {
                $row['engine'] = VideoPlayerModel::inferEngine($to, '');
            }
            VideoPlayerModel::query()->create($row);
            $created = true;
        }
        $msg = '已把 '.$count.' 条线路从 '.$from.' 换成 '.$to;
        if ($created) {
            $msg .= '，并加了播放器 '.$to.'，请去核内核';
        }

        return Result::success(array_merge($this->playerStock(), [
            'count' => $count,
            'from' => $from,
            'to' => $to,
            'mode' => 'rename',
            'created' => $created,
        ]), $msg);
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

    /** @return array{unions: list<array<string, mixed>>, union_count: int, collect_count: int, pending_count: int, prefill: string} */
    public function hubPage(): array
    {
        $unions = [];
        $collectCount = 0;
        try {
            if (Schema::hasTable('collect_sources')) {
                $collectCount = (int) CollectSourceModel::query()->count();
            }
        } catch (\Throwable) {
            $collectCount = 0;
        }
        try {
            if (Schema::hasTable('video_unions')) {
                $unions = VideoUnion::query()->orderByDesc('sort')->orderByDesc('id')->limit(40)->get()->map(function ($row) {
                    return $row->toArray();
                })->all();
            }
        } catch (\Throwable) {
            $unions = [];
        }
        $unions = $this->decorateHubUnions($unions);
        $pending = 0;
        foreach ($unions as $row) {
            if ((int) ($row['adopted'] ?? 0) !== 1) {
                $pending++;
            }
        }

        return [
            'unions' => $unions,
            'union_count' => count($unions),
            'collect_count' => $collectCount,
            'pending_count' => $pending,
            'prefill' => trim((string) request()->query('url', '')),
        ];
    }

    public function probeHub(string $apiUrl): array
    {
        $apiUrl = $this->normalizeHubUrl($apiUrl);
        if ($apiUrl === '') {
            return Result::fail('请先粘贴接口地址');
        }

        $parsed = $this->client->fetch($apiUrl, ['ac' => 'list', 'pg' => 1]);
        if (! ($parsed['ok'] ?? false)) {
            return Result::fail($this->friendlyProbeMsg((string) ($parsed['msg'] ?? '探测失败')));
        }

        $types = is_array($parsed['types'] ?? null) ? $parsed['types'] : [];
        $list = is_array($parsed['list'] ?? null) ? $parsed['list'] : [];
        $page = is_array($parsed['page'] ?? null) ? $parsed['page'] : [];
        $typeNames = [];
        foreach ($types as $ty) {
            $name = trim((string) ($ty['type_name'] ?? ''));
            if ($name !== '' && ! in_array($name, $typeNames, true)) {
                $typeNames[] = $name;
            }
        }
        $titles = [];
        foreach (array_slice($list, 0, 8) as $row) {
            $name = trim((string) ($row['vod_name'] ?? ''));
            if ($name !== '') {
                $titles[] = $name;
            }
        }
        $matched = $this->matchHubUrl($apiUrl);
        $host = (string) (parse_url($apiUrl, PHP_URL_HOST) ?: '');

        return Result::success([
            'api_url' => $apiUrl,
            'host' => $host,
            'format' => (string) ($parsed['format'] ?? ''),
            'type_count' => count($types),
            'record_count' => (int) ($page['recordcount'] ?? 0),
            'type_names' => array_slice($typeNames, 0, 16),
            'sample_titles' => $titles,
            'union_id' => $matched['union_id'],
            'collect_id' => $matched['collect_id'],
        ], '接口可用');
    }

    private function normalizeHubUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (preg_match('#^(javascript|data|vbscript):#i', $url)) {
            return '';
        }
        if (! preg_match('#^[a-z][a-z0-9+.-]*://#i', $url)) {
            $url = 'https://'.ltrim($url, '/');
        }

        return $url;
    }

    private function friendlyProbeMsg(string $msg): string
    {
        if ($msg === '' || str_contains($msg, '采集地址无效')) {
            return '地址不对。需要 http 或 https 开头，一般是 …/api.php/provide/vod/';
        }
        if (str_contains($msg, '接口无返回')) {
            return '连不上这个地址。检查网址、https，或资源站是否已关闭。';
        }
        if (str_contains($msg, '无法解析') || str_contains($msg, '解析失败')) {
            return '这不是苹果 CMS 兼容接口，或返回不是 JSON/XML。';
        }

        return $msg;
    }

    /** @param  list<array<string, mixed>>  $rows */
    private function decorateHubUnions(array $rows): array
    {
        $map = [];
        try {
            if (Schema::hasTable('collect_sources')) {
                foreach (CollectSourceModel::query()->get(['id', 'api_url']) as $src) {
                    $normalized = $this->normalizeHubUrl((string) $src->api_url);
                    if ($normalized === '') {
                        continue;
                    }
                    $id = (int) $src->id;
                    $map[$normalized] = $id;
                    $map[rtrim($normalized, '/')] = $id;
                }
            }
        } catch (\Throwable) {
        }
        foreach ($rows as &$row) {
            $url = $this->normalizeHubUrl((string) ($row['api_url'] ?? ''));
            $collectId = 0;
            if ($url !== '') {
                $collectId = (int) ($map[$url] ?? $map[rtrim($url, '/')] ?? 0);
            }
            $row['api_url'] = $url;
            $row['host'] = $url !== '' ? (string) (parse_url($url, PHP_URL_HOST) ?: '') : '';
            $row['collect_id'] = $collectId;
            $row['adopted'] = $collectId > 0 ? 1 : 0;
        }
        unset($row);

        return $rows;
    }

    /** @return array{union_id:int, collect_id:int} */
    private function matchHubUrl(string $url): array
    {
        $alts = array_values(array_unique(array_filter([
            $url,
            rtrim($url, '/'),
            rtrim($url, '/').'/',
        ])));
        $unionId = 0;
        $collectId = 0;
        try {
            if ($alts !== [] && Schema::hasTable('video_unions')) {
                $unionId = (int) (VideoUnion::query()->whereIn('api_url', $alts)->orderBy('id')->value('id') ?? 0);
            }
        } catch (\Throwable) {
        }
        try {
            if ($alts !== [] && Schema::hasTable('collect_sources')) {
                $collectId = (int) (CollectSourceModel::query()->whereIn('api_url', $alts)->orderBy('id')->value('id') ?? 0);
            }
        } catch (\Throwable) {
        }

        return ['union_id' => $unionId, 'collect_id' => $collectId];
    }

    public function promoteTemps(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return Result::fail('请先勾选要转入的片子');
        }
        if (count($ids) === 1) {
            return $this->promoteTemp($ids[0]);
        }
        $ok = 0;
        $fail = 0;
        $skip = 0;
        $last = '';
        foreach ($ids as $id) {
            $row = VideoCollectTemp::query()->find($id);
            if ($row && (int) $row->status === 1) {
                $skip++;
                continue;
            }
            $res = $this->promoteTemp($id);
            if ((int) ($res['code'] ?? 1) === 0) {
                $ok++;
            } else {
                $fail++;
                $last = (string) ($res['msg'] ?? '转入失败');
            }
        }
        if ($ok === 0 && $fail === 0) {
            return Result::fail('勾选的片子都已经入库了');
        }
        if ($ok === 0) {
            return Result::fail($last !== '' ? $last : '转入失败');
        }

        return Result::success(
            ['ok' => $ok, 'fail' => $fail, 'skip' => $skip],
            $fail > 0 ? ('已转入 '.$ok.' 部，'.$fail.' 部没进去') : ('已转入 '.$ok.' 部')
        );
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

    /** @return array{total: int, remote_n: int, local_n: int, empty_n: int} */
    private function imageStock(): array
    {
        $zero = ['total' => 0, 'remote_n' => 0, 'local_n' => 0, 'empty_n' => 0];
        try {
            if (! Schema::hasTable('videos')) {
                return $zero;
            }
            $total = (int) VideoModel::query()->count();
            $remote = (int) VideoModel::query()->where(function ($q) {
                $q->where('cover', 'like', 'http://%')->orWhere('cover', 'like', 'https://%');
            })->count();
            $empty = (int) VideoModel::query()->where(function ($q) {
                $q->whereNull('cover')->orWhere('cover', '');
            })->count();

            return [
                'total' => $total,
                'remote_n' => $remote,
                'empty_n' => $empty,
                'local_n' => max(0, $total - $remote - $empty),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @param  object{id?: mixed, title?: mixed}  $row */
    private function imageItem(object $row, string $cover, bool $ok): array
    {
        return [
            'id' => (int) ($row->id ?? 0),
            'title' => (string) ($row->title ?? ''),
            'cover' => $cover,
            'host' => (string) (parse_url($cover, PHP_URL_HOST) ?: ''),
            'ok' => $ok,
        ];
    }

    private function scanImagesMsg(int $checked, int $remote, int $broken): string
    {
        if ($checked < 1) {
            return '最近没有带封面的片子';
        }
        if ($remote < 1 && $broken < 1) {
            return '看了 '.$checked.' 部，封面都在本站、也能打开';
        }
        $bits = [];
        if ($remote > 0) {
            $bits[] = $remote.' 张还在外站';
        }
        if ($broken > 0) {
            $bits[] = $broken.' 张打不开';
        }

        return '看了 '.$checked.' 部：'.implode('，', $bits);
    }

    private function localizeImagesMsg(int $tried, int $done, int $remaining): string
    {
        if ($tried < 1) {
            return '这批片子没有外站封面';
        }
        if ($done < 1) {
            return '试了 '.$tried.' 张，一张都没下下来。资源站可能挂了';
        }
        $msg = '已下载 '.$done.' 张到本站';
        if ($remaining > 0) {
            $msg .= '，还剩 '.$remaining.' 张在外站';
        }

        return $msg;
    }

    /**
     * @return array{players: list<array<string, mixed>>, usages: list<array<string, mixed>>, source_n: int, unknown_n: int}
     */
    private function playerStock(): array
    {
        $players = $this->playerCatalog();
        $codes = [];
        foreach ($players as $row) {
            $code = (string) ($row['code'] ?? '');
            if ($code !== '') {
                $codes[$code] = $row;
            }
        }
        $usages = [];
        $sourceN = 0;
        $unknownN = 0;
        try {
            if (Schema::hasTable('video_sources')) {
                $q = VideoSourceModel::query()->select('player')->selectRaw('COUNT(*) as c');
                if (Schema::hasColumn('video_sources', 'status')) {
                    $q->selectRaw('SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as on_n');
                }
                $groups = $q->groupBy('player')->orderByDesc('c')->limit(40)->get();
                foreach ($groups as $row) {
                    $code = trim((string) ($row->player ?? ''));
                    $count = (int) ($row->c ?? 0);
                    $sourceN += $count;
                    $known = $code !== '' && isset($codes[$code]);
                    if ($code !== '' && ! $known) {
                        $unknownN += $count;
                    }
                    $usages[] = [
                        'code' => $code,
                        'count' => $count,
                        'on_n' => (int) ($row->on_n ?? $count),
                        'known' => $known,
                        'name' => $known ? (string) ($codes[$code]['name'] ?? $code) : '',
                        'engine' => $known ? (string) ($codes[$code]['engine'] ?? '') : '',
                    ];
                }
            }
        } catch (\Throwable) {
            $usages = [];
            $sourceN = 0;
            $unknownN = 0;
        }

        return [
            'players' => $players,
            'usages' => $usages,
            'source_n' => $sourceN,
            'unknown_n' => $unknownN,
        ];
    }

    /** @return list<array{id:int,code:string,name:string,engine:string,engine_code:string,status:int}> */
    private function playerCatalog(): array
    {
        try {
            if (! Schema::hasTable('video_players')) {
                return [];
            }

            return VideoPlayerModel::query()->orderByDesc('sort')->orderBy('id')->get()->map(function ($row) {
                $engine = 'artplayer';
                try {
                    $engine = VideoPlayerModel::resolveEngine($row);
                } catch (\Throwable) {
                    $engine = VideoPlayerModel::inferEngine((string) ($row->code ?? ''), (string) ($row->parse ?? ''));
                }

                return [
                    'id' => (int) $row->id,
                    'code' => (string) ($row->code ?? ''),
                    'name' => (string) ($row->name ?? ''),
                    'engine' => VideoPlayerModel::engineLabel($engine),
                    'engine_code' => $engine,
                    'status' => (int) ($row->status ?? 1),
                ];
            })->all();
        } catch (\Throwable) {
            return [];
        }
    }

    private function sourcePlayerQuery(string $from)
    {
        return VideoSourceModel::query()->where(function ($w) use ($from) {
            $w->where('player', $from)->orWhere('name', $from);
        });
    }
}
