<?php

namespace App\Services\Collect;

use App\Models\Video\CollectSourceModel;
use App\Models\Video\VideoEpisodeModel;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoSourceModel;
use App\Models\Video\VideoStatModel;
use App\Models\Video\VideoTypeModel;
use App\Support\Utils\Result;
use App\Support\VideoMeta;
use Illuminate\Support\Facades\DB;

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
            'local_types' => VideoTypeModel::query()->orderByDesc('sort')->orderBy('id')->get(['id', 'name', 'parent_id']),
        ]);
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

        return Result::success(['bind' => $clean]);
    }

    public function run(int $sourceId, array $params = []): array
    {
        $source = CollectSourceModel::query()->find($sourceId);
        if (! $source) {
            return Result::fail('采集源不存在');
        }
        $start = max(1, (int) ($params['page'] ?? 1));
        $pages = max(1, (int) ($params['pages'] ?? 1));
        $hours = (int) ($params['hours'] ?? $params['h'] ?? 0);
        $ids = (string) ($params['ids'] ?? '');
        $remoteType = (string) ($params['t'] ?? '');

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $logs = [];
        $pageInfo = [];
        $pageCount = $start;

        for ($page = $start; $page < $start + $pages; $page++) {
            $query = [
                'ac' => $ids !== '' ? 'detail' : 'videolist',
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
            if (trim((string) $source->param) !== '') {
                parse_str(ltrim((string) $source->param, '&'), $extra);
                $query = array_merge($query, $extra);
            }

            $fetched = $this->client->fetch((string) $source->api_url, $query, (string) ($source->api_type ?: 'auto'));
            if (! ($fetched['ok'] ?? false)) {
                return Result::fail((string) $fetched['msg']);
            }
            $pageInfo = $fetched['page'] ?? [];
            $pageCount = (int) ($pageInfo['pagecount'] ?? $pageInfo['pageCount'] ?? $page);
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
            }
            if ($ids !== '' || ($pageCount > 0 && $page >= $pageCount)) {
                break;
            }
        }

        $source->last_collect_at = time();
        $source->updated_at = time();
        $source->save();

        return Result::success([
            'page' => $pageInfo,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'logs' => array_slice($logs, 0, 80),
        ], "入库新建 {$created}，更新 {$updated}，跳过 {$skipped}");
    }

    /** @param  array<string, mixed>  $item */
    private function upsert(CollectSourceModel $source, array $item): array
    {
        $title = trim((string) ($item['vod_name'] ?? ''));
        if ($title === '') {
            return ['action' => 'skipped', 'msg' => '无标题', 'title' => ''];
        }
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

        $type = VideoTypeModel::query()->find($typeId);
        $typePid = (int) ($type?->parent_id ?: 0);
        $collectId = (string) ($item['vod_id'] ?? '');
        $now = time();

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

        $payload = [
            'title' => $title,
            'subtitle' => (string) ($item['vod_sub'] ?? ''),
            'cover' => (string) ($item['vod_pic'] ?? ''),
            'type_id' => $typeId,
            'type_pid' => $typePid,
            'area' => (string) ($item['vod_area'] ?? ''),
            'lang' => (string) ($item['vod_lang'] ?? ''),
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
            'status' => ($hit && $hit['action'] === 'review') ? 0 : 1,
            'collect_id' => $collectId,
            'collect_source_id' => $source->id,
            'updated_at' => $now,
        ];

        return DB::transaction(function () use ($video, $payload, $item, $now, $title, $hit) {
            $action = 'updated';
            if (! $video) {
                $payload['created_at'] = $now;
                $video = new VideoModel();
                $video->fill($payload);
                $video->save();
                VideoStatModel::query()->create([
                    'video_id' => $video->id,
                    'hits' => 0,
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

            $this->mergePlay($video, (string) ($item['vod_play_from'] ?? ''), (string) ($item['vod_play_url'] ?? ''), $now, 'play');
            $this->mergePlay($video, (string) ($item['vod_down_from'] ?? ''), (string) ($item['vod_down_url'] ?? ''), $now, 'down');
            $this->syncPeople($video, $item);

            return ['action' => $action, 'msg' => ($hit ? '审核:'.$hit['rule'] : 'ok'), 'title' => $title, 'id' => $video->id];
        });
    }

    private function mergePlay(VideoModel $video, string $from, string $url, int $now, string $kind = 'play'): void
    {
        foreach ($this->parser->parse($from, $url) as $group) {
            $name = $kind === 'down' ? ('下载-'.$group['name']) : $group['name'];
            $source = VideoSourceModel::query()
                ->where('video_id', $video->id)
                ->where('name', $name)
                ->first();
            if (! $source) {
                $source = new VideoSourceModel();
                $source->fill([
                    'video_id' => $video->id,
                    'name' => $name,
                    'type' => $kind === 'down' ? 'down' : 'play',
                    'player' => $group['name'],
                    'status' => 1,
                    'sort' => $kind === 'down' ? 0 : 10,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $source->save();
            }
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
        $actors = preg_split('/[,，\/]+/u', (string) ($item['vod_actor'] ?? '')) ?: [];
        $sort = 10;
        foreach (array_unique(array_filter(array_map('trim', $actors))) as $name) {
            $actor = \App\Models\Video\ActorModel::query()->firstOrCreate(
                ['name' => $name],
                ['avatar' => '', 'status' => 1, 'sort' => 0, 'created_at' => $now, 'updated_at' => $now]
            );
            \App\Models\Video\VideoActorRelModel::query()->firstOrCreate(
                ['video_id' => $video->id, 'actor_id' => $actor->id, 'role_type' => 1],
                ['sort' => $sort--, 'role_name' => '']
            );
        }
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
