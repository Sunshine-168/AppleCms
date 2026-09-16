<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vodSeo
    <link rel="stylesheet" href="{{ asset('css/vod.css') }}">
</head>
<body>
<header class="site">
    <div class="wrap">
        <a class="logo" href="{{ vod_url('home') }}">{{ $site['title'] ?? config('app.name') }}</a>
        <nav class="main">
            @vodType(['type' => 'top'])
                <a href="{{ $item->url }}">{{ $item->name }}</a>
            @endvodType
            @vodTopic(['num' => 6])
                <a href="{{ $item->url }}">{{ $item->name }}</a>
            @endvodTopic
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
</body>
</html>
