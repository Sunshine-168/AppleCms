<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Member\MemberFavorite;
use App\Models\Member\MemberHistory;
use App\Models\Video\VideoModel;
use App\Services\Member\MemberActivityService;
use App\Services\Video\InteractionService;
use App\Services\Video\SiteFrontService;
use App\Support\Utils\Ajax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function __construct(
        private readonly SiteFrontService $front,
        private readonly InteractionService $interaction,
    ) {}

    public function showLogin(): View
    {
        return $this->page('member.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $ok = Auth::guard('member')->attempt(
            ['email' => $request->input('email'), 'password' => $request->input('password'), 'status' => 1],
            $request->boolean('remember')
        );
        if (! $ok) {
            return back()->withErrors(['email' => '账号或密码错误'])->withInput();
        }
        $request->session()->regenerate();
        $user = Auth::guard('member')->user();
        if ($user) {
            $user->last_login_at = time();
            $user->save();
        }

        return redirect()->intended(url('/member'))->with('status', '登录成功');
    }

    public function showRegister(): View
    {
        return $this->page('member.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $result = $this->interaction->register($request->all() + ['ip' => (string) $request->ip()]);
        if ($result['code'] !== 0) {
            return back()->withErrors(['email' => $result['msg']])->withInput();
        }
        Auth::guard('member')->loginUsingId((int) $result['data']['id']);

        return redirect('/member')->with('status', '注册成功');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('member')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', '已退出');
    }

    public function center(): View
    {
        $site = $this->front->bootSite();
        $member = Auth::guard('member')->user();
        $invites = \Illuminate\Support\Facades\Schema::hasTable('member_invites')
            ? \App\Models\Member\MemberInvite::query()->where('member_id', $member->id)->orderByDesc('id')->get()
            : collect();
        $growth = app(\App\Services\Member\MemberGrowthService::class);
        $growthMode = $growth->mode();
        if ($growthMode === 'vip_days') {
            $growth->ensurePersonalCode($member);
            $member->refresh();
        }
        $inviteStats = $growth->myStats($member);
        $inviteRank = $growth->rank('month', 10);

        return view($this->front->themeView('member.center'), compact('site', 'member', 'invites', 'growthMode', 'inviteStats', 'inviteRank'));
    }

    public function password(Request $request): RedirectResponse
    {
        $result = $this->interaction->changePassword(
            Auth::guard('member')->user(),
            (string) $request->input('old_password', ''),
            (string) $request->input('password', '')
        );
        if ($result['code'] !== 0) {
            return back()->withErrors(['password' => $result['msg']]);
        }

        return back()->with('status', $result['msg']);
    }

    public function redeem(Request $request): RedirectResponse
    {
        $result = $this->interaction->redeemCard(
            Auth::guard('member')->user(),
            (string) $request->input('code', '')
        );
        if ($result['code'] !== 0) {
            return back()->withErrors(['code' => $result['msg']]);
        }

        return back()->with('status', $result['msg']);
    }

    public function generateInvite(): RedirectResponse
    {
        $result = $this->interaction->generateInvite(Auth::guard('member')->user());
        if ($result['code'] !== 0) {
            return back()->withErrors(['invite' => $result['msg']]);
        }

        return back()->with('status', $result['msg']);
    }

    public function invitePoster()
    {
        $png = app(\App\Services\Member\MemberGrowthService::class)->posterPng(Auth::guard('member')->user());
        if ($png === null || $png === '') {
            return back()->with('error', '无法生成海报');
        }

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="invite.png"',
        ]);
    }

    public function favorites(): View
    {
        $site = $this->front->bootSite();
        $member = Auth::guard('member')->user();
        $ids = MemberFavorite::query()->where('member_id', $member->id)->orderByDesc('id')->pluck('video_id');
        $videos = VideoModel::query()->published()->whereIn('id', $ids)->with(['type', 'stat'])->get();

        return view($this->front->themeView('member.list'), [
            'site' => $site,
            'member' => $member,
            'title' => '我的收藏',
            'videos' => $videos,
        ]);
    }

    public function histories(): View
    {
        $site = $this->front->bootSite();
        $member = Auth::guard('member')->user();
        $ids = MemberHistory::query()->where('member_id', $member->id)->orderByDesc('updated_at')->pluck('video_id');
        $videos = VideoModel::query()->published()->whereIn('id', $ids)->with(['type', 'stat'])->get()
            ->sortBy(fn ($v) => array_search($v->id, $ids->all()))->values();

        return view($this->front->themeView('member.list'), [
            'site' => $site,
            'member' => $member,
            'title' => '观看历史',
            'videos' => $videos,
        ]);
    }

    public function activity(): View
    {
        $site = $this->front->bootSite();
        $member = Auth::guard('member')->user();
        $activity = app(MemberActivityService::class);
        $state = $activity->ready()
            ? $activity->frontState($member)
            : ['signed_today' => false, 'days' => 0, 'points' => (int) $member->points, 'tasks' => [], 'milestones' => []];

        return view($this->front->themeView('member.activity'), compact('site', 'member', 'state'));
    }

    public function sign(Request $request): JsonResponse|RedirectResponse
    {
        $result = app(MemberActivityService::class)->sign(Auth::guard('member')->user());
        if ($request->expectsJson() || $request->ajax()) {
            return Ajax::message($result['code'], $result['msg'], $result['data'] ?? []);
        }
        if ($result['code'] !== 0) {
            return back()->with('error', $result['msg']);
        }

        return back()->with('status', $result['msg']);
    }

    public function inbox(): View
    {
        $site = $this->front->bootSite();
        $member = Auth::guard('member')->user();
        $messages = app(\App\Services\Video\SiteOpsService::class)->memberInbox((int) $member->id);
        \App\Models\Member\MemberPm::query()->where('to_id', $member->id)->where('is_read', 0)->update(['is_read' => 1]);

        return view($this->front->themeView('member.inbox'), compact('site', 'member', 'messages'));
    }

    private function page(string $view): View
    {
        $site = $this->front->bootSite();

        return view($this->front->themeView($view), compact('site'));
    }
}
