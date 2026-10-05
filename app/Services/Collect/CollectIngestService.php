<?php

namespace App\Services\Collect;

use App\Models\Video\CollectSourceModel;
use App\Models\Video\VideoCollectLog;
use App\Models\Video\VideoEpisodeModel;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoPlayerModel;
use App\Models\Video\VideoSourceModel;
use App\Models\Video\VideoStatModel;
use App\Models\Video\VideoTypeModel;
use App\Services\Video\SynonymService;
use App\Support\AdminOpLog;
use App\Support\PlayLineName;
use App\Support\Utils\Result;
use App\Support\VideoMeta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Plugins\Manga\Services\MangaCollectService;

class CollectIngestService
{
    public function __construct(
        private readonly MacResourceClient $client,
        private readonly PlayUrlParser $parser,
        private readonly CollectAuditService $audit,
    ) {}

    public function fetchClasses(int $sourceId): array
    {
        $source = CollectSourceModel::query()->find($sourceId);
        if (! $source) {
            return Result::fail('采集源不存在');
        }
        if ($this->isMangaSource($source) && ! $this->mangaCollect()->ready()) {
            return Result::fail('漫画插件未启用');
        }
        $fetched = $this->client->fetch((string) $source->api_url, [
            'ac' => 'list',
            'pg' => 1,
        ], (string) ($source->api_type ?: 'auto'));
        if (! ($fetched['ok'] ?? false)) {
            return Result::fail((string) $fetched['msg']);
        }
        $bind = $this->bindMap($source);

        $types = [];
        foreach ($fetched['types'] as $ty) {
            $rid = (int) $ty['type_id'];
            $types[] = [
                'remote_id' => $rid,
                'name' => $ty['type_name'],
                'local_id' => (int) ($bind[(string) $rid] ?? 0),
            ];
        }

        return Result::success([
            'types' => $types,
            'page' => $fetched['page'],
            'local_types' => $this->isMangaSource($source)
                ? $this->mangaCollect()->localTypes()
                : VideoTypeModel::query()->orderByDesc('sort')->orderBy('id')->get(['id', 'name', 'parent_id']),
            'mid' => $this->isMangaSource($source) ? MangaCollectService::MID : 1,
        ]);
    }

    public function suggestBind(int $sourceId): array
    {
        $data = $this->fetchClasses($sourceId);
        if (($data['code'] ?? 1) !== 0) {
            return $data;
        }
        $payload = $data['data'] ?? [];
        $locals = collect($payload['local_types'] ?? []);
        $bind = [];
        foreach ($payload['types'] ?? [] as $type) {
            $name = trim((string) ($type['name'] ?? ''));
            $rid = (int) ($type['remote_id'] ?? 0);
            if ($rid < 1 || $name === '') {
                continue;
            }
            $hit = $locals->first(function ($row) use ($name) {
                $local = is_array($row) ? $row : $row->toArray();

                return mb_strtolower((string) ($local['name'] ?? '')) === mb_strtolower($name);
            });
            if ($hit) {
                $bind[(string) $rid] = (int) (is_array($hit) ? $hit['id'] : $hit->id);
            }
        }
        if ($bind === []) {
            return Result::fail('没有名称完全相同的分类可自动绑定');
        }

        return $this->saveBind($sourceId, $bind);
    }

    public function saveBind(int $sourceId, array $bind): array
    {
        $source = CollectSourceModel::query()->find($sourceId);
        if (! $source) {
            return Result::fail('采集源不存在');
        }
        $clean = [];
        foreach ($bind as $remote => $local) {
            $remote = (string) $remote;
            $local = (int) $local;
            if ($remote !== '' && $local > 0) {
                $clean[$remote] = $local;
            }
        }
        $source->bind_json = json_encode($clean, JSON_UNESCAPED_UNICODE);
        $source->updated_at = time();
        $source->save();

        return AdminOpLog::ifOk(Result::success(['bind' => $clean]), 'save', '绑定了采集源《'.trim((string) $source->name).'》的分类', [
            'module' => '采集源',
            'target_type' => 'collects',
            'target_id' => $sourceId,
        ]);
    }

