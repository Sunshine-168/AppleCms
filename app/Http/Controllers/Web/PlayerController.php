<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Video\VideoPlayerModel;
use App\Services\Video\SiteFrontService;
use App\Cms\CmsViewContext;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PlayerController extends Controller
{
    public function __construct(
        private readonly SiteFrontService $front,
        private readonly CmsViewContext $context,
    ) {}

    public function show(int $id, ?int $sid = null, ?int $nid = null): View
    {
        $site = $this->front->bootSite();
        $video = $this->front->findVideo($id);
        if (! $video) {
            throw new NotFoundHttpException();
        }
        [$source, $episode] = $this->front->resolvePlay($video, $sid, $nid, 'play');
        $this->context->setVideo($video);
        $this->context->setSource($source);
        $this->context->setEpisode($episode);

        $playUrl = (string) ($episode?->url ?? '');
        $playerCode = (string) ($source?->player ?: $source?->name ?: '');
        $parser = VideoPlayerModel::query()->where('status', 1)
            ->when($playerCode !== '', fn ($q) => $q->where('code', $playerCode))
            ->orderByDesc('sort')
            ->first();
        if ($parser && trim((string) $parser->parse) !== '' && $playUrl !== '') {
            $playUrl = str_replace(['{url}', '{id}'], [rawurlencode((string) $episode?->url), (string) $id], (string) $parser->parse);
        }

        return view($this->front->themeView('vod.player'), compact('site', 'video', 'source', 'episode', 'playUrl', 'parser'));
    }
}
