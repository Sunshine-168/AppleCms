@extends('themes.default.layout')
@section('content')
<div class="list-head manga-head"><h1>小说</h1><p class="muted">连载与完结小说。</p></div>
@include('novel::partials.subnav')
<form class="filters" method="get">
    <input type="search" name="wd" value="{{ request('wd') }}" placeholder="搜标题 / 作者 / 标签">
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
    <a class="chip{{ request('tag') === '' || request('tag') === null ? ' active' : '' }}" href="{{ url('/novel') }}">全部标签</a>
    @foreach($tags as $tag)
        <a class="chip{{ request('tag') === $tag ? ' active' : '' }}" href="{{ url('/novel?tag='.urlencode($tag)) }}">{{ $tag }}</a>
    @endforeach
</div>
@endif
<div class="grid">@forelse($list as $row) @include('novel::partials.card', ['row' => $row]) @empty <p class="muted">还没有小说。</p> @endforelse</div>
{{ $list->links() }}
@endsection
