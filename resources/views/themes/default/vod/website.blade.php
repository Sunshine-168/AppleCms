@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => $website->name])
    <h1>{{ $website->name }}</h1>
    @if($website->logo)
        <p><img src="{{ $website->logo }}" alt="{{ $website->name }}"></p>
    @endif
    <p class="desc">{{ $website->blurb }}</p>
    <p><a href="{{ $website->url }}" target="_blank" rel="nofollow">访问网站</a></p>
@endsection
