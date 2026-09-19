@extends('themes.default.layout')

@section('content')
@php
    $list = $list ?? collect();
    $types = collect();
    if (\Illuminate\Support\Facades\Schema::hasTable('video_types') && \Illuminate\Support\Facades\Schema::hasColumn('video_types', 'mid')) {
        $types = \App\Models\Video\VideoTypeModel::query()->active()->where('mid', 3)->where(function ($q) {
            $q->where('parent_id', 0)->orWhereNull('parent_id');
        })->orderByDesc('sort')->orderBy('id')->get();
    }
    $typeId = (int) request('type_id', request('t', 0));
@endphp
    @vodBreadcrumb(['last' => '网址导航'])

    <div class="list-head">
        <h1>网址导航</h1>
        <p class="muted">常用工具与站外资源入口。</p>
    </div>

    <form class="search list-search" method="get" action="{{ vod_url('websites') }}">
        @if($typeId > 0)
            <input type="hidden" name="type_id" value="{{ $typeId }}">
        @endif
        <input type="search" name="wd" value="{{ request('wd') }}" placeholder="搜站名" aria-label="搜网址">
        <button type="submit" class="btn-ghost">搜索</button>
    </form>

    @if($types->isNotEmpty())
        <nav class="art-tabs" aria-label="导航分类">
            <a href="{{ vod_url('websites') }}" class="{{ $typeId === 0 ? 'on' : '' }}">全部</a>
            @foreach($types as $type)
                <a href="{{ vod_url('websites') }}?type_id={{ $type->id }}" class="{{ $typeId === (int) $type->id ? 'on' : '' }}">{{ $type->name }}</a>
            @endforeach
        </nav>
    @endif

    @if($list->isEmpty())
        <div class="list-empty">
            <p>还没有网址</p>
            <p class="muted">后台「网址导航」添加后会出现在这里。</p>
            <p><a class="btn-link" href="{{ url('/') }}">回首页</a></p>
        </div>
    @else
        <div class="web-grid">
            @foreach($list as $item)
                @php $logo = trim((string) ($item->logo ?? '')); @endphp
                <article class="web-card">
                    <a class="web-card-logo{{ $logo === '' ? ' is-empty' : '' }}" href="{{ vod_url('website', ['id' => $item->id]) }}" title="{{ $item->name }}">
                        @if($logo !== '')
                            <img src="{{ $logo }}" alt="" loading="lazy">
                        @else
                            <span>{{ mb_substr($item->name, 0, 1) }}</span>
                        @endif
                    </a>
                    <div class="web-card-meta">
                        <h3><a href="{{ vod_url('website', ['id' => $item->id]) }}">{{ $item->name }}</a></h3>
                        @if(trim((string) ($item->blurb ?? '')) !== '')
                            <p class="muted">{{ $item->blurb }}</p>
                        @endif
                        <p><a class="btn-link" href="{{ $item->url }}" target="_blank" rel="nofollow">打开网站</a></p>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
@endsection
