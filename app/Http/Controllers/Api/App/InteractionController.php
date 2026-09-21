<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Services\Member\MemberActivityService;
use App\Services\Video\InteractionService;
use App\Services\Video\SiteOpsService;
use App\Support\AppApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InteractionController extends Controller
{
    public function __construct(private readonly InteractionService $interaction) {}

    public function comment(Request $request, int $id): JsonResponse
    {
        return $this->pack($this->interaction->addComment(
            $id,
            (string) $request->input('content', ''),
            Auth::guard('member')->user(),
            (string) $request->input('author_name', ''),
            (string) $request->ip(),
            1
        ));
    }

    public function artComment(Request $request, int $id): JsonResponse
    {
        return $this->pack($this->interaction->addComment(
            $id,
            (string) $request->input('content', ''),
            Auth::guard('member')->user(),
            (string) $request->input('author_name', ''),
            (string) $request->ip(),
            2
        ));
    }

    public function report(Request $request, int $id): JsonResponse
    {
        $result = $this->interaction->addReport(
            $id,
            (string) $request->input('content', ''),
            Auth::guard('member')->user(),
            (string) $request->ip()
        );

        return $this->pack($result);
    }

    public function score(Request $request, int $id): JsonResponse
    {
        return $this->pack($this->interaction->score($id, (float) $request->input('score', 0)));
    }

    public function favorite(int $id): JsonResponse
    {
        $member = Auth::guard('member')->user();
        if (! $member) {
            return AppApi::fail('请先登录');
        }

        return $this->pack($this->interaction->toggleFavorite((int) $member->id, $id));
    }

    public function share(int $id): JsonResponse
    {
        $member = Auth::guard('member')->user();
        if (! $member) {
            return AppApi::fail('请先登录');
        }

        return $this->pack(app(MemberActivityService::class)->reportShare($member, $id));
    }

    public function playFail(Request $request): JsonResponse
    {
        $data = app(SiteOpsService::class)->addPlayFail(
            (int) $request->input('video_id', 0),
            (int) $request->input('source_id', 0),
            (int) $request->input('episode_id', 0),
            (string) $request->input('url', ''),
            (string) $request->input('content', ''),
            (string) $request->ip()
        );

        return $this->pack($data);
    }

    public function guestbook(Request $request): JsonResponse
    {
        $member = Auth::guard('member')->user();
        $data = app(SiteOpsService::class)->addGuestbook(
            (string) $request->input('author_name', $member->name ?? ''),
            (string) $request->input('content', ''),
            (int) ($member->id ?? 0),
            (string) $request->ip()
        );

        return $this->pack($data);
    }

    public function reportComment(int $id): JsonResponse
    {
        return $this->pack($this->interaction->reportComment($id));
    }

    public function likeComment(int $id): JsonResponse
    {
        return $this->pack($this->interaction->likeComment($id));
    }

    /** @param  array{code:int,msg:string,data?:array<string,mixed>}  $result */
    private function pack(array $result): JsonResponse
    {
        return response()->json([
            'code' => $result['code'],
            'msg' => $result['msg'],
            'data' => $result['data'] ?? [],
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
