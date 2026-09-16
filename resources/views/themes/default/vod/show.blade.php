@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => '筛选'])
    <h1>筛选</h1>
    @include('themes.default.partials.filters')
    <div class="grid">
        @vod(['page' => true, 'num' => 24])
            @include('themes.default.partials.vod-card')
        @endvod
    </div>
    @vodPaginate
@endsection
