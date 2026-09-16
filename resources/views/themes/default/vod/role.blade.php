@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => $role->name])
    <h1>{{ $role->name }}</h1>
    <p class="desc">{{ $role->blurb }}</p>
    <article>{!! $role->content !!}</article>
@endsection
