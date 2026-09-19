@extends('themes.default.layout')

@section('content')
@php
    $cover = trim((string) ($topic->cover ?? ''));
    $total = $videos instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator ? $videos->total() : $videos->count();
@endphp
    @vodBreadcrumb(['last' => $topic->name])

    <div class="topic-hero{{ $cover === '' ? ' is-empty' : '' }}">
        @if($cover !== '')
            <img src="{{ $cover }}" alt="" class="topic-hero-bg" aria-hidden="true">
        @endif
        <div class="topic-hero-body">
            <h1>{{ $topic->name }}</h1>
            @if(trim((string) ($topic->sub ?? '')) !== '')
                <p class="topic-sub">{{ $topic->sub }}</p>
            @endif
            @if(trim((string) ($topic->blurb ?? '')) !== '')
                <p class="muted">{{ $topic->blurb }}</p>
            @endif
            <p class="topic-stat">本专题共 <strong>{{ $total }}</strong> 部影片</p>
        </div>
    </div>

    @if(trim(strip_tags((string) ($topic->content ?? ''))) !== '')
        <div class="desc detail-desc topic-content">{!! $topic->content !!}</div>
    @endif

    <section class="home-sec">
        <div class="sec-head"><h2>专题影片</h2></div>
        @if($total < 1)
            <div class="list-empty">
                <p>这个专题还没有影片</p>
            </div>
        @else
            <div class="grid">
                @foreach($videos as $item)
                    @include('themes.default.partials.vod-card')
                @endforeach
            </div>
            @vodPaginate
        @endif
    </section>

    @if(isset($arts) && count($arts))
        <section class="home-sec">
            <div class="sec-head"><h2>相关文章</h2></div>
            <div class="art-list">
                @foreach($arts as $art)
                    <article class="art-card">
                        <a href="{{ $art->url }}">
                            @if(trim((string) ($art->cover ?? '')) !== '')
                                <img src="{{ $art->cover }}" alt="" loading="lazy">
                            @endif
                            <div class="meta">
                                <h3>{{ $art->title }}</h3>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
@endsection
