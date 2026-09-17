@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => '专题'])
    <h1>专题</h1>
    <form class="search topic-search" action="{{ vod_url('topic_search') }}" method="get">
        <input type="search" name="wd" value="{{ request('wd') }}" placeholder="搜专题">
        <button type="submit">搜索</button>
    </form>
    <div class="grid">
        @vodTopic(['num' => 20, 'page' => true, 'by' => 'sort', 'order' => 'desc'])
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
                        @if(($item->videos_count ?? $item->video_count ?? $item->topic_rel_vod_count ?? 0) > 0)
                            <div class="muted">{{ $item->videos_count ?? $item->video_count ?? $item->topic_rel_vod_count }} 部</div>
                        @endif
                    </div>
                </a>
            </article>
        @endvodTopic
    </div>
    @if(!app(\App\Cms\CmsViewContext::class)->paginator() || app(\App\Cms\CmsViewContext::class)->paginator()->isEmpty())
        <p class="muted">还没有专题</p>
    @endif
    @vodPaginate
@endsection
