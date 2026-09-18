<?php

namespace Plugins\Manga\Http\Controllers;

use App\Cms\CmsViewContext;
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
        private readonly CmsViewContext $context,
    ) {}

    public function index(): View
    {
        if (! $this->manga->ready()) {
            throw new NotFoundHttpException();
        }
        $filters = $this->manga->frontFilters();
        $typeId = (int) $filters['type'];
        $rails = $this->manga->typeRails($typeId);
        $filtered = $this->manga->isFiltered($filters);
        $blocks = $filtered ? ['recommend' => collect(), 'hot' => collect(), 'newest' => collect()] : $this->manga->indexBlocks();
        $this->seo('漫画', '漫画,连载,完结', '站内漫画库');

        return view('manga::index', $this->page([
            'list' => $this->manga->paginate($this->manga->pageSizeForType($typeId), $filters),
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
        $this->seo('漫画排行', '漫画排行,人气漫画', '漫画人气与完结榜');

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
        $this->seo('漫画更新', '漫画更新,最近更新', '最近更新的漫画');

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
        $this->seo('我的书架', '漫画书架', '收藏的漫画');

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
        $member = Auth::guard('member')->user();
        $account = $member ? $this->manga->memberHistory((int) $member->id) : [];
        $this->seo('阅读历史', '漫画历史', '最近阅读的漫画');

        return view('manga::history', $this->page([
            'page' => 'history',
            'accountHistory' => $account,
            'loggedIn' => (bool) $member,
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
        $desc = trim(strip_tags((string) ($row->content ?? '')));
        if ($desc === '') {
            $desc = $row->title.' · '.$row->serializeLabel();
        }
        $tags = implode(',', $row->tagNames());
        $this->seo($row->title.' - 漫画', $tags !== '' ? $tags : $row->title, mb_substr($desc, 0, 160));

        return view('manga::show', $this->page([
            'manga' => $row,
            'related' => $this->manga->related($row),
            'comments' => $this->manga->listedComments($row),
            'commentCount' => $this->manga->commentCount($row),
            'favored' => $member ? $this->manga->favored((int) $member->id, (int) $row->id) : false,
            'continueId' => $member ? $this->manga->continueChapterId((int) $member->id, (int) $row->id) : 0,
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
        $member = Auth::guard('member')->user();
        if ($member) {
            $this->manga->recordHistory((int) $member->id, $row, $ep);
        }
        $allPics = $ep->picList();
        $gate = app(\Plugins\Manga\Services\MangaAccessService::class)->gate(
            $member instanceof \App\Models\Member\Member ? $member : null,
            $ep,
            $allPics
        );
        $epName = (string) ($ep->name ?: ('第'.$ep->id.'话'));
        $this->seo($row->title.' · '.$epName, $row->title.',漫画阅读', $row->title.' '.$epName);

        return view('manga::read', $this->page([
            'manga' => $row,
            'chapter' => $ep,
            'pics' => $gate['pics'],
            'gate' => $gate,
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

    private function seo(string $title, string $keywords = '', string $description = ''): void
    {
        $site = $this->front->bootSite();
        $this->context->setSite(is_array($site) ? $site : []);
        $siteTitle = (string) ($site['title'] ?? config('app.name'));
        $full = $title !== '' ? ($title.' - '.$siteTitle) : $siteTitle;
        $this->context->setSeo($full, $keywords, $description);
    }

    /** @param  array<string, mixed>  $extra */
    private function page(array $extra): array
    {
        return array_merge([
            'site' => $this->front->bootSite(),
            'listUrl' => fn (array $over = []) => $this->manga->listUrl($over),
        ], $extra);
    }
}
