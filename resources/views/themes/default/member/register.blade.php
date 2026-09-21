@extends('themes.default.layout')

@section('content')
    <div class="auth-page">
        <div class="auth-shell">
            <aside class="auth-aside" aria-hidden="true">
                <p class="auth-brand">{{ $site['title'] ?? config('app.name') }}</p>
                <h2>加入会员</h2>
                <p class="muted">注册后可收藏影片、同步观看记录，还能用积分在商城兑换。</p>
            </aside>

            <section class="auth-card">
                <header class="auth-head">
                    <h1>会员注册</h1>
                    <p class="muted">几步填完就能看片</p>
                </header>

                @if ($errors->any())
                    <div class="auth-alert" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif
                @if (session('error') || session('fail'))
                    <div class="auth-alert" role="alert">{{ session('error') ?: session('fail') }}</div>
                @endif

                <form class="auth-form" method="post" action="{{ url('/member/register') }}" autocomplete="on">
                    @csrf
                    <label class="auth-field">
                        <span>昵称</span>
                        <input name="name" placeholder="显示名称" value="{{ old('name') }}" required maxlength="40" autofocus>
                    </label>
                    <label class="auth-field">
                        <span>邮箱</span>
                        <input name="email" type="email" placeholder="name@example.com" value="{{ old('email') }}" required>
                    </label>

                    @includeIf('sms::register')

                    <label class="auth-field">
                        <span>密码</span>
                        <input name="password" type="password" placeholder="至少 6 位" required minlength="6">
                    </label>
                    <label class="auth-field">
                        <span>邀请码 @if((int) ($site['member_invite'] ?? 0) !== 1)<em class="muted">选填</em>@endif</span>
                        <input name="invite" placeholder="有邀请码可填这里" value="{{ old('invite', request('invite')) }}" @if((int) ($site['member_invite'] ?? 0) === 1) required @endif>
                    </label>

                    <div class="auth-actions">
                        <button type="submit" class="btn-play">注册</button>
                        <a class="btn-ghost" href="{{ url('/member/login') }}">已有账号</a>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection
