@extends('themes.default.layout')

@section('content')
@php $logo = trim((string) ($website->logo ?? '')); @endphp
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
            <div class="detail-actions">
                <a class="btn-play" href="{{ $website->url }}" target="_blank" rel="nofollow">访问网站</a>
                <a class="btn-ghost" href="{{ vod_url('websites') }}">返回导航</a>
            </div>
        </div>
    </div>
@endsection
