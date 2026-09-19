@extends('themes.default.layout')
@section('content')
<div class="list-head"><h1>我的图集收藏</h1></div>
<div class="queue-chips">
    <a class="chip" href="{{ url('/gallery') }}">全部</a>
    <a class="chip active" href="{{ url('/gallery/shelf') }}">我的收藏</a>
</div>
<div class="grid">
@forelse($list as $row)
<article class="vod-card">
    <a class="vod-card-cover{{ $row->cover ? '' : ' is-empty' }}" href="{{ url('/gallery/'.$row->id) }}">
        @if($row->cover)<img src="{{ $row->cover }}" alt="{{ $row->title }}" loading="lazy">@endif
        <span class="vod-card-empty">暂无封面</span>
    </a>
    <div class="vod-card-meta">
        <h3><a href="{{ url('/gallery/'.$row->id) }}">{{ $row->title }}</a></h3>
        <p class="muted">{{ $row->author ?: '佚名' }}</p>
    </div>
</article>
@empty
<p class="muted">还没有收藏。打开任意图集点「收藏图集」即可。</p>
@endforelse
</div>
@endsection
