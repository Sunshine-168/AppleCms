@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => '搜索'])
    <h1>搜索 {{ $q }}</h1>
    @if($q === '')
        <p class="muted">请输入关键词</p>
    @else
        <div class="grid">
            @vod(['wd' => $q, 'page' => true, 'num' => 24])
                @include('themes.default.partials.vod-card')
            @endvod
        </div>
        @vodPaginate
    @endif
@endsection
