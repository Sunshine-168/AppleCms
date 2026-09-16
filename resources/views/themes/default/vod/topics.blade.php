@extends('themes.default.layout')

@section('content')
    @vodBreadcrumb(['last' => '专题'])
    <h1>专题</h1>
    <div class="grid">
        @foreach($topics as $item)
            <article class="card">
                <a href="{{ $item->url }}">
                    @if($item->cover)
                        <img src="{{ $item->cover }}" alt="{{ $item->name }}">
                    @else
                        <img alt="{{ $item->name }}">
                    @endif
                    <div class="meta">
                        <h3>{{ $item->name }}</h3>
                        <div class="muted">{{ $item->blurb }}</div>
                    </div>
                </a>
            </article>
        @endforeach
    </div>
    @vodPaginate
@endsection
