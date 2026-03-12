<?php $__env->startSection('title', $article->art_name); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-body">
        <h2 class="card-title"><?php echo e($article->art_name); ?></h2>
        <h6 class="card-subtitle mb-2 text-muted">
            <?php echo e($article->type->type_name); ?> | <?php echo e(date('Y-m-d H:i:s', $article->art_time)); ?> | Author: <?php echo e($article->art_author); ?>

        </h6>
        <div class="card-text mt-4">
            <?php echo $article->art_content; ?>

        </div>
    </div>
</div>

<div id="comment-container"></div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    $(document).ready(function() {
        var commentUrl = "<?php echo e(route('comment.index', ['mid' => 2, 'rid' => $article->art_id])); ?>";
        loadComments(commentUrl);
    });

    function loadComments(url) {
        $.get(url, function(data) {
            $('#comment-container').html(data);
        });
    }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\art\detail.blade.php ENDPATH**/ ?>