@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => '搜专题'])
    <div class="list-head">
        <h1>搜专题</h1>
        <p class="muted">按专题名称找合集</p>
    </div>
    <form class="search list-search" action="{{ vod_url('topic_search') }}" method="get">
        <input type="search" name="wd" value="{{ $q }}" placeholder="专题名" aria-label="搜专题">
        <button type="submit" class="btn-ghost">搜索</button>
    </form>
    @if($q === '')
        <div class="list-empty">
            <p>输入专题名开始搜索</p>
            <p><a class="btn-link" href="{{ vod_url('topics') }}">去专题列表</a></p>
        </div>
    @else
        <p class="list-result muted">“{{ $q }}” 的搜索结果</p>
        <div class="topic-grid">
            @vodTopic(['wd' => $q, 'page' => true, 'num' => 20])
                @php
                    $cover = trim((string) ($item->cover ?? ''));
                    $count = 0;
                    try { $count = (int) $item->videos()->count(); } catch (\Throwable) { $count = 0; }
                @endphp
                <article class="topic-card">
                    <a class="topic-card-cover{{ $cover === '' ? ' is-empty' : '' }}" href="{{ $item->url }}" title="{{ $item->name }}">
                        @if($cover !== '')
                            <img src="{{ $cover }}" alt="{{ $item->name }}" loading="lazy">
                        @endif
                        <span class="topic-card-empty">专题</span>
                        @if($count > 0)<em class="topic-card-count">{{ $count }} 部</em>@endif
                    </a>
                    <div class="topic-card-meta">
                        <h3><a href="{{ $item->url }}">{{ $item->name }}</a></h3>
                        @if(trim((string) ($item->blurb ?? '')) !== '')
                            <p class="muted">{{ $item->blurb }}</p>
                        @endif
                    </div>
                </article>
            @endvodTopic
        </div>
        @if(!app(\App\Cms\CmsViewContext::class)->paginator() || app(\App\Cms\CmsViewContext::class)->paginator()->isEmpty())
            <div class="list-empty">
                <p>没有叫这个名字的专题</p>
                <p><a class="btn-link" href="{{ vod_url('topics') }}">去专题列表</a></p>
            </div>
        @endif
        @vodPaginate
    @endif
@endsection
