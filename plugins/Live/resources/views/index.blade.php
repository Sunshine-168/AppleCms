@extends('themes.default.layout')
@section('content')
<div class="list-head manga-head">
    <h1>直播</h1>
    <p class="muted">精选 IPTV 电视频道。</p>
</div>
<nav class="queue-chips live-cates" aria-label="直播分类">
    <a class="chip{{ $cateId===0?' is-on':'' }}" href="{{ url('/live') }}">全部</a>
    @foreach($categories as $category)
        <a class="chip{{ $cateId===$category->id?' is-on':'' }}" href="{{ url('/live?cate='.$category->id) }}">{{ $category->name }}</a>
    @endforeach
</nav>
<div class="grid live-grid">
    @forelse($channels as $channel)
        <article class="vod-card">
            <a class="vod-card-cover{{ $channel->cover?'':' is-empty' }}" href="{{ $channel->front_url }}">
                @if($channel->cover)<img src="{{ $channel->cover }}" alt="{{ $channel->title }}" loading="lazy">@endif
                <span class="vod-card-empty">LIVE</span>
            </a>
            <div class="vod-card-meta">
                <h3><a href="{{ $channel->front_url }}">{{ $channel->title }}</a></h3>
                <p class="muted">{{ $channel->sub ?: $channel->cate_name }}</p>
            </div>
        </article>
    @empty
        <p class="muted">暂无上线频道。</p>
    @endforelse
</div>
@endsection
@push('head')
<style>
.live-cates{display:flex;gap:8px;flex-wrap:wrap;margin:0 0 22px}.live-cates .chip{padding:7px 14px;border-radius:999px;background:var(--vod-card,#fff)}.live-cates .is-on{background:var(--vod-primary,#e53935);color:#fff}
</style>
@endpush
