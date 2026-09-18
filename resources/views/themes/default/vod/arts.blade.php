@extends('themes.default.layout')

@section('content')
    @php
        $currentType = $currentType ?? null;
        $currentTag = $currentTag ?? null;
        $typeTitle = $currentTag ? (string) $currentTag->name : ($currentType ? (string) $currentType->name : '资讯');
    @endphp
    @vodBreadcrumb(['last' => $typeTitle])
    <h1>{{ $typeTitle }}</h1>
    @if($currentType && trim((string) ($currentType->pic ?? '')) !== '')
        <p class="art-cover"><img src="{{ $currentType->pic }}" alt=""></p>
    @endif
    @if($currentTag)
        <p class="muted">标签 · {{ $currentTag->name }}</p>
    @endif
    <form class="search" method="get" action="{{ vod_url('arts') }}">
        @if(!empty($typeId))
            <input type="hidden" name="type_id" value="{{ (int) $typeId }}">
        @endif
        <input type="search" name="wd" value="{{ request('wd') }}" placeholder="搜资讯">
        <button type="submit">搜索</button>
    </form>
    @php
        $artTypes = $artTypes ?? collect();
        $typeId = (int) ($typeId ?? 0);
    @endphp
    @if($artTypes->isNotEmpty())
        <nav class="art-nav">
            <a href="{{ vod_url('arts') }}" class="{{ $typeId === 0 ? 'on' : '' }}">全部</a>
            @foreach($artTypes as $type)
                <a href="{{ $type->url }}" class="{{ $typeId === (int) $type->id ? 'on' : '' }}">{{ $type->name }}</a>
                @foreach($type->children as $child)
                    <a href="{{ $child->url }}" class="{{ $typeId === (int) $child->id ? 'on' : '' }}">{{ $child->name }}</a>
                @endforeach
            @endforeach
        </nav>
    @endif
    <div class="art-list">
        @foreach($arts as $item)
            @php
                $excerpt = trim((string) ($item->blurb ?? ''));
                if ($excerpt === '') {
                    $excerpt = mb_substr(strip_tags((string) $item->content), 0, 80);
                }
                $when = (int) ($item->published_at ?? 0) > 0 ? (int) $item->published_at : (int) $item->created_at;
            @endphp
            <article class="art-card">
                <a href="{{ $item->url }}">
                    @if(trim((string) $item->cover) !== '')
                        <img src="{{ $item->cover }}" alt="">
                    @endif
                    <div class="meta">
                        <h3>{{ $item->title }}</h3>
                        @if($excerpt !== '')
                            <p class="muted">{{ $excerpt }}</p>
                        @endif
                        <p class="muted">{{ $when > 0 ? date('Y-m-d', $when) : '' }}</p>
                    </div>
                </a>
            </article>
        @endforeach
    </div>
    @vodPaginate
@endsection
