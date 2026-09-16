<?php

namespace App\Services\Admin\Video;

use App\Models\Video\ActorModel;
use App\Models\Video\CollectSourceModel;
use App\Models\Video\VideoActorRelModel;
use App\Models\Video\VideoEpisodeModel;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoSourceModel;
use App\Models\Video\VideoStatModel;
use App\Models\Video\VideoTagModel;
use App\Models\Video\VideoTagRelModel;
use App\Models\Video\VideoTypeModel;
use App\Support\Utils\Result;
use App\Support\VideoMeta;
use Exception;
use Illuminate\Support\Facades\DB;

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

        $limit = (int)($params['limit'] ?? 10);
        if ($limit < 1) {
            $limit = 10;
        }

        $data = $this->videoModel->paginates($where, '*', $limit, ['id' => 'desc']);

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
        if (!empty($videoIds))
        {
            $stats = $this->videoStatModel->selectByCondition([['video_id', 'in', $videoIds]], ['video_id', 'hits']);
            foreach ($stats as $s)
            {
                $statMap[(int)$s['video_id']] = $s;
            }
        }

        if (!empty($data['data']) && is_array($data['data']))
        {
            foreach ($data['data'] as &$item)
        {
            $tid = (int)($item['type_id'] ?? 0);
            $item['type_name'] = $tid > 0 && isset($typeMap[$tid]) ? (string)$typeMap[$tid]['name'] : '';
            $item['hits'] = (int)($statMap[(int)$item['id']]['hits'] ?? 0);
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
     * 获取视频详情
     */
    public function getVideoInfo(int $id): array
    {
        $info = $this->videoModel->findById($id);
        if (empty($info)) {
            return Result::fail('数据不存在');
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
            return Result::fail('标题不能为空');
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
        if (!empty($payload['type_id'])) {
            $type = $this->videoTypeModel->findById((int) $payload['type_id']);
            $payload['type_pid'] = (int) ($type['parent_id'] ?? 0);
        }

        $tagsText = (string)($data['tags_text'] ?? '');
        $actorsText = (string)($data['actors_text'] ?? '');

        try {
            return DB::transaction(function () use ($id, $payload, $now, $tagsText, $actorsText) {
                if ($id)
                {
                    $exists = $this->videoModel->findById($id);
                    if (empty($exists))
                    {
                        return Result::fail('数据不存在');
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
            return Result::fail($e->getMessage() ?: '操作失败');
        }
    }
    /**
     * 删除视频
     */
    public function deleteVideo(int $id): array
    {
        $info = $this->videoModel->findById($id);
        if (empty($info))
        {
            return Result::fail('数据不存在');
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

                $ok = $this->videoModel->deleteById($id);
                if (!$ok) {
                    return Result::fail();
                }

                return Result::success();
            });
        } catch (Exception $e)
        {
            return Result::fail($e->getMessage() ?: '删除失败');
        }
    }
    /**
     * 获取视频类型选项
     */
    public function getTypeOptions(): array
    {
        $all = $this->videoTypeModel->selectByCondition([], ['id', 'parent_id', 'name'], ['sort' => 'desc', 'id' => 'asc']);
        $byParent = [];
        foreach ($all as $row)
        {
            $pid = (int)($row['parent_id'] ?? 0);
            $byParent[$pid][] = $row;
        }

        $options = [];
        foreach (($byParent[0] ?? []) as $p)
        {
            $options[] = ['id' => (int)$p['id'], 'name' => (string)$p['name']];
            foreach (($byParent[(int)$p['id']] ?? []) as $c)
            {
                $options[] = ['id' => (int)$c['id'], 'name' => '— ' . (string)$c['name']];
            }
        }

        return Result::success($options);
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
        $limit = (int)($params['limit'] ?? 10);
        if ($limit < 1) {
            $limit = 10;
        }

        $where = [];
        $name = trim((string)($params['name'] ?? ''));
        if ($name !== '')
        {
            $where['name'] = ['like' => "%{$name}%"];
        }

        if (array_key_exists('parent_id', $params) && $params['parent_id'] !== '' && $params['parent_id'] !== null)
        {
            $where['parent_id'] = (int)$params['parent_id'];
        }

        $data = $this->videoTypeModel->paginates($where, '*', $limit, ['sort' => 'desc', 'id' => 'desc']);
        $rows = $data['data'] ?? [];

        $parentIds = [];
        foreach ($rows as $r)
        {
            $pid = (int)($r['parent_id'] ?? 0);
            if ($pid > 0) {
                $parentIds[] = $pid;
            }
        }
        $parentIds = array_values(array_unique($parentIds));

        $parentMap = [];
        if (!empty($parentIds))
        {
            $parents = $this->videoTypeModel->selectByCondition([['id', 'in', $parentIds]], ['id', 'name']);
            foreach ($parents as $p)
            {
                $parentMap[(int)$p['id']] = (string)($p['name'] ?? '');
            }
        }

        foreach ($rows as &$item)
        {
            $pid = (int)($item['parent_id'] ?? 0);
            $item['parent_name'] = $pid > 0 ? (string)($parentMap[$pid] ?? '') : '顶级';
            $item['created_at_text'] = !empty($item['created_at']) ? date('Y-m-d H:i:s', (int)$item['created_at']) : '';
            $item['updated_at_text'] = !empty($item['updated_at']) ? date('Y-m-d H:i:s', (int)$item['updated_at']) : '';
        }
        unset($item);

        $data['data'] = $rows;
        return Result::success($data);
    }
    /**
     * 保存视频类型
     */
    public function saveVideoType(array $data, ?int $id = null): array
    {
        $name = trim((string)($data['name'] ?? ''));
        if ($name === '')
        {
            return Result::fail('分类名称不能为空');
        }

        $now = time();
        $candidate = [
            'name' => $name,
            'parent_id' => (int)($data['parent_id'] ?? 0),
            'sort' => (int)($data['sort'] ?? 0),
            'status' => (int)($data['status'] ?? 1),
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
                return Result::fail('数据不存在');
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
            return Result::fail('数据不存在');
        }

        $childCount = $this->videoTypeModel->countByCondition([['parent_id', '=', $id]]);
        if ($childCount > 0)
        {
            return Result::fail('请先删除子分类');
        }

        $useCount = $this->videoModel->countByCondition([['type_id', '=', $id]]);
        if ($useCount > 0)
        {
            return Result::fail('该分类下存在视频，无法删除');
        }

        $ok = $this->videoTypeModel->deleteById($id);
        return $ok ? Result::success() : Result::fail();
    }
    /**
     * 获取采集源列表
     */
    public function getCollectSourceLists(array $params): array
    {
        $limit = (int)($params['limit'] ?? 10);
        if ($limit < 1) {
            $limit = 10;
        }

        $where = [];
        $name = trim((string)($params['name'] ?? ''));
        if ($name !== '')
        {
            $where['name'] = ['like' => "%{$name}%"];
        }
        if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null)
        {
            $where[] = ['status', '=', (int)$params['status']];
        }

        $data = $this->collectSourceModel->paginates($where, '*', $limit, ['sort' => 'desc', 'id' => 'desc']);
        $rows = $data['data'] ?? [];

        foreach ($rows as &$item)
        {
            $item['created_at_text'] = !empty($item['created_at']) ? date('Y-m-d H:i:s', (int)$item['created_at']) : '';
            $item['updated_at_text'] = !empty($item['updated_at']) ? date('Y-m-d H:i:s', (int)$item['updated_at']) : '';
        }
        unset($item);

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
            return Result::fail('采集源名称不能为空');
        }

        $now = time();
        $candidate = [
            'name' => $name,
            'api_url' => (string)($data['api_url'] ?? ''),
            'api_type' => (string)($data['api_type'] ?? 'auto'),
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
                return Result::fail('数据不存在');
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
            return Result::fail('数据不存在');
        }

        $useCount = $this->videoModel->countByCondition([['collect_source_id', '=', $id]]);
        if ($useCount > 0)
        {
            return Result::fail('该采集源已被视频使用，无法删除');
        }

        $ok = $this->collectSourceModel->deleteById($id);
        return $ok ? Result::success() : Result::fail();
    }
    /**
     * 获取视频标签列表
     */
    public function getVideoTagLists(array $params): array
    {
        $limit = (int)($params['limit'] ?? 10);
        if ($limit < 1) {
            $limit = 10;
        }

        $where = [];
        $name = trim((string)($params['name'] ?? ''));
        if ($name !== '')
        {
            $where['name'] = ['like' => "%{$name}%"];
        }

        $data = $this->videoTagModel->paginates($where, '*', $limit, ['id' => 'desc']);
        $rows = $data['data'] ?? [];

        foreach ($rows as &$item)
        {
            $item['created_at_text'] = !empty($item['created_at']) ? date('Y-m-d H:i:s', (int)$item['created_at']) : '';
            $item['updated_at_text'] = !empty($item['updated_at']) ? date('Y-m-d H:i:s', (int)$item['updated_at']) : '';
        }
        unset($item);

        $data['data'] = $rows;
        return Result::success($data);
    }
    /**
     * 保存视频标签
     */
    public function saveVideoTag(array $data, ?int $id = null): array
    {
        $name = trim((string)($data['name'] ?? ''));
        if ($name === '')
        {
            return Result::fail('标签名称不能为空');
        }

        $now = time();
        $candidate = [
            'name' => $name,
            'sort' => (int)($data['sort'] ?? 0),
            'status' => (int)($data['status'] ?? 1),
            'updated_at' => $now,
        ];
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
                return Result::fail('数据不存在');
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
            return Result::fail('数据不存在');
        }

        $this->videoTagRelModel->deleteByCondition([['tag_id', '=', $id]]);

        $ok = $this->videoTagModel->deleteById($id);
        return $ok ? Result::success() : Result::fail();
    }
    /**
     * 获取视频演员列表
     */
    public function getActorLists(array $params): array
    {
        $limit = (int)($params['limit'] ?? 10);
        if ($limit < 1) {
            $limit = 10;
        }

        $where = [];
        $name = trim((string)($params['name'] ?? ''));
        if ($name !== '')
        {
            $where['name'] = ['like' => "%{$name}%"];
        }

        $data = $this->actorModel->paginates($where, '*', $limit, ['id' => 'desc']);
        $rows = $data['data'] ?? [];

        foreach ($rows as &$item)
        {
            $item['created_at_text'] = !empty($item['created_at']) ? date('Y-m-d H:i:s', (int)$item['created_at']) : '';
            $item['updated_at_text'] = !empty($item['updated_at']) ? date('Y-m-d H:i:s', (int)$item['updated_at']) : '';
        }
        unset($item);

        $data['data'] = $rows;
        return Result::success($data);
    }
    /**
     * 保存视频演员
     */
    public function saveActor(array $data, ?int $id = null): array
    {
        $name = trim((string)($data['name'] ?? ''));
        if ($name === '')
        {
            return Result::fail('演员名称不能为空');
        }

        $now = time();
        $candidate = [
            'name' => $name,
            'avatar' => (string)($data['avatar'] ?? ''),
            'sort' => (int)($data['sort'] ?? 0),
            'status' => (int)($data['status'] ?? 1),
            'updated_at' => $now,
        ];
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
                return Result::fail('数据不存在');
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
            return Result::fail('数据不存在');
        }

        $this->videoActorRelModel->deleteByCondition([['actor_id', '=', $id]]);

        $ok = $this->actorModel->deleteById($id);
        return $ok ? Result::success() : Result::fail();
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
            return Result::fail('参数错误');
        }

        $name = trim((string)($data['name'] ?? ''));
        if ($name === '')
        {
            return Result::fail('线路名不能为空');
        }

        $now = time();
        $payload = [
            'video_id' => $videoId,
            'name' => $name,
            'type' => (string)($data['type'] ?? 'm3u8'),
            'sort' => (int)($data['sort'] ?? 0),
            'updated_at' => $now,
        ];

        if ($id !== null && $id > 0)
        {
            $exists = $this->videoSourceModel->findById($id);
            if (empty($exists))
             {
                return Result::fail('数据不存在');
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
            return Result::fail('数据不存在');
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
            return Result::fail('参数错误');
        }

        $source = $this->videoSourceModel->findById($sourceId);
        if (empty($source))
        {
            return Result::fail('线路不存在');
        }

        $episodeNum = (int)($data['episode_num'] ?? 1);
        if ($episodeNum < 1)
        {
            $episodeNum = 1;
        }

        $url = trim((string)($data['url'] ?? ''));
        if ($url === '')
        {
            return Result::fail('播放地址不能为空');
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
                return Result::fail('数据不存在');
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
            return Result::fail('数据不存在');
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

        $rows = DB::select('SHOW FULL COLUMNS FROM `' . $table . '`');
        $cols = [];
        foreach ($rows as $r)
        {
            $col = (string)($r->Field ?? '');
            if ($col !== '') {
                $cols[] = $col;
            }
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
            return Result::fail('请选择数据');
        }

        $now = time();
        if ($action === 'delete') {
            foreach ($ids as $id) {
                $this->deleteVideo($id);
            }
            return Result::success(['count' => count($ids)]);
        }

        $payload = ['updated_at' => $now];
        match ($action) {
            'status' => $payload['status'] = (int) $value,
            'recommend' => $payload['is_recommend'] = (int) $value,
            'hot' => $payload['is_hot'] = (int) $value,
            'lock' => $payload['lock'] = (int) $value,
            'type' => $payload['type_id'] = (int) $value,
            default => null,
        };
        if (count($payload) === 1) {
            return Result::fail('不支持的批量操作');
        }
        if ($action === 'type') {
            $type = $this->videoTypeModel->findById((int) $value);
            $payload['type_pid'] = (int) ($type['parent_id'] ?? 0);
        }

        $ok = $this->videoModel->updateByCondition([['id', 'in', $ids]], $payload);

        return $ok ? Result::success(['count' => count($ids)]) : Result::fail();
    }
}
