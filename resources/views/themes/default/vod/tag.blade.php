@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => $tag->name])
    <div class="list-head">
        <h1># {{ $tag->name }}</h1>
        <p class="muted">带这个标签的影片</p>
    </div>
    <div class="grid">
        @vod(['tag' => $tag->id, 'page' => true])
            @include('themes.default.partials.vod-card')
        @endvod
    </div>
    @if(!app(\App\Cms\CmsViewContext::class)->paginator() || app(\App\Cms\CmsViewContext::class)->paginator()->isEmpty())
        <div class="list-empty">
            <p>这个标签下还没有影片</p>
            <p><a class="btn-link" href="{{ url('/') }}">回首页</a></p>
        </div>
    @endif
    @vodPaginate
@endsection
