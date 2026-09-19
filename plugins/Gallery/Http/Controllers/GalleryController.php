<?php

namespace Plugins\Gallery\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Video\SiteFrontService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Plugins\Gallery\Services\GalleryService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GalleryController extends Controller
{
    public function __construct(private readonly GalleryService $service, private readonly SiteFrontService $front) {}

    /** 显示图集列表。 */
    public function index()
    {
        if (! $this->service->ready()) {
            throw new NotFoundHttpException;
        }

        return view('gallery::index', [
            'site' => $this->front->bootSite(),
            'list' => $this->service->paginate(),
            'types' => $this->service->types(),
            'tags' => $this->service->tagCloud(),
            'page' => 'index',
        ]);
    }

    /** 显示图集详情。 */
    public function show(int $id)
    {
        $gallery = $this->service->published($id);
        if (! $gallery) {
            throw new NotFoundHttpException;
        }
        $gallery->increment('hits');
        $member = Auth::guard('member')->user();

        $gallery->load('pics');

        return view('gallery::show', [
            'site' => $this->front->bootSite(),
            'gallery' => $gallery,
            'favored' => $member ? $this->service->favored((int) $member->id, $id) : false,
            'favor_count' => $this->service->favorCount($id),
            'tag_list' => GalleryService::splitTags((string) ($gallery->tags ?? '')),
            'comments' => $this->service->listedComments($gallery),
            'commentCount' => $this->service->commentCount($gallery),
            'page' => 'show',
        ]);
    }

    /** 显示会员收藏夹。 */
    public function shelf()
    {
        $member = Auth::guard('member')->user();
        if (! $member) {
            return redirect()->guest('/member/login');
        }

        return view('gallery::shelf', [
            'site' => $this->front->bootSite(),
            'list' => $this->service->shelf((int) $member->id),
            'page' => 'shelf',
        ]);
    }

    /** 提交图集评论。 */
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

    /** 切换图集收藏。 */
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
