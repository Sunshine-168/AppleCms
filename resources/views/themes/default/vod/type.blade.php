@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb
    <h1>{{ $type->name }}</h1>
    @include('themes.default.partials.filters')
    <div class="grid">
        @vod(['page' => true, 'num' => 24])
            @include('themes.default.partials.vod-card')
        @endvod
    </div>
    @vodPaginate
@endsection
