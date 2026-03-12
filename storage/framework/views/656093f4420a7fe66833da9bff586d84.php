<div class="card mt-4">
    <div class="card-header">
        Comments (<?php echo e($comments->total()); ?>)
    </div>
    <div class="card-body">
        <ul class="list-group list-group-flush mb-3">
            <?php $__empty_1 = true; $__currentLoopData = $comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <li class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <strong><?php echo e($comment->comment_name); ?></strong>
                        <small class="text-muted"><?php echo e(date('Y-m-d H:i', $comment->comment_time)); ?></small>
                    </div>
                    <p class="mb-1"><?php echo e($comment->comment_content); ?></p>
                    <div class="d-flex justify-content-end">
                        <small class="text-muted">#<?php echo e($loop->iteration); ?></small>
                    </div>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <li class="list-group-item text-center">No comments yet. Be the first!</li>
            <?php endif; ?>
        </ul>

        <div class="d-flex justify-content-center">
            <?php echo e($comments->links()); ?>

        </div>

        <hr>

        <form id="comment-form" action="<?php echo e(route('comment.save')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="comment_mid" value="<?php echo e(request('mid')); ?>">
            <input type="hidden" name="comment_rid" value="<?php echo e(request('rid')); ?>">
            <input type="hidden" name="comment_pid" value="0">
            
            <div class="mb-3">
                <textarea class="form-control" name="comment_content" rows="3" placeholder="Write a comment..." required></textarea>
            </div>
            
            <div class="d-flex justify-content-between align-items-center">
                <?php if(!Auth::check() && config('maccms.comment.login') == 1): ?>
                    <small class="text-danger">Please <a href="<?php echo e(route('user.login')); ?>">login</a> to comment.</small>
                    <button type="button" class="btn btn-primary" disabled>Submit</button>
                <?php else: ?>
                    <span></span>
                    <button type="submit" class="btn btn-primary">Submit</button>
                <?php endif; ?>
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
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\comment\ajax.blade.php ENDPATH**/ ?>