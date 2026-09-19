@extends('themes.default.layout')

@section('content')
@php
    $types = $types ?? collect();
    $groups = is_array($groups ?? null) ? $groups : [];
    $hot = $hot ?? collect();
    $list = $list ?? collect();
    $typeId = (int) ($typeId ?? 0);
    $wd = (string) ($wd ?? request('wd', ''));
    $byParent = $types->groupBy(fn ($t) => (int) ($t->parent_id ?? 0));
    $roots = $byParent->get(0, collect());
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
        <input type="search" name="wd" value="{{ $wd }}" placeholder="搜站名、简介或网址" aria-label="搜网址">
        <button type="submit" class="btn-ghost">搜索</button>
    </form>

    <div class="web-portal">
        @if($types->isNotEmpty())
            <aside class="web-portal-side" aria-label="导航分类">
                <a class="web-side-link{{ $typeId === 0 ? ' on' : '' }}" href="{{ vod_url('websites') }}{{ $wd !== '' ? '?wd='.urlencode($wd) : '' }}">全部</a>
                @foreach($roots as $root)
                    @php
                        $rid = (int) $root->id;
                        $children = $byParent->get($rid, collect());
                        $rootOn = $typeId === $rid || $children->contains(fn ($c) => (int) $c->id === $typeId);
                    @endphp
                    <div class="web-side-group">
                        <a class="web-side-link{{ $typeId === $rid ? ' on' : ($rootOn ? ' open' : '') }}" href="{{ vod_url('websites') }}?type_id={{ $rid }}{{ $wd !== '' ? '&wd='.urlencode($wd) : '' }}">{{ $root->name }}</a>
                        @if($children->isNotEmpty())
                            <div class="web-side-children">
                                @foreach($children as $child)
                                    <a class="web-side-link is-child{{ $typeId === (int) $child->id ? ' on' : '' }}" href="{{ vod_url('websites') }}?type_id={{ $child->id }}{{ $wd !== '' ? '&wd='.urlencode($wd) : '' }}">{{ $child->name }}</a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </aside>
        @endif

        <div class="web-portal-main">
            @if($hot->isNotEmpty() && $wd === '' && $typeId === 0)
                <section class="web-hot" aria-label="热门站点">
                    <h2>热门</h2>
                    <div class="web-hot-list">
                        @foreach($hot as $item)
                            <a href="{{ url('/website/'.$item->id.'/go') }}" target="_blank" rel="nofollow noopener">{{ $item->name }}<em>{{ (int) ($item->hits ?? 0) }}</em></a>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($list->isEmpty())
                <div class="list-empty">
                    <p>还没有网址</p>
                    <p class="muted">后台「网址导航」添加后会出现在这里。</p>
                    <p><a class="btn-link" href="{{ url('/') }}">回首页</a></p>
                </div>
            @else
                @foreach($groups as $group)
                    @php
                        $gType = $group['type'] ?? null;
                        $items = $group['items'] ?? collect();
                    @endphp
                    @continue($items->isEmpty())
                    <section class="web-group">
                        <h2>{{ $gType ? $gType->name : ($typeId > 0 || $wd !== '' ? '结果' : '未分组') }}</h2>
                        <div class="web-grid">
                            @foreach($items as $item)
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
                                        <p>
                                            <a class="btn-link" href="{{ url('/website/'.$item->id.'/go') }}" target="_blank" rel="nofollow noopener">打开</a>
                                            @if((int) ($item->hits ?? 0) > 0)
                                                <span class="muted">人气 {{ (int) $item->hits }}</span>
                                            @endif
                                        </p>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            @endif
        </div>
    </div>
@endsection
