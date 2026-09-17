<?php

namespace App\Http\Controllers\Admin\Video;

use App\Http\Controllers\Controller;
use App\Services\Admin\Video\VideoService;
use App\Services\Video\SiteToolsService;
use App\Support\AdminOpLog;
use App\Support\Utils\Ajax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteTools extends Controller
{
    public function __construct(private readonly SiteToolsService $tools) {}

    public function page(string $tool): View
    {
        $ok = ['images', 'quality', 'players', 'annex', 'recycle', 'hub'];
        if (! in_array($tool, $ok, true)) {
            abort(404);
        }
        if ($tool === 'hub') {
            $hub = $this->tools->hubPage();

            return view('admin.video.hub', $hub);
        }
        if ($tool === 'images') {
            return view('admin.video.images', $this->tools->imagesPage());
        }
        if ($tool === 'players') {
            return view('admin.video.batch_players', $this->tools->playersPage());
        }
        if ($tool === 'recycle') {
            $types = [];
            try {
                $opt = app(VideoService::class)->getTypeOptions();
                $types = ($opt['code'] ?? 1) === 0 ? ($opt['data'] ?? []) : [];
            } catch (\Throwable) {
            }

            return view('admin.video.recycle', [
                'title' => admin_t('page.tool_recycle'),
                'count' => $this->tools->recycleCount(),
                'types' => is_array($types) ? $types : [],
            ]);
        }

        return view('admin.video.tools', compact('tool'));
    }

    public function run(Request $request, string $tool): JsonResponse
    {
        $action = (string) $request->input('action', '');
        $data = match ($tool.'.'.$action) {
            'recycle.list' => $this->tools->recycleList($request->all()),
            'recycle.restore' => $this->tools->restore($request->input('ids', [])),
            'recycle.purge' => $this->purge($request->input('ids', [])),
            'recycle.empty' => $this->emptyBin(),
            'images.scan' => $this->tools->scanImages((int) $request->input('limit', 80)),
            'images.localize' => $this->tools->localizeImages((int) $request->input('limit', 40)),
            'quality.scan' => $this->tools->quality((int) $request->input('limit', 50)),
            'players.replace' => $this->tools->replacePlayer(
                (string) $request->input('from', ''),
                (string) $request->input('to', ''),
                (string) $request->input('mode', 'rename')
            ),
            'annex.scan' => $this->tools->scanAnnex(),
            'annex.delete' => $this->tools->deleteOrphans(),
            'hub.probe' => $this->tools->probeHub((string) $request->input('api_url', '')),
            default => ['code' => 1, 'msg' => '未知操作', 'data' => []],
        };
        if ((int) ($data['code'] ?? 1) === 0 && ! in_array($action, ['list', 'probe', 'scan'], true)) {
            $summary = match ($tool.'.'.$action) {
                'recycle.restore' => '从回收站恢复了影片',
                'recycle.purge' => '彻底删除了回收站影片',
                'recycle.empty' => '清空了回收站',
                'images.localize' => '下载了远程封面',
                'players.replace' => '批量换了播放器',
                'annex.delete' => '删了没用的附件',
                default => '',
            };
            if ($summary !== '') {
                AdminOpLog::write($action !== '' ? $action : 'save', $summary, [
                    'module' => $tool === 'recycle' ? '影片' : '站点',
                    'target_type' => $tool,
                ]);
            }
        }

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function apidoc(): View
    {
        return view('admin.video.apidoc');
    }

    public function promoteTemp(Request $request): JsonResponse
    {
        $raw = $request->input('ids', $request->input('id', []));
        if (is_string($raw)) {
            $raw = explode(',', $raw);
        }
        if (! is_array($raw)) {
            $raw = [$raw];
        }
        $data = $this->tools->promoteTemps($raw);
        if ((int) ($data['code'] ?? 1) === 0) {
            $n = is_array($raw) ? count(array_filter($raw)) : 1;
            AdminOpLog::write('save', '转入了 '.$n.' 条待审采集', [
                'module' => '待审采集',
                'target_type' => 'collect_temps',
                'payload' => ['count' => $n],
            ]);
        }

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    private function emptyBin(): array
    {
        $ids = $this->tools->recycleIds();
        if ($ids === []) {
            return ['code' => 1, 'msg' => '回收站是空的', 'data' => []];
        }

        return $this->purge($ids);
    }

    private function purge(mixed $ids): array
    {
        $service = app(VideoService::class);
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        $list = [];
        foreach ((array) $ids as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $list[] = $id;
            }
        }
        if ($list === []) {
            return ['code' => 1, 'msg' => '请先勾选影片', 'data' => []];
        }
        $n = 0;
        foreach ($list as $id) {
            $res = $service->purgeVideo($id);
            if (($res['code'] ?? 1) === 0) {
                $n++;
            }
        }

        return ['code' => 0, 'msg' => '已彻底删除 '.$n.' 部', 'data' => ['count' => $n]];
    }
}
