@extends('layouts.front')

@section('title', 'Search: ' . $wd)

@section('content')
<h2>Search Results for: "{{ $wd }}"</h2>

@if($videos->isEmpty())
    <div class="alert alert-warning">No videos found.</div>
@else
    <div class="row">
        @foreach($videos as $video)
        <div class="col-md-3 col-sm-6 vod-item">
            <div class="card h-100">
                <a href="{{ route('vod.detail', $video->vod_id) }}">
                    <img src="{{ $video->vod_pic }}" class="card-img-top" alt="{{ $video->vod_name }}">
                </a>
                <div class="card-body">
                    <h5 class="card-title vod-title">
                        <a href="{{ route('vod.detail', $video->vod_id) }}" class="text-decoration-none text-dark">{{ $video->vod_name }}</a>
                    </h5>
                    <p class="card-text text-muted">{{ $video->vod_remarks }}</p>
                    <a href="{{ route('vod.detail', $video->vod_id) }}" class="btn btn-primary btn-sm">Watch Now</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $videos->links() }}
    </div>
@endif
@endsection
