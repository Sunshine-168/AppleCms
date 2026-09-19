@extends('themes.default.layout')
@section('content')
@php
    $lines = is_array($channel->url_list ?? null) ? $channel->url_list : [];
@endphp
<p class="breadcrumb"><a href="{{ url('/live') }}">直播</a> / {{ $channel->title }}</p>
<div class="live-page">
    <section class="live-main">
        <div class="live-player"><video id="live-video" controls autoplay playsinline></video></div>
        <div class="list-head">
            <h1>{{ $channel->title }}</h1>
            <p class="muted">{{ $channel->cate_name }} · 播放 {{ (int) $channel->hits }} · 推荐 HLS（.m3u8）</p>
        </div>
        @if($lines === [])
            <p class="muted">暂无可用线路。请到后台补上 m3u8 地址。</p>
        @else
            <div class="live-lines" id="live-lines">
                @foreach($lines as $index => $line)
                    <button type="button" class="btn-ghost{{ $index === 0 ? ' is-on' : '' }}" data-url="{{ $line['url'] }}">{{ $line['name'] }}</button>
                @endforeach
            </div>
        @endif
        @if($channel->remarks)<p>{{ $channel->remarks }}</p>@endif
        @if($channel->content)<div class="desc">{!! nl2br(e($channel->content)) !!}</div>@endif
    </section>
    <aside class="live-side">
        <h2>相关频道</h2>
        @forelse($related as $item)
            <a class="live-side-item" href="{{ $item->front_url }}">
                <strong>{{ $item->title }}</strong><span>{{ $item->cate_name }}</span>
            </a>
        @empty
            <p class="muted">暂无其他频道。</p>
        @endforelse
    </aside>
</div>
@endsection
@push('head')
<style>
.live-page{display:grid;grid-template-columns:minmax(0,1fr) 280px;gap:24px}.live-player{background:#050505;aspect-ratio:16/9}.live-player video{width:100%;height:100%}.live-lines{display:flex;gap:8px;flex-wrap:wrap;margin:12px 0}.live-lines .is-on{background:var(--vod-primary,#e53935);color:#fff}.live-side{background:var(--vod-card,#fff);padding:16px;border-radius:10px}.live-side h2{margin-top:0}.live-side-item{display:flex;justify-content:space-between;gap:10px;padding:11px 0;border-bottom:1px solid rgba(128,128,128,.2);text-decoration:none;color:inherit}.live-side-item span{color:#888}@media(max-width:780px){.live-page{grid-template-columns:1fr}.live-side{order:2}}
</style>
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script>
(function(){
    var video=document.getElementById('live-video'),buttons=document.querySelectorAll('#live-lines [data-url]'),hls;
    if(!buttons.length)return;
    function play(url,button){
        if(hls){hls.destroy();hls=null}
        buttons.forEach(function(el){el.classList.toggle('is-on',el===button)});
        if(/\.m3u8(?:$|\?)/i.test(url)&&window.Hls&&Hls.isSupported()){hls=new Hls();hls.loadSource(url);hls.attachMedia(video)}
        else{video.src=url}
        var promise=video.play();if(promise)promise.catch(function(){});
    }
    buttons.forEach(function(button){button.addEventListener('click',function(){play(button.dataset.url,button)})});
    play(buttons[0].dataset.url,buttons[0]);
})();
</script>
@endpush
