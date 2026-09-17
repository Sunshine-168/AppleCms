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
        if ($module === 'collect_logs') {
            $sourceId = (int) request()->query('collect_source_id', 0);
            $ok = (string) request()->query('ok', '');
            if ($ok !== '0' && $ok !== '1') {
                $ok = '';
            }

            return view('admin.video.collect_logs', [
                'title' => $cfg['title'],
                'queues' => $this->modules->collectLogQueues(),
                'sourceId' => $sourceId,
                'sourceName' => $sourceId > 0 ? $this->modules->collectSourceName($sourceId) : '',
                'okPrefill' => $ok,
                'todayPrefill' => (string) request()->query('today', '') === '1' ? '1' : '',
            ]);
        }
        if ($module === 'collect_tasks') {
            $sourceId = (int) request()->query('collect_source_id', 0);

            return view('admin.video.collect_tasks', [
                'title' => $cfg['title'],
                'queues' => $this->modules->collectTaskQueues(),
                'sourceId' => $sourceId,
                'sourceName' => $sourceId > 0 ? $this->modules->collectSourceName($sourceId) : '',
            ]);
        }
        if ($module === 'collect_temps') {
            $sourceId = (int) request()->query('collect_source_id', 0);

            return view('admin.video.collect_temps', [
                'title' => $cfg['title'],
                'queues' => $this->modules->collectTempQueues(),
                'toTemp' => $this->modules->collectToTempEnabled(),
                'sourceId' => $sourceId,
                'sourceName' => $sourceId > 0 ? $this->modules->collectSourceName($sourceId) : '',
            ]);
        }
        if ($module === 'searchwords') {
            return view('admin.video.searchwords', [
                'title' => $cfg['title'],
                'queues' => $this->modules->searchWordQueues(),
            ]);
        }
        if ($module === 'reports') {
            return view('admin.video.reports', [
                'title' => $cfg['title'],
                'queues' => $this->modules->reportQueues(),
            ]);
        }
        if ($module === 'guestbooks') {
            return view('admin.video.guestbooks', [
                'title' => $cfg['title'],
                'queues' => $this->modules->guestbookQueues(),
                'audit' => $this->modules->guestbookAuditEnabled(),
            ]);
        }
        if ($module === 'playfails') {
            return view('admin.video.playfails', [
                'title' => $cfg['title'],
                'queues' => $this->modules->playFailQueues(),
            ]);
        }
        if ($module === 'pms') {
            return view('admin.video.pms', [
                'title' => $cfg['title'],
                'queues' => $this->modules->pmQueues(),
                'toId' => (int) request()->query('to', 0),
            ]);
        }
        if ($module === 'notifies') {
            return view('admin.video.notifies', [
                'title' => $cfg['title'],
                'queues' => $this->modules->notifyQueues(),
                'memberId' => (int) request()->query('member', 0),
            ]);
        }
        if ($module === 'withdraws') {
            return view('admin.video.withdraws', [
                'title' => $cfg['title'],
                'queues' => $this->modules->withdrawQueues(),
            ]);
        }
        if ($module === 'invites') {
            return view('admin.video.invites', [
                'title' => $cfg['title'],
                'queues' => $this->modules->inviteQueues(),
            ]);
        }
        if ($module === 'audits') {
            return view('admin.video.audits', [
                'title' => $cfg['title'],
                'queues' => $this->modules->auditQueues(),
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

    public function createArt(): View
    {
        return view('admin.video.art_form', [
            'art' => [
                'status' => 1,
                'hits' => 0,
                'type_id' => (int) request()->query('type_id', 0),
                'title' => '',
                'cover' => '',
                'content' => '',
            ],
            'isEdit' => false,
            'types' => $this->modules->artTypeOptions(),
        ]);
    }

    public function editArt(int $id): View
    {
        $art = $this->modules->getArt($id);
        if ($art === null) {
            abort(404);
        }

        return view('admin.video.art_form', [
            'art' => $art,
            'isEdit' => true,
            'types' => $this->modules->artTypeOptions(),
        ]);
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

    public function createCollectTask(Request $request): View
    {
        $sourceId = (int) $request->query('collect_source_id', 0);

        return view('admin.video.collect_task_form', [
            'task' => [
                'status' => 1,
                'pages' => 1,
                'hours' => 24,
                'cron_expression' => '0 * * * *',
                'collect_source_id' => $sourceId,
            ],
            'isEdit' => false,
            'sources' => $this->modules->collectSourceOptions(),
            'cronPresets' => $this->modules->collectCronPresets(),
            'hourPresets' => $this->modules->collectHourPresets(),
        ]);
    }

    public function editCollectTask(int $id): View
    {
        $task = $this->modules->getCollectTask($id);
        if ($task === null) {
            abort(404);
        }

        return view('admin.video.collect_task_form', [
            'task' => $task,
            'isEdit' => true,
            'sources' => $this->modules->collectSourceOptions(),
            'cronPresets' => $this->modules->collectCronPresets(),
            'hourPresets' => $this->modules->collectHourPresets(),
        ]);
    }

    public function createAuditRule(): View
    {
        return view('admin.video.audit_form', [
            'rule' => [
                'status' => 1,
                'scope' => 'title',
                'action' => 'skip',
                'is_regex' => 0,
                'sort' => 0,
                'words' => '',
            ],
            'isEdit' => false,
            'scopes' => $this->modules->auditScopeOptions(),
            'actions' => $this->modules->auditActionOptions(),
        ]);
    }

    public function editAuditRule(int $id): View
    {
        $rule = $this->modules->getAuditRule($id);
        if ($rule === null) {
            abort(404);
        }

        return view('admin.video.audit_form', [
            'rule' => $rule,
            'isEdit' => true,
            'scopes' => $this->modules->auditScopeOptions(),
            'actions' => $this->modules->auditActionOptions(),
        ]);
    }

    public function tryAuditRule(Request $request): JsonResponse
    {
        $data = $this->modules->tryAuditRule($request->all());

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
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
