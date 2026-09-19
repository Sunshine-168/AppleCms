@extends('themes.default.layout')

@section('content')
@php
    $sons = app(\App\Services\Video\Tags\TypeTag::class)->get(['type' => 'son', 'id' => $type->id]);
@endphp
    @vodBreadcrumb
    <div class="list-head">
        <h1>{{ $type->name }}</h1>
        <p class="muted">本分类下的影片，可配合筛选缩小范围</p>
    </div>
    @if($sons->isNotEmpty())
        <div class="type-sons">
            @foreach($sons as $child)
                <a href="{{ $child->url }}" class="{{ (int) $type->id === (int) $child->id ? 'active' : '' }}">{{ $child->name }}</a>
            @endforeach
        </div>
    @endif
    @include('themes.default.partials.filters')
    <div class="grid">
        @vod(['page' => true, 'num' => 24])
            @include('themes.default.partials.vod-card')
        @endvod
    </div>
    @if(!app(\App\Cms\CmsViewContext::class)->paginator() || app(\App\Cms\CmsViewContext::class)->paginator()->isEmpty())
        <div class="list-empty">
            <p>这个分类暂时没有影片</p>
            <p class="muted">换个筛选条件，或去看看其它分类。</p>
            <p><a class="btn-link" href="{{ url('/') }}">回首页</a></p>
        </div>
    @endif
    @vodPaginate
@endsection
