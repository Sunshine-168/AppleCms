@extends('themes.default.layout')

@section('content')
@php
    $avatar = trim((string) ($actor->avatar ?? ''));
    $blurb = trim(strip_tags((string) ($actor->content ?? $actor->blurb ?? '')));
@endphp
    @vodBreadcrumb(['last' => $actor->name])

    <div class="actor-detail">
        <div class="actor-avatar{{ $avatar === '' ? ' is-empty' : '' }}">
            @if($avatar !== '')
                <img src="{{ $avatar }}" alt="{{ $actor->name }}"
                     onerror="var p=this.parentElement;this.remove();if(p)p.classList.add('is-empty');">
            @endif
            <span class="actor-avatar-empty">暂无头像</span>
        </div>
        <div class="actor-main">
            <h1>{{ $actor->name }}</h1>
            @if($blurb !== '')
                <div class="desc detail-desc">{{ \Illuminate\Support\Str::limit($blurb, 500) }}</div>
            @else
                <p class="muted">暂无简介</p>
            @endif
            <p class="muted">相关作品 {{ method_exists($videos, 'total') ? $videos->total() : $videos->count() }} 部</p>
        </div>
    </div>

    <section class="home-sec">
        <div class="sec-head"><h2>作品</h2></div>
        @if($videos->isEmpty())
            <div class="list-empty">
                <p>还没有关联作品</p>
                <p><a class="btn-link" href="{{ vod_url('actors') }}">回演员库</a></p>
            </div>
        @else
            <div class="grid">
                @foreach($videos as $item)
                    @include('themes.default.partials.vod-card')
                @endforeach
            </div>
            @vodPaginate
        @endif
    </section>
@endsection
