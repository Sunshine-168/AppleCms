<?php

namespace App\Services\Admin\Video;

use App\Models\Video\ActorModel;
use App\Models\Video\CollectSourceModel;
use App\Models\Video\VideoActorRelModel;
use App\Models\Video\VideoEpisodeModel;
use App\Models\Video\VideoArt;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoSourceModel;
use App\Models\Video\VideoStatModel;
use App\Models\Video\VideoTagModel;
use App\Models\Video\VideoTagRelModel;
use App\Models\Video\VideoTypeModel;
use App\Models\Video\VideoWebsite;
use App\Support\AdminOpLog;
use App\Support\AdminPage;
use App\Support\AdminSeedLabel;
use App\Support\Utils\Result;
use App\Support\VideoMeta;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 视频服务
 */
class VideoService
{
    protected VideoModel $videoModel;
    protected VideoTypeModel $videoTypeModel;
    protected CollectSourceModel $collectSourceModel;
    protected VideoSourceModel $videoSourceModel;
    protected VideoEpisodeModel $videoEpisodeModel;
    protected VideoTagModel $videoTagModel;
    protected ActorModel $actorModel;
    protected VideoStatModel $videoStatModel;
    protected VideoTagRelModel $videoTagRelModel;
    protected VideoActorRelModel $videoActorRelModel;
    protected static array $tableColumnsCache = [];

    public function __construct()
    {
        $this->videoModel = new VideoModel();
        $this->videoTypeModel = new VideoTypeModel();
        $this->collectSourceModel = new CollectSourceModel();
        $this->videoSourceModel = new VideoSourceModel();
        $this->videoEpisodeModel = new VideoEpisodeModel();
        $this->videoTagModel = new VideoTagModel();
        $this->actorModel = new ActorModel();
        $this->videoStatModel = new VideoStatModel();
        $this->videoTagRelModel = new VideoTagRelModel();
        $this->videoActorRelModel = new VideoActorRelModel();
    }

    /**
     * 获取视频列表
     */
    public function getVideoLists(array $params): array
    {
        $where = [];

        $title = trim((string)($params['title'] ?? ''));
        if ($title !== '')
        {
            $where['title'] = ['like' => '%'.$title.'%'];
        }

        $typeId = $params['type_id'] ?? null;
        if ($typeId !== null && $typeId !== '')
        {
            $where[] = ['type_id', '=', (int)$typeId];
        }

        $status = $params['status'] ?? null;
        if ($status !== null && $status !== '')
        {
            $where[] = ['status', '=', (int)$status];
        }

        $isRecommend = $params['is_recommend'] ?? null;
        if ($isRecommend !== null && $isRecommend !== '')
        {
            $where[] = ['is_recommend', '=', (int)$isRecommend];
        }

        $isHot = $params['is_hot'] ?? null;
        if ($isHot !== null && $isHot !== '')
        {
            $where[] = ['is_hot', '=', (int)$isHot];
        }

        $lock = $params['lock'] ?? null;
        if ($lock !== null && $lock !== '') {
            $where[] = ['lock', '=', (int) $lock];
        }
        $year = trim((string) ($params['year'] ?? ''));
        if ($year !== '') {
            $where[] = ['year', '=', $year];
        }
        $area = trim((string) ($params['area'] ?? ''));
        if ($area !== '') {
            $where['area'] = ['like' => '%'.$area.'%'];
        }
        $pointsMin = $params['points_min'] ?? null;
        if ($pointsMin !== null && $pointsMin !== '') {
            $where[] = ['points', '>=', (int) $pointsMin];
        }

        $limit = (int)($params['limit'] ?? 10);
        if ($limit < 1) {
            $limit = 10;
        }

        $relIds = null;
        $actorId = (int) ($params['actor_id'] ?? 0);
        if ($actorId > 0) {
            $relIds = VideoActorRelModel::query()
                ->where('actor_id', $actorId)
                ->pluck('video_id')
                ->map(fn ($id) => (int) $id)
                ->all();
            if ($relIds === []) {
                return Result::success(['total' => 0, 'data' => []]);
            }
        }
        $tagId = (int) ($params['tag_id'] ?? 0);
        if ($tagId > 0) {
            $tagVideoIds = VideoTagRelModel::query()
                ->where('tag_id', $tagId)
                ->pluck('video_id')
                ->map(fn ($id) => (int) $id)
                ->all();
            if ($tagVideoIds === []) {
                return Result::success(['total' => 0, 'data' => []]);
            }
            $relIds = $relIds === null ? $tagVideoIds : array_values(array_intersect($relIds, $tagVideoIds));
            if ($relIds === []) {
                return Result::success(['total' => 0, 'data' => []]);
            }
        }
        if ($relIds !== null) {
            $where[] = ['id', 'in', $relIds];
        }

        $weekday = trim((string) ($params['weekday'] ?? ''));
        if ($weekday !== '' && Schema::hasColumn('videos', 'weekday')) {
            $where[] = ['weekday', '=', $weekday];
        }

        if ((string) ($params['trash'] ?? '') === '1' && Schema::hasColumn('videos', 'deleted_at')) {
            $q = VideoModel::query()->withoutGlobalScope('alive')->where('deleted_at', '>', 0);
            $kw = trim((string) ($params['q'] ?? $title));
            if ($kw !== '') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('title', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw);
                    }
                });
            }
            if ($typeId !== null && $typeId !== '') {
                $q->where('type_id', (int) $typeId);
            }
            $page = $q->orderByDesc('deleted_at')->paginate($limit);
            $data = AdminPage::of($page);
            $typeIds = [];
            $videoIds = [];
            foreach (($data['data'] ?? []) as $row) {
                if (! empty($row['type_id'])) {
                    $typeIds[] = (int) $row['type_id'];
                }
                $videoIds[] = (int) $row['id'];
            }
            $typeMap = [];
            if ($typeIds !== []) {
                $types = $this->videoTypeModel->selectByCondition([['id', 'in', array_values(array_unique($typeIds))]], ['id', 'name', 'parent_id']);
                foreach ($types as $t) {
                    $typeMap[(int) $t['id']] = $t;
                }
            }
            foreach ($data['data'] as &$row) {
                $tid = (int) ($row['type_id'] ?? 0);
                $row['type_name'] = AdminSeedLabel::type((string) ($typeMap[$tid]['name'] ?? ''));
            }
            unset($row);

            return Result::success($data);
        }

        $emptyUrl = (string) ($params['empty_url'] ?? '') === '1';
        $repeat = (string) ($params['repeat'] ?? '') === '1';
        $needPoints = (string) ($params['need_points'] ?? '') === '1';
        $hasPlot = (string) ($params['has_plot'] ?? '') === '1';
        $emptyPic = (string) ($params['empty_pic'] ?? '') === '1';
        $emptyContent = (string) ($params['empty_content'] ?? '') === '1';
        $noActor = (string) ($params['no_actor'] ?? '') === '1';
        $missingEp = (string) ($params['missing_ep'] ?? '') === '1';
        if ($emptyUrl || $repeat || $needPoints || $hasPlot || $emptyPic || $emptyContent || $noActor || $missingEp) {
            $q = VideoModel::query();
            if ($title !== '') {
                $q->where('title', 'like', '%'.$title.'%');
            }
            if ($typeId !== null && $typeId !== '') {
                $q->where('type_id', (int) $typeId);
            }
            if ($status !== null && $status !== '') {
                $q->where('status', (int) $status);
            }
            if ($lock !== null && $lock !== '') {
                $q->where('lock', (int) $lock);
            }
            if ($weekday !== '' && Schema::hasColumn('videos', 'weekday')) {
                $q->where('weekday', $weekday);
            }
            if ($needPoints) {
                $q->where('points', '>', 0);
            }
            if ($emptyUrl) {
                $this->scopeEmptyUrl($q);
            }
            if ($hasPlot) {
                $q->whereHas('plots');
            }
            if ($emptyPic) {
                $this->scopeEmptyPic($q);
            }
            if ($emptyContent) {
                $this->scopeEmptyContent($q);
            }
            if ($noActor) {
                $this->scopeNoActor($q);
            }
            if ($missingEp) {
                $this->scopeMissingEp($q);
            }
            if ($relIds !== null) {
                $q->whereIn('id', $relIds);
            }
            if ($repeat) {
                $dup = $this->duplicateTitles();
                if ($dup === []) {
                    $q->whereRaw('0 = 1');
                } else {
                    $q->whereIn('title', $dup);
                }
            }
            $page = $q->orderByDesc('id')->paginate($limit);
            $data = AdminPage::of($page);
        } else {
            $data = $this->videoModel->paginates($where, '*', $limit, ['id' => 'desc']);
        }

        $typeIds = [];
        $videoIds = [];
        foreach (($data['data'] ?? []) as $row)
        {
            if (!empty($row['type_id']))
            {
                $typeIds[] = (int)$row['type_id'];
            }
            $videoIds[] = (int)$row['id'];
        }
        $typeIds = array_values(array_unique(array_filter($typeIds)));

        $typeMap = [];
        if (!empty($typeIds))
        {
            $types = $this->videoTypeModel->selectByCondition([['id', 'in', $typeIds]], ['id', 'name', 'parent_id']);

            foreach ($types as $t)
            {
                $typeMap[(int)$t['id']] = $t;
            }
        }

        $statMap = [];
        $playMap = [];
        if (!empty($videoIds))
        {
            $stats = $this->videoStatModel->selectByCondition([['video_id', 'in', $videoIds]], ['video_id', 'hits']);
            foreach ($stats as $s)
            {
                $statMap[(int)$s['video_id']] = $s;
            }
            try {
                if (Schema::hasTable('video_episodes')) {
                    $countRows = VideoEpisodeModel::query()
                        ->selectRaw('video_id, COUNT(*) as c')
                        ->whereIn('video_id', $videoIds)
                        ->groupBy('video_id')
                        ->get();
                    foreach ($countRows as $row) {
                        $playMap[(int) $row->video_id] = (int) $row->c;
                    }
                }
            } catch (\Throwable) {
            }
        }

        if (!empty($data['data']) && is_array($data['data']))
        {
            foreach ($data['data'] as &$item)
        {
            $tid = (int)($item['type_id'] ?? 0);
            $vid = (int) ($item['id'] ?? 0);
            $epCount = (int) ($playMap[$vid] ?? 0);
            $total = (int) ($item['total'] ?? 0);
            $item['type_name'] = $tid > 0 && isset($typeMap[$tid]) ? AdminSeedLabel::type((string)$typeMap[$tid]['name']) : '';
            $item['hits'] = (int)($statMap[$vid]['hits'] ?? 0);
            $createdTs = (int)($item['created_at'] ?? ($item['create_time'] ?? 0));
            $updatedTs = (int)($item['updated_at'] ?? ($item['update_time'] ?? 0));
            $item['created_at_text'] = $createdTs > 0 ? date('Y-m-d H:i:s', $createdTs) : '';
            $item['updated_at_text'] = $updatedTs > 0 ? date('Y-m-d H:i:s', $updatedTs) : '';
            $item['has_cover'] = trim((string) ($item['cover'] ?? '')) !== '';
            $item['episode_count'] = $epCount;
            $item['has_play'] = $epCount > 0;
            $item['ep_gap'] = $total > 0 ? max(0, $total - $epCount) : 0;
        }
        unset($item);
        }

        return Result::success($data);
    }

    /** @return array<string, int> */
    public function queueCounts(): array
    {
        $zero = [
            'all' => 0,
            'pending' => 0,
            'empty_url' => 0,
            'empty_pic' => 0,
            'empty_content' => 0,
            'no_actor' => 0,
            'repeat' => 0,
            'repeat_groups' => 0,
            'missing_ep' => 0,
            'recycle' => 0,
        ];
        try {
            if (! Schema::hasTable('videos')) {
                return $zero;
            }
            $quality = $this->qualityCounts();

            return array_merge($zero, $quality, [
                'pending' => (int) VideoModel::query()->where('status', 0)->count(),
                'recycle' => Schema::hasColumn('videos', 'deleted_at')
                    ? (int) VideoModel::query()->withoutGlobalScope('alive')->where('deleted_at', '>', 0)->count()
                    : 0,
            ]);
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @return array<string, int> */
    public function qualityCounts(): array
    {
        $zero = [
            'all' => 0,
            'empty_url' => 0,
            'empty_pic' => 0,
            'empty_content' => 0,
            'no_actor' => 0,
            'repeat' => 0,
            'repeat_groups' => 0,
            'missing_ep' => 0,
        ];
        try {
            if (! Schema::hasTable('videos')) {
                return $zero;
            }
            $dupTitles = $this->duplicateTitles();

            return [
                'all' => (int) VideoModel::query()->count(),
                'empty_url' => $this->issueCount('empty_url'),
                'empty_pic' => $this->issueCount('empty_pic'),
                'empty_content' => $this->issueCount('empty_content'),
                'no_actor' => $this->issueCount('no_actor'),
                'repeat' => $dupTitles === [] ? 0 : (int) VideoModel::query()->whereIn('title', $dupTitles)->count(),
                'repeat_groups' => count($dupTitles),
                'missing_ep' => $this->issueCount('missing_ep'),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    private function issueCount(string $issue): int
    {
        $q = VideoModel::query();
        match ($issue) {
            'empty_url' => $this->scopeEmptyUrl($q),
            'empty_pic' => $this->scopeEmptyPic($q),
            'empty_content' => $this->scopeEmptyContent($q),
            'no_actor' => $this->scopeNoActor($q),
            'missing_ep' => $this->scopeMissingEp($q),
            default => $q->whereRaw('0 = 1'),
        };

        return (int) $q->count();
    }

    /** @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Video\VideoModel>  $q */
    private function scopeEmptyUrl($q): void
    {
        if (! Schema::hasTable('video_episodes')) {
            $q->whereRaw('0 = 1');

            return;
        }
        $q->whereDoesntHave('episodes');
    }

    /** @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Video\VideoModel>  $q */
    private function scopeEmptyPic($q): void
    {
        $q->where(function ($inner) {
            $inner->whereNull('cover')->orWhere('cover', '');
        });
    }

    /** @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Video\VideoModel>  $q */
    private function scopeEmptyContent($q): void
    {
        $q->where(function ($inner) {
            $inner->whereNull('description')->orWhere('description', '');
        });
    }

    /** @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Video\VideoModel>  $q */
    private function scopeNoActor($q): void
    {
        if (Schema::hasTable('video_actor_rel')) {
            $q->whereDoesntHave('actors');
        }
        if (Schema::hasColumn('videos', 'actor')) {
            $q->where(function ($inner) {
                $inner->whereNull('actor')->orWhere('actor', '');
            });
        }
    }

    /** @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Video\VideoModel>  $q */
    private function scopeMissingEp($q): void
    {
        if (! Schema::hasColumn('videos', 'total') || ! Schema::hasTable('video_episodes')) {
            $q->whereRaw('0 = 1');

            return;
        }
        $q->where('total', '>', 0)
            ->whereRaw('(select count(*) from video_episodes where video_episodes.video_id = videos.id) < videos.total');
    }

    /** @return list<string> */
    private function duplicateTitles(): array
    {
        try {
            return VideoModel::query()
                ->select('title')
                ->groupBy('title')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('title')
                ->map(fn ($title) => (string) $title)
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * 获取视频详情
     */
    public function getVideoInfo(int $id): array
    {
        $info = $this->videoModel->findById($id);
        if (empty($info)) {
            return Result::fail(admin_t('ui.data_missing'));
        }

        $tagRelRows = $this->videoTagRelModel->selectByCondition([['video_id', '=', $id]], ['tag_id'], ['tag_id' => 'asc']);
        $tagIds = [];
        foreach ($tagRelRows as $r)
        {
            $tagIds[] = (int)($r['tag_id'] ?? 0);
        }
        $tagIds = array_values(array_unique(array_filter($tagIds)));
        $tags = [];
        if (!empty($tagIds))
        {
            $tagRows = $this->videoTagModel->selectByCondition([['id', 'in', $tagIds]], ['id', 'name'], ['id' => 'asc']);
            foreach ($tagRows as $t)
            {
                $tags[] = (string)($t['name'] ?? '');
            }
        }

        $actorRelRows = $this->videoActorRelModel->selectByCondition(
            [
                ['video_id', '=', $id],
                ['role_type', '=', 1],
            ],
            ['actor_id'],
            ['sort' => 'desc']
        );
        $actorIds = [];
        foreach ($actorRelRows as $r)
        {
            $actorIds[] = (int)($r['actor_id'] ?? 0);
        }
        $actorIds = array_values(array_unique(array_filter($actorIds)));

        $actors = [];
        if (!empty($actorIds))
        {
            $actorRows = $this->actorModel->selectByCondition([['id', 'in', $actorIds]], ['id', 'name']);
            $actorMap = [];
            foreach ($actorRows as $a)
            {
                $actorMap[(int)$a['id']] = (string)($a['name'] ?? '');
            }
            foreach ($actorIds as $aid)
            {
                if (isset($actorMap[$aid]))
                {
                    $actors[] = $actorMap[$aid];
                }
            }
        }

        $info['tags_text'] = implode(',', array_filter(array_map('strval', $tags)));
        $info['actors_text'] = implode(',', array_filter(array_map('strval', $actors)));

        return Result::success($info);
    }
    /**
     * 保存视频
     */
    public function saveVideo(array $data, ?int $id = null): array
    {
        $title = trim((string)($data['title'] ?? ''));
        if ($title === '') {
            return Result::fail(admin_t('ui.title_empty'));
        }

        $now = time();

        $payload = [
            'title' => $title,
            'subtitle' => (string)($data['subtitle'] ?? ''),
            'cover' => (string)($data['cover'] ?? ''),
            'banner' => (string)($data['banner'] ?? ''),
            'type_id' => ($data['type_id'] ?? '') === '' ? null : (int)$data['type_id'],
            'area' => (string)($data['area'] ?? ''),
            'lang' => (string)($data['lang'] ?? ''),
            'class' => (string)($data['class'] ?? ''),
            'year' => (string)($data['year'] ?? ''),
            'director' => (string)($data['director'] ?? ''),
            'remarks' => (string)($data['remarks'] ?? ''),
            'description' => (string)($data['description'] ?? ''),
            'status' => (int)($data['status'] ?? 1),
            'lock' => (int)($data['lock'] ?? 0),
            'points' => (int)($data['points'] ?? 0),
            'is_recommend' => (int)($data['is_recommend'] ?? 0),
            'is_hot' => (int)($data['is_hot'] ?? 0),
            'score' => (float)($data['score'] ?? 0),
            'collect_id' => (string)($data['collect_id'] ?? ''),
            'collect_source_id' => ($data['collect_source_id'] ?? '') === '' ? null : (int)$data['collect_source_id'],
            'sort' => (int)($data['sort'] ?? 0),
            'letter' => VideoMeta::letter($title),
            'updated_at' => $now,
        ];
        if (Schema::hasColumn('videos', 'class')) {
            $parts = preg_split('/[,，]+/u', (string) $payload['class']) ?: [];
            $seen = [];
            $clean = [];
            foreach ($parts as $part) {
                $part = trim((string) $part);
                if ($part === '' || isset($seen[$part])) {
                    continue;
                }
                $seen[$part] = true;
                $clean[] = $part;
            }
            $payload['class'] = implode(',', $clean);
        } else {
            unset($payload['class']);
        }
        if (Schema::hasColumn('videos', 'weekday')) {
            $payload['weekday'] = trim((string) ($data['weekday'] ?? ''));
        }
        if (Schema::hasColumn('videos', 'publish_at')) {
            $payload['publish_at'] = $this->toUnix($data['publish_at'] ?? 0);
        }
        if (!empty($payload['type_id'])) {
            $type = $this->videoTypeModel->findById((int) $payload['type_id']);
            $payload['type_pid'] = (int) ($type['parent_id'] ?? 0);
        }

        $tagsText = (string)($data['tags_text'] ?? '');
        $actorsText = (string)($data['actors_text'] ?? '');

        try {
            $result = DB::transaction(function () use ($id, $payload, $now, $tagsText, $actorsText) {
                if ($id)
                {
                    $exists = $this->videoModel->findById($id);
                    if (empty($exists))
                    {
                        return Result::fail(admin_t('ui.data_missing'));
                    }

                    $ok = $this->videoModel->updateById($id, $payload);
                    if (!$ok)
                    {
                        return Result::fail();
                    }

                    $this->videoStatModel->updateById($id, [
                        'score' => (float)$payload['score'],
                        'updated_at' => $now,
                    ]);
                } else
                {
                    $payload['created_at'] = $now;
                    $newId = $this->videoModel->insertsGetId($payload);
                    if (!$newId)
                    {
                        return Result::fail();
                    }
                    $id = (int)$newId;
                    $this->videoStatModel->inserts([
                        'video_id' => $id,
                        'hits' => 0,
                        'hits_day' => 0,
                        'hits_week' => 0,
                        'hits_month' => 0,
                        'up' => 0,
                        'down' => 0,
                        'score' => (float)$payload['score'],
                        'score_num' => 0,
                        'updated_at' => $now,
                    ]);
                }

                $this->syncTags($id, $tagsText);
                $this->syncActors($id, $actorsText);

                return Result::success(['id' => $id]);
            });
        } catch (Exception $e)
        {
            return Result::fail($e->getMessage() ?: admin_t('ui.op_fail'));
        }
        if ((int) ($result['code'] ?? 1) === 0) {
            $vid = (int) ($result['data']['id'] ?? $id ?? 0);
            AdminOpLog::write('save', ($id ? '保存了' : '新增了').AdminOpLog::named('影片', $title, $vid), [
                'module' => '影片',
                'target_type' => 'videos',
                'target_id' => $vid,
                'payload' => ['id' => $vid, 'title' => $title],
            ]);
        }

        return $result;
    }
    /**
     * 删除视频
     */
    public function deleteVideo(int $id): array
    {
        $info = $this->videoModel->findById($id);
        if (empty($info))
        {
            return Result::fail(admin_t('ui.data_missing'));
        }
        $title = (string) ($info['title'] ?? '');
        if (Schema::hasColumn('videos', 'deleted_at')) {
            VideoModel::query()->where('id', $id)->update([
                'deleted_at' => time(),
                'updated_at' => time(),
            ]);

            return AdminOpLog::ifOk(Result::success([], admin_t('ui.moved_to_recycle')), 'delete', '删除了'.AdminOpLog::named('影片', $title, $id), [
                'module' => '影片',
                'target_type' => 'videos',
                'target_id' => $id,
            ]);
        }

        $purged = $this->purgeVideo($id);

        return AdminOpLog::ifOk($purged, 'delete', '删除了'.AdminOpLog::named('影片', $title, $id), [
            'module' => '影片',
            'target_type' => 'videos',
            'target_id' => $id,
        ]);
    }

    public function restoreVideo(int $id): array
    {
        if (! Schema::hasColumn('videos', 'deleted_at')) {
            return Result::fail(admin_t('ui.recycle_off'));
        }
        $n = VideoModel::query()->withoutGlobalScope('alive')->where('id', $id)->where('deleted_at', '>', 0)->update([
            'deleted_at' => 0,
            'updated_at' => time(),
        ]);

        return $n > 0 ? Result::success() : Result::fail(admin_t('ui.not_in_recycle'));
    }

    public function purgeVideo(int $id): array
    {
        $row = VideoModel::query()->withoutGlobalScope('alive')->find($id);
        if (! $row) {
            return Result::fail(admin_t('ui.data_missing'));
        }
        try {
            return DB::transaction(function () use ($id) {
                $sourceRows = $this->videoSourceModel->selectByCondition([['video_id', '=', $id]], ['id']);
                $sourceIds = [];
                foreach ($sourceRows as $r)
                {
                    $sourceIds[] = (int)($r['id'] ?? 0);
                }
                $sourceIds = array_values(array_unique(array_filter($sourceIds)));

                if (!empty($sourceIds))
                {
                    $this->videoEpisodeModel->deleteByCondition([['source_id', 'in', $sourceIds]]);
                }

                $this->videoEpisodeModel->deleteByCondition([['video_id', '=', $id]]);
                $this->videoSourceModel->deleteByCondition([['video_id', '=', $id]]);
                $this->videoTagRelModel->deleteByCondition([['video_id', '=', $id]]);
                $this->videoActorRelModel->deleteByCondition([['video_id', '=', $id]]);
                $this->videoStatModel->deleteById($id);

                $ok = VideoModel::query()->withoutGlobalScope('alive')->where('id', $id)->delete() > 0;
                if (!$ok) {
                    return Result::fail();
                }

                return Result::success();
            });
        } catch (Exception $e)
        {
            return Result::fail($e->getMessage() ?: admin_t('ui.delete_fail'));
        }
    }
    /**
     * 获取视频类型选项
     */
    public function getTypeOptions(?int $mid = 1): array
    {
        $cols = ['id', 'parent_id', 'name'];
        $hasMid = Schema::hasTable('video_types') && Schema::hasColumn('video_types', 'mid');
        if ($hasMid) {
            $cols[] = 'mid';
        }
        $all = $this->videoTypeModel->selectByCondition([], $cols, ['sort' => 'desc', 'id' => 'asc']);
        if ($hasMid && $mid !== null) {
            $want = $mid < 1 ? 1 : $mid;
            $all = array_values(array_filter($all, static function (array $row) use ($want): bool {
                $got = (int) ($row['mid'] ?? 1);
                if ($got < 1) {
                    $got = 1;
                }

                return $got === $want;
            }));
        }
        $byParent = [];
        foreach ($all as $row) {
            $byParent[(int) ($row['parent_id'] ?? 0)][] = $row;
        }
        $options = [];
        $this->walkTypeOptions($byParent, 0, 0, $options);

        return Result::success($options);
    }

    /**
     * @param  array<int, list<array<string, mixed>>>  $byParent
     * @param  list<array{id:int,name:string}>  $out
     */
    private function walkTypeOptions(array $byParent, int $parentId, int $depth, array &$out): void
    {
        foreach ($byParent[$parentId] ?? [] as $row) {
            $pad = $depth > 0 ? str_repeat('└ ', $depth) : '';
            $rawName = (string) ($row['name'] ?? '');
            $out[] = [
                'id' => (int) ($row['id'] ?? 0),
                'name' => $pad.AdminSeedLabel::type($rawName),
            ];
            $this->walkTypeOptions($byParent, (int) ($row['id'] ?? 0), $depth + 1, $out);
        }
    }

    /**
     * 获取采集源选项
     */
    public function getCollectSourceOptions(): array
    {
        $rows = $this->collectSourceModel->selectByCondition([], ['id', 'name', 'status'], ['sort' => 'desc', 'id' => 'asc']);
        return Result::success($rows);
    }
    /**
     * 获取视频类型列表
     */
    public function getVideoTypeLists(array $params): array
    {
        $name = trim((string) ($params['name'] ?? ''));
        $all = $this->videoTypeModel->selectByCondition([], '*', ['sort' => 'desc', 'id' => 'asc']);
        $mid = (int) ($params['mid'] ?? 0);
        $hasMid = Schema::hasTable('video_types') && Schema::hasColumn('video_types', 'mid');
        if ($hasMid && $mid > 0) {
            $all = array_values(array_filter($all, static function (array $row) use ($mid): bool {
                $got = (int) ($row['mid'] ?? 1);
                if ($got < 1) {
                    $got = 1;
                }

                return $got === $mid;
            }));
        }

        $videoCounts = [];
        try {
            if (Schema::hasTable('videos')) {
                $countRows = VideoModel::query()
                    ->selectRaw('type_id, COUNT(*) as c')
                    ->groupBy('type_id')
                    ->get();
                foreach ($countRows as $row) {
                    $videoCounts[(int) $row->type_id] = (int) $row->c;
                }
            }
        } catch (\Throwable) {
        }

        $artCounts = [];
        try {
            if (Schema::hasTable('video_arts')) {
                $artRows = VideoArt::query()
                    ->selectRaw('type_id, COUNT(*) as c')
                    ->groupBy('type_id')
                    ->get();
                foreach ($artRows as $row) {
                    $artCounts[(int) $row->type_id] = (int) $row->c;
                }
            }
        } catch (\Throwable) {
        }

        $websiteCounts = [];
        try {
            if (Schema::hasTable('video_websites') && Schema::hasColumn('video_websites', 'type_id')) {
                $siteRows = VideoWebsite::query()
                    ->selectRaw('type_id, COUNT(*) as c')
                    ->groupBy('type_id')
                    ->get();
                foreach ($siteRows as $row) {
                    $websiteCounts[(int) $row->type_id] = (int) $row->c;
                }
            }
        } catch (\Throwable) {
        }

        $byParent = [];
        $byId = [];
        foreach ($all as $row) {
            $id = (int) ($row['id'] ?? 0);
            $byId[$id] = $row;
            $byParent[(int) ($row['parent_id'] ?? 0)][] = $row;
        }

        $flat = [];
        $this->walkTypeTree($byParent, 0, 0, $flat, 5);
        $seen = [];
        foreach ($flat as $row) {
            $seen[(int) $row['id']] = true;
        }
        foreach ($all as $row) {
            $id = (int) ($row['id'] ?? 0);
            if (! isset($seen[$id])) {
                $row['depth'] = 0;
                $flat[] = $row;
                $seen[$id] = true;
            }
        }

        if ($name !== '') {
            $keep = [];
            foreach ($flat as $row) {
                if (mb_stripos((string) ($row['name'] ?? ''), $name) === false) {
                    continue;
                }
                $id = (int) $row['id'];
                $keep[$id] = true;
                $pid = (int) ($row['parent_id'] ?? 0);
                $guard = 0;
                while ($pid > 0 && $guard++ < 8 && isset($byId[$pid])) {
                    $keep[$pid] = true;
                    $pid = (int) ($byId[$pid]['parent_id'] ?? 0);
                }
            }
            $flat = array_values(array_filter(
                $flat,
                static fn (array $row): bool => isset($keep[(int) $row['id']])
            ));
        }

        foreach ($flat as &$item) {
            $id = (int) ($item['id'] ?? 0);
            $pid = (int) ($item['parent_id'] ?? 0);
            $item['parent_name'] = $pid > 0 ? AdminSeedLabel::type((string) ($byId[$pid]['name'] ?? '')) : admin_t('ui.top_level');
            $item['name_label'] = AdminSeedLabel::type((string) ($item['name'] ?? ''));
            $item['video_count'] = $videoCounts[$id] ?? 0;
            $item['art_count'] = $artCounts[$id] ?? 0;
            $item['website_count'] = $websiteCounts[$id] ?? 0;
            $item['child_count'] = count($byParent[$id] ?? []);
            $item['depth'] = (int) ($item['depth'] ?? 0);
            $item['kind'] = VideoTypeModel::normalizeKind($item['kind'] ?? 'list');
            $item['kind_label'] = VideoTypeModel::kindLabel($item['kind']);
            $item['jump_url'] = (string) ($item['jump_url'] ?? '');
            $item['page_size'] = (int) ($item['page_size'] ?? 0);
            $item['pic'] = (string) ($item['pic'] ?? '');
            if ((int) ($item['mid'] ?? 1) !== 2) {
                $item['kind'] = 'list';
                $item['kind_label'] = '';
            }
        }
        unset($item);

        return Result::success([
            'total' => count($flat),
            'data' => $flat,
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => max(count($flat), 1),
        ]);
    }

    public function getTypeInfo(int $id): array
    {
        $row = $this->videoTypeModel->findById($id);
        if (empty($row)) {
            return Result::fail(admin_t('ui.type_missing'));
        }

        return Result::success($row);
    }

    /**
     * @param array<int, list<array<string, mixed>>> $byParent
     * @param list<array<string, mixed>> $out
     */
    private function walkTypeTree(array $byParent, int $parentId, int $depth, array &$out, int $maxDepth): void
    {
        foreach ($byParent[$parentId] ?? [] as $row) {
            $row['depth'] = $depth;
            $out[] = $row;
            if ($depth < $maxDepth) {
                $this->walkTypeTree($byParent, (int) ($row['id'] ?? 0), $depth + 1, $out, $maxDepth);
            }
        }
    }
    /**
     * 保存视频类型
     */
    public function saveVideoType(array $data, ?int $id = null): array
    {
        $parentId = (int) ($data['parent_id'] ?? 0);
        $mid = (int) ($data['mid'] ?? 1);
        if (! in_array($mid, [1, 2, 3], true)) {
            $mid = 1;
        }
        $noun = match ($mid) {
            2 => admin_t('ui.noun_art_type'),
            3 => admin_t('ui.noun_web_type'),
            default => admin_t('ui.noun_vod_type'),
        };
        $name = trim((string)($data['name'] ?? ''));
        if ($name === '')
        {
            return Result::fail(admin_t('ui.name_required', ['noun' => $noun]));
        }
        if ($parentId > 0) {
            $parent = $this->videoTypeModel->findById($parentId);
            if (empty($parent)) {
                return Result::fail(admin_t('ui.parent_missing'));
            }
            $parentMid = (int) ($parent['mid'] ?? 1);
            if ($parentMid < 1) {
                $parentMid = 1;
            }
            if ($parentMid !== $mid) {
                return Result::fail(admin_t('ui.parent_same_kind'));
            }
        }
        if ($id !== null && $id > 0) {
            if ($parentId === $id) {
                return Result::fail(admin_t('ui.parent_self'));
            }
            $node = VideoTypeModel::query()->find($id);
            if ($node && in_array($parentId, $node->descendantIds(), true)) {
                return Result::fail(admin_t('ui.parent_descendant'));
            }
        }

        $kind = 'list';
        $jump = '';
        $pageSize = 0;
        if ($mid === 2) {
            $kind = VideoTypeModel::normalizeKind($data['kind'] ?? 'list');
            if ($kind === 'link') {
                $safe = VideoTypeModel::safeJumpUrl((string) ($data['jump_url'] ?? ''));
                if ($safe === null) {
                    return Result::fail(admin_t('ui.need_jump_url'));
                }
                $jump = $safe;
            }
            $pageSize = max(0, min(100, (int) ($data['page_size'] ?? 0)));
        }

        $now = time();
        $candidate = [
            'name' => $name,
            'slug' => trim((string) ($data['slug'] ?? '')),
            'parent_id' => $parentId,
            'mid' => $mid,
            'sort' => (int) ($data['sort'] ?? 0),
            'status' => (int) ($data['status'] ?? 1),
            'pic' => mb_substr(trim((string) ($data['pic'] ?? '')), 0, 255),
            'tpl_list' => VideoTypeModel::normalizeTpl((string) ($data['tpl_list'] ?? '')),
            'tpl_detail' => VideoTypeModel::normalizeTpl((string) ($data['tpl_detail'] ?? '')),
            'kind' => $kind,
            'jump_url' => $jump,
            'page_size' => $pageSize,
            'seo_title' => trim((string) ($data['seo_title'] ?? '')),
            'seo_keywords' => trim((string) ($data['seo_keywords'] ?? '')),
            'seo_description' => trim((string) ($data['seo_description'] ?? '')),
            'updated_at' => $now,
        ];
        if ($id === null || $id < 1)
        {
            $candidate['created_at'] = $now;
        }

        $payload = $this->filterPayloadByTableColumns('video_types', $candidate);

        if ($id !== null && $id > 0)
        {
            $exists = $this->videoTypeModel->findById($id);
            if (empty($exists))
            {
                return Result::fail(admin_t('ui.data_missing'));
            }
            $ok = $this->videoTypeModel->updateById($id, $payload);
            return $ok ? Result::success() : Result::fail();
        }

        $newId = $this->videoTypeModel->insertsGetId($payload);
        return $newId ? Result::success(['id' => (int)$newId]) : Result::fail();
    }
    /**
     * 删除视频类型
     */
    public function deleteVideoType(int $id): array
    {
        $exists = $this->videoTypeModel->findById($id);
        if (empty($exists))
        {
            return Result::fail(admin_t('ui.data_missing'));
        }

        $isArt = (int) ($exists['mid'] ?? 1) === 2;
        $isWebsite = (int) ($exists['mid'] ?? 1) === 3;
        $childCount = $this->videoTypeModel->countByCondition([['parent_id', '=', $id]]);
        if ($childCount > 0)
        {
            return Result::fail($isArt ? admin_t('ui.del_child_arts') : admin_t('ui.del_child_types'));
        }

        if (! $isArt && ! $isWebsite) {
            $useCount = $this->videoModel->countByCondition([['type_id', '=', $id]]);
            if ($useCount > 0)
            {
                return Result::fail(admin_t('ui.type_has_videos'));
            }
        }
        if ($isArt) {
            $artCount = 0;
            try {
                if (Schema::hasTable('video_arts')) {
                    $artCount = (int) VideoArt::query()->where('type_id', $id)->count();
                }
            } catch (\Throwable) {
            }
            if ($artCount > 0) {
                return Result::fail(admin_t('ui.type_has_arts'));
            }
        }
        if ($isWebsite) {
            $siteCount = 0;
            try {
                if (Schema::hasTable('video_websites') && Schema::hasColumn('video_websites', 'type_id')) {
                    $siteCount = (int) VideoWebsite::query()->where('type_id', $id)->count();
                }
            } catch (\Throwable) {
            }
            if ($siteCount > 0) {
                return Result::fail(admin_t('ui.type_has_sites'));
            }
        }

        $ok = $this->videoTypeModel->deleteById($id);
        return $ok ? Result::success() : Result::fail();
    }

    /**
     * @param list<int|string> $ids
     */
    public function batchVideoTypes(array $ids, string $action, mixed $value = ''): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return Result::fail(admin_t('ui.please_select_types'));
        }
        $ok = 0;
        $fail = 0;
        $last = '';
        foreach ($ids as $id) {
            $row = $this->videoTypeModel->findById($id);
            if (empty($row)) {
                $fail++;
                continue;
            }
            $res = match ($action) {
                'status' => $this->saveVideoType(array_merge($row, ['status' => (int) $value]), $id),
                'parent' => $this->saveVideoType(array_merge($row, ['parent_id' => (int) $value]), $id),
                'delete' => $this->deleteVideoType($id),
                default => Result::fail(admin_t('ui.unsupported_op')),
            };
            if (($res['code'] ?? 1) === 0) {
                $ok++;
            } else {
                $fail++;
                $last = (string) ($res['msg'] ?? '');
            }
        }
        if ($ok === 0) {
            return Result::fail($last !== '' ? $last : admin_t('ui.op_fail'));
        }

        return Result::success(['ok' => $ok, 'fail' => $fail], $fail > 0 ? (admin_t('ui.batch_n_unhandled', ['ok' => $ok, 'fail' => $fail]).($last !== '' ? '。'.$last : '')) : admin_t('ui.op_ok'));
    }

    /**
     * 获取采集源列表
     */
    public function getCollectSourceLists(array $params): array
    {
        $limit = (int) ($params['limit'] ?? 100);
        if ($limit < 1) {
            $limit = 100;
        }

        $where = [];
        $name = trim((string) ($params['name'] ?? ''));
        if ($name !== '') {
            $where['name'] = ['like' => '%'.$name.'%'];
        }
        if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
            $where[] = ['status', '=', (int) $params['status']];
        }
        if (array_key_exists('mid', $params) && $params['mid'] !== '' && $params['mid'] !== null) {
            $where[] = ['mid', '=', (int) $params['mid']];
        }

        $data = $this->collectSourceModel->paginates($where, '*', $limit, ['sort' => 'desc', 'id' => 'desc']);
        $rows = $data['data'] ?? [];
        $emptyBind = (string) ($params['empty_bind'] ?? '') === '1';
        $hasError = (string) ($params['has_error'] ?? '') === '1';

        foreach ($rows as &$item) {
            $bind = json_decode((string) ($item['bind_json'] ?? ''), true);
            $bind = is_array($bind) ? $bind : [];
            $bindCount = 0;
            foreach ($bind as $local) {
                if ((int) $local > 0) {
                    $bindCount++;
                }
            }
            $err = trim((string) ($item['last_error'] ?? ''));
            $lastAt = (int) ($item['last_collect_at'] ?? 0);
            $url = trim((string) ($item['api_url'] ?? ''));
            $host = parse_url($url, PHP_URL_HOST);
            $item['bind_count'] = $bindCount;
            $item['has_error'] = $err !== '';
            $item['has_break'] = (int) ($item['last_page'] ?? 0) > 0;
            $item['last_error'] = $err;
            $item['mid'] = (int) ($item['mid'] ?? 1);
            $item['mid_label'] = $item['mid'] === 2 ? admin_t('nav.manga') : admin_t('nav.videos');
            $item['last_collect_at_text'] = $lastAt > 0 ? date('Y-m-d H:i', $lastAt) : '';
            $item['api_host'] = is_string($host) && $host !== '' ? $host : $url;
            $item['created_at_text'] = ! empty($item['created_at']) ? date('Y-m-d H:i:s', (int) $item['created_at']) : '';
            $item['updated_at_text'] = ! empty($item['updated_at']) ? date('Y-m-d H:i:s', (int) $item['updated_at']) : '';
        }
        unset($item);

        if ($emptyBind || $hasError) {
            $rows = array_values(array_filter($rows, static function (array $row) use ($emptyBind, $hasError): bool {
                if ($emptyBind && (int) ($row['bind_count'] ?? 0) > 0) {
                    return false;
                }
                if ($hasError && empty($row['has_error'])) {
                    return false;
                }

                return true;
            }));
            $data['total'] = count($rows);
            $data['last_page'] = 1;
            $data['current_page'] = 1;
        }

        $data['data'] = $rows;

        return Result::success($data);
    }
    /**
     * 保存采集源
     */
    public function saveCollectSource(array $data, ?int $id = null): array
    {
        $name = trim((string)($data['name'] ?? ''));
        if ($name === '')
        {
            return Result::fail(admin_t('ui.need_collect_name'));
        }
        $apiUrl = trim((string) ($data['api_url'] ?? ''));
        if ($apiUrl === '') {
            return Result::fail(admin_t('ui.please_fill_api'));
        }
        $mid = (int) ($data['mid'] ?? 1) === 2 ? 2 : 1;
        if ($mid === 2) {
            $manga = class_exists(\Plugins\Manga\Services\MangaCollectService::class)
                ? app(\Plugins\Manga\Services\MangaCollectService::class)
                : null;
            if (! $manga || ! $manga->ready()) {
                return Result::fail(admin_t('ui.manga_plugin_off'));
            }
        }

        $now = time();
        $candidate = [
            'name' => $name,
            'api_url' => $apiUrl,
            'api_type' => (string)($data['api_type'] ?? 'auto'),
            'mid' => $mid,
            'param' => (string)($data['param'] ?? ''),
            'status' => (int)($data['status'] ?? 1),
            'sort' => (int)($data['sort'] ?? 0),
            'updated_at' => $now,
        ];
        if ($id === null || $id < 1)
        {
            $candidate['created_at'] = $now;
        }
        $payload = $this->filterPayloadByTableColumns('collect_sources', $candidate);

        if ($id !== null && $id > 0)
        {
            $exists = $this->collectSourceModel->findById($id);
            if (empty($exists))
            {
                return Result::fail(admin_t('ui.data_missing'));
            }
            $ok = $this->collectSourceModel->updateById($id, $payload);
            return $ok ? Result::success() : Result::fail();
        }

        $newId = $this->collectSourceModel->insertsGetId($payload);
        return $newId ? Result::success(['id' => (int)$newId]) : Result::fail();
    }
    /**
     * 删除采集源
     */
    public function deleteCollectSource(int $id): array
    {
        $exists = $this->collectSourceModel->findById($id);
        if (empty($exists))
        {
            return Result::fail(admin_t('ui.data_missing'));
        }

        $useCount = $this->videoModel->countByCondition([['collect_source_id', '=', $id]]);
        if ($useCount > 0)
        {
            return Result::fail(admin_t('ui.collect_used_video'));
        }
        if (class_exists(\Plugins\Manga\Models\Manga::class) && Schema::hasTable('plugin_mangas') && Schema::hasColumn('plugin_mangas', 'collect_source_id')) {
            if (\Plugins\Manga\Models\Manga::query()->where('collect_source_id', $id)->exists()) {
                return Result::fail(admin_t('ui.collect_used_manga'));
            }
        }

        $ok = $this->collectSourceModel->deleteById($id);
        return $ok ? Result::success() : Result::fail();
    }
    /**
     * 获取视频标签列表
     */
    public function getVideoTagLists(array $params): array
    {
        $limit = max(1, (int) ($params['limit'] ?? 20));
        try {
            if (! Schema::hasTable('video_tags')) {
                return Result::fail(admin_t('ui.migrate_first'));
            }
        } catch (\Throwable) {
            return Result::fail(admin_t('ui.migrate_first'));
        }

        $q = VideoTagModel::query();
        $name = trim((string) ($params['name'] ?? ''));
        if ($name !== '') {
            $q->where('name', 'like', '%'.$name.'%');
        }
        if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
            $q->where('status', (int) $params['status']);
        }
        if ((string) ($params['unused'] ?? '') === '1') {
            $q->whereDoesntHave('videos');
        }
        $page = $q->orderByDesc('sort')->orderByDesc('id')->paginate($limit);
        $rows = collect($page->items())->map(function ($row) {
            $arr = $row->toArray();
            $arr['created_at_text'] = ! empty($arr['created_at']) ? date('Y-m-d H:i:s', (int) $arr['created_at']) : '';

            return $arr;
        })->all();

        $ids = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        $counts = [];
        if ($ids !== [] && Schema::hasTable('video_tag_rel')) {
            $countRows = VideoTagRelModel::query()
                ->selectRaw('tag_id, COUNT(*) as c')
                ->whereIn('tag_id', $ids)
                ->groupBy('tag_id')
                ->get();
            foreach ($countRows as $row) {
                $counts[(int) $row->tag_id] = (int) $row->c;
            }
        }
        foreach ($rows as &$row) {
            $row['video_count'] = $counts[(int) ($row['id'] ?? 0)] ?? 0;
        }
        unset($row);

        return Result::success(AdminPage::of($page, $rows));
    }
    /**
     * 保存视频标签
     */
    public function saveVideoTag(array $data, ?int $id = null): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        if (($id === null || $id < 1) && $name === '') {
            return Result::fail(admin_t('ui.need_tag_name'));
        }

        $now = time();
        $candidate = [
            'updated_at' => $now,
        ];
        if ($name !== '') {
            $candidate['name'] = $name;
        }
        if (array_key_exists('slug', $data)) {
            $candidate['slug'] = trim((string) $data['slug']);
        }
        if (array_key_exists('sort', $data)) {
            $candidate['sort'] = (int) $data['sort'];
        }
        if (array_key_exists('status', $data)) {
            $candidate['status'] = (int) $data['status'];
        }
        if ($id === null || $id < 1)
        {
            $candidate['created_at'] = $now;
        }
        $payload = $this->filterPayloadByTableColumns('video_tags', $candidate);

        if ($id !== null && $id > 0)
        {
            $exists = $this->videoTagModel->findById($id);
            if (empty($exists))
            {
                return Result::fail(admin_t('ui.data_missing'));
            }
            $ok = $this->videoTagModel->updateById($id, $payload);
            return $ok ? Result::success() : Result::fail();
        }

        $newId = $this->videoTagModel->insertsGetId($payload);
        return $newId ? Result::success(['id' => (int)$newId]) : Result::fail();
    }
    /**
     * 删除视频标签
     */
    public function deleteVideoTag(int $id): array
    {
        $exists = $this->videoTagModel->findById($id);
        if (empty($exists))
        {
            return Result::fail(admin_t('ui.data_missing'));
        }

        $this->videoTagRelModel->deleteByCondition([['tag_id', '=', $id]]);

        $ok = $this->videoTagModel->deleteById($id);
        return $ok ? Result::success() : Result::fail();
    }

    /**
     * @param list<int|string> $ids
     */
    public function batchTags(array $ids, string $action, mixed $value = ''): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return Result::fail(admin_t('ui.please_select_tags'));
        }
        $ok = 0;
        $fail = 0;
        foreach ($ids as $id) {
            $res = match ($action) {
                'status' => $this->saveVideoTag(['status' => (int) $value], $id),
                'delete' => $this->deleteVideoTag($id),
                default => Result::fail(admin_t('ui.unsupported_op')),
            };
            if (($res['code'] ?? 1) === 0) {
                $ok++;
            } else {
                $fail++;
            }
        }
        if ($ok === 0) {
            return Result::fail(admin_t('ui.op_fail'));
        }

        return Result::success(['ok' => $ok, 'fail' => $fail], $fail > 0 ? admin_t('ui.batch_n_unhandled', ['ok' => $ok, 'fail' => $fail]) : admin_t('ui.op_ok'));
    }
    /**
     * 获取视频演员列表
     */
    public function getActorLists(array $params): array
    {
        $limit = max(1, (int) ($params['limit'] ?? 20));
        try {
            if (! Schema::hasTable('actors')) {
                return Result::fail(admin_t('ui.migrate_first'));
            }
        } catch (\Throwable) {
            return Result::fail(admin_t('ui.migrate_first'));
        }

        $q = ActorModel::query();
        if (array_key_exists('id', $params) && $params['id'] !== '' && $params['id'] !== null) {
            $q->where('id', (int) $params['id']);
        }
        $name = trim((string) ($params['name'] ?? ''));
        if ($name !== '') {
            $q->where('name', 'like', '%'.$name.'%');
        }
        if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
            $q->where('status', (int) $params['status']);
        }
        if ((string) ($params['empty_pic'] ?? '') === '1') {
            $q->where(function ($inner) {
                $inner->whereNull('avatar')->orWhere('avatar', '');
            });
        }
        if ((string) ($params['repeat'] ?? '') === '1') {
            $dups = ActorModel::query()->select('name')->groupBy('name')->havingRaw('COUNT(*) > 1')->pluck('name');
            $q->whereIn('name', $dups);
        }
        $page = $q->orderByDesc('sort')->orderByDesc('id')->paginate($limit);
        $rows = collect($page->items())->map(function ($row) {
            $arr = $row->toArray();
            $arr['created_at_text'] = ! empty($arr['created_at']) ? date('Y-m-d H:i:s', (int) $arr['created_at']) : '';
            $arr['has_avatar'] = trim((string) ($arr['avatar'] ?? '')) !== '';

            return $arr;
        })->all();

        $ids = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        $counts = [];
        if ($ids !== [] && Schema::hasTable('video_actor_rel')) {
            $countRows = VideoActorRelModel::query()
                ->selectRaw('actor_id, COUNT(*) as c')
                ->whereIn('actor_id', $ids)
                ->groupBy('actor_id')
                ->get();
            foreach ($countRows as $row) {
                $counts[(int) $row->actor_id] = (int) $row->c;
            }
        }
        foreach ($rows as &$row) {
            $row['video_count'] = $counts[(int) ($row['id'] ?? 0)] ?? 0;
            $sex = trim((string) ($row['sex'] ?? ''));
            $row['sex_label'] = match ($sex) {
                '男' => admin_t('ui.gender_m'),
                '女' => admin_t('ui.gender_f'),
                default => $sex,
            };
        }
        unset($row);

        return Result::success(AdminPage::of($page, $rows));
    }
    /**
     * 保存视频演员
     */
    public function saveActor(array $data, ?int $id = null): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        if (($id === null || $id < 1) && $name === '') {
            return Result::fail(admin_t('ui.need_actor_name'));
        }

        $now = time();
        $candidate = [
            'slug' => trim((string) ($data['slug'] ?? '')),
            'avatar' => (string) ($data['avatar'] ?? ''),
            'sex' => trim((string) ($data['sex'] ?? '')),
            'area' => trim((string) ($data['area'] ?? '')),
            'birthday' => trim((string) ($data['birthday'] ?? '')),
            'content' => (string) ($data['content'] ?? ''),
            'sort' => (int) ($data['sort'] ?? 0),
            'status' => (int) ($data['status'] ?? 1),
            'updated_at' => $now,
        ];
        if ($name !== '') {
            $candidate['name'] = $name;
        }
        if (! array_key_exists('status', $data)) {
            unset($candidate['status']);
        }
        if (! array_key_exists('sort', $data)) {
            unset($candidate['sort']);
        }
        if (! array_key_exists('slug', $data)) {
            unset($candidate['slug']);
        }
        if (! array_key_exists('avatar', $data)) {
            unset($candidate['avatar']);
        }
        if (! array_key_exists('sex', $data)) {
            unset($candidate['sex']);
        }
        if (! array_key_exists('area', $data)) {
            unset($candidate['area']);
        }
        if (! array_key_exists('birthday', $data)) {
            unset($candidate['birthday']);
        }
        if (! array_key_exists('content', $data)) {
            unset($candidate['content']);
        }
        if ($id === null || $id < 1)
        {
            $candidate['created_at'] = $now;
        }
        $payload = $this->filterPayloadByTableColumns('actors', $candidate);

        if ($id !== null && $id > 0)
        {
            $exists = $this->actorModel->findById($id);
            if (empty($exists))
            {
                return Result::fail(admin_t('ui.data_missing'));
            }
            $ok = $this->actorModel->updateById($id, $payload);
            return $ok ? Result::success() : Result::fail();
        }

        $newId = $this->actorModel->insertsGetId($payload);
        return $newId ? Result::success(['id' => (int)$newId]) : Result::fail();
    }
    /**
     * 删除视频演员
     */
    public function deleteActor(int $id): array
    {
        $exists = $this->actorModel->findById($id);
        if (empty($exists))
        {
            return Result::fail(admin_t('ui.data_missing'));
        }

        $this->videoActorRelModel->deleteByCondition([['actor_id', '=', $id]]);

        $ok = $this->actorModel->deleteById($id);
        return $ok ? Result::success() : Result::fail();
    }

    /**
     * @param list<int|string> $ids
     */
    public function batchActors(array $ids, string $action, mixed $value = ''): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return Result::fail(admin_t('ui.please_select_actors'));
        }
        $ok = 0;
        $fail = 0;
        foreach ($ids as $id) {
            $res = match ($action) {
                'status' => $this->saveActor(['status' => (int) $value], $id),
                'delete' => $this->deleteActor($id),
                default => Result::fail(admin_t('ui.unsupported_op')),
            };
            if (($res['code'] ?? 1) === 0) {
                $ok++;
            } else {
                $fail++;
            }
        }
        if ($ok === 0) {
            return Result::fail(admin_t('ui.op_fail'));
        }

        return Result::success(['ok' => $ok, 'fail' => $fail], $fail > 0 ? admin_t('ui.batch_n_unhandled', ['ok' => $ok, 'fail' => $fail]) : admin_t('ui.op_ok'));
    }
    /**
     * 获取视频采集源列表
     */
    public function getSourceLists(int $videoId, int $limit): array
    {
        $where = [
            ['video_id', '=', $videoId],
        ];

        $data = $this->videoSourceModel->paginates($where, '*', $limit, ['sort' => 'desc', 'id' => 'desc']);

        $sourceIds = [];
        foreach (($data['data'] ?? []) as $row)
        {
            $sourceIds[] = (int)$row['id'];
        }

        $episodeCountMap = [];
        if (!empty($sourceIds))
        {
            $counts = $this->videoEpisodeModel->groupByCondition([['source_id', 'in', $sourceIds]], ['source_id', DB::raw('COUNT(id) as total')], 'source_id');
            foreach ($counts as $c)
            {
                $episodeCountMap[(int)$c['source_id']] = (int)$c['total'];
            }
        }

        if (!empty($data['data']) && is_array($data['data']))
        {
            foreach ($data['data'] as &$item)
            {
                $sid = (int)($item['id'] ?? 0);
                $item['episode_total'] = (int)($episodeCountMap[$sid] ?? 0);
                $createdTs = (int)($item['created_at'] ?? ($item['create_time'] ?? 0));
                $updatedTs = (int)($item['updated_at'] ?? ($item['update_time'] ?? 0));
                $item['created_at_text'] = $createdTs > 0 ? date('Y-m-d H:i:s', $createdTs) : '';
                $item['updated_at_text'] = $updatedTs > 0 ? date('Y-m-d H:i:s', $updatedTs) : '';
            }
            unset($item);
        }

        return Result::success($data);
    }
    /**
     * 保存视频采集源
     */
    public function saveSource(array $data, ?int $id = null): array
    {
        $videoId = (int)($data['video_id'] ?? 0);
        if ($videoId < 1)
        {
            return Result::fail(admin_t('ui.sched_bad_param'));
        }

        $name = trim((string)($data['name'] ?? ''));
        if ($name === '')
        {
            return Result::fail(admin_t('ui.need_line_name'));
        }

        $now = time();
        $payload = [
            'video_id' => $videoId,
            'name' => $name,
            'type' => (string)($data['type'] ?? 'm3u8'),
            'sort' => (int)($data['sort'] ?? 0),
            'updated_at' => $now,
        ];
        if (array_key_exists('player', $data) && Schema::hasColumn('video_sources', 'player')) {
            $payload['player'] = trim((string) $data['player']);
        }
        if (array_key_exists('downer', $data) && Schema::hasColumn('video_sources', 'downer')) {
            $payload['downer'] = trim((string) $data['downer']);
        }
        if (array_key_exists('server_id', $data) && Schema::hasColumn('video_sources', 'server_id')) {
            $payload['server_id'] = max(0, (int) $data['server_id']);
        }

        if ($id !== null && $id > 0)
        {
            $exists = $this->videoSourceModel->findById($id);
            if (empty($exists))
             {
                return Result::fail(admin_t('ui.data_missing'));
            }
            $ok = $this->videoSourceModel->updateById($id, $payload);
            return $ok ? Result::success() : Result::fail();
        }

        $payload['created_at'] = $now;
        $newId = $this->videoSourceModel->insertsGetId($payload);
        return $newId ? Result::success(['id' => (int)$newId]) : Result::fail();
    }
    /**
     * 删除视频采集源
     */
    public function deleteSource(int $id): array
    {
        $exists = $this->videoSourceModel->findById($id);
        if (empty($exists))
        {
            return Result::fail(admin_t('ui.data_missing'));
        }
        $ok = $this->videoSourceModel->deleteById($id);
        return $ok ? Result::success() : Result::fail();
    }

    /**
     * 获取视频采集源剧集列表
     */
    public function getEpisodeLists(int $sourceId, int $limit): array
    {
        $where = [
            ['source_id', '=', $sourceId],
        ];

        $data = $this->videoEpisodeModel->paginates($where, '*', $limit, ['episode_num' => 'asc', 'id' => 'asc']);

        if (!empty($data['data']) && is_array($data['data'])) {
            foreach ($data['data'] as &$item)
            {
                $createdTs = (int)($item['created_at'] ?? ($item['create_time'] ?? 0));
                $updatedTs = (int)($item['updated_at'] ?? ($item['update_time'] ?? 0));
                $item['created_at_text'] = $createdTs > 0 ? date('Y-m-d H:i:s', $createdTs) : '';
                $item['updated_at_text'] = $updatedTs > 0 ? date('Y-m-d H:i:s', $updatedTs) : '';
            }
            unset($item);
        }

        return Result::success($data);
    }

    /**
     * 保存视频采集源剧集
     */
    public function saveEpisode(array $data, ?int $id = null): array
    {
        $sourceId = (int)($data['source_id'] ?? 0);
        if ($sourceId < 1)
        {
            return Result::fail(admin_t('ui.sched_bad_param'));
        }

        $source = $this->videoSourceModel->findById($sourceId);
        if (empty($source))
        {
            return Result::fail(admin_t('ui.line_missing'));
        }

        $episodeNum = (int)($data['episode_num'] ?? 1);
        if ($episodeNum < 1)
        {
            $episodeNum = 1;
        }

        $url = trim((string)($data['url'] ?? ''));
        if ($url === '')
        {
            return Result::fail(admin_t('ui.need_play_url'));
        }

        $now = time();
        $payload = [
            'video_id' => (int)$source['video_id'],
            'source_id' => $sourceId,
            'episode_name' => (string)($data['episode_name'] ?? ''),
            'episode_num' => $episodeNum,
            'url' => $url,
            'duration' => (int)($data['duration'] ?? 0),
            'status' => (int)($data['status'] ?? 1),
            'sort' => (int)($data['sort'] ?? 0),
            'updated_at' => $now,
        ];

        if ($id !== null && $id > 0)
        {
            $exists = $this->videoEpisodeModel->findById($id);
            if (empty($exists))
            {
                return Result::fail(admin_t('ui.data_missing'));
            }
            $ok = $this->videoEpisodeModel->updateById($id, $payload);
            return $ok ? Result::success() : Result::fail();
        }

        $payload['created_at'] = $now;
        $newId = $this->videoEpisodeModel->insertsGetId($payload);
        return $newId ? Result::success(['id' => (int)$newId]) : Result::fail();
    }
    /**
     * 删除视频采集源剧集
     */
    public function deleteEpisode(int $id): array
    {
        $exists = $this->videoEpisodeModel->findById($id);
        if (empty($exists))
        {
            return Result::fail(admin_t('ui.data_missing'));
        }
        $ok = $this->videoEpisodeModel->deleteById($id);
        return $ok ? Result::success() : Result::fail();
    }
    /**
     * 获取视频采集源剧集有效字段
     */
    private function getTableColumns(string $table): array
    {
        if (isset(self::$tableColumnsCache[$table]))
        {
            return self::$tableColumnsCache[$table];
        }

        if (!preg_match('/^[A-Za-z0-9_]+$/', $table))
        {
            self::$tableColumnsCache[$table] = [];
            return [];
        }

        try {
            $cols = Schema::hasTable($table) ? Schema::getColumnListing($table) : [];
        } catch (\Throwable) {
            $cols = [];
        }
        self::$tableColumnsCache[$table] = $cols;
        return $cols;
    }
    /**
     * 过滤视频采集源剧集有效字段
     */
    private function filterPayloadByTableColumns(string $table, array $payload): array
    {
        $cols = $this->getTableColumns($table);
        if (empty($cols))
        {
            return [];
        }

        $flip = array_fill_keys($cols, true);
        $filtered = [];
        foreach ($payload as $k => $v)
        {
            if (isset($flip[$k]))
            {
                $filtered[$k] = $v;
            }
        }
        return $filtered;
    }

    private function toUnix(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }
        if (is_numeric($value)) {
            return (int) $value;
        }
        $ts = strtotime((string) $value);

        return $ts !== false ? $ts : 0;
    }

    /**
     * 同步视频采集源剧集标签
     */
    private function syncTags(int $videoId, string $tagsText): void
    {
        $names = array_filter(array_map(function ($v) {
            return trim((string)$v);
        }, preg_split('/[,，\s]+/u', $tagsText) ?: []));

        $names = array_values(array_unique($names));

        $this->videoTagRelModel->deleteByCondition([['video_id', '=', $videoId]]);

        if (empty($names))
        {
            return;
        }

        $existing = $this->videoTagModel->selectByCondition([['name', 'in', $names]], ['id', 'name']);
        $tagMap = [];
        foreach ($existing as $t)
        {
            $tagMap[(string)$t['name']] = (int)$t['id'];
        }

        $now = time();
        foreach ($names as $name)
        {
            if (!isset($tagMap[$name]))
            {
                $id = $this->videoTagModel->insertsGetId(['name' => $name, 'created_at' => $now]);
                if ($id) {
                    $tagMap[$name] = (int)$id;
                }
            }
        }

        $rows = [];
        foreach ($names as $name)
        {
            if (!empty($tagMap[$name]))
            {
                $rows[] = ['video_id' => $videoId, 'tag_id' => (int)$tagMap[$name], 'created_at' => $now];
            }
        }
        if (!empty($rows))
        {
            $this->videoTagRelModel->inserts($rows);
        }
    }
    /**
     * 同步视频采集源剧集演员
     */
    private function syncActors(int $videoId, string $actorsText): void
    {
        $names = array_filter(array_map(function ($v) {
            return trim((string)$v);
        }, preg_split('/[,，\s]+/u', $actorsText) ?: []));
        $names = array_values(array_unique($names));

        $this->videoActorRelModel->deleteByCondition([
            ['video_id', '=', $videoId],
            ['role_type', '=', 1],
        ]);

        if (empty($names))
        {
            return;
        }

        $existing = $this->actorModel->selectByCondition([['name', 'in', $names]], ['id', 'name']);
        $actorMap = [];
        foreach ($existing as $a)
        {
            $actorMap[(string)$a['name']] = (int)$a['id'];
        }

        $now = time();
        foreach ($names as $name)
        {
            if (!isset($actorMap[$name]))
            {
                $id = $this->actorModel->insertsGetId(['name' => $name, 'avatar' => '', 'created_at' => $now]);
                if ($id) {
                    $actorMap[$name] = (int)$id;
                }
            }
        }

        $rows = [];
        $sort = count($names);
        foreach ($names as $name)
        {
            if (!empty($actorMap[$name]))
            {
                $rows[] = [
                    'video_id' => $videoId,
                    'actor_id' => (int)$actorMap[$name],
                    'role_type' => 1,
                    'role_name' => '',
                    'sort' => $sort--,
                    'created_at' => $now,
                ];
            }
        }
        if (!empty($rows))
        {
            $this->videoActorRelModel->inserts($rows);
        }
    }

    /**
     * 批量改状态 / 推荐 / 锁定 / 分类 / 删除
     */
    public function batchVideos(mixed $ids, string $action, mixed $value = null): array
    {
        $ids = VideoMeta::ids($ids);
        if ($ids === []) {
            return Result::fail(admin_t('ui.please_select_rows'));
        }

        $now = time();
        $count = count($ids);
        if ($action === 'delete') {
            AdminOpLog::quiet(function () use ($ids) {
                foreach ($ids as $id) {
                    $this->deleteVideo($id);
                }
            });

            return AdminOpLog::ifOk(Result::success(['count' => $count]), 'batch', '批量删除了 '.$count.' 部影片', [
                'module' => '影片',
                'target_type' => 'videos',
                'payload' => ['count' => $count, 'action' => 'delete'],
            ]);
        }
        if ($action === 'restore') {
            foreach ($ids as $id) {
                $this->restoreVideo($id);
            }

            return AdminOpLog::ifOk(Result::success(['count' => $count]), 'batch', '从回收站恢复了 '.$count.' 部影片', [
                'module' => '影片',
                'target_type' => 'videos',
                'payload' => ['count' => $count, 'action' => 'restore'],
            ]);
        }
        if ($action === 'purge') {
            AdminOpLog::quiet(function () use ($ids) {
                foreach ($ids as $id) {
                    $this->purgeVideo($id);
                }
            });

            return AdminOpLog::ifOk(Result::success(['count' => $count]), 'batch', '彻底删除了 '.$count.' 部影片', [
                'module' => '影片',
                'target_type' => 'videos',
                'payload' => ['count' => $count, 'action' => 'purge'],
            ]);
        }
        if ($action === 'merge') {
            return $this->mergeVideos($ids, (int) $value);
        }
        if ($action === 'replace_url') {
            $replaced = app(\App\Services\Video\SiteOpsService::class)->replacePlayUrl('', '', $ids, (string) $value);

            return AdminOpLog::ifOk($replaced, 'batch', '批量替换了 '.$count.' 部影片的播放地址', [
                'module' => '影片',
                'target_type' => 'videos',
                'payload' => ['count' => $count, 'action' => 'replace_url'],
            ]);
        }

        $payload = ['updated_at' => $now];
        match ($action) {
            'status' => $payload['status'] = (int) $value,
            'recommend' => $payload['is_recommend'] = (int) $value,
            'hot' => $payload['is_hot'] = (int) $value,
            'lock' => $payload['lock'] = (int) $value,
            'type' => $payload['type_id'] = (int) $value,
            'points' => $payload['points'] = (int) $value,
            default => null,
        };
        if (count($payload) === 1) {
            return Result::fail(admin_t('ui.unsupported_op'));
        }
        if ($action === 'type') {
            $type = $this->videoTypeModel->findById((int) $value);
            $payload['type_pid'] = (int) ($type['parent_id'] ?? 0);
        }

        $ok = $this->videoModel->updateByCondition([['id', 'in', $ids]], $payload);

        $label = match ($action) {
            'status' => ((int) $value === 1 ? '批量上架了 ' : '批量下架了 ').$count.' 部影片',
            'recommend' => ((int) $value === 1 ? '批量推荐了 ' : '批量取消推荐了 ').$count.' 部影片',
            'hot' => ((int) $value === 1 ? '批量标热了 ' : '批量取消热门了 ').$count.' 部影片',
            'lock' => ((int) $value === 1 ? '批量锁定了 ' : '批量解锁了 ').$count.' 部影片',
            'type' => '批量改了 '.$count.' 部影片的分类',
            'points' => '批量改了 '.$count.' 部影片的积分',
            default => '批量处理了 '.$count.' 部影片',
        };

        return $ok
            ? AdminOpLog::ifOk(Result::success(['count' => $count]), 'batch', $label, [
                'module' => '影片',
                'target_type' => 'videos',
                'payload' => ['count' => $count, 'action' => $action],
            ])
            : Result::fail();
    }

    public function mergeVideos(array $ids, int $keepId = 0): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (count($ids) < 2) {
            return Result::fail(admin_t('ui.please_select_two_videos'));
        }
        if ($keepId < 1 || ! in_array($keepId, $ids, true)) {
            $keepId = min($ids);
        }
        $keep = $this->videoModel->findById($keepId);
        if (empty($keep)) {
            return Result::fail(admin_t('ui.keep_video_missing'));
        }
        $drop = array_values(array_filter($ids, fn ($id) => $id !== $keepId));
        AdminOpLog::quiet(function () use ($drop, $keepId) {
            foreach ($drop as $id) {
                $this->videoSourceModel->updateByCondition([['video_id', '=', $id]], ['video_id' => $keepId, 'updated_at' => time()]);
                $this->videoEpisodeModel->updateByCondition([['video_id', '=', $id]], ['video_id' => $keepId]);
                $this->deleteVideo($id);
            }
        });

        return AdminOpLog::ifOk(
            Result::success(['keep_id' => $keepId, 'merged' => count($drop)], admin_t('ui.merged_to_id', ['id' => $keepId])),
            'batch',
            '合并了 '.count($drop).' 部影片到《'.(string) ($keep['title'] ?? '').'》',
            ['module' => '影片', 'target_type' => 'videos', 'target_id' => $keepId]
        );
    }

    public function disableSource(int $sourceId): array
    {
        $row = $this->videoSourceModel->findById($sourceId);
        if (empty($row)) {
            return Result::fail(admin_t('ui.line_missing'));
        }
        $ok = $this->videoSourceModel->updateById($sourceId, ['status' => 0, 'updated_at' => time()]);
        $name = (string) ($row['name'] ?? '');

        return $ok
            ? AdminOpLog::ifOk(Result::success([], admin_t('ui.line_offlined')), 'save', '下线了线路'.($name !== '' ? '《'.$name.'》' : ' #'.$sourceId), [
                'module' => '线路',
                'target_type' => 'sources',
                'target_id' => $sourceId,
            ])
            : Result::fail();
    }
}
