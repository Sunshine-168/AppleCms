<?php

namespace Plugins\Manga\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Video\SiteFrontService;
use Illuminate\Contracts\View\View;
use Plugins\Manga\Services\MangaService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MangaController extends Controller
{
    public function __construct(
        private readonly MangaService $manga,
        private readonly SiteFrontService $front,
    ) {}

    public function index(): View
    {
        if (! $this->manga->ready()) {
            throw new NotFoundHttpException();
        }
        $site = $this->front->bootSite();
        $typeId = (int) request()->query('type', 0);

        return view('manga::index', [
            'site' => $site,
            'list' => $this->manga->paginate(24, $typeId > 0 ? $typeId : null),
            'types' => $this->manga->listedTypes(),
            'typeId' => $typeId,
        ]);
    }

    public function show(int $id): View
    {
        $row = $this->manga->published($id);
        if (! $row) {
            throw new NotFoundHttpException();
        }
        $this->manga->bumpHits($row);
        $row->load('chapters');
        $site = $this->front->bootSite();

        return view('manga::show', ['site' => $site, 'manga' => $row]);
    }

    public function read(int $id, int $chapter): View
    {
        $row = $this->manga->published($id);
        if (! $row) {
            throw new NotFoundHttpException();
        }
        $ep = $this->manga->chapter($row, $chapter);
        if (! $ep) {
            throw new NotFoundHttpException();
        }
        $site = $this->front->bootSite();

        return view('manga::read', [
            'site' => $site,
            'manga' => $row,
            'chapter' => $ep,
            'pics' => $ep->picList(),
        ]);
    }
}
