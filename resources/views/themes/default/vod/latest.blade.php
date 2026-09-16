@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => '最新更新'])
    <h1>最新更新</h1>
    @include('themes.default.partials.filters')
    <div class="grid">
        @vod(['page' => 1, 'num' => 24, 'order' => 'time'])
            @include('themes.default.partials.vod-card')
        @endvod
    </div>
    @vodPaginate
@endsection
