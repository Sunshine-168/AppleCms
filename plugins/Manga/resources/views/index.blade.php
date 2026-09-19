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
        $blocks = $blocks ?? ['recommend' => collect(), 'hot' => collect(), 'favor' => collect(), 'newest' => collect()];
        $tags = $tags ?? [];
        $filtered = (bool) ($filtered ?? false);
        $hasActiveFilter = $filtered
            || ($filters['wd'] ?? '') !== ''
            || ($filters['author'] ?? '') !== ''
            || ($filters['tag'] ?? '') !== '';
    @endphp

    <div class="list-head manga-head">
        <h1>漫画</h1>
        <p class="muted">连载追更、完结补番，按分类 / 标签随便逛。</p>
    </div>

    @include('manga::partials.subnav')

    <div class="filters manga-filters">
        <form class="manga-search" action="{{ url('/manga') }}" method="get">
            @if((int) ($filters['type'] ?? 0) > 0)<input type="hidden" name="type" value="{{ (int) $filters['type'] }}">@endif
            @if(($filters['serialize'] ?? '') === '0' || ($filters['serialize'] ?? '') === '1')<input type="hidden" name="serialize" value="{{ $filters['serialize'] }}">@endif
            @if(($filters['recommend'] ?? '') === '1')<input type="hidden" name="recommend" value="1">@endif
            @if(($filters['author'] ?? '') !== '')<input type="hidden" name="author" value="{{ $filters['author'] }}">@endif
            @if(($filters['tag'] ?? '') !== '')<input type="hidden" name="tag" value="{{ $filters['tag'] }}">@endif
            @if(($filters['order'] ?? 'new') !== 'new' && ($filters['order'] ?? '') !== '')<input type="hidden" name="order" value="{{ $filters['order'] }}">@endif
            <input type="search" name="wd" value="{{ $filters['wd'] ?? '' }}" placeholder="搜标题 / 作者" maxlength="64" aria-label="搜漫画">
            <button type="submit" class="btn-ghost">搜索</button>
            @if(($filters['wd'] ?? '') !== '')
                <a class="btn-link" href="{{ $listUrl(['wd' => '']) }}">清除</a>
            @endif
        </form>

        @if($types->isNotEmpty())
            <div class="filter-row">
                <span class="filter-label">分类</span>
                <div class="filter-choices">
                    <a href="{{ $listUrl(['type' => 0]) }}"@if($typeId === 0) class="active"@endif>全部</a>
                    @foreach($types as $type)
                        <a href="{{ $listUrl(['type' => $type->id]) }}"@if($typeId === (int) $type->id || $activeParent === (int) $type->id) class="active"@endif>{{ $type->name }}</a>
                    @endforeach
                </div>
            </div>
        @endif
        @if($subTypes->isNotEmpty())
            <div class="filter-row">
                <span class="filter-label">子类</span>
                <div class="filter-choices">
                    @foreach($subTypes as $type)
                        <a href="{{ $listUrl(['type' => $type->id]) }}"@if($typeId === (int) $type->id) class="active"@endif>{{ $type->name }}</a>
                    @endforeach
                </div>
            </div>
        @endif
        <div class="filter-row">
            <span class="filter-label">状态</span>
            <div class="filter-choices">
                <a href="{{ $listUrl(['serialize' => '', 'recommend' => '']) }}"@if(($filters['serialize'] ?? '') === '' && ($filters['recommend'] ?? '') !== '1') class="active"@endif>全部</a>
                <a href="{{ $listUrl(['serialize' => '0', 'recommend' => '']) }}"@if(($filters['serialize'] ?? '') === '0') class="active"@endif>连载</a>
                <a href="{{ $listUrl(['serialize' => '1', 'recommend' => '']) }}"@if(($filters['serialize'] ?? '') === '1') class="active"@endif>完结</a>
                <a href="{{ $listUrl(['recommend' => '1', 'serialize' => '']) }}"@if(($filters['recommend'] ?? '') === '1') class="active"@endif>推荐</a>
            </div>
        </div>
        <div class="filter-row">
            <span class="filter-label">排序</span>
            <div class="filter-choices">
                <a href="{{ $listUrl(['order' => 'new']) }}"@if(($filters['order'] ?? 'new') === 'new') class="active"@endif>最新</a>
                <a href="{{ $listUrl(['order' => 'hits']) }}"@if(($filters['order'] ?? '') === 'hits') class="active"@endif>人气</a>
                <a href="{{ $listUrl(['order' => 'favor']) }}"@if(($filters['order'] ?? '') === 'favor') class="active"@endif>收藏</a>
                <a href="{{ $listUrl(['order' => 'update']) }}"@if(($filters['order'] ?? '') === 'update') class="active"@endif>更新</a>
            </div>
        </div>
        @if($tags !== [])
            <div class="filter-row">
                <span class="filter-label">标签</span>
                <div class="filter-choices manga-tags{{ count($tags) > 16 ? ' is-collapsible' : '' }}" id="manga-tags">
                    @foreach($tags as $tag)
                        @php
                            $tagHref = (string) ($tag['slug'] ?? $tag['name'] ?? '');
                            $cur = (string) ($filters['tag'] ?? '');
                            $tagOn = $cur !== '' && ($cur === $tagHref || $cur === (string) ($tag['name'] ?? '') || $cur === (string) ($tag['slug'] ?? ''));
                        @endphp
                        <a href="{{ $listUrl(['tag' => ($tagHref !== '' ? $tagHref : (string) ($tag['name'] ?? ''))]) }}"@if($tagOn) class="active"@endif>{{ $tag['name'] }}</a>
                    @endforeach
                </div>
            </div>
            @if(count($tags) > 16)
                <button type="button" class="manga-tags-more btn-link" id="manga-tags-more" aria-controls="manga-tags">展开全部标签</button>
            @endif
        @endif
    </div>

    @if($hasActiveFilter)
        <p class="manga-active-filters muted">
            @if(($filters['wd'] ?? '') !== '')
                搜索「{{ $filters['wd'] }}」
            @endif
            @if(($filters['author'] ?? '') !== '')
                作者「{{ $filters['author'] }}」 <a href="{{ $listUrl(['author' => '']) }}">清除</a>
            @endif
            @if(($filters['tag'] ?? '') !== '')
                标签「{{ $filters['tag'] }}」 <a href="{{ $listUrl(['tag' => '']) }}">清除</a>
            @endif
        </p>
    @endif

    @if(! $filtered)
        @foreach(['recommend' => '推荐', 'hot' => '热门', 'favor' => '收藏热门', 'newest' => '最近更新'] as $key => $title)
            @if(($blocks[$key] ?? collect())->isNotEmpty())
                <section class="home-sec manga-block">
                    <div class="sec-head">
                        <h2>{{ $title }}</h2>
                        @if($key === 'recommend')
                            <a class="more" href="{{ url('/manga?recommend=1') }}">更多</a>
                        @elseif($key === 'hot')
                            <a class="more" href="{{ url('/manga/rank') }}">更多</a>
                        @elseif($key === 'favor')
                            <a class="more" href="{{ url('/manga/rank?board=favor') }}">更多</a>
                        @elseif($key === 'newest')
                            <a class="more" href="{{ url('/manga/update') }}">更多</a>
                        @endif
                    </div>
                    <div class="grid">
                        @foreach($blocks[$key] as $row)
                            @include('manga::partials.card', ['row' => $row])
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach
        @if(! $list->isEmpty())
            <div class="sec-head">
                <h2>全部作品</h2>
            </div>
        @endif
    @endif

    @if($list->isEmpty())
        <div class="list-empty">
            <p>{{ $filtered ? '没有符合条件的漫画' : '还没有上架的漫画' }}</p>
            <p class="muted">{{ $filtered ? '换个筛选条件试试。' : '后台启用「漫画」插件后，在插件工作台添加。' }}</p>
            @if($filtered)
                <p><a class="btn-link" href="{{ url('/manga') }}">清除筛选</a></p>
            @endif
        </div>
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

@push('scripts')
<script>
(function () {
    var box = document.getElementById('manga-tags');
    var btn = document.getElementById('manga-tags-more');
    if (!box || !btn) return;
    btn.addEventListener('click', function () {
        var open = box.classList.toggle('is-open');
        btn.textContent = open ? '收起标签' : '展开全部标签';
    });
})();
</script>
@endpush
