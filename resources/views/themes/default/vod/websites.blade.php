@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => '网址导航'])
    <h1>网址导航</h1>
    <ul>
        @foreach($list as $item)
            <li><a href="{{ $item->url }}" target="_blank" rel="nofollow">{{ $item->name }}</a> {{ $item->blurb }}</li>
        @endforeach
    </ul>
@endsection
