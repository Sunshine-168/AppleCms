<?php

namespace Plugins\AiContent\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Video\VideoModel;
use App\Support\Utils\Ajax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Plugins\AiContent\Services\AiContentService;

class AiContentController extends Controller
{
    public function __construct(private readonly AiContentService $ai) {}

    public function generate(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        if (isset($ctx['error'])) {
            return Ajax::fail((string) $ctx['error']);
        }
        $data = $this->ai->generate((string) $ctx['title'], (string) $ctx['hint']);

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function seo(Request $request): JsonResponse
    {
        $id = (int) $request->input('id', 0);
        $apply = (string) $request->input('apply', '') === '1';
        if ($apply && $id > 0) {
            $overwrite = (string) $request->input('overwrite', '') === '1';
            $data = $this->ai->applySeo($id, $overwrite);

            return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
        }
        $ctx = $this->context($request);
        if (isset($ctx['error'])) {
            return Ajax::fail((string) $ctx['error']);
        }
        $data = $this->ai->generateSeo($ctx);

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function seoBatch(Request $request): JsonResponse
    {
        $raw = $request->input('ids', '');
        $ids = is_array($raw) ? $raw : (preg_split('/[,\s]+/', (string) $raw) ?: []);
        $overwrite = (string) $request->input('overwrite', '') === '1';
        $data = $this->ai->applySeoBatch($ids, $overwrite);

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    /** @return array<string, mixed> */
    private function context(Request $request): array
    {
        $id = (int) $request->input('id', 0);
        $title = trim((string) $request->input('title', ''));
        $hint = trim((string) $request->input('hint', ''));
        $type = trim((string) $request->input('type', ''));
        $year = trim((string) $request->input('year', ''));
        $area = trim((string) $request->input('area', ''));
        $actors = trim((string) $request->input('actors', ''));
        if ($id > 0) {
            $video = VideoModel::query()->find($id);
            if (! $video) {
                return ['error' => '影片不存在'];
            }
            $from = $this->ai->videoCtx($video);
            if ($title === '') {
                $title = (string) $from['title'];
            }
            if ($hint === '') {
                $hint = (string) $from['hint'];
            }
            if ($type === '') {
                $type = (string) $from['type'];
            }
            if ($year === '') {
                $year = (string) $from['year'];
            }
            if ($area === '') {
                $area = (string) $from['area'];
            }
            if ($actors === '') {
                $actors = (string) $from['actors'];
            }
        }

        return compact('title', 'hint', 'type', 'year', 'area', 'actors');
    }
}
