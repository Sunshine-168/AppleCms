@extends('themes.default.layout')

@section('content')
@php
    $logo = trim((string) ($website->logo ?? ''));
    $related = $related ?? collect();
    $hits = (int) ($website->hits ?? 0);
@endphp
    @vodBreadcrumb(['last' => $website->name])

    <div class="web-detail">
        <div class="web-detail-logo{{ $logo === '' ? ' is-empty' : '' }}">
            @if($logo !== '')
                <img src="{{ $logo }}" alt="{{ $website->name }}">
            @else
                <span>{{ mb_substr($website->name, 0, 1) }}</span>
            @endif
        </div>
        <div>
            <h1>{{ $website->name }}</h1>
            @if(trim((string) ($website->blurb ?? '')) !== '')
                <p class="muted">{{ $website->blurb }}</p>
            @endif
            <p class="muted">人气 {{ $hits }}</p>
            <div class="detail-actions">
                <a class="btn-play" href="{{ url('/website/'.$website->id.'/go') }}" target="_blank" rel="nofollow noopener">访问网站</a>
                <a class="btn-ghost" href="{{ vod_url('websites') }}">返回导航</a>
            </div>
        </div>
    </div>

    @if($related->isNotEmpty())
        <section class="web-related">
            <h2>同分类推荐</h2>
            <div class="web-grid">
                @foreach($related as $item)
                    @php $rLogo = trim((string) ($item->logo ?? '')); @endphp
                    <article class="web-card">
                        <a class="web-card-logo{{ $rLogo === '' ? ' is-empty' : '' }}" href="{{ vod_url('website', ['id' => $item->id]) }}">
                            @if($rLogo !== '')
                                <img src="{{ $rLogo }}" alt="" loading="lazy">
                            @else
                                <span>{{ mb_substr($item->name, 0, 1) }}</span>
                            @endif
                        </a>
                        <div class="web-card-meta">
                            <h3><a href="{{ vod_url('website', ['id' => $item->id]) }}">{{ $item->name }}</a></h3>
                            <p><a class="btn-link" href="{{ url('/website/'.$item->id.'/go') }}" target="_blank" rel="nofollow noopener">打开</a></p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
@endsection
