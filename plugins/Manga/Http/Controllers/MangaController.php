<?php

namespace Plugins\Manga\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Video\SiteFrontService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $filters = $this->manga->frontFilters();
        $rails = $this->manga->typeRails((int) $filters['type']);
        $filtered = $this->manga->isFiltered($filters);
        $blocks = $filtered ? ['recommend' => collect(), 'hot' => collect(), 'newest' => collect()] : $this->manga->indexBlocks();

        return view('manga::index', $this->page([
            'list' => $this->manga->paginate(24, $filters),
            'types' => $rails['top'],
            'subTypes' => $rails['sub'],
            'filters' => $filters,
            'tags' => $filtered ? [] : $this->manga->tagCloud(),
            'blocks' => $blocks,
            'filtered' => $filtered,
            'page' => 'index',
        ]));
    }

    public function rank(): View
    {
        if (! $this->manga->ready()) {
            throw new NotFoundHttpException();
        }
        $filters = $this->manga->frontFilters();

        return view('manga::rank', $this->page([
            'list' => $this->manga->rank((string) $filters['board']),
            'filters' => $filters,
            'page' => 'rank',
        ]));
    }

    public function updates(): View
    {
        if (! $this->manga->ready()) {
            throw new NotFoundHttpException();
        }
        $filters = $this->manga->frontFilters();
        $filters['order'] = 'update';

        return view('manga::update', $this->page([
            'list' => $this->manga->paginate(24, $filters),
            'filters' => $filters,
            'days' => $this->manga->updateDays(),
            'page' => 'update',
        ]));
    }

    public function shelf(): View|RedirectResponse
    {
        if (! $this->manga->ready()) {
            throw new NotFoundHttpException();
        }
        $member = Auth::guard('member')->user();
        if (! $member) {
            return redirect()->guest('/member/login');
        }

        return view('manga::shelf', $this->page([
            'list' => $this->manga->shelf((int) $member->id),
            'page' => 'shelf',
        ]));
    }

    public function history(): View
    {
        if (! $this->manga->ready()) {
            throw new NotFoundHttpException();
        }

        return view('manga::history', $this->page([
            'page' => 'history',
        ]));
    }

    public function show(int $id): View
    {
        $row = $this->manga->published($id);
        if (! $row) {
            throw new NotFoundHttpException();
        }
        $this->manga->bumpHits($row);
        $row->load(['chapters', 'type']);
        $member = Auth::guard('member')->user();

        return view('manga::show', $this->page([
            'manga' => $row,
            'related' => $this->manga->related($row),
            'comments' => $this->manga->listedComments($row),
            'commentCount' => $this->manga->commentCount($row),
            'favored' => $member ? $this->manga->favored((int) $member->id, (int) $row->id) : false,
            'page' => 'show',
        ]));
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
        $row->load('chapters');
        $near = $this->manga->neighbors($row, $ep);

        return view('manga::read', $this->page([
            'manga' => $row,
            'chapter' => $ep,
            'pics' => $ep->picList(),
            'prev' => $near['prev'],
            'next' => $near['next'],
            'page' => 'read',
        ]));
    }

    public function comment(Request $request, int $id): RedirectResponse
    {
        $result = $this->manga->addComment(
            $id,
            (string) $request->input('content', ''),
            Auth::guard('member')->user(),
            (string) $request->input('author_name', ''),
            (string) $request->ip()
        );
        if ($result['code'] !== 0) {
            return back()->with('error', $result['msg']);
        }

        return back()->with('status', $result['msg']);
    }

    public function favor(int $id): RedirectResponse
    {
        $member = Auth::guard('member')->user();
        if (! $member) {
            return redirect()->guest('/member/login');
        }
        $result = $this->manga->toggleFavor((int) $member->id, $id);
        if ($result['code'] !== 0) {
            return back()->with('error', $result['msg']);
        }

        return back()->with('status', $result['msg']);
    }

    /** @param array<string, mixed> $extra */
    private function page(array $extra): array
    {
        return array_merge([
            'site' => $this->front->bootSite(),
            'listUrl' => fn (array $over = []) => $this->manga->listUrl($over),
        ], $extra);
    }
}
