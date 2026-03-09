@extends('layouts.front')

@section('title', 'Search: ' . $wd)

@section('content')
<h2>Search Results for: "{{ $wd }}"</h2>

@if($articles->isEmpty())
    <div class="alert alert-warning">No articles found.</div>
@else
    <div class="list-group">
        @foreach($articles as $article)
        <a href="{{ route('art.detail', $article->art_id) }}" class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
                <h5 class="mb-1">{{ $article->art_name }}</h5>
                <small>{{ date('Y-m-d', $article->art_time) }}</small>
            </div>
            <p class="mb-1">{{ $article->art_remarks }}</p>
            <small>{{ $article->type ? $article->type->type_name : '' }}</small>
        </a>
        @endforeach
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $articles->links() }}
    </div>
@endif
@endsection
