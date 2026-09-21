<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Models\Member\MemberFavorite;
use App\Models\Member\MemberHistory;
use App\Models\Member\MemberInvite;
use App\Models\Video\VideoModel;
use App\Services\Member\MemberActivityService;
use App\Services\Video\InteractionService;
use App\Services\Video\SiteFrontService;
use App\Support\AppApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MemberController extends Controller
{
    public function __construct(
        private readonly SiteFrontService $front,
        private readonly InteractionService $interaction,
    ) {}

    public function login(Request $request): JsonResponse
    {
        $email = strtolower(trim((string) $request->input('email', $request->input('username', ''))));
        $password = (string) $request->input('password', '');
        if ($email === '' || $password === '') {
            return AppApi::fail('请填写账号和密码');
        }
        $ok = Auth::guard('member')->attempt(
            ['email' => $email, 'password' => $password, 'status' => 1],
            false
        );
        if (! $ok) {
            return AppApi::fail('账号或密码错误');
        }
        $user = Auth::guard('member')->user();
        if (! $user) {
            return AppApi::fail('账号或密码错误');
        }
        $user->last_login_at = time();
        $token = $this->issueToken($user);
        $user->save();

        return AppApi::ok([
            'token' => $token,
            'member' => AppApi::member($user),
        ], '登录成功');
    }

    public function register(Request $request): JsonResponse
    {
        $result = $this->interaction->register($request->all() + ['ip' => (string) $request->ip()]);
        if ($result['code'] !== 0) {
            return AppApi::fail((string) $result['msg']);
        }
        $user = Auth::guard('member')->loginUsingId((int) $result['data']['id']);
        $token = $user ? $this->issueToken($user) : '';
        if ($user) {
            $user->save();
        }

        return AppApi::ok([
            'token' => $token,
            'member' => $user ? AppApi::member($user) : [],
        ], (string) ($result['msg'] ?: '注册成功'));
    }

    public function logout(Request $request): JsonResponse
    {
        $user = Auth::guard('member')->user();
        if ($user && Schema::hasColumn('members', 'api_token')) {
            $user->api_token = '';
            $user->save();
        }
        Auth::guard('member')->logout();

        return AppApi::ok([], '已退出');
    }

    public function center(): JsonResponse
    {
        $this->front->bootSite();
        $member = Auth::guard('member')->user();
        $invites = Schema::hasTable('member_invites')
            ? MemberInvite::query()->where('member_id', $member->id)->orderByDesc('id')->get()
                ->map(fn ($row) => [
                    'id' => (int) $row->id,
                    'code' => (string) ($row->code ?? ''),
                    'used' => (int) ($row->used ?? $row->used_at ?? 0) > 0,
                ])->values()->all()
            : [];

        $growth = app(\App\Services\Member\MemberGrowthService::class);
        $stats = $growth->myStats($member);

        return AppApi::ok([
            'member' => AppApi::member($member),
            'invites' => $invites,
            'invite' => $stats,
            'rank' => $growth->rank('month', 10),
        ]);
    }

    public function password(Request $request): JsonResponse
    {
        $result = $this->interaction->changePassword(
            Auth::guard('member')->user(),
            (string) $request->input('old_password', ''),
            (string) $request->input('password', '')
        );

        return $result['code'] === 0 ? AppApi::ok([], $result['msg']) : AppApi::fail($result['msg']);
    }

    public function redeem(Request $request): JsonResponse
    {
        $result = $this->interaction->redeemCard(
            Auth::guard('member')->user(),
            (string) $request->input('code', '')
        );

        return response()->json([
            'code' => $result['code'],
            'msg' => $result['msg'],
            'data' => $result['data'] ?? [],
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function generateInvite(): JsonResponse
    {
        $result = $this->interaction->generateInvite(Auth::guard('member')->user());

        return response()->json([
            'code' => $result['code'],
            'msg' => $result['msg'],
            'data' => $result['data'] ?? [],
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function inviteRank(Request $request): JsonResponse
    {
        $period = (string) $request->query('period', 'month');
        if (! in_array($period, ['month', 'all'], true)) {
            $period = 'month';
        }
        $growth = app(\App\Services\Member\MemberGrowthService::class);

        return AppApi::ok([
            'period' => $period,
            'rank' => $growth->rank($period, 50),
            'mine' => $growth->myStats(Auth::guard('member')->user()),
        ]);
    }

    public function invitePoster()
    {
        $png = app(\App\Services\Member\MemberGrowthService::class)->posterPng(Auth::guard('member')->user());
        if ($png === null || $png === '') {
            return AppApi::fail('无法生成海报');
        }

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="invite.png"',
        ]);
    }

    public function favorites(): JsonResponse
    {
        $member = Auth::guard('member')->user();
        $ids = MemberFavorite::query()->where('member_id', $member->id)->orderByDesc('id')->pluck('video_id');
        $videos = VideoModel::query()->published()->whereIn('id', $ids)->with(['type', 'stat'])->get()
            ->sortBy(fn ($v) => array_search($v->id, $ids->all()))->values();

        return AppApi::ok(['list' => $videos->map(fn ($v) => AppApi::videoCard($v))->all()]);
    }

    public function histories(): JsonResponse
    {
        $member = Auth::guard('member')->user();
        $ids = MemberHistory::query()->where('member_id', $member->id)->orderByDesc('updated_at')->pluck('video_id');
        $videos = VideoModel::query()->published()->whereIn('id', $ids)->with(['type', 'stat'])->get()
            ->sortBy(fn ($v) => array_search($v->id, $ids->all()))->values();

        return AppApi::ok(['list' => $videos->map(fn ($v) => AppApi::videoCard($v))->all()]);
    }

    public function activity(): JsonResponse
    {
        $member = Auth::guard('member')->user();
        $activity = app(MemberActivityService::class);
        $state = $activity->ready()
            ? $activity->frontState($member)
            : ['signed_today' => false, 'days' => 0, 'points' => (int) $member->points, 'tasks' => [], 'milestones' => []];

        return AppApi::ok(['member' => AppApi::member($member), 'state' => $state]);
    }

    public function sign(): JsonResponse
    {
        $result = app(MemberActivityService::class)->sign(Auth::guard('member')->user());

        return response()->json([
            'code' => $result['code'],
            'msg' => $result['msg'],
            'data' => $result['data'] ?? [],
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function inbox(): JsonResponse
    {
        $member = Auth::guard('member')->user();
        $messages = app(\App\Services\Video\SiteOpsService::class)->memberInbox((int) $member->id);
        if (Schema::hasTable('member_pms')) {
            \App\Models\Member\MemberPm::query()->where('to_id', $member->id)->where('is_read', 0)->update(['is_read' => 1]);
        }

        return AppApi::ok([
            'list' => collect($messages)->map(fn ($m) => [
                'id' => (int) ($m->id ?? $m['id'] ?? 0),
                'title' => (string) ($m->title ?? $m['title'] ?? ''),
                'content' => (string) ($m->content ?? $m['content'] ?? ''),
                'created_at' => (int) ($m->created_at ?? $m['created_at'] ?? 0),
            ])->values()->all(),
        ]);
    }

    private function issueToken(\App\Models\Member\Member $user): string
    {
        $token = Str::random(48);
        if (Schema::hasColumn('members', 'api_token')) {
            $user->api_token = $token;
        }

        return $token;
    }
}
