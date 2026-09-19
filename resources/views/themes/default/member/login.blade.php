@extends('themes.default.layout')

@section('content')
    <div class="auth-page">
        <div class="auth-shell">
            <aside class="auth-aside" aria-hidden="true">
                <p class="auth-brand">{{ $site['title'] ?? config('app.name') }}</p>
                <h2>欢迎回来</h2>
                <p class="muted">登录后可收藏、追更、用积分兑换，观看记录也会同步到账号。</p>
            </aside>

            <section class="auth-card">
                <header class="auth-head">
                    <h1>会员登录</h1>
                    <p class="muted">使用注册邮箱登录</p>
                </header>

                @if ($errors->any())
                    <div class="auth-alert" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif
                @if (session('ok'))
                    <div class="auth-alert is-ok" role="status">{{ session('ok') }}</div>
                @endif
                @if (session('error') || session('fail'))
                    <div class="auth-alert" role="alert">{{ session('error') ?: session('fail') }}</div>
                @endif

                <form class="auth-form" method="post" action="{{ url('/member/login') }}" autocomplete="on">
                    @csrf
                    <label class="auth-field">
                        <span>邮箱</span>
                        <input class="lay-like" name="email" type="email" placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
                    </label>
                    <label class="auth-field">
                        <span>密码</span>
                        <input name="password" type="password" placeholder="输入密码" required>
                    </label>

                    <div class="auth-actions">
                        <button type="submit" class="btn-play">登录</button>
                        <a class="btn-ghost" href="{{ url('/member/register') }}">注册账号</a>
                    </div>
                </form>

                @if(View::exists('connect::buttons'))
                    <div class="auth-social">
                        <p class="auth-social-label muted">其他方式</p>
                        @include('connect::buttons')
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
