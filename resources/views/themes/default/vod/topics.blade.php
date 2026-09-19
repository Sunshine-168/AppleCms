@extends('themes.default.layout')

@section('content')
@php
    $topics = app(\App\Services\Video\Tags\TopicTag::class)->get([
        'num' => 20,
        'page' => true,
        'by' => 'sort',
        'order' => 'desc',
    ]);
@endphp
    @vodBreadcrumb(['last' => '专题'])

    <div class="list-head">
        <h1>专题</h1>
        <p class="muted">精选合集，按主题一口气看完。</p>
    </div>

    <form class="search list-search" action="{{ vod_url('topic_search') }}" method="get">
        <input type="search" name="wd" value="{{ request('wd') }}" placeholder="搜专题名 / 简介" aria-label="搜专题">
        <button type="submit" class="btn-ghost">搜索</button>
    </form>

    @if($topics->isEmpty())
        <div class="list-empty">
            <p>还没有专题</p>
            <p class="muted">后台创建专题」添加合集后会出现在这里。</p>
            <p><a class="btn-link" href="{{ url('/') }}">去逛逛</a></p>
        </div>
    @else
        <div class="topic-grid">
            @foreach($topics as $item)
                @php
                    $cover = trim((string) ($item->cover ?? ''));
                    $count = (int) ($item->videos_count ?? $item->video_count ?? $item->topic_rel_vod_count ?? 0);
                    if ($count < 1 && method_exists($item, 'videos')) {
                        try { $count = (int) $item->videos()->count(); } catch (\Throwable) { $count = 0; }
                    }
                @endphp
                <article class="topic-card">
                    <a class="topic-card-cover{{ $cover === '' ? ' is-empty' : '' }}" href="{{ $item->url }}" title="{{ $item->name }}">
                        @if($cover !== '')
                            <img src="{{ $cover }}" alt="{{ $item->name }}" loading="lazy"
                                 onerror="var p=this.parentElement;this.remove();if(p)p.classList.add('is-empty');">
                        @endif
                        <span class="topic-card-empty">暂无封面</span>
                        @if($count > 0)
                            <em class="topic-card-count">{{ $count }} 部</em>
                        @endif
                    </a>
                    <div class="topic-card-meta">
                        <h3><a href="{{ $item->url }}">{{ $item->name }}</a></h3>
                        @if(trim((string) ($item->blurb ?? '')) !== '')
                            <p class="muted">{{ $item->blurb }}</p>
                        @elseif(trim((string) ($item->sub ?? '')) !== '')
                            <p class="muted">{{ $item->sub }}</p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
        @vodPaginate
    @endif
@endsection
