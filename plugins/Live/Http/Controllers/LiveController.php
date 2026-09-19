<?php

namespace Plugins\Live\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Video\SiteFrontService;
use Plugins\Live\Services\LiveService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LiveController extends Controller
{
    public function __construct(
        private readonly LiveService $service,
        private readonly SiteFrontService $front
    ) {}

    /** 显示直播频道目录。 */
    public function index()
    {
        if (! $this->service->ready()) {
            throw new NotFoundHttpException;
        }
        $cateId = max(0, (int) request()->query('cate', 0));

        return view('live::index', [
            'site' => $this->front->bootSite(),
            'categories' => $this->service->publishedCategories(),
            'channels' => $this->service->publishedChannels($cateId ?: null),
            'cateId' => $cateId,
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
        $related = $this->service->publishedChannels($channel->cate_id ?: null)
            ->reject(fn ($item) => (int) $item->id === $channel->id);

        return view('live::show', [
            'site' => $this->front->bootSite(),
            'channel' => $channel,
            'related' => $related,
        ]);
    }
}
