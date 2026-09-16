@extends('themes.default.layout')

@section('content')
    <div class="slides">
        @vodSlide(['slot' => 'home', 'num' => 8])
            <a class="slide" href="{{ $item->url ?: '#' }}">
                @if($item->pic)<img src="{{ $item->pic }}" alt="{{ $item->name }}">@endif
                <span>{{ $item->name }}</span>
            </a>
        @endvodSlide
    </div>
    <h1>{{ $site['title'] ?? 'LaraVideo' }}</h1>
    <p class="muted">{{ $site['description'] ?? '' }}</p>

    <h2>推荐</h2>
    <div class="grid">
        @vod(['flag' => 'recommend', 'num' => 8])
            @include('themes.default.partials.vod-card')
        @endvod
    </div>

    <h2>最新</h2>
    <div class="grid">
        @vod(['num' => 12, 'order' => 'time'])
            @include('themes.default.partials.vod-card')
        @endvod
    </div>

    <h2>热门</h2>
    <div class="grid">
        @vod(['flag' => 'hot', 'num' => 12])
            @include('themes.default.partials.vod-card')
        @endvod
    </div>

    @vodType(['type' => 'top', 'as' => 'type'])
        <div class="type-block">
            <h2>{{ $type->name }} <a class="more" href="{{ $type->url }}">更多</a></h2>
            <div class="grid">
                @vod(['typeid' => $type->id, 'num' => 12, 'order' => 'time'])
                    @include('themes.default.partials.vod-card')
                @endvod
            </div>
        </div>
    @endvodType
@endsection
