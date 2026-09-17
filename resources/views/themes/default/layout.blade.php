<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vodSeo
    <link rel="stylesheet" href="{{ asset('css/vod.css') }}">
    @if(!empty($site['theme_primary']))
    <style>:root{--vod-primary: {{ $site['theme_primary'] }};}</style>
    @endif
</head>
<body>
<header class="site">
    <div class="wrap">
        <a class="logo" href="{{ vod_url('home') }}">
            @if(!empty($site['theme_logo']))
                <img src="{{ $site['theme_logo'] }}" alt="{{ $site['title'] ?? config('app.name') }}">
            @else
                {{ $site['title'] ?? config('app.name') }}
            @endif
        </a>
        <nav class="main">
            @vodType(['type' => 'top'])
                <a href="{{ $item->url }}">{{ $item->name }}</a>
            @endvodType
            @vodTopic(['num' => 6])
                <a href="{{ $item->url }}">{{ $item->name }}</a>
            @endvodTopic
            <a href="{{ vod_url('latest') }}">最新</a>
            <a href="{{ vod_url('topics') }}">专题</a>
            <a href="{{ vod_url('actors') }}">演员</a>
            <a href="{{ vod_url('roles') }}">角色</a>
            <a href="{{ vod_url('arts') }}">资讯</a>
            <a href="{{ vod_url('websites') }}">导航</a>
            @vodAd(['slot' => 'header'])
                {!! $item->content !!}
            @endvodAd
        </nav>
        <form class="search" action="{{ vod_url('search') }}" method="get">
            <input type="search" name="wd" value="{{ request('wd', request('q')) }}" placeholder="搜影片">
        </form>
        <nav class="main">
            @auth('member')
                <a href="{{ url('/member') }}">{{ auth('member')->user()->name }}</a>
            @else
                <a href="{{ url('/member/login') }}">登录</a>
                <a href="{{ url('/member/register') }}">注册</a>
            @endauth
        </nav>
    </div>
</header>
<main class="wrap page">
    @if(session('status'))
        <div class="flash is-ok" role="status">{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="flash is-err" role="alert">{{ session('error') }}</div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="flash is-err" role="alert">{{ $errors->first() }}</div>
    @endif
    @yield('content')
</main>
<footer class="site">
    <div class="wrap">
        @vodLink
            <a href="{{ $item->url }}" target="_blank" rel="nofollow">{{ $item->name }}</a>
        @endvodLink
        @vodAd(['slot' => 'footer'])
            {!! $item->content !!}
        @endvodAd
        <div>{{ $site['title'] ?? config('app.name') }} · LaraVideo · <a href="{{ url('/gbook') }}">留言</a></div>
    </div>
</footer>
@if(!empty($site['analytics_code']))
{!! $site['analytics_code'] !!}
@endif
<script>
window.vodToast = function (text, type) {
    var old = document.querySelector('.vod-toast');
    if (old) old.remove();
    if (!text) return;
    var el = document.createElement('div');
    el.className = 'vod-toast' + (type === 'ok' ? ' is-ok' : ' is-err');
    el.textContent = text;
    document.body.appendChild(el);
    setTimeout(function () { el.remove(); }, 2800);
};
window.vodResult = function (res, fallback) {
    var msg = (res && (res.msg || res.message)) || fallback || '失败';
    var ok = !!(res && Number(res.code) === 0);
    window.vodToast(msg, ok ? 'ok' : 'err');
    return ok;
};
</script>
</body>
</html>
