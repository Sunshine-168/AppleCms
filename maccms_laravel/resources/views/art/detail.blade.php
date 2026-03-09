@extends('layouts.front')

@section('title', $article->art_name)

@section('content')
<div class="card">
    <div class="card-body">
        <h2 class="card-title">{{ $article->art_name }}</h2>
        <h6 class="card-subtitle mb-2 text-muted">
            {{ $article->type->type_name }} | {{ date('Y-m-d H:i:s', $article->art_time) }} | Author: {{ $article->art_author }}
        </h6>
        <div class="card-text mt-4">
            {!! $article->art_content !!}
        </div>
    </div>
</div>

<div id="comment-container"></div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var commentUrl = "{{ route('comment.index', ['mid' => 2, 'rid' => $article->art_id]) }}";
        loadComments(commentUrl);
    });

    function loadComments(url) {
        $.get(url, function(data) {
            $('#comment-container').html(data);
        });
    }
</script>
@endpush
