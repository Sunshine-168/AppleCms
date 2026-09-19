@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => '角色'])

    <div class="list-head">
        <h1>角色</h1>
        <p class="muted">片子里的角色卡，可按名字搜索。</p>
    </div>

    <form class="search list-search" method="get" action="{{ vod_url('roles') }}">
        <input type="search" name="wd" value="{{ request('wd') }}" placeholder="搜角色名" aria-label="搜角色">
        <button type="submit" class="btn-ghost">搜索</button>
    </form>

    @if($roles->isEmpty())
        <div class="list-empty">
            <p>还没有角色</p>
            <p class="muted">后台「角色」添加后会出现在这里。</p>
            <p><a class="btn-link" href="{{ url('/') }}">回首页</a></p>
        </div>
    @else
        <div class="grid">
            @foreach($roles as $item)
                @php
                    $cover = trim((string) ($item->cover ?? ''));
                    $href = $item->url ?? vod_url('role', ['id' => $item->id]);
                    $blurb = trim((string) ($item->blurb ?? ''));
                @endphp
                <article class="vod-card">
                    <a class="vod-card-cover{{ $cover === '' ? ' is-empty' : '' }}" href="{{ $href }}" title="{{ $item->name }}">
                        @if($cover !== '')
                            <img src="{{ $cover }}" alt="{{ $item->name }}" loading="lazy"
                                 onerror="var p=this.parentElement;this.remove();if(p)p.classList.add('is-empty');">
                        @endif
                        <span class="vod-card-empty">暂无封面</span>
                    </a>
                    <div class="vod-card-meta">
                        <h3><a href="{{ $href }}" title="{{ $item->name }}">{{ $item->name }}</a></h3>
                        @if($blurb !== '')
                            <p class="muted">{{ $blurb }}</p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
        @vodPaginate
    @endif
@endsection
