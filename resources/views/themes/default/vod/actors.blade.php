@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => '演员库'])
    <h1>演员库</h1>
    <div class="grid">
        @foreach($actors as $item)
            <article class="card">
                <a href="{{ $item->url }}">
                    @if($item->avatar)
                        <img src="{{ $item->avatar }}" alt="{{ $item->name }}">
                    @else
                        <img alt="{{ $item->name }}">
                    @endif
                    <div class="meta">
                        <h3>{{ $item->name }}</h3>
                    </div>
                </a>
            </article>
        @endforeach
    </div>
    @vodPaginate
@endsection
