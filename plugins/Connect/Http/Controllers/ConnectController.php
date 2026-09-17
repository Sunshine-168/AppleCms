<?php

namespace Plugins\Connect\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Plugins\Connect\Services\ConnectService;

class ConnectController extends Controller
{
    public function __construct(private readonly ConnectService $connect) {}

    public function redirectQq(Request $request): RedirectResponse
    {
        $state = Str::random(16);
        $request->session()->put('oauth_state', $state);
        $url = $this->connect->qqAuthorizeUrl($state);
        if (is_array($url)) {
            return redirect('/member/login')->withErrors(['email' => $url['msg'] ?? '未配置 QQ 登录参数']);
        }

        return redirect()->away($url);
    }

    public function callbackQq(Request $request): RedirectResponse
    {
        if (! $this->stateOk($request)) {
            return redirect('/member/login')->withErrors(['email' => '登录状态失效，请重试']);
        }
        $result = $this->connect->loginQq((string) $request->query('code', ''));

        return $this->finish($result);
    }

    public function redirectWechat(Request $request): RedirectResponse
    {
        $state = Str::random(16);
        $request->session()->put('oauth_state', $state);
        $url = $this->connect->wechatAuthorizeUrl($state);
        if (is_array($url)) {
            return redirect('/member/login')->withErrors(['email' => $url['msg'] ?? '未配置微信登录参数']);
        }

        return redirect()->away($url);
    }

    public function callbackWechat(Request $request): RedirectResponse
    {
        if (! $this->stateOk($request)) {
            return redirect('/member/login')->withErrors(['email' => '登录状态失效，请重试']);
        }
        $result = $this->connect->loginWechat((string) $request->query('code', ''));

        return $this->finish($result);
    }

    private function stateOk(Request $request): bool
    {
        $expect = (string) $request->session()->pull('oauth_state', '');
        $got = (string) $request->query('state', '');

        return $expect !== '' && hash_equals($expect, $got);
    }

    /** @param array{code:int,msg:string,data?:array<string,mixed>} $result */
    private function finish(array $result): RedirectResponse
    {
        if (($result['code'] ?? 1) !== 0) {
            return redirect('/member/login')->withErrors(['email' => $result['msg'] ?? '登录失败']);
        }
        $id = (int) ($result['data']['id'] ?? 0);
        if ($id < 1) {
            return redirect('/member/login')->withErrors(['email' => '登录失败']);
        }
        Auth::guard('member')->loginUsingId($id);
        $user = Auth::guard('member')->user();
        if ($user) {
            $user->last_login_at = time();
            $user->save();
        }

        return redirect('/member')->with('status', $result['msg'] ?? '登录成功');
    }
}
