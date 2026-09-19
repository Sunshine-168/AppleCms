<?php

namespace Plugins\Live\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Video\SiteFrontService;
use Illuminate\Http\Request;
use Plugins\Live\Services\LiveService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LiveController extends Controller
{
    public function __construct(
        private readonly LiveService $service,
        private readonly SiteFrontService $front
    ) {}

    /** 显示直播频道目录。 */
    public function index(Request $request)
    {
        if (! $this->service->ready()) {
            throw new NotFoundHttpException;
        }
        $cateId = max(0, (int) $request->query('cate', $request->query('cate_id', 0)));
        $q = trim((string) $request->query('q', $request->query('wd', '')));
        $channels = $this->service->paginateChannels($cateId ?: null, $q, 24);
        $recommended = ($cateId < 1 && $q === '')
            ? $this->service->recommendedChannels(8)
            : collect();

        return view('live::index', [
            'site' => $this->front->bootSite(),
            'categories' => $this->service->publishedCategories(),
            'channels' => $channels,
            'recommended' => $recommended,
            'cateId' => $cateId,
            'q' => $q,
            'paginator' => $channels,
        ]);
    }

    /** 显示并播放直播频道。 */
    public function show(int $id)
    {
        $channel = $this->service->findChannel($id);
        if (! $channel) {
            throw new NotFoundHttpException;
        }
        $this->service->incrementHit($channel);
        $related = $this->service->relatedChannels($channel);

        return view('live::show', [
            'site' => $this->front->bootSite(),
            'channel' => $channel,
            'related' => $related,
        ]);
    }
}
