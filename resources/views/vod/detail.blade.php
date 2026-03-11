@extends('layouts.front')

@section('title', $video->vod_name)

@section('content')
<div class="card mb-4">
    <div class="row g-0">
        <div class="col-md-4">
            <img src="{{ $video->vod_pic }}" class="img-fluid rounded-start" alt="{{ $video->vod_name }}">
        </div>
        <div class="col-md-8">
            <div class="card-body">
                <h2 class="card-title">{{ $video->vod_name }}</h2>
                <p class="card-text"><small class="text-muted">{{ $video->type->type_name }} | {{ $video->vod_year }} | {{ $video->vod_area }}</small></p>
                <p class="card-text"><strong>Director:</strong> {{ $video->vod_director }}</p>
                <p class="card-text"><strong>Actor:</strong> {{ $video->vod_actor }}</p>
                <p class="card-text"><strong>Description:</strong> {{ strip_tags($video->vod_content) }}</p>
                
                @foreach($playList as $sid => $player)
                <div class="mt-3">
                    <h5>{{ $player['player_name'] }}</h5>
                    <div class="btn-group flex-wrap" role="group">
                        @foreach($player['urls'] as $nid => $episode)
                        <a href="{{ route('vod.play', ['id' => $video->vod_id, 'sid' => $sid + 1, 'nid' => $nid + 1]) }}" class="btn btn-outline-primary m-1">{{ $episode['name'] }}</a>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div id="comment-container"></div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var commentUrl = "{{ route('comment.index', ['mid' => 1, 'rid' => $video->vod_id]) }}";
        loadComments(commentUrl);
    });

    function loadComments(url) {
        $.get(url, function(data) {
            $('#comment-container').html(data);
        });
    }
</script>
@endpush
