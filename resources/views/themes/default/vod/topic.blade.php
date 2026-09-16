@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => $topic->name])
    <h1>{{ $topic->name }}</h1>
    <p class="desc">{{ $topic->blurb }}</p>
    <div class="grid">
        @foreach($videos as $item)
            @include('themes.default.partials.vod-card')
        @endforeach
    </div>
    @vodPaginate
@endsection
