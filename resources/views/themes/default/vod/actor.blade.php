@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => $actor->name])
    <h1>{{ $actor->name }}</h1>
    <p class="desc">{{ $actor->content }}</p>
    <div class="grid">
        @foreach($videos as $item)
            @include('themes.default.partials.vod-card')
        @endforeach
    </div>
    @vodPaginate
@endsection
