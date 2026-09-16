@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => $tag->name])
    <h1>标签：{{ $tag->name }}</h1>
    <div class="grid">
        @vod(['tag' => $tag->id, 'page' => true])
            @include('themes.default.partials.vod-card')
        @endvod
    </div>
    @vodPaginate
@endsection
