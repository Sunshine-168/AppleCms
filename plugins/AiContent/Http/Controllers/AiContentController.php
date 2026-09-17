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
        $id = (int) $request->input('id', 0);
        $title = trim((string) $request->input('title', ''));
        $hint = trim((string) $request->input('hint', ''));
        if ($id > 0) {
            $video = VideoModel::query()->find($id);
            if (! $video) {
                return Ajax::fail('影片不存在');
            }
            if ($title === '') {
                $title = (string) $video->title;
            }
            if ($hint === '') {
                $hint = (string) $video->description;
            }
        }
        $data = $this->ai->generate($title, $hint);

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }
}
