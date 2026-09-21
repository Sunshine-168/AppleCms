@extends('themes.default.layout')
@section('content')
@php
    $cateId = (int) ($cateId ?? 0);
    $q = (string) ($q ?? '');
    $recommended = $recommended ?? collect();
    $channels = $channels ?? collect();
    $categories = $categories ?? collect();
@endphp
<div class="list-head manga-head">
    <h1>直播</h1>
    <p class="muted">精选 IPTV 电视频道。推荐使用 HLS（.m3u8）线路。</p>
</div>

<form class="search list-search" method="get" action="{{ $cateId > 0 ? url('/live/cate/'.$cateId) : url('/live') }}">
    <input type="search" name="q" value="{{ $q }}" placeholder="搜频道名、副标题" aria-label="搜直播">
    <button type="submit" class="btn-ghost">搜索</button>
</form>

<nav class="queue-chips live-cates" aria-label="直播分类">
    <a class="chip{{ $cateId === 0 ? ' is-on' : '' }}" href="{{ url('/live'.($q !== '' ? '?q='.urlencode($q) : '')) }}">全部</a>
    @foreach($categories as $category)
        <a class="chip{{ $cateId === (int) $category->id ? ' is-on' : '' }}" href="{{ url('/live/cate/'.$category->id.($q !== '' ? '?q='.urlencode($q) : '')) }}">{{ $category->name }}</a>
    @endforeach
</nav>

@if($recommended->isNotEmpty())
    <section class="live-hot" aria-label="推荐频道">
        <h2>推荐</h2>
        <div class="live-hot-list">
            @foreach($recommended as $item)
                <a href="{{ $item->front_url }}">{{ $item->title }}@if((int) ($item->recommend ?? 0) > 0)<em>荐{{ (int) $item->recommend }}</em>@endif</a>
            @endforeach
        </div>
    </section>
@endif

<div class="grid live-grid">
    @forelse($channels as $channel)
        <article class="vod-card">
            <a class="vod-card-cover{{ $channel->cover ? '' : ' is-empty' }}" href="{{ $channel->front_url }}">
                @if($channel->cover)<img src="{{ $channel->cover }}" alt="{{ $channel->title }}" loading="lazy">@endif
                <span class="vod-card-empty">LIVE</span>
            </a>
            <div class="vod-card-meta">
                <h3><a href="{{ $channel->front_url }}">{{ $channel->title }}</a></h3>
                <p class="muted">{{ $channel->sub ?: $channel->cate_name }}@if((int) ($channel->hits ?? 0) > 0) · {{ (int) $channel->hits }}@endif</p>
            </div>
        </article>
    @empty
        <div class="list-empty">
            <p>暂无上线频道</p>
            <p class="muted">后台「直播」添加并上架后会出现在这里。</p>
        </div>
    @endforelse
</div>

@if(($paginator ?? null) && method_exists($paginator, 'hasPages') && $paginator->hasPages())
    <div class="pager">{{ $paginator->withQueryString()->links() }}</div>
@endif
@endsection
@push('head')
<style>
.live-cates{display:flex;gap:8px;flex-wrap:wrap;margin:0 0 22px}.live-cates .chip{padding:7px 14px;border-radius:999px;background:var(--vod-card,#fff);text-decoration:none;color:inherit}.live-cates .is-on{background:var(--vod-primary,#e53935);color:#fff}
.live-hot{margin:0 0 22px}.live-hot h2{margin:0 0 10px;font-size:18px}.live-hot-list{display:flex;flex-wrap:wrap;gap:8px}.live-hot-list a{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid rgba(128,128,128,.25);border-radius:999px;background:var(--vod-card,#fff);text-decoration:none;color:inherit;font-size:13px}.live-hot-list em{font-style:normal;color:#888;font-size:12px}
</style>
@endpush
