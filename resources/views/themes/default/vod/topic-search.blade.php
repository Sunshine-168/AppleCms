@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => '搜专题'])
    <h1>搜专题 {{ $q }}</h1>
    <form class="search topic-search" action="{{ vod_url('topic_search') }}" method="get">
        <input type="search" name="wd" value="{{ $q }}" placeholder="搜专题">
        <button type="submit">搜索</button>
    </form>
    @if($q === '')
        <p class="muted">请输入专题名</p>
    @else
        <div class="grid">
            @vodTopic(['wd' => $q, 'page' => true, 'num' => 20])
                <article class="card">
                    <a href="{{ $item->url }}">
                        @if($item->cover)
                            <img src="{{ $item->cover }}" alt="{{ $item->name }}">
                        @else
                            <img alt="{{ $item->name }}">
                        @endif
                        <div class="meta">
                            <h3>{{ $item->name }}</h3>
                            <div class="muted">{{ $item->blurb }}</div>
                        </div>
                    </a>
                </article>
            @endvodTopic
        </div>
        @if(!app(\App\Cms\CmsViewContext::class)->paginator() || app(\App\Cms\CmsViewContext::class)->paginator()->isEmpty())
            <p class="muted">没有叫这个名字的专题</p>
        @endif
        @vodPaginate
    @endif
@endsection
