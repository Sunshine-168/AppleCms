<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Video\InteractionService;
use App\Support\Utils\Ajax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InteractionController extends Controller
{
    public function __construct(private readonly InteractionService $interaction) {}

    public function comment(Request $request, int $id): RedirectResponse
    {
        $result = $this->interaction->addComment(
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

    public function report(Request $request, int $id): RedirectResponse
    {
        $result = $this->interaction->addReport(
            $id,
            (string) $request->input('content', ''),
            Auth::guard('member')->user(),
            (string) $request->ip()
        );
        if ($result['code'] !== 0) {
            return back()->with('error', $result['msg']);
        }

        return back()->with('status', $result['msg']);
    }

    public function score(Request $request, int $id): JsonResponse
    {
        $result = $this->interaction->score($id, (float) $request->input('score', 0));

        return Ajax::message($result['code'], $result['msg'], $result['data']);
    }

    public function favorite(int $id): JsonResponse
    {
        $member = Auth::guard('member')->user();
        if (! $member) {
            return Ajax::message(1, '请先登录');
        }
        $result = $this->interaction->toggleFavorite((int) $member->id, $id);

        return Ajax::message($result['code'], $result['msg'], $result['data']);
    }

    public function playFail(Request $request): JsonResponse
    {
        $data = app(\App\Services\Video\SiteOpsService::class)->addPlayFail(
            (int) $request->input('video_id', 0),
            (int) $request->input('source_id', 0),
            (int) $request->input('episode_id', 0),
            (string) $request->input('url', ''),
            (string) $request->input('content', ''),
            (string) $request->ip()
        );

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function guestbookForm(\App\Services\Video\SiteFrontService $front): \Illuminate\View\View
    {
        $site = $front->bootSite();

        return view($front->themeView('vod.gbook'), compact('site'));
    }

    public function guestbook(Request $request): RedirectResponse
    {
        $member = Auth::guard('member')->user();
        $data = app(\App\Services\Video\SiteOpsService::class)->addGuestbook(
            (string) $request->input('author_name', $member->name ?? ''),
            (string) $request->input('content', ''),
            (int) ($member->id ?? 0),
            (string) $request->ip()
        );
        if ($data['code'] !== 0) {
            return back()->with('error', $data['msg']);
        }

        return back()->with('status', $data['msg']);
    }
}
