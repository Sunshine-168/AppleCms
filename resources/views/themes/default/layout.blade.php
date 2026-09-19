<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vodSeo
    @if(!empty($site['theme_favicon']))
    <link rel="icon" href="{{ $site['theme_favicon'] }}">
    @endif
    @if(!empty($site['theme_webapp']))
    <link rel="apple-touch-icon" href="{{ $site['theme_webapp'] }}">
    @endif
    @if(!empty($site['theme_head_code']))
    {!! $site['theme_head_code'] !!}
    @endif
    <link rel="stylesheet" href="{{ asset('css/vod.css') }}">
    @stack('head')
    @if(!empty($site['theme_primary']))
    <style>:root{--vod-primary: {{ $site['theme_primary'] }};}</style>
    @endif
</head>
<body>
@includeIf('advert::top')
<header class="site" id="site-header">
    <div class="wrap header-inner">
        <a class="logo" href="{{ vod_url('home') }}">
            @if(!empty($site['theme_logo']))
                <img src="{{ $site['theme_logo'] }}" alt="{{ $site['title'] ?? config('app.name') }}">
            @else
                {{ $site['title'] ?? config('app.name') }}
            @endif
        </a>
        <button type="button" class="nav-toggle" id="nav-toggle" aria-expanded="false" aria-controls="site-nav" aria-label="打开菜单">
            <span class="nav-toggle-bars" aria-hidden="true"></span>
        </button>
        <nav class="main" id="site-nav" aria-label="主导航">
            @includeIf('manga::nav')
            @includeIf('live::nav')
            @vodType(['type' => 'top'])
                @php
                    $navTypeIds = method_exists($item, 'descendantIds') ? $item->descendantIds() : [(int) $item->id];
                    $navHasVod = \App\Models\Video\VideoModel::query()
                        ->published()
                        ->where(function ($q) use ($navTypeIds) {
                            $q->whereIn('type_id', $navTypeIds)->orWhereIn('type_pid', $navTypeIds);
                        })
                        ->exists();
                @endphp
                @if($navHasVod)
                    <a href="{{ $item->url }}">{{ $item->name }}</a>
                @endif
            @endvodType
            @if((string) ($site['theme_nav_latest'] ?? '1') === '1')
                <a href="{{ vod_url('latest') }}">最新</a>
            @endif
            @if((string) ($site['theme_nav_topic'] ?? '1') === '1')
                <a href="{{ vod_url('topics') }}">专题</a>
            @endif
            @if((string) ($site['theme_nav_actor'] ?? '1') === '1')
                <a href="{{ vod_url('actors') }}">演员</a>
            @endif
            @if((string) ($site['theme_nav_role'] ?? '1') === '1')
                <a href="{{ vod_url('roles') }}">角色</a>
            @endif
            @if((string) ($site['theme_nav_art'] ?? '1') === '1')
                <a href="{{ vod_url('arts') }}">资讯</a>
            @endif
            @if((string) ($site['theme_nav_website'] ?? '1') === '1')
                <a href="{{ vod_url('websites') }}">导航</a>
            @endif
            @for($i = 1; $i <= 4; $i++)
                @php
                    $navName = trim((string) ($site['theme_nav_name'.$i] ?? ''));
                    $navUrl = trim((string) ($site['theme_nav_url'.$i] ?? ''));
                @endphp
                @if($navName !== '' && $navUrl !== '')
                    <a href="{{ $navUrl }}">{{ $navName }}</a>
                @endif
            @endfor
            @includeIf('mall::nav')
            @unless(View::exists('advert::top'))
            @vodAd(['slot' => 'header'])
                {!! $item->content !!}
            @endvodAd
            @endunless
        </nav>
        <div class="header-tools">
            @if(request()->is('manga*'))
            <form class="search" action="{{ url('/manga') }}" method="get">
                <input type="search" name="wd" value="{{ request('wd') }}" placeholder="搜漫画" aria-label="搜漫画">
                <button type="submit" aria-label="搜索">搜索</button>
            </form>
            @else
            <form class="search" action="{{ vod_url('search') }}" method="get">
                <input type="search" name="wd" value="{{ request('wd', request('q')) }}" placeholder="搜影片" aria-label="搜影片">
                <button type="submit" aria-label="搜索">搜索</button>
            </form>
            @endif
            <nav class="user-nav" aria-label="用户">
                @auth('member')
                    <a class="btn-user" href="{{ url('/member') }}">{{ auth('member')->user()->name }}</a>
                @else
                    <a href="{{ url('/member/login') }}">登录</a>
                    <a class="btn-reg" href="{{ url('/member/register') }}">注册</a>
                @endauth
            </nav>
        </div>
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
    @includeIf('advert::content')
    @yield('content')
</main>
<footer class="site">
    <div class="wrap foot-inner">
        @if(!empty($site['theme_logo_foot']))
            <div class="foot-logo"><img src="{{ $site['theme_logo_foot'] }}" alt="{{ $site['title'] ?? config('app.name') }}"></div>
        @endif
        @if(\Illuminate\Support\Facades\View::exists('friendlink::footer'))
            @include('friendlink::footer')
        @else
        <div class="flink">
        @vodLink
            <a href="{{ $item->url }}" target="_blank" rel="nofollow">{{ $item->name }}</a>
        @endvodLink
        </div>
        @endif
        @unless(View::exists('advert::bottom'))
        @vodAd(['slot' => 'footer'])
            {!! $item->content !!}
        @endvodAd
        @endunless
        <div class="foot-meta">
            <span>{{ $site['title'] ?? config('app.name') }} · LaraVideo</span>
            <span class="foot-links">
                <a href="{{ url('/gbook') }}">留言</a>
                <a href="{{ url('/links/apply') }}">友链申请</a>
                <a href="{{ url('/member') }}">会员</a>
            </span>
        </div>
        @if(!empty($site['theme_foot_code']))
            <div class="foot-code">{!! $site['theme_foot_code'] !!}</div>
        @endif
    </div>
</footer>
@includeIf('advert::bottom')
@if(!empty($site['analytics_code']))
{!! $site['analytics_code'] !!}
@endif
<button type="button" class="site-gotop" id="site-gotop" aria-label="回到顶部" hidden>↑</button>
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
(function () {
    var header = document.getElementById('site-header');
    var btn = document.getElementById('nav-toggle');
    if (header && btn) {
        btn.addEventListener('click', function () {
            var open = header.classList.toggle('is-nav-open');
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            btn.setAttribute('aria-label', open ? '关闭菜单' : '打开菜单');
        });
    }
    var topBtn = document.getElementById('site-gotop');
    if (topBtn) {
        var sync = function () {
            var show = window.scrollY > 480;
            topBtn.hidden = !show;
            topBtn.classList.toggle('is-on', show);
        };
        window.addEventListener('scroll', sync, { passive: true });
        sync();
        topBtn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
})();
</script>
@stack('scripts')
</body>
</html>
