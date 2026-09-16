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
        return view('admin.video.index');
    }

    /**
     * 获取视频列表
     */
    public function getVideoLists(Request $request): JsonResponse
    {
        $params = [
            'title' => (string)$request->input('title', ''),
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
            'no_actor' => $request->input('no_actor', ''),
            'weekday' => (string) $request->input('weekday', ''),
            'trash' => $request->input('trash', ''),
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
        return view('admin.video.source', compact('videoId'));
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
            'name'      => (string)$request->input('name', ''),
            'parent_id' => (int)$request->input('parent_id', 0),
            'sort'      => (int)$request->input('sort', 0),
            'status'    => (int)$request->input('status', 1),
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
            'limit' => (int)$request->input('limit', 10),
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
            'name' => (string)$request->input('name', ''),
            'limit' => (int)$request->input('limit', 10),
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
            'name' => (string)$request->input('name', ''),
            'sort' => (int)$request->input('sort', 0),
            'status' => (int)$request->input('status', 1),
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
            'name' => (string)$request->input('name', ''),
            'limit' => (int)$request->input('limit', 10),
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
            'avatar' => (string)$request->input('avatar', ''),
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
}
