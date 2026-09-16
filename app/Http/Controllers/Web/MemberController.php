<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Member\MemberFavorite;
use App\Models\Member\MemberHistory;
use App\Models\Video\VideoModel;
use App\Services\Video\InteractionService;
use App\Services\Video\SiteFrontService;
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
        $result = $this->interaction->register($request->all());
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

        return view($this->front->themeView('member.center'), compact('site', 'member'));
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

    private function page(string $view): View
    {
        $site = $this->front->bootSite();

        return view($this->front->themeView($view), compact('site'));
    }
}
