@extends('themes.default.layout')

@section('content')
    @php
        $currentType = $currentType ?? null;
        $typeTitle = $currentType ? (string) $currentType->name : '资讯';
        $children = $children ?? collect();
        $arts = $arts ?? collect();
        $artTypes = $artTypes ?? collect();
        $typeId = (int) ($typeId ?? 0);
    @endphp
    @vodBreadcrumb(['last' => $typeTitle])

    <div class="list-head">
        <h1>{{ $typeTitle }}</h1>
        <p class="muted">浏览下级栏目，或直接看最新文章。</p>
    </div>

    @if($currentType && trim((string) ($currentType->pic ?? '')) !== '')
        <p class="art-cover"><img src="{{ $currentType->pic }}" alt=""></p>
    @endif

    @if($artTypes->isNotEmpty())
        <nav class="art-tabs" aria-label="资讯分类">
            <a href="{{ vod_url('arts') }}" class="{{ $typeId === 0 ? 'on' : '' }}">全部</a>
            @foreach($artTypes as $type)
                <a href="{{ $type->url }}" class="{{ $typeId === (int) $type->id ? 'on' : '' }}">{{ $type->name }}</a>
                @foreach($type->children as $child)
                    <a href="{{ $child->url }}" class="{{ $typeId === (int) $child->id ? 'on' : '' }}">{{ $child->name }}</a>
                @endforeach
            @endforeach
        </nav>
    @endif

    @if($children->isNotEmpty())
        <div class="art-hub">
            @foreach($children as $child)
                <a class="art-hub-item" href="{{ $child->url }}">
                    @if(trim((string) ($child->pic ?? '')) !== '')
                        <img src="{{ $child->pic }}" alt="">
                    @endif
                    <span>{{ $child->name }}</span>
                </a>
            @endforeach
        </div>
    @else
        <div class="list-empty" style="margin:12px 0 20px">
            <p>还没有下级栏目</p>
        </div>
    @endif

    @if($arts && count($arts))
        <section class="home-sec">
            <div class="sec-head"><h2>最新</h2></div>
            <div class="art-list">
                @foreach($arts as $item)
                    <article class="art-card">
                        <a href="{{ $item->url }}">
                            @if(trim((string) ($item->cover ?? '')) !== '')
                                <img src="{{ $item->cover }}" alt="" loading="lazy">
                            @endif
                            <div class="meta">
                                <h3>{{ $item->title }}</h3>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
            @vodPaginate
        </section>
    @endif
@endsection
