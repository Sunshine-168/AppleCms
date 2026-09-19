@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => '筛选'])
    <div class="list-head">
        <h1>筛选</h1>
        <p class="muted">按类型、地区、年份等组合查找</p>
    </div>
    @include('themes.default.partials.filters')
    <div class="grid">
        @vod(['page' => true, 'num' => 24])
            @include('themes.default.partials.vod-card')
        @endvod
    </div>
    @if(!app(\App\Cms\CmsViewContext::class)->paginator() || app(\App\Cms\CmsViewContext::class)->paginator()->isEmpty())
        <div class="list-empty">
            <p>没有符合条件的影片</p>
            <p class="muted">放宽筛选条件再试一次。</p>
            <p><a class="btn-link" href="{{ url('/') }}">回首页</a></p>
        </div>
    @endif
    @vodPaginate
@endsection
