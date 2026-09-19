@extends('themes.default.layout')

@section('content')
@php
    $currentType = $currentType ?? null;
    $currentTag = $currentTag ?? null;
    $typeTitle = $currentTag ? (string) $currentTag->name : ($currentType ? (string) $currentType->name : '资讯');
    $artTypes = $artTypes ?? collect();
    $typeId = (int) ($typeId ?? 0);
@endphp
    @vodBreadcrumb(['last' => $typeTitle])

    <div class="list-head">
        <h1>{{ $typeTitle }}</h1>
        <p class="muted">影讯、剧评与行业观察，了解片单背后的故事。</p>
    </div>

    <form class="search list-search" method="get" action="{{ vod_url('arts') }}">
        @if($typeId > 0)
            <input type="hidden" name="type_id" value="{{ $typeId }}">
        @endif
        <input type="search" name="wd" value="{{ request('wd') }}" placeholder="搜标题 / 摘要 / 标签" aria-label="搜资讯">
        <button type="submit" class="btn-ghost">搜索</button>
    </form>

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

    @if($arts->isEmpty())
        <div class="list-empty">
            <p>还没有资讯</p>
            <p class="muted">后台添加文章后会出现在这里。</p>
            <p><a class="btn-link" href="{{ url('/') }}">去逛逛</a></p>
        </div>
    @else
        <div class="art-list">
            @foreach($arts as $item)
                @php
                    $excerpt = trim((string) ($item->blurb ?? ''));
                    if ($excerpt === '') {
                        $excerpt = mb_substr(strip_tags((string) $item->content), 0, 90);
                    }
                    $when = (int) ($item->published_at ?? 0) > 0 ? (int) $item->published_at : (int) $item->created_at;
                    $cover = trim((string) ($item->cover ?? ''));
                @endphp
                <article class="art-card">
                    <a href="{{ $item->url }}">
                        <div class="art-card-cover{{ $cover === '' ? ' is-empty' : '' }}">
                            @if($cover !== '')
                                <img src="{{ $cover }}" alt="" loading="lazy"
                                     onerror="var p=this.parentElement;this.remove();if(p)p.classList.add('is-empty');">
                            @endif
                            <span class="art-card-empty">资讯</span>
                        </div>
                        <div class="meta">
                            <h3>{{ $item->title }}</h3>
                            @if($excerpt !== '')
                                <p class="muted art-excerpt">{{ $excerpt }}</p>
                            @endif
                            <p class="muted art-meta-line">
                                @if($when > 0){{ date('Y-m-d', $when) }}@endif
                                @if(trim((string) ($item->author ?? '')) !== '')
                                    · {{ $item->author }}
                                @endif
                                @if((int) ($item->hits ?? 0) > 0)
                                    · {{ (int) $item->hits }} 阅读
                                @endif
                            </p>
                        </div>
                    </a>
                </article>
            @endforeach
        </div>
        @vodPaginate
    @endif
@endsection
