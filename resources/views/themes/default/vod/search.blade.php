@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => '搜索'])
    <div class="list-head">
        <h1>搜索</h1>
        <p class="muted">支持片名、演员等模糊匹配</p>
    </div>
    <form class="search list-search" action="{{ vod_url('search') }}" method="get">
        <input type="search" name="wd" value="{{ $q }}" placeholder="输入片名 / 演员 / 关键词" aria-label="搜索">
        <button type="submit" class="btn-ghost">搜索</button>
    </form>
    @if($q === '')
        <div class="list-empty">
            <p>输入关键词开始搜索</p>
            <p class="muted">支持片名、演员等模糊匹配。</p>
        </div>
    @else
        <p class="list-result muted">“{{ $q }}” 的搜索结果</p>
        <div class="grid">
            @vod(['wd' => $q, 'page' => true, 'num' => 24])
                @include('themes.default.partials.vod-card')
            @endvod
        </div>
        @if(!app(\App\Cms\CmsViewContext::class)->paginator() || app(\App\Cms\CmsViewContext::class)->paginator()->isEmpty())
            <div class="list-empty">
                <p>没有找到相关影片</p>
                <p class="muted">换个词试试，或回首页逛逛。</p>
                <p><a class="btn-link" href="{{ url('/') }}">回首页</a></p>
            </div>
        @endif
        @vodPaginate
    @endif
@endsection
