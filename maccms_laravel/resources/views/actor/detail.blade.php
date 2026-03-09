@extends('layouts.front')

@section('title', $actor->actor_name)

@section('content')
<div class="card mb-4">
    <div class="row g-0">
        <div class="col-md-3">
            <img src="{{ $actor->actor_pic }}" class="img-fluid rounded-start" alt="{{ $actor->actor_name }}" style="max-height: 400px; object-fit: cover; width: 100%;">
        </div>
        <div class="col-md-9">
            <div class="card-body">
                <h2 class="card-title">{{ $actor->actor_name }}</h2>
                <p class="card-text"><strong>Alias:</strong> {{ $actor->actor_en }}</p>
                <p class="card-text"><strong>Area:</strong> {{ $actor->actor_area }}</p>
                <p class="card-text"><strong>Birthday:</strong> {{ $actor->actor_birthday }}</p>
                <p class="card-text"><strong>Height:</strong> {{ $actor->actor_height }}</p>
                <p class="card-text"><strong>Weight:</strong> {{ $actor->actor_weight }}</p>
                <div class="card-text mt-3">
                    <strong>Description:</strong> 
                    <p>{{ strip_tags($actor->actor_content) }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<h3 class="mb-4">Works</h3>
@if($related_vods->isEmpty())
    <div class="alert alert-info">No related works found.</div>
@else
    <div class="row">
        @foreach($related_vods as $video)
        <div class="col-md-2 col-sm-4 mb-4">
            <div class="card h-100">
                <a href="{{ route('vod.detail', $video->vod_id) }}">
                    <img src="{{ $video->vod_pic }}" class="card-img-top" alt="{{ $video->vod_name }}" style="height: 200px; object-fit: cover;">
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

<div id="comment-container"></div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Load comments for actor (mid=8)
        var commentUrl = "{{ route('comment.index', ['mid' => 8, 'rid' => $actor->actor_id]) }}";
        loadComments(commentUrl);
    });

    function loadComments(url) {
        $.get(url, function(data) {
            $('#comment-container').html(data);
        });
    }
</script>
@endpush
