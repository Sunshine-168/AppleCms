@extends('layouts.front')

@section('title', $topic->topic_name)

@section('content')
<div class="card mb-4">
    <div class="row g-0">
        <div class="col-md-4">
            <img src="{{ $topic->topic_pic }}" class="img-fluid rounded-start" alt="{{ $topic->topic_name }}" style="max-height: 300px; object-fit: cover; width: 100%;">
        </div>
        <div class="col-md-8">
            <div class="card-body">
                <h2 class="card-title">{{ $topic->topic_name }}</h2>
                <p class="card-text text-muted">{{ date('Y-m-d H:i', $topic->topic_time) }}</p>
                <div class="card-text mt-3">
                    <strong>Description:</strong> 
                    <p>{{ strip_tags($topic->topic_content) }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

@if($vod_list->isNotEmpty())
<h3 class="mb-3">Related Videos</h3>
<div class="row mb-4">
    @foreach($vod_list as $video)
    <div class="col-md-2 col-sm-4 mb-4">
        <div class="card h-100">
            <a href="{{ route('vod.detail', $video->vod_id) }}">
                <img src="{{ $video->vod_pic }}" class="card-img-top" alt="{{ $video->vod_name }}" style="height: 180px; object-fit: cover;">
            </a>
            <div class="card-body text-center p-2">
                <h6 class="card-title mb-0" style="font-size: 0.9rem;">
                    <a href="{{ route('vod.detail', $video->vod_id) }}" class="text-decoration-none text-dark">{{ $video->vod_name }}</a>
                </h6>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

@if($art_list->isNotEmpty())
<h3 class="mb-3">Related Articles</h3>
<div class="list-group mb-4">
    @foreach($art_list as $article)
    <a href="{{ route('art.detail', $article->art_id) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
        <span>{{ $article->art_name }}</span>
        <small class="text-muted">{{ date('Y-m-d', $article->art_time) }}</small>
    </a>
    @endforeach
</div>
@endif

<div id="comment-container"></div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Load comments for topic (mid=3) - assuming mid=3 for topic based on maccms logic
        var commentUrl = "{{ route('comment.index', ['mid' => 3, 'rid' => $topic->topic_id]) }}";
        loadComments(commentUrl);
    });

    function loadComments(url) {
        $.get(url, function(data) {
            $('#comment-container').html(data);
        });
    }
</script>
@endpush
