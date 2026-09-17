<?php

namespace App\Http\Controllers\Admin\Video;

use App\Http\Controllers\Controller;
use App\Services\Admin\Video\VideoService;
use App\Support\Utils\Ajax;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 视频管理
 */
class Video extends Controller
{
    protected VideoService $videoService;

    public function __construct()
    {
        $this->videoService = new VideoService();
    }

    /**
     * 视频列表
     */
    public function index(): View|Factory
    {
        return view('admin.video.index', [
            'queues' => $this->videoService->queueCounts(),
        ]);
    }

    public function create(): View|Factory
    {
        return $this->formPage([]);
    }

    public function edit(int $id): View|Factory
    {
        $res = $this->videoService->getVideoInfo($id);
        if ((int) ($res['code'] ?? 1) !== 0) {
            abort(404);
        }

        return $this->formPage(is_array($res['data'] ?? null) ? $res['data'] : []);
    }

    /** @param array<string, mixed> $video */
    private function formPage(array $video): View|Factory
    {
        $types = $this->videoService->getTypeOptions()['data'] ?? [];
        $collects = $this->videoService->getCollectSourceOptions()['data'] ?? [];
        $settings = [];
        try {
            $settings = app(\App\Services\Video\VideoSettingService::class)->all();
        } catch (\Throwable) {
        }
        $dict = app(\App\Services\Admin\System\SysDictService::class);
        $split = static function (string $raw): array {
            return array_values(array_filter(array_map('trim', preg_split('/[,，]/u', $raw) ?: [])));
        };
        $ts = (int) ($video['publish_at'] ?? 0);
        $roles = [];
        $plots = [];
        $vid = (int) ($video['id'] ?? 0);
        if ($vid > 0) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('video_roles')) {
                    $roles = \App\Models\Video\VideoRole::query()
                        ->where('video_id', $vid)
                        ->orderByDesc('sort')
                        ->orderByDesc('id')
                        ->limit(20)
                        ->get(['id', 'name', 'actor_id', 'status'])
                        ->toArray();
                }
            } catch (\Throwable) {
                $roles = [];
            }
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('video_plots')) {
                    $plots = \App\Models\Video\VideoPlot::query()
                        ->where('video_id', $vid)
                        ->orderBy('episode_num')
                        ->orderBy('sort')
                        ->orderBy('id')
                        ->limit(20)
                        ->get(['id', 'episode_num', 'title', 'content'])
                        ->toArray();
                }
            } catch (\Throwable) {
                $plots = [];
            }
        }

        return view('admin.video.form', [
            'video' => $video,
            'isEdit' => $vid > 0,
            'types' => is_array($types) ? $types : [],
            'collects' => is_array($collects) ? $collects : [],
            'areas' => $dict->filterChoices('area') ?: $split((string) ($settings['filter_area'] ?? '')),
            'langs' => $dict->filterChoices('lang') ?: $split((string) ($settings['filter_lang'] ?? '')),
            'years' => $dict->filterChoices('year') ?: $split((string) ($settings['filter_year'] ?? '')),
            'publishAt' => $ts > 0 ? date('Y-m-d\TH:i', $ts) : '',
            'roles' => $roles,
            'plots' => $plots,
        ]);
    }

    /**
     * 获取视频列表
     */
    public function getVideoLists(Request $request): JsonResponse
    {
        $params = [
            'title' => (string)$request->input('title', ''),
            'q' => (string) $request->input('q', ''),
            'type_id' => $request->input('type_id', ''),
            'status' => $request->input('status', ''),
            'is_recommend' => $request->input('is_recommend', ''),
            'is_hot' => $request->input('is_hot', ''),
            'lock' => $request->input('lock', ''),
            'year' => (string) $request->input('year', ''),
            'area' => (string) $request->input('area', ''),
            'points_min' => $request->input('points_min', ''),
            'empty_url' => $request->input('empty_url', ''),
            'repeat' => $request->input('repeat', ''),
            'need_points' => $request->input('need_points', ''),
            'has_plot' => $request->input('has_plot', ''),
            'empty_pic' => $request->input('empty_pic', ''),
            'empty_content' => $request->input('empty_content', ''),
            'missing_ep' => $request->input('missing_ep', ''),
            'no_actor' => $request->input('no_actor', ''),
            'weekday' => (string) $request->input('weekday', ''),
            'trash' => $request->input('trash', ''),
            'actor_id' => $request->input('actor_id', ''),
            'tag_id' => $request->input('tag_id', ''),
            'limit' => (int)$request->input('limit', 10),
        ];

        $data = $this->videoService->getVideoLists($params);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 获取视频详情
     */
    public function getVideoInfo(Request $request): JsonResponse
    {
        $id = (int)$request->input('id', 0);
        $data = $this->videoService->getVideoInfo($id);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 保存视频
     */
    public function saveVideo(Request $request): JsonResponse
    {
        $id = $request->has('id') ? (int)$request->input('id', 0) : null;
        if ($id !== null && $id < 1) {
            $id = null;
        }

        $payload = [
            'title' => (string)$request->input('title', ''),
            'subtitle' => (string)$request->input('subtitle', ''),
            'cover' => (string)$request->input('cover', ''),
            'banner' => (string)$request->input('banner', ''),
            'type_id' => $request->input('type_id', ''),
            'area' => (string)$request->input('area', ''),
            'lang' => (string)$request->input('lang', ''),
            'year' => (string)$request->input('year', ''),
            'class' => (string)$request->input('class', ''),
            'director' => (string)$request->input('director', ''),
            'remarks' => (string)$request->input('remarks', ''),
            'description' => (string)$request->input('description', ''),
            'status' => (int)$request->input('status', 1),
            'lock' => (int)$request->input('lock', 0),
            'points' => (int)$request->input('points', 0),
            'is_recommend' => (int)$request->input('is_recommend', 0),
            'is_hot' => (int)$request->input('is_hot', 0),
            'score' => $request->input('score', 0),
            'collect_id' => (string)$request->input('collect_id', ''),
            'collect_source_id' => $request->input('collect_source_id', ''),
            'sort' => (int)$request->input('sort', 0),
            'weekday' => (string) $request->input('weekday', ''),
            'publish_at' => $request->input('publish_at', 0),
            'tags_text' => (string)$request->input('tags_text', ''),
            'actors_text' => (string)$request->input('actors_text', ''),
        ];

        $data = $this->videoService->saveVideo($payload, $id);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 删除视频
     */
    public function deleteVideo(Request $request): JsonResponse
    {
        $id = (int)$request->input('id', 0);
        $data = $this->videoService->deleteVideo($id);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 批量操作
     */
    public function batchVideo(Request $request): JsonResponse
    {
        $data = $this->videoService->batchVideos(
            $request->input('ids', []),
            (string) $request->input('action', ''),
            $request->input('value', '')
        );

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function disableSource(Request $request): JsonResponse
    {
        $data = $this->videoService->disableSource((int) $request->input('id', 0));

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    /**
     * 获取视频类型选项
     */
    public function getTypeOptions(): JsonResponse
    {
        $data = $this->videoService->getTypeOptions();
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 获取采集源选项
     */
    public function getCollectSourceOptions(): JsonResponse
    {
        $data = $this->videoService->getCollectSourceOptions();
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 显示视频采集源
     */
    public function showSources(Request $request): View|Factory
    {
        $videoId = (int)$request->input('video_id', 0);
        $downloaders = [];
        $servers = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('video_downloaders')) {
                $downloaders = \App\Models\Video\VideoDownloader::query()->where('status', 1)->orderByDesc('sort')->orderBy('id')->get(['id', 'code', 'name'])->toArray();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('video_servers')) {
                $servers = \App\Models\Video\VideoServer::query()->where('status', 1)->orderByDesc('sort')->orderBy('id')->get(['id', 'name', 'url'])->toArray();
            }
        } catch (\Throwable) {
        }

        return view('admin.video.source', compact('videoId', 'downloaders', 'servers'));
    }

    /**
     * 获取视频采集源列表
     */
    public function getSourceLists(Request $request): JsonResponse
    {
        $videoId = (int)$request->input('video_id', 0);
        $limit = (int)$request->input('limit', 10);
        $data = $this->videoService->getSourceLists($videoId, $limit);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 保存视频采集源
     */
    public function saveSource(Request $request): JsonResponse
    {
        $id = $request->has('id') ? (int)$request->input('id', 0) : null;
        if ($id !== null && $id < 1) {
            $id = null;
        }

        $payload = [
            'video_id' => (int)$request->input('video_id', 0),
            'name' => (string)$request->input('name', ''),
            'type' => (string)$request->input('type', 'm3u8'),
            'player' => (string)$request->input('player', ''),
            'downer' => (string)$request->input('downer', ''),
            'server_id' => (int)$request->input('server_id', 0),
            'sort' => (int)$request->input('sort', 0),
        ];

        $data = $this->videoService->saveSource($payload, $id);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 删除视频采集源
     */
    public function deleteSource(Request $request): JsonResponse
    {
        $id = (int)$request->input('id', 0);
        $data = $this->videoService->deleteSource($id);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 显示视频剧集
     */
    public function showEpisodes(Request $request): View|Factory
    {
        $sourceId = (int)$request->input('source_id', 0);
        return view('admin.video.episode', compact('sourceId'));
    }

    /**
     * 获取视频剧集列表
     */
    public function getEpisodeLists(Request $request): JsonResponse
    {
        $sourceId = (int)$request->input('source_id', 0);
        $limit = (int)$request->input('limit', 10);
        $data = $this->videoService->getEpisodeLists($sourceId, $limit);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 保存视频剧集
     */
    public function saveEpisode(Request $request): JsonResponse
    {
        $id = $request->has('id') ? (int)$request->input('id', 0) : null;
        if ($id !== null && $id < 1) {
            $id = null;
        }

        $payload = [
            'source_id' => (int)$request->input('source_id', 0),
            'episode_name' => (string)$request->input('episode_name', ''),
            'episode_num' => (int)$request->input('episode_num', 1),
            'url' => (string)$request->input('url', ''),
            'duration' => (int)$request->input('duration', 0),
            'status' => (int)$request->input('status', 1),
            'sort' => (int)$request->input('sort', 0),
        ];

        $data = $this->videoService->saveEpisode($payload, $id);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 删除视频剧集
     */
    public function deleteEpisode(Request $request): JsonResponse
    {
        $id = (int)$request->input('id', 0);
        $data = $this->videoService->deleteEpisode($id);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 显示视频类型
     */
    public function showTypes(): View|Factory
    {
        return view('admin.video.types');
    }

    public function createType(Request $request): View|Factory
    {
        $parentId = (int) $request->query('parent_id', 0);
        $parents = $this->typeParentOptions(null);
        $parent = $this->findTypeOption($parents, $parentId);
        if ($parentId > 0 && $parent === null) {
            $parentId = 0;
        }

        return $this->typeFormPage([
            'parent_id' => $parentId,
            'mid' => (int) ($parent['mid'] ?? 1),
            'sort' => 0,
            'status' => 1,
        ], $parents, $parent);
    }

    public function editType(int $id): View|Factory
    {
        $res = $this->videoService->getTypeInfo($id);
        if ((int) ($res['code'] ?? 1) !== 0) {
            abort(404);
        }
        $type = is_array($res['data'] ?? null) ? $res['data'] : [];
        $parents = $this->typeParentOptions($id);
        $parentId = (int) ($type['parent_id'] ?? 0);

        return $this->typeFormPage($type, $parents, $this->findTypeOption($parents, $parentId));
    }

    /**
     * @param array<string, mixed> $type
     * @param list<array<string, mixed>> $parents
     * @param array<string, mixed>|null $parent
     */
    private function typeFormPage(array $type, array $parents, ?array $parent): View|Factory
    {
        return view('admin.video.type_form', [
            'type' => $type,
            'isEdit' => (int) ($type['id'] ?? 0) > 0,
            'parents' => $parents,
            'parent' => $parent,
        ]);
    }

    /** @return list<array{id:int,name:string,depth:int,parent_id:int,mid:int}> */
    private function typeParentOptions(?int $excludeId): array
    {
        $res = $this->videoService->getVideoTypeLists(['name' => '']);
        $rows = $res['data']['data'] ?? [];
        if (! is_array($rows)) {
            return [];
        }
        $skip = [];
        $ex = (int) ($excludeId ?? 0);
        if ($ex > 0) {
            $skip[$ex] = true;
        }
        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $id = (int) ($row['id'] ?? 0);
            $pid = (int) ($row['parent_id'] ?? 0);
            if ($pid > 0 && isset($skip[$pid])) {
                $skip[$id] = true;
            }
            if (isset($skip[$id])) {
                continue;
            }
            $out[] = [
                'id' => $id,
                'name' => (string) ($row['name'] ?? ''),
                'depth' => (int) ($row['depth'] ?? 0),
                'parent_id' => $pid,
                'mid' => (int) ($row['mid'] ?? 1),
            ];
        }

        return $out;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return array<string, mixed>|null
     */
    private function findTypeOption(array $rows, int $id): ?array
    {
        if ($id < 1) {
            return null;
        }
        foreach ($rows as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                return $row;
            }
        }

        return null;
    }

    /**
     * 获取视频类型列表
     */
    public function getTypeLists(Request $request): JsonResponse
    {
        $params = [
            'name' => (string)$request->input('name', ''),
            'parent_id' => $request->input('parent_id', ''),
            'limit' => (int)$request->input('limit', 10),
        ];
        $data = $this->videoService->getVideoTypeLists($params);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 保存视频类型
     */
    public function saveType(Request $request): JsonResponse
    {
        $id = $request->has('id') ? (int)$request->input('id', 0) : null;

        if ($id !== null && $id < 1)
        {
            $id = null;
        }

        $payload = [
            'name' => (string) $request->input('name', ''),
            'slug' => (string) $request->input('slug', ''),
            'parent_id' => (int) $request->input('parent_id', 0),
            'mid' => (int) $request->input('mid', 1),
            'sort' => (int) $request->input('sort', 0),
            'status' => (int) $request->input('status', 1),
            'seo_title' => (string) $request->input('seo_title', ''),
            'seo_keywords' => (string) $request->input('seo_keywords', ''),
            'seo_description' => (string) $request->input('seo_description', ''),
        ];

        $data = $this->videoService->saveVideoType($payload, $id);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 删除视频类型
     */
    public function deleteType(Request $request): JsonResponse
    {
        $id = (int)$request->input('id', 0);
        $data = $this->videoService->deleteVideoType($id);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function batchTypes(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }
        $data = $this->videoService->batchVideoTypes(
            is_array($ids) ? $ids : [],
            (string) $request->input('action', ''),
            $request->input('value', '')
        );

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    /**
     * 显示视频采集源
     */
    public function showCollectSources(): View|Factory
    {
        return view('admin.video.collects');
    }

    /**
     * 获取视频采集源列表
     */
    public function getCollectSourceLists(Request $request): JsonResponse
    {
        $params = [
            'name' => (string)$request->input('name', ''),
            'status' => $request->input('status', ''),
            'empty_bind' => $request->input('empty_bind', ''),
            'has_error' => $request->input('has_error', ''),
            'limit' => (int) $request->input('limit', 100),
        ];
        $data = $this->videoService->getCollectSourceLists($params);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 保存视频采集源
     */
    public function saveCollectSource(Request $request): JsonResponse
    {
        $id = $request->has('id') ? (int)$request->input('id', 0) : null;
        if ($id !== null && $id < 1) {
            $id = null;
        }

        $payload = [
            'name' => (string)$request->input('name', ''),
            'api_url' => (string)$request->input('api_url', ''),
            'api_type' => (string)$request->input('api_type', 'auto'),
            'param' => (string)$request->input('param', ''),
            'status' => (int)$request->input('status', 1),
            'sort' => (int)$request->input('sort', 0),
        ];

        $data = $this->videoService->saveCollectSource($payload, $id);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 删除视频采集源
     */
    public function deleteCollectSource(Request $request): JsonResponse
    {
        $id = (int)$request->input('id', 0);
        $data = $this->videoService->deleteCollectSource($id);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 显示视频标签
     */
    public function showTags(): View|Factory
    {
        return view('admin.video.tags');
    }

    /**
     * 获取视频标签列表
     */
    public function getTagLists(Request $request): JsonResponse
    {
        $params = [
            'name' => (string) $request->input('name', ''),
            'status' => $request->input('status', ''),
            'unused' => $request->input('unused', ''),
            'limit' => (int) $request->input('limit', 20),
        ];
        $data = $this->videoService->getVideoTagLists($params);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 保存视频标签
     */
    public function saveTag(Request $request): JsonResponse
    {
        $id = $request->has('id') ? (int)$request->input('id', 0) : null;
        if ($id !== null && $id < 1) {
            $id = null;
        }

        $payload = [
            'name' => (string) $request->input('name', ''),
            'slug' => (string) $request->input('slug', ''),
            'sort' => (int) $request->input('sort', 0),
            'status' => (int) $request->input('status', 1),
        ];

        $data = $this->videoService->saveVideoTag($payload, $id);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 删除视频标签
     */
    public function deleteTag(Request $request): JsonResponse
    {
        $id = (int)$request->input('id', 0);
        $data = $this->videoService->deleteVideoTag($id);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function batchTags(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }
        $data = $this->videoService->batchTags(
            is_array($ids) ? $ids : [],
            (string) $request->input('action', ''),
            $request->input('value', '')
        );

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    /**
     * 显示视频演员
     */
    public function showActors(): View|Factory
    {
        return view('admin.video.actors');
    }

    /**
     * 获取视频演员列表
     */
    public function getActorLists(Request $request): JsonResponse
    {
        $params = [
            'id' => $request->input('id', ''),
            'name' => (string) $request->input('name', ''),
            'status' => $request->input('status', ''),
            'empty_pic' => $request->input('empty_pic', ''),
            'repeat' => $request->input('repeat', ''),
            'limit' => (int) $request->input('limit', 20),
        ];
        $data = $this->videoService->getActorLists($params);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 保存视频演员
     */
    public function saveActor(Request $request): JsonResponse
    {
        $id = $request->has('id') ? (int)$request->input('id', 0) : null;
        if ($id !== null && $id < 1) {
            $id = null;
        }

        $payload = [
            'name' => (string)$request->input('name', ''),
            'slug' => (string)$request->input('slug', ''),
            'avatar' => (string)$request->input('avatar', ''),
            'sex' => (string)$request->input('sex', ''),
            'area' => (string)$request->input('area', ''),
            'birthday' => (string)$request->input('birthday', ''),
            'content' => (string)$request->input('content', ''),
            'sort' => (int)$request->input('sort', 0),
            'status' => (int)$request->input('status', 1),
        ];

        $data = $this->videoService->saveActor($payload, $id);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 删除视频演员
     */
    public function deleteActor(Request $request): JsonResponse
    {
        $id = (int)$request->input('id', 0);
        $data = $this->videoService->deleteActor($id);
        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function batchActors(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }
        $data = $this->videoService->batchActors(
            is_array($ids) ? $ids : [],
            (string) $request->input('action', ''),
            $request->input('value', '')
        );

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }
}
