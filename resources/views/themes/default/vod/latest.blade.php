@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => '最新更新'])
    <div class="list-head">
        <h1>最新更新</h1>
        <p class="muted">按更新时间看看最近入库的片子</p>
    </div>
    @include('themes.default.partials.filters')
    <div class="grid">
        @vod(['page' => 1, 'num' => 24, 'order' => 'time'])
            @include('themes.default.partials.vod-card')
        @endvod
    </div>
    @if(!app(\App\Cms\CmsViewContext::class)->paginator() || app(\App\Cms\CmsViewContext::class)->paginator()->isEmpty())
        <div class="list-empty">
            <p>还没有更新</p>
            <p><a class="btn-link" href="{{ url('/') }}">回首页</a></p>
        </div>
    @endif
    @vodPaginate
@endsection
