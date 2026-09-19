@extends('themes.default.layout')
@section('content')
<div class="list-head manga-head"><h1>图集</h1><p class="muted">写真与图片专辑。</p></div>
<div class="queue-chips">
    <a class="chip{{ ($page ?? '') === 'index' ? ' active' : '' }}" href="{{ url('/gallery') }}">全部</a>
    <a class="chip{{ ($page ?? '') === 'shelf' ? ' active' : '' }}" href="{{ url('/gallery/shelf') }}">我的收藏</a>
</div>
<form class="filters" method="get">
    <input type="search" name="wd" value="{{ request('wd') }}" placeholder="搜图集 / 作者 / 标签">
    <select name="type"><option value="0">全部分类</option>@foreach($types as $type)<option value="{{ $type->id }}" @selected((int) request('type') === (int) $type->id)>{{ $type->name }}</option>@endforeach</select>
    <select name="order">
        <option value="new" @selected(request('order', 'new') === 'new')>最新</option>
        <option value="hits" @selected(request('order') === 'hits')>人气</option>
        <option value="favor" @selected(request('order') === 'favor')>收藏</option>
    </select>
    @if(request('tag'))<input type="hidden" name="tag" value="{{ request('tag') }}">@endif
    <button class="btn-ghost">搜索</button>
</form>
@if(!empty($tags))
<div class="queue-chips manga-tag-cloud">
    <a class="chip{{ request('tag') === '' || request('tag') === null ? ' active' : '' }}" href="{{ url('/gallery') }}">全部标签</a>
    @foreach($tags as $tag)
        <a class="chip{{ request('tag') === $tag ? ' active' : '' }}" href="{{ url('/gallery?tag='.urlencode($tag)) }}">{{ $tag }}</a>
    @endforeach
</div>
@endif
<div class="grid">
@forelse($list as $row)
<article class="vod-card">
    <a class="vod-card-cover{{ $row->cover ? '' : ' is-empty' }}" href="{{ url('/gallery/'.$row->id) }}">
        @if($row->cover)<img src="{{ $row->cover }}" alt="{{ $row->title }}" loading="lazy">@endif
        <span class="vod-card-empty">暂无封面</span>
    </a>
    <div class="vod-card-meta">
        <h3><a href="{{ url('/gallery/'.$row->id) }}">{{ $row->title }}</a></h3>
        <p class="muted">{{ $row->author ?: '佚名' }} · 人气 {{ (int) $row->hits }}@if(!empty($row->favor_count)) · 收藏 {{ (int) $row->favor_count }}@endif</p>
        @if(!empty($row->tag_list))
            <p class="muted">{{ implode(' · ', array_slice($row->tag_list, 0, 4)) }}</p>
        @endif
    </div>
</article>
@empty
<p class="muted">还没有图集。</p>
@endforelse
</div>
{{ $list->links() }}
@endsection
