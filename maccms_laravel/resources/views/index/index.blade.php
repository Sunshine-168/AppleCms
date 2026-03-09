@extends('layouts.front')

@section('content')
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">New Videos</h2>
            <div class="row">
                @foreach($new_videos as $video)
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
                            <a href="{{ route('vod.detail', $video->vod_id) }}" class="btn btn-primary btn-sm">Watch</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <h2 class="mb-4 mt-5">Hot Videos</h2>
            <div class="row">
                @foreach($hot_videos as $video)
                <div class="col-md-3 col-sm-6 vod-item">
                    <div class="card h-100">
                        <a href="{{ route('vod.detail', $video->vod_id) }}">
                            <img src="{{ $video->vod_pic }}" class="card-img-top" alt="{{ $video->vod_name }}">
                        </a>
                        <div class="card-body">
                            <h5 class="card-title vod-title">
                                <a href="{{ route('vod.detail', $video->vod_id) }}" class="text-decoration-none text-dark">{{ $video->vod_name }}</a>
                            </h5>
                            <p class="card-text text-muted">Hits: {{ $video->vod_hits }}</p>
                            <a href="{{ route('vod.detail', $video->vod_id) }}" class="btn btn-primary btn-sm">Watch</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @if(count($new_articles) > 0)
            <h2 class="mb-4 mt-5">Latest Articles</h2>
            <div class="list-group">
                @foreach($new_articles as $article)
                <a href="{{ route('art.detail', $article->art_id) }}" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">{{ $article->art_name }}</h5>
                        <small>{{ date('Y-m-d', $article->art_time) }}</small>
                    </div>
                    <p class="mb-1">{{ $article->art_remarks }}</p>
                </a>
                @endforeach
            </div>
            @endif
        </div>
    </div>
@endsection
