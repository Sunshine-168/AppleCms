<?php

namespace App\Http\Controllers\Admin\Video;

use App\Http\Controllers\Controller;
use App\Models\Video\VideoPlayerModel;
use App\Models\Video\VideoUnion;
use App\Services\Admin\Video\VideoService;
use App\Services\Video\SiteToolsService;
use App\Support\Utils\Ajax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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
        $players = [];
        $unions = [];
        if ($tool === 'players' && Schema::hasTable('video_players')) {
            $players = VideoPlayerModel::query()->orderByDesc('sort')->orderBy('id')->get();
        }
        if ($tool === 'hub' && Schema::hasTable('video_unions')) {
            $unions = VideoUnion::query()->orderByDesc('sort')->orderBy('id')->get();
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

        return view('admin.video.tools', compact('tool', 'players', 'unions'));
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

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function apidoc(): View
    {
        return view('admin.video.apidoc');
    }

    public function promoteTemp(Request $request): JsonResponse
    {
        $data = $this->tools->promoteTemp((int) $request->input('id', 0));

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
