@extends('themes.default.layout')
@section('content')
    @php
        $page = $page ?? 'index';
        $filters = $filters ?? ['type' => 0, 'serialize' => '', 'recommend' => '', 'wd' => '', 'author' => '', 'tag' => '', 'order' => 'new'];
        $types = $types ?? collect();
        $subTypes = $subTypes ?? collect();
        $listUrl = $listUrl ?? fn (array $over = []) => url('/manga');
        $typeId = (int) ($filters['type'] ?? 0);
        $selectedSub = $subTypes->firstWhere('id', $typeId);
        $activeParent = $selectedSub ? (int) $selectedSub->parent_id : 0;
        $blocks = $blocks ?? ['recommend' => collect(), 'hot' => collect(), 'newest' => collect()];
        $tags = $tags ?? [];
        $filtered = (bool) ($filtered ?? false);
    @endphp
    <h1>漫画</h1>
    @include('manga::partials.subnav')
    @if($types->isNotEmpty())
        <div class="filter-row">
            <span>分类</span>
            <a href="{{ $listUrl(['type' => 0]) }}"@if($typeId === 0) class="active"@endif>全部</a>
            @foreach($types as $type)
                <a href="{{ $listUrl(['type' => $type->id]) }}"@if($typeId === (int) $type->id || $activeParent === (int) $type->id) class="active"@endif>{{ $type->name }}</a>
            @endforeach
        </div>
    @endif
    @if($subTypes->isNotEmpty())
        <div class="filter-row">
            <span>子类</span>
            @foreach($subTypes as $type)
                <a href="{{ $listUrl(['type' => $type->id]) }}"@if($typeId === (int) $type->id) class="active"@endif>{{ $type->name }}</a>
            @endforeach
        </div>
    @endif
    <div class="filter-row">
        <span>状态</span>
        <a href="{{ $listUrl(['serialize' => '']) }}"@if(($filters['serialize'] ?? '') === '') class="active"@endif>全部</a>
        <a href="{{ $listUrl(['serialize' => '0']) }}"@if(($filters['serialize'] ?? '') === '0') class="active"@endif>连载</a>
        <a href="{{ $listUrl(['serialize' => '1']) }}"@if(($filters['serialize'] ?? '') === '1') class="active"@endif>完结</a>
        <a href="{{ $listUrl(['recommend' => ($filters['recommend'] ?? '') === '1' ? '' : '1']) }}"@if(($filters['recommend'] ?? '') === '1') class="active"@endif>推荐</a>
    </div>
    <div class="filter-row">
        <span>排序</span>
        <a href="{{ $listUrl(['order' => 'new']) }}"@if(($filters['order'] ?? 'new') === 'new') class="active"@endif>最新</a>
        <a href="{{ $listUrl(['order' => 'hits']) }}"@if(($filters['order'] ?? '') === 'hits') class="active"@endif>人气</a>
        <a href="{{ $listUrl(['order' => 'update']) }}"@if(($filters['order'] ?? '') === 'update') class="active"@endif>更新</a>
    </div>
    @if($tags !== [])
        <div class="filter-row">
            <span>标签</span>
            @foreach($tags as $tag)
                <a href="{{ url('/manga?tag='.urlencode($tag['name'])) }}"@if(($filters['tag'] ?? '') === $tag['name']) class="active"@endif>{{ $tag['name'] }}</a>
            @endforeach
        </div>
    @endif
    @if(($filters['wd'] ?? '') !== '')
        <p class="muted">搜索「{{ $filters['wd'] }}」</p>
    @endif
    @if(($filters['author'] ?? '') !== '')
        <p class="muted">作者「{{ $filters['author'] }}」 <a href="{{ url('/manga') }}">清除</a></p>
    @endif
    @if(($filters['tag'] ?? '') !== '')
        <p class="muted">标签「{{ $filters['tag'] }}」 <a href="{{ url('/manga') }}">清除</a></p>
    @endif
    @if(! $filtered)
        @foreach(['recommend' => '推荐', 'hot' => '热门', 'newest' => '最近更新'] as $key => $title)
            @if(($blocks[$key] ?? collect())->isNotEmpty())
                <div class="type-block">
                    <h2>{{ $title }} @if($key === 'hot')<a class="more" href="{{ url('/manga/rank') }}">更多</a>@elseif($key === 'newest')<a class="more" href="{{ url('/manga/update') }}">更多</a>@endif</h2>
                    <div class="grid">
                        @foreach($blocks[$key] as $row)
                            @include('manga::partials.card', ['row' => $row])
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
        @if(! $list->isEmpty())
            <h2>全部作品</h2>
        @endif
    @endif
    @if($list->isEmpty())
        <p class="muted">{{ $filtered ? '没有符合条件的漫画。' : '还没有上架的漫画。后台启用「漫画」插件后，在插件工作台添加。' }}</p>
    @else
        <div class="grid">
            @foreach($list as $row)
                @include('manga::partials.card', ['row' => $row])
            @endforeach
        </div>
        @if(method_exists($list, 'links'))
            <div class="pager">{{ $list->links() }}</div>
        @endif
    @endif
    @include('manga::partials.continue')
@endsection
