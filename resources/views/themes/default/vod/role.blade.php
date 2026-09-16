@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => $role->name])
    <h1>{{ $role->name }}</h1>
    <p class="desc">{{ $role->blurb }}</p>
    @if((int) ($role->video_id ?? 0) > 0)
        <p><a href="{{ vod_url('detail', ['id' => $role->video_id]) }}">相关影片</a></p>
    @endif
    <article>{!! $role->content !!}</article>
@endsection
