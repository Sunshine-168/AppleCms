@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => $role->name])
    <h1>{{ $role->name }}</h1>
    <p class="desc">{{ $role->blurb }}</p>
    @if($role->actor)
        <p>演员：<a href="{{ $role->actor->url ?? vod_url('actor', ['id' => $role->actor_id]) }}">{{ $role->actor->name }}</a></p>
    @endif
    @if((int) ($role->video_id ?? 0) > 0)
        <p><a href="{{ vod_url('detail', ['id' => $role->video_id]) }}">相关影片{{ $role->video?->title ? '：'.$role->video->title : '' }}</a></p>
    @endif
    <article>{!! $role->content !!}</article>
@endsection
