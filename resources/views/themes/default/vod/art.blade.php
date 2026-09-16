@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => $art->title])
    <h1>{{ $art->title }}</h1>
    <article class="desc">{!! $art->content !!}</article>
@endsection