    public function ingestRemote(array $item, bool $allowTemp = true): array
    {
        $now = time();
        $source = CollectSourceModel::query()->firstOrCreate(
            ['name' => '站外入库'],
            [
                'api_url' => 'inbound',
                'api_type' => 'json',
                'param' => '',
                'bind_json' => '{}',
                'status' => 1,
                'sort' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
        $localType = (int) ($item['local_type_id'] ?? $item['type_id'] ?? 0);
        if ($localType > 0) {
            $bind = $this->bindMap($source);
            $bind[(string) $localType] = $localType;
            $item['type_id'] = $localType;
            $source->bind_json = json_encode($bind, JSON_UNESCAPED_UNICODE);
            $source->updated_at = $now;
            $source->save();
        }

        return $this->upsert($source, $item, $allowTemp);
    }

    /** @param  array<string, mixed>  $item */
    public function ingestDirect(CollectSourceModel $source, array $item): array
    {
        return $this->upsert($source, $item, false);
    }

    public function run(int $sourceId, array $params = []): array
    {
        $source = CollectSourceModel::query()->find($sourceId);
        if (! $source) {
            return Result::fail('采集源不存在');
        }
        if ($this->isMangaSource($source) && ! $this->mangaCollect()->ready()) {
            return Result::fail('漫画插件未启用');
        }
        $start = max(1, (int) ($params['page'] ?? 1));
        $pages = max(1, (int) ($params['pages'] ?? 1));
        $hours = (int) ($params['hours'] ?? $params['h'] ?? 0);
        $ids = (string) ($params['ids'] ?? '');
        $remoteType = (string) ($params['t'] ?? '');
        $wd = app(SynonymService::class)->expand(trim((string) ($params['wd'] ?? $params['keyword'] ?? '')));

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $logs = [];
        $pageInfo = [];
        $pageCount = $start;
        $lastPage = $start;
        $failMsg = '';
        $sourcePk = (int) $source->id;
        CollectProgress::start($sourcePk, trim((string) $source->name));

        for ($page = $start; $page < $start + $pages; $page++) {
            CollectProgress::tick($sourcePk, [
                'page' => $page,
                'pages' => $pageCount,
                'msg' => '正在采集第 '.$page.' 页…',
                'action' => 'fetch',
                'title' => '',
            ], [
                'action' => 'fetch',
                'page' => $page,
                'msg' => '正在采集第 '.$page.' 页…',
            ]);
            $query = [
                'ac' => $ids !== '' ? 'detail' : ($this->isMangaSource($source) ? 'detail' : 'videolist'),
                'pg' => $page,
            ];
            if ($hours > 0) {
                $query['h'] = $hours;
            }
            if ($ids !== '') {
                $query['ids'] = $ids;
                $query['ac'] = 'detail';
            }
            if ($remoteType !== '') {
                $query['t'] = $remoteType;
            }
            if ($wd !== '') {
                $query['wd'] = $wd;
            }
            if (trim((string) $source->param) !== '') {
                parse_str(ltrim((string) $source->param, '&'), $extra);
                $query = array_merge($query, $extra);
            }

            $fetched = $this->client->fetch((string) $source->api_url, $query, (string) ($source->api_type ?: 'auto'));
            if (! ($fetched['ok'] ?? false)) {
                $failMsg = (string) $fetched['msg'];
                $this->persistCollectState($source, $page, $created, $updated, $failMsg, false);
                CollectProgress::finish($sourcePk, [
                    'ok' => 0,
                    'page' => $page,
                    'pages' => $pageCount,
                    'created' => $created,
                    'updated' => $updated,
                    'skipped' => $skipped,
                    'msg' => $failMsg,
                    'action' => 'fail',
                    'title' => '',
                ], [
                    'action' => 'fail',
                    'page' => $page,
                    'msg' => $failMsg,
                ]);

                return Result::fail($failMsg);
            }
            $lastPage = $page;
            $pageInfo = $fetched['page'] ?? [];
            $pageCount = (int) ($pageInfo['pagecount'] ?? $pageInfo['pageCount'] ?? $page);
            $listN = count($fetched['list'] ?? []);
            CollectProgress::tick($sourcePk, [
                'page' => $page,
                'pages' => $pageCount,
                'msg' => '第 '.$page.' 页获取到 '.$listN.' 条，正在入库…',
            ], [
                'action' => 'page',
                'page' => $page,
                'msg' => '获取到数据 '.$listN.' 条，正在入库…',
            ]);
            foreach ($fetched['list'] as $item) {
                $result = $this->upsert($source, $item);
                $logs[] = $result;
                if ($result['action'] === 'created') {
                    $created++;
                } elseif ($result['action'] === 'updated') {
                    $updated++;
                } else {
                    $skipped++;
                }
                CollectProgress::tick($sourcePk, [
                    'page' => $page,
                    'pages' => $pageCount,
                    'created' => $created,
                    'updated' => $updated,
                    'skipped' => $skipped,
                    'title' => (string) ($result['title'] ?? ''),
                    'action' => (string) ($result['action'] ?? ''),
                    'msg' => (string) ($result['msg'] ?? ''),
                ], [
                    'action' => (string) ($result['action'] ?? ''),
                    'title' => (string) ($result['title'] ?? ''),
                    'msg' => (string) ($result['msg'] ?? ''),
                    'page' => $page,
                ]);
            }
            CollectProgress::tick($sourcePk, [
                'page' => $page,
                'pages' => $pageCount,
                'msg' => '第 '.$page.' 页采集完成',
            ], [
                'action' => 'page_done',
                'page' => $page,
                'msg' => '本页采集完成',
            ]);
            if ($ids !== '' || ($pageCount > 0 && $page >= $pageCount)) {
                break;
            }
        }

        $summary = "采集完成，共 {$lastPage} 页，新增 {$created}，更新 {$updated}，跳过 {$skipped}";
        $this->persistCollectState($source, $lastPage, $created, $updated, "入库新建 {$created}，更新 {$updated}，跳过 {$skipped}", true, $skipped);
        CollectProgress::finish($sourcePk, [
            'ok' => 1,
            'page' => $lastPage,
            'pages' => $pageCount,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'action' => 'done',
            'title' => '',
            'msg' => $summary,
        ], [
            'action' => 'done',
            'page' => $lastPage,
            'msg' => $summary,
        ]);

        return AdminOpLog::ifOk(Result::success([
            'page' => $pageInfo,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'logs' => array_slice($logs, 0, 80),
        ], "入库新建 {$created}，更新 {$updated}，跳过 {$skipped}"), 'run', '运行了采集《'.trim((string) $source->name).'》', [
            'module' => '采集源',
            'target_type' => 'collects',
            'target_id' => $sourceId,
            'payload' => ['created' => $created, 'updated' => $updated, 'skipped' => $skipped],
        ]);
    }

    public function resume(int $sourceId, array $params = []): array
    {
        $source = CollectSourceModel::query()->find($sourceId);
        if (! $source) {
            return Result::fail('采集源不存在');
        }
        $next = max(1, (int) ($source->last_page ?? 0) + 1);
        $params['page'] = $next;
        $params['pages'] = max(1, (int) ($params['pages'] ?? 1));

        return $this->run($sourceId, $params);
    }

    public function retry(int $sourceId): array
    {
        $source = CollectSourceModel::query()->find($sourceId);
        if (! $source) {
            return Result::fail('采集源不存在');
        }
        $page = max(1, (int) ($source->last_page ?? 1));

        return $this->run($sourceId, ['page' => $page, 'pages' => 1]);
    }

    private function persistCollectState(CollectSourceModel $source, int $page, int $created, int $updated, string $msg, bool $ok, int $skipped = 0): void
    {
        $source->last_collect_at = time();
        $source->updated_at = time();
        if (Schema::hasColumn('collect_sources', 'last_page')) {
            $source->last_page = $page;
            $source->last_created = $created;
            $source->last_updated = $updated;
            $source->last_error = $ok ? '' : mb_substr($msg, 0, 250);
        }
        $source->save();
        if (! Schema::hasTable('video_collect_logs')) {
            return;
        }
        VideoCollectLog::query()->create([
            'collect_source_id' => $source->id,
            'page' => $page,
            'created_n' => $created,
            'updated_n' => $updated,
            'skipped_n' => $skipped,
            'ok' => $ok ? 1 : 0,
            'msg' => mb_substr($msg, 0, 250),
            'created_at' => time(),
        ]);
    }

    /** @param  array<string, mixed>  $item */
    private function upsert(CollectSourceModel $source, array $item, bool $allowTemp = true): array
    {
        if ($this->isMangaSource($source)) {
            return $this->mangaCollect()->upsert($source, $item, true);
        }
        $title = app(SynonymService::class)->expand(trim((string) ($item['vod_name'] ?? '')));
        if ($title === '') {
            return ['action' => 'skipped', 'msg' => '无标题', 'title' => ''];
        }
        $item['vod_name'] = $title;
        $bind = $this->bindMap($source);
        $remoteType = (int) ($item['type_id'] ?? 0);
        $typeId = (int) ($bind[(string) $remoteType] ?? 0);
        if ($typeId < 1) {
            return ['action' => 'skipped', 'msg' => '未绑定分类', 'title' => $title, 'remote_type' => $remoteType];
        }

        $hit = $this->audit->inspect(
            $title,
            (string) ($item['vod_content'] ?? ''),
            (string) ($item['vod_actor'] ?? '')
        );
        if ($hit && $hit['action'] === 'skip') {
            return ['action' => 'skipped', 'msg' => '审核拦截:'.$hit['rule'], 'title' => $title];
        }
        if ($hit && $hit['action'] === 'replace') {
            $title = $this->audit->applyReplace($title, (string) ($hit['words'] ?? ''));
            $item['vod_name'] = $title;
            $item['vod_content'] = $this->audit->applyReplace((string) ($item['vod_content'] ?? ''), (string) ($hit['words'] ?? ''));
        }

        $type = VideoTypeModel::query()->find($typeId);
        $typePid = (int) ($type?->parent_id ?: 0);
        $collectId = (string) ($item['vod_id'] ?? '');
        $now = time();
        $settings = app(\App\Services\Video\VideoSettingService::class);

        if ($allowTemp && (int) $settings->get('collect_to_temp', '0') === 1 && Schema::hasTable('video_collect_temps')) {
            $exists = null;
            if ($collectId !== '') {
                $exists = VideoModel::query()
                    ->where('collect_source_id', $source->id)
                    ->where('collect_id', $collectId)
                    ->first();
            }
            if (! $exists) {
                \App\Models\Video\VideoCollectTemp::query()->updateOrCreate(
                    [
                        'collect_source_id' => $source->id,
                        'collect_id' => $collectId !== '' ? $collectId : md5($title),
                    ],
                    [
                        'title' => $title,
                        'cover' => (string) ($item['vod_pic'] ?? ''),
                        'type_id' => $typeId,
                        'payload' => json_encode($item, JSON_UNESCAPED_UNICODE),
                        'status' => 0,
                        'msg' => '待转入',
                        'created_at' => $now,
                    ]
                );

                return ['action' => 'temp', 'msg' => '写入临时表', 'title' => $title];
            }
        }

        $video = null;
        if ($collectId !== '') {
            $video = VideoModel::query()
                ->where('collect_source_id', $source->id)
                ->where('collect_id', $collectId)
                ->first();
        }
        if (! $video) {
            $q = VideoModel::query()->where('title', $title);
            $year = trim((string) ($item['vod_year'] ?? ''));
            if ($year !== '') {
                $q->where('year', $year);
            }
            $video = $q->first();
        }

        if ($video && (int) $video->lock === 1) {
            return ['action' => 'skipped', 'msg' => '已锁定', 'title' => $title, 'id' => $video->id];
        }

        $cover = ((int) $settings->get('collect_sync_pic', '1') === 1)
            ? (string) ($item['vod_pic'] ?? '')
            : '';
        $payload = [
            'title' => $title,
            'subtitle' => (string) ($item['vod_sub'] ?? ''),
            'cover' => $cover,
            'type_id' => $typeId,
            'type_pid' => $typePid,
            'area' => $this->mapWords((string) ($item['vod_area'] ?? ''), (string) $settings->get('collect_areawords', '')),
            'lang' => $this->mapWords((string) ($item['vod_lang'] ?? ''), (string) $settings->get('collect_langwords', '')),
            'year' => (string) ($item['vod_year'] ?? ''),
            'director' => (string) ($item['vod_director'] ?? ''),
            'description' => (string) ($item['vod_content'] ?? ''),
            'remarks' => (string) ($item['vod_remarks'] ?? ''),
            'class' => (string) ($item['vod_class'] ?? $item['type_name'] ?? ''),
            'serial' => (string) ($item['vod_serial'] ?? ''),
            'total' => (int) ($item['vod_total'] ?? 0),
            'isend' => (int) ($item['vod_isend'] ?? 0),
            'letter' => VideoMeta::letter($title),
            'score' => (float) ($item['vod_score'] ?? 0),
            'status' => ($hit && $hit['action'] === 'review') ? 0 : (int) $settings->get('collect_in_status', '1'),
            'collect_id' => $collectId,
            'collect_source_id' => $source->id,
            'updated_at' => $now,
        ];
        if (Schema::hasColumn('videos', 'weekday')) {
            $payload['weekday'] = (string) ($item['vod_weekday'] ?? $item['weekday'] ?? '');
        }

        return DB::transaction(function () use ($video, $payload, $item, $now, $title, $hit, $settings) {
            $action = 'updated';
            if (! $video) {
                $payload['created_at'] = $now;
                $video = new VideoModel();
                $video->fill($payload);
                $video->save();
                VideoStatModel::query()->create([
                    'video_id' => $video->id,
                    'hits' => $this->randomCollectHits(),
                    'hits_day' => 0,
                    'hits_week' => 0,
                    'hits_month' => 0,
                    'up' => 0,
                    'down' => 0,
                    'score' => $payload['score'],
                    'score_all' => 0,
                    'score_num' => 0,
                    'updated_at' => $now,
                ]);
                $action = 'created';
            } else {
                $video->fill($payload);
                $video->save();
            }

            if ((string) ($video->cover ?? '') !== '' && (int) $settings->get('collect_pic_local', '0') === 1) {
                $local = $this->localizeCover((string) $video->cover, (int) $video->id);
                if ($local !== (string) $video->cover) {
                    $video->cover = $local;
                    $video->save();
                }
            }

            $this->mergePlay($video, (string) ($item['vod_play_from'] ?? ''), (string) ($item['vod_play_url'] ?? ''), $now, 'play');
            $this->mergePlay($video, (string) ($item['vod_down_from'] ?? ''), (string) ($item['vod_down_url'] ?? ''), $now, 'down');
            $this->syncPeople($video, $item);

            return ['action' => $action, 'msg' => ($hit ? '审核:'.$hit['rule'] : 'ok'), 'title' => $title, 'id' => $video->id];
        });
    }

    private function mergePlay(VideoModel $video, string $from, string $url, int $now, string $kind = 'play'): void
    {
        foreach ($this->parser->parse($from, $url) as $group) {
            $code = trim((string) ($group['name'] ?? ''));
            if ($code === '') {
                continue;
            }
            $label = PlayLineName::guess($code);
            $name = $kind === 'down' ? ('下载-'.$label) : $label;
            $source = VideoSourceModel::query()
                ->where('video_id', $video->id)
                ->where(function ($q) use ($code, $name, $kind) {
                    $q->where('player', $code)
                        ->orWhere('name', $code)
                        ->orWhere('name', $name);
                    if ($kind === 'down') {
                        $q->orWhere('name', '下载-'.$code);
                    }
                })
                ->first();
            if (! $source) {
                $source = new VideoSourceModel();
                $source->fill([
                    'video_id' => $video->id,
                    'name' => $name,
                    'type' => $kind === 'down' ? 'down' : 'play',
                    'player' => $code,
                    'status' => 1,
                    'sort' => $kind === 'down' ? 0 : 10,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $source->save();
            } else {
                $dirty = false;
                if ((string) $source->player !== $code) {
                    $source->player = $code;
                    $dirty = true;
                }
                $stored = (string) $source->name;
                if (PlayLineName::isFlag($stored) || $stored === $code || $stored === '下载-'.$code) {
                    $source->name = $name;
                    $dirty = true;
                }
                if ($dirty) {
                    $source->updated_at = $now;
                    $source->save();
                }
            }
            $this->ensurePlayerCode($code);
            foreach ($group['episodes'] as $ep) {
                $exists = VideoEpisodeModel::query()
                    ->where('source_id', $source->id)
                    ->where(function ($q) use ($ep) {
                        $q->where('episode_num', $ep['num'])->orWhere('url', $ep['url']);
                    })
                    ->first();
                if ($exists) {
                    $exists->fill([
                        'episode_name' => $ep['name'],
                        'episode_num' => $ep['num'],
                        'url' => $ep['url'],
                        'status' => 1,
                        'updated_at' => $now,
                    ]);
                    $exists->save();
                    continue;
                }
                $row = new VideoEpisodeModel();
                $row->fill([
                    'video_id' => $video->id,
                    'source_id' => $source->id,
                    'episode_name' => $ep['name'],
                    'episode_num' => $ep['num'],
                    'url' => $ep['url'],
                    'duration' => 0,
                    'status' => 1,
                    'sort' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $row->save();
            }
        }
    }

    private function ensurePlayerCode(string $code): void
    {
        $code = trim($code);
        if ($code === '' || ! Schema::hasTable('video_players')) {
            return;
        }
        $label = PlayLineName::guess($code);
        $existing = VideoPlayerModel::query()->where('code', $code)->first();
        if ($existing) {
            if (PlayLineName::isFlag((string) $existing->name) || (string) $existing->name === $code) {
                $existing->name = $label;
                $existing->save();
                PlayLineName::remember($code, $label);
            }

            return;
        }
        $row = [
            'code' => $code,
            'name' => $label,
            'parse' => '',
            'sort' => 0,
            'status' => 1,
        ];
        $engine = VideoPlayerModel::inferEngine($code, '');
        if (str_contains(strtolower($code), 'yun')) {
            $engine = 'iframe';
        } elseif (str_contains(strtolower($code), 'm3u8')) {
            $engine = 'artplayer';
        }
        if (Schema::hasColumn('video_players', 'engine')) {
            $row['engine'] = $engine;
        }
        VideoPlayerModel::query()->create($row);
        PlayLineName::remember($code, $label);
    }

    /** @param  array<string, mixed>  $item */
    private function syncPeople(\App\Models\Video\VideoModel $video, array $item): void
    {
        $now = time();
        $tags = preg_split('/[,，\/\s]+/u', (string) ($item['vod_tag'] ?? $item['vod_class'] ?? '')) ?: [];
        foreach (array_unique(array_filter(array_map('trim', $tags))) as $name) {
            $tag = \App\Models\Video\VideoTagModel::query()->firstOrCreate(
                ['name' => $name],
                ['slug' => '', 'sort' => 0, 'status' => 1, 'created_at' => $now, 'updated_at' => $now]
            );
            \App\Models\Video\VideoTagRelModel::query()->firstOrCreate([
                'video_id' => $video->id,
                'tag_id' => $tag->id,
            ]);
        }
        $actors = array_values(array_unique(array_filter(array_map('trim', preg_split('/[,，\/]+/u', (string) ($item['vod_actor'] ?? '')) ?: []))));
        $sort = count($actors);
        foreach ($actors as $name) {
            $actor = \App\Models\Video\ActorModel::query()->firstOrCreate(
                ['name' => $name],
                ['avatar' => '', 'status' => 1, 'sort' => 0, 'created_at' => $now, 'updated_at' => $now]
            );
            $rel = [
                'video_id' => $video->id,
                'actor_id' => $actor->id,
                'role_type' => 1,
            ];
            $extra = ['sort' => max(0, $sort--)];
            if (Schema::hasColumn('video_actor_rel', 'role_name')) {
                $extra['role_name'] = '';
            }
            \App\Models\Video\VideoActorRelModel::query()->firstOrCreate($rel, $extra);
        }
    }

    private function localizeCover(string $url, int $videoId = 0): string
    {
        if ($videoId < 1 || (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://'))) {
            return $url;
        }
        try {
            $resp = Http::timeout(15)->withHeaders(['User-Agent' => 'LaraVideo/1.0'])->get($url);
            if (! $resp->successful() || $resp->body() === '') {
                return $url;
            }
            $path = (string) (parse_url($url, PHP_URL_PATH) ?: '');
            $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
            $ext = preg_replace('/[^a-z0-9]/', '', $ext) ?: 'jpg';
            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                $ext = 'jpg';
            }
            $dir = public_path('uploads/vod');
            File::ensureDirectoryExists($dir);
            File::put($dir.DIRECTORY_SEPARATOR.$videoId.'.'.$ext, $resp->body());

            return '/uploads/vod/'.$videoId.'.'.$ext;
        } catch (\Throwable) {
            return $url;
        }
    }

    private function randomCollectHits(): int
    {
        $settings = app(\App\Services\Video\VideoSettingService::class);
        $min = max(0, (int) $settings->get('collect_hits_min', '0'));
        $max = max(0, (int) $settings->get('collect_hits_max', '0'));
        if ($max < 1) {
            return 0;
        }
        if ($max < $min) {
            $max = $min;
        }

        return random_int($min, $max);
    }

    private function mapWords(string $value, string $map): string
    {
        $value = trim($value);
        if ($value === '' || trim($map) === '') {
            return $value;
        }
        foreach (preg_split('/[\r\n]+/', $map) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (str_contains($line, '=')) {
                foreach (explode(',', $line) as $piece) {
                    $piece = trim($piece);
                    if (! str_contains($piece, '=')) {
                        continue;
                    }
                    [$from, $to] = array_map('trim', explode('=', $piece, 2));
                    if ($from !== '' && $value === $from) {
                        return $to;
                    }
                }
                continue;
            }
            [$from, $to] = array_pad(array_map('trim', explode(',', $line, 2)), 2, '');
            if ($from !== '' && $value === $from) {
                return $to;
            }
        }

        return $value;
    }

    private function isMangaSource(CollectSourceModel $source): bool
    {
        return class_exists(MangaCollectService::class) && (int) ($source->mid ?? 1) === MangaCollectService::MID;
    }

    private function mangaCollect(): MangaCollectService
    {
        return app(MangaCollectService::class);
    }

    /** @return array<string, int> */
    private function bindMap(CollectSourceModel $source): array
    {
        $raw = $source->bind_json;
        if (is_array($raw)) {
            return array_map('intval', $raw);
        }
        $decoded = json_decode((string) $raw, true);

        return is_array($decoded) ? array_map('intval', $decoded) : [];
    }
}
