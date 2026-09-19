<?php

namespace Plugins\Novel\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Video\SiteFrontService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Plugins\Novel\Services\NovelService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NovelController extends Controller
{
    public function __construct(private readonly NovelService $service, private readonly SiteFrontService $front) {}

    /** 显示小说列表。 */
    public function index()
    {
        if (! $this->service->ready()) {
            throw new NotFoundHttpException;
        }

        return view('novel::index', [
            'site' => $this->front->bootSite(),
            'list' => $this->service->paginate(),
            'types' => $this->service->types(),
            'tags' => $this->service->tagCloud(),
            'page' => 'index',
        ]);
    }

    /** 显示小说详情。 */
    public function show(int $id)
    {
        $novel = $this->service->published($id);
        if (! $novel) {
            throw new NotFoundHttpException;
        }
        $novel->increment('hits');
        $novel->load(['chapters', 'type']);
        $member = Auth::guard('member')->user();

        return view('novel::show', [
            'site' => $this->front->bootSite(),
            'novel' => $novel,
            'favored' => $member ? $this->service->favored((int) $member->id, $id) : false,
            'favor_count' => $this->service->favorCount($id),
            'tag_list' => NovelService::splitTags((string) ($novel->tags ?? '')),
            'comments' => $this->service->listedComments($novel),
            'commentCount' => $this->service->commentCount($novel),
            'page' => 'show',
        ]);
    }

    /** 阅读小说章节。 */
    public function read(int $id, int $chapter)
    {
        $novel = $this->service->published($id);
        $ep = $novel ? $this->service->chapter($novel, $chapter) : null;
        if (! $novel || ! $ep) {
            throw new NotFoundHttpException;
        }
        $near = $this->service->neighbors($novel, $ep);
        $member = Auth::guard('member')->user();
        if ($member) {
            $this->service->recordHistory((int) $member->id, $novel, $ep);
        }

        return view('novel::read', ['site' => $this->front->bootSite(), 'novel' => $novel, 'chapter' => $ep] + $near);
    }

    /** 显示会员小说书架。 */
    public function shelf()
    {
        $member = Auth::guard('member')->user();
        if (! $member) {
            return redirect()->guest('/member/login');
        }

        return view('novel::shelf', [
            'site' => $this->front->bootSite(),
            'list' => $this->service->shelf((int) $member->id),
            'page' => 'shelf',
        ]);
    }

    /** 显示会员阅读历史。 */
    public function history()
    {
        $member = Auth::guard('member')->user();

        return view('novel::history', [
            'site' => $this->front->bootSite(),
            'list' => $member ? $this->service->history((int) $member->id) : collect(),
            'page' => 'history',
        ]);
    }

    /** 提交小说评论。 */
    public function comment(Request $request, int $id): RedirectResponse
    {
        $result = $this->service->addComment(
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

    /** 切换小说收藏。 */
    public function favor(int $id): RedirectResponse
    {
        $member = Auth::guard('member')->user();
        if (! $member) {
            return redirect()->guest('/member/login');
        }
        $result = $this->service->toggleFavor((int) $member->id, $id);

        return back()->with($result['code'] === 0 ? 'status' : 'error', $result['msg']);
    }
}
