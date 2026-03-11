<div class="card mt-4">
    <div class="card-header">
        Comments ({{ $comments->total() }})
    </div>
    <div class="card-body">
        <ul class="list-group list-group-flush mb-3">
            @forelse($comments as $comment)
                <li class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <strong>{{ $comment->comment_name }}</strong>
                        <small class="text-muted">{{ date('Y-m-d H:i', $comment->comment_time) }}</small>
                    </div>
                    <p class="mb-1">{{ $comment->comment_content }}</p>
                    <div class="d-flex justify-content-end">
                        <small class="text-muted">#{{ $loop->iteration }}</small>
                    </div>
                </li>
            @empty
                <li class="list-group-item text-center">No comments yet. Be the first!</li>
            @endforelse
        </ul>

        <div class="d-flex justify-content-center">
            {{ $comments->links() }}
        </div>

        <hr>

        <form id="comment-form" action="{{ route('comment.save') }}" method="POST">
            @csrf
            <input type="hidden" name="comment_mid" value="{{ request('mid') }}">
            <input type="hidden" name="comment_rid" value="{{ request('rid') }}">
            <input type="hidden" name="comment_pid" value="0">
            
            <div class="mb-3">
                <textarea class="form-control" name="comment_content" rows="3" placeholder="Write a comment..." required></textarea>
            </div>
            
            <div class="d-flex justify-content-between align-items-center">
                @if(!Auth::check() && config('maccms.comment.login') == 1)
                    <small class="text-danger">Please <a href="{{ route('user.login') }}">login</a> to comment.</small>
                    <button type="button" class="btn btn-primary" disabled>Submit</button>
                @else
                    <span></span>
                    <button type="submit" class="btn btn-primary">Submit</button>
                @endif
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Handle pagination links
        $('.pagination a').on('click', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            loadComments(url);
        });

        // Handle form submission
        $('#comment-form').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');
            var data = form.serialize();

            $.post(url, data, function(response) {
                if (response.code == 1) {
                    alert(response.msg);
                    form[0].reset();
                    // Reload comments
                    loadComments(window.location.href); 
                } else {
                    alert(response.msg);
                }
            }, 'json');
        });
    });

    function loadComments(url) {
        // This function should be defined in the parent page or modify here to reload the container
        // But since this is partial, we assume the parent handles the container reload or we do it here
        // Ideally, we replace the container content.
        $.get(url, function(data) {
            $('#comment-container').html(data);
        });
    }
</script>
