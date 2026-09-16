<?php

namespace App\Http\Controllers\Admin\Video;

use App\Http\Controllers\Controller;
use App\Services\Admin\Video\SiteModuleService;
use App\Support\Utils\Ajax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteModule extends Controller
{
    public function __construct(private readonly SiteModuleService $modules) {}

    public function index(string $module): View
    {
        $cfg = $this->cfg($module);
        if ($module === 'comments') {
            return view('admin.video.comments', [
                'title' => $cfg['title'],
                'queues' => $this->modules->commentQueues(),
            ]);
        }
        if ($module === 'topics') {
            return view('admin.video.topics', [
                'title' => $cfg['title'],
            ]);
        }
        if ($module === 'arts') {
            return view('admin.video.arts', [
                'title' => $cfg['title'],
                'types' => $this->modules->artTypeOptions(),
                'queues' => $this->modules->artQueues(),
            ]);
        }
        if ($module === 'slides') {
            return view('admin.video.slides', [
                'title' => $cfg['title'],
                'queues' => $this->modules->slideQueues(),
            ]);
        }
        if ($module === 'members') {
            return view('admin.video.members', [
                'title' => $cfg['title'],
                'groups' => $this->modules->memberGroupOptions(),
                'queues' => $this->modules->memberQueues(),
            ]);
        }
        if ($module === 'orders') {
            return view('admin.video.orders', [
                'title' => $cfg['title'],
                'queues' => $this->modules->orderQueues(),
            ]);
        }
        if ($module === 'groups') {
            return view('admin.video.groups', [
                'title' => $cfg['title'],
                'queues' => $this->modules->groupQueues(),
            ]);
        }
        if ($module === 'cards') {
            return view('admin.video.cards', [
                'title' => $cfg['title'],
                'queues' => $this->modules->cardQueues(),
            ]);
        }
        if ($module === 'plogs') {
            return view('admin.video.plogs', [
                'title' => $cfg['title'],
                'queues' => $this->modules->plogQueues(),
            ]);
        }
        if ($module === 'ads') {
            return view('admin.video.ads', [
                'title' => $cfg['title'],
                'queues' => $this->modules->adQueues(),
                'types' => $this->modules->vodTypeOptions(),
            ]);
        }
        if ($module === 'links') {
            return view('admin.video.links', [
                'title' => $cfg['title'],
                'queues' => $this->modules->linkQueues(),
            ]);
        }
        if ($module === 'players') {
            return view('admin.video.players', [
                'title' => $cfg['title'],
                'queues' => $this->modules->playerQueues(),
            ]);
        }
        if ($module === 'unions') {
            return view('admin.video.unions', [
                'title' => $cfg['title'],
                'queues' => $this->modules->unionQueues(),
            ]);
        }

        return view('admin.video.module', [
            'module' => $module,
            'title' => $cfg['title'],
            'hint' => $cfg['hint'] ?? '',
            'fields' => $cfg['fields'],
            'cols' => $cfg['cols'],
            'search' => $cfg['search'],
        ]);
    }

    public function list(Request $request, string $module): JsonResponse
    {
        $this->cfg($module);
        $data = $this->modules->lists($module, $request->all());

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function save(Request $request, string $module): JsonResponse
    {
        $this->cfg($module);
        $id = (int) $request->input('id', 0);
        $data = $this->modules->save($module, $request->all(), $id > 0 ? $id : null);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function delete(Request $request, string $module): JsonResponse
    {
        $this->cfg($module);
        $data = $this->modules->delete($module, (int) $request->input('id', 0));

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function batch(Request $request, string $module): JsonResponse
    {
        $this->cfg($module);
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }
        $data = $this->modules->batch(
            $module,
            is_array($ids) ? $ids : [],
            (string) $request->input('action', ''),
            $request->input('value', '')
        );

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function topicVideos(int $id): JsonResponse
    {
        $data = $this->modules->topicVideos($id);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function saveTopicVideos(Request $request, int $id): JsonResponse
    {
        $data = $this->modules->saveTopicVideos($id, (string) $request->input('video_ids', ''));

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function generateCards(Request $request): JsonResponse
    {
        $data = $this->modules->generateCards((int) $request->input('count', 10), (int) $request->input('points', 100));

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function generateInvites(Request $request): JsonResponse
    {
        $data = $this->modules->generateInvites(
            (int) $request->input('count', 10),
            (int) $request->input('points', 0),
            (int) $request->input('member_id', 0)
        );

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function runCollectTask(Request $request): JsonResponse
    {
        $data = $this->modules->runCollectTask((int) $request->input('id', 0));

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function ensurePlayers(): JsonResponse
    {
        $data = $this->modules->ensurePlayers();

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function createUnion(): View
    {
        return view('admin.video.union_form', [
            'union' => ['status' => 1, 'sort' => 0],
            'isEdit' => false,
        ]);
    }

    public function editUnion(int $id): View
    {
        $union = $this->modules->getUnion($id);
        if ($union === null) {
            abort(404);
        }

        return view('admin.video.union_form', [
            'union' => $union,
            'isEdit' => true,
        ]);
    }

    public function adoptUnion(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }
        if (is_array($ids) && $ids !== []) {
            $data = $this->modules->adoptUnions($ids);
        } else {
            $data = $this->modules->adoptUnion((int) $request->input('id', 0));
        }

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    /** @return array<string, mixed> */
    private function cfg(string $module): array
    {
        try {
            return $this->modules->config($module);
        } catch (\InvalidArgumentException) {
            abort(404);
        }
    }
}
