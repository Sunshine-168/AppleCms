@extends('layouts.front')

@section('title', 'Topics')

@section('content')
<h2 class="mb-4">Topics</h2>

<form class="d-flex mb-4" action="{{ route('topic.search') }}" method="GET">
    <input class="form-control me-2" type="search" name="wd" placeholder="Search Topic" aria-label="Search">
    <button class="btn btn-outline-success" type="submit">Search</button>
</form>

<div class="row">
    @foreach($topics as $topic)
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="card h-100">
            <a href="{{ route('topic.detail', $topic->topic_id) }}">
                <img src="{{ $topic->topic_pic }}" class="card-img-top" alt="{{ $topic->topic_name }}" style="height: 180px; object-fit: cover;">
            </a>
            <div class="card-body">
                <h5 class="card-title">
                    <a href="{{ route('topic.detail', $topic->topic_id) }}" class="text-decoration-none text-dark">{{ $topic->topic_name }}</a>
                </h5>
                <p class="card-text text-muted">{{ Str::limit(strip_tags($topic->topic_blurb), 80) }}</p>
                <small class="text-muted">{{ date('Y-m-d', $topic->topic_time) }}</small>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $topics->links() }}
</div>
@endsection
