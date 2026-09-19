@extends('themes.default.layout')

@section('content')
@php
    $vodTag = app(\App\Services\Video\Tags\VodTag::class);
    $typeTag = app(\App\Services\Video\Tags\TypeTag::class);
    $recNum = max(1, (int) ($site['theme_home_rec_num'] ?? 12));
    $recommend = $vodTag->get(['flag' => 'recommend', 'num' => $recNum]);
    $latest = $vodTag->get(['num' => 12, 'order' => 'time']);
    $arts = app(\App\Services\Video\Tags\ArtTag::class)->get(['num' => 6]);
    $topics = app(\App\Services\Video\Tags\TopicTag::class)->get(['num' => 6, 'by' => 'sort', 'order' => 'desc']);
    $topTypes = $typeTag->get(['type' => 'top'])->filter(function ($type) {
        $ids = method_exists($type, 'descendantIds') ? $type->descendantIds() : [(int) $type->id];

        return \App\Models\Video\VideoModel::query()
            ->published()
            ->where(function ($q) use ($ids) {
                $q->whereIn('type_id', $ids)->orWhereIn('type_pid', $ids);
            })
            ->exists();
    })->values();
    $hotTabs = collect([
        (object) ['id' => 0, 'name' => '全部', 'items' => $vodTag->get(['flag' => 'hot', 'num' => 12, 'order' => 'hits'])],
    ]);
    foreach ($topTypes->take(6) as $type) {
        $hotTabs->push((object) [
            'id' => (int) $type->id,
            'name' => $type->name,
            'items' => $vodTag->get(['typeid' => $type->id, 'num' => 12, 'order' => 'hits']),
        ]);
    }
@endphp

    <div class="home-hero">
        <div class="slides">
            @vodSlide(['slot' => 'home', 'num' => 8])
                <a class="slide" href="{{ $item->url ?: '#' }}">
                    @if($item->pic)<img src="{{ $item->pic }}" alt="{{ $item->name }}" loading="lazy">@endif
                    <span>{{ $item->name }}</span>
                </a>
            @endvodSlide
        </div>
    </div>

    @if($recommend->isNotEmpty())
    <section class="home-sec">
        <div class="sec-head">
            <h2>推荐</h2>
        </div>
        <div class="grid">
            @foreach($recommend as $item)
                @include('themes.default.partials.vod-card')
            @endforeach
        </div>
    </section>
    @endif

    @if($latest->isNotEmpty())
    <section class="home-sec">
        <div class="sec-head">
            <h2>最新更新</h2>
            <a class="more" href="{{ vod_url('latest') }}">更多</a>
        </div>
        <div class="grid">
            @foreach($latest as $item)
                @include('themes.default.partials.vod-card')
            @endforeach
        </div>
    </section>
    @endif

    @if($hotTabs->first()->items->isNotEmpty())
    <section class="home-sec" id="home-hot">
        <div class="sec-head">
            <h2>热门</h2>
            <div class="hot-tabs" role="tablist">
                @foreach($hotTabs as $i => $tab)
                    <button type="button" class="hot-tab{{ $i === 0 ? ' on' : '' }}" data-hot="{{ $tab->id }}" role="tab">{{ $tab->name }}</button>
                @endforeach
            </div>
        </div>
        @foreach($hotTabs as $i => $tab)
            <div class="grid hot-panel{{ $i === 0 ? ' is-on' : '' }}" data-hot-panel="{{ $tab->id }}" @if($i !== 0) hidden @endif>
                @foreach($tab->items as $item)
                    @include('themes.default.partials.vod-card')
                @endforeach
            </div>
        @endforeach
    </section>
    @endif

    @if($topics->isNotEmpty())
    <section class="home-sec">
        <div class="sec-head">
            <h2>专题</h2>
            <a class="more" href="{{ vod_url('topics') }}">更多</a>
        </div>
        <div class="topic-grid">
            @foreach($topics as $item)
                @php
                    $cover = trim((string) ($item->cover ?? ''));
                    $count = 0;
                    try { $count = (int) $item->videos()->count(); } catch (\Throwable) { $count = 0; }
                @endphp
                <article class="topic-card">
                    <a class="topic-card-cover{{ $cover === '' ? ' is-empty' : '' }}" href="{{ $item->url }}" title="{{ $item->name }}">
                        @if($cover !== '')
                            <img src="{{ $cover }}" alt="{{ $item->name }}" loading="lazy">
                        @endif
                        <span class="topic-card-empty">专题</span>
                        @if($count > 0)<em class="topic-card-count">{{ $count }} 部</em>@endif
                    </a>
                    <div class="topic-card-meta">
                        <h3><a href="{{ $item->url }}">{{ $item->name }}</a></h3>
                        @if(trim((string) ($item->blurb ?? '')) !== '')
                            <p class="muted">{{ $item->blurb }}</p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>
    @endif

    @includeIf('manga::home')

    @if($arts->isNotEmpty())
    <section class="home-sec">
        <div class="sec-head">
            <h2>资讯</h2>
            <a class="more" href="{{ vod_url('arts') }}">更多</a>
        </div>
        <div class="art-list">
            @foreach($arts as $item)
                <article class="art-card">
                    <a href="{{ $item->url }}">
                        @if(trim((string) ($item->cover ?? '')) !== '')
                            <img src="{{ $item->cover }}" alt="" loading="lazy">
                        @endif
                        <div class="meta">
                            <h3>{{ $item->title }}</h3>
                            <p class="muted">{{ date('Y-m-d', (int) (($item->published_at ?? 0) > 0 ? $item->published_at : $item->created_at)) }}</p>
                        </div>
                    </a>
                </article>
            @endforeach
        </div>
    </section>
    @endif

    @foreach($topTypes as $type)
        @php
            $typeItems = $vodTag->get(['typeid' => $type->id, 'num' => 12, 'order' => 'time']);
        @endphp
        @if($typeItems->isNotEmpty())
        <section class="home-sec type-block">
            <div class="sec-head">
                <h2>{{ $type->name }}</h2>
                <a class="more" href="{{ $type->url }}">更多</a>
            </div>
            <div class="grid">
                @foreach($typeItems as $item)
                    @include('themes.default.partials.vod-card')
                @endforeach
            </div>
        </section>
        @endif
    @endforeach

    @if($recommend->isEmpty() && $latest->isEmpty())
        <div class="list-empty home-empty">
            <p>片库还是空的</p>
            <p class="muted">采集入库或手动添加影片后，这里会出现推荐与分类列表。</p>
            <p><a class="btn-link" href="{{ vod_url('latest') }}">去最新更新</a></p>
        </div>
    @endif

    @push('scripts')
    <script>
    (function () {
        var root = document.getElementById('home-hot');
        if (!root) return;
        var tabs = root.querySelectorAll('.hot-tab');
        var panels = root.querySelectorAll('[data-hot-panel]');
        tabs.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = btn.getAttribute('data-hot');
                tabs.forEach(function (t) { t.classList.toggle('on', t === btn); });
                panels.forEach(function (p) {
                    var on = p.getAttribute('data-hot-panel') === id;
                    p.classList.toggle('is-on', on);
                    p.hidden = !on;
                });
            });
        });
    })();
    </script>
    @endpush
@endsection
