@extends('themes.default.layout')

@section('content')
@php
    $cover = trim((string) ($role->cover ?? ''));
@endphp
    @vodBreadcrumb(['last' => $role->name])

    <div class="role-detail">
        <div class="role-detail-cover{{ $cover === '' ? ' is-empty' : '' }}">
            @if($cover !== '')
                <img src="{{ $cover }}" alt="{{ $role->name }}">
            @else
                暂无封面
            @endif
        </div>
        <div class="role-detail-main">
            <h1>{{ $role->name }}</h1>
            @if(trim((string) ($role->blurb ?? '')) !== '')
                <p class="role-blurb">{{ $role->blurb }}</p>
            @endif
            <ul class="detail-tags">
                @if($role->actor)
                    <li><a href="{{ $role->actor->url ?? vod_url('actor', ['id' => $role->actor_id]) }}">演员 · {{ $role->actor->name }}</a></li>
                @endif
                @if((int) ($role->video_id ?? 0) > 0)
                    <li><a href="{{ vod_url('detail', ['id' => $role->video_id]) }}">影片 · {{ $role->video?->title ?: '#'.$role->video_id }}</a></li>
                @endif
            </ul>
            @if(trim(strip_tags((string) ($role->content ?? ''))) !== '')
                <div class="desc detail-desc">{!! $role->content !!}</div>
            @endif
        </div>
    </div>
@endsection
