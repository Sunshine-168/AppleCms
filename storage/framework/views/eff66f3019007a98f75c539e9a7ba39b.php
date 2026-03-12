<?php $__env->startSection('title', $topic->topic_name); ?>

<?php $__env->startSection('content'); ?>
<div class="card mb-4">
    <div class="row g-0">
        <div class="col-md-4">
            <img src="<?php echo e($topic->topic_pic); ?>" class="img-fluid rounded-start" alt="<?php echo e($topic->topic_name); ?>" style="max-height: 300px; object-fit: cover; width: 100%;">
        </div>
        <div class="col-md-8">
            <div class="card-body">
                <h2 class="card-title"><?php echo e($topic->topic_name); ?></h2>
                <p class="card-text text-muted"><?php echo e(date('Y-m-d H:i', $topic->topic_time)); ?></p>
                <div class="card-text mt-3">
                    <strong>Description:</strong> 
                    <p><?php echo e(strip_tags($topic->topic_content)); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if($vod_list->isNotEmpty()): ?>
<h3 class="mb-3">Related Videos</h3>
<div class="row mb-4">
    <?php $__currentLoopData = $vod_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $video): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="col-md-2 col-sm-4 mb-4">
        <div class="card h-100">
            <a href="<?php echo e(route('vod.detail', $video->vod_id)); ?>">
                <img src="<?php echo e($video->vod_pic); ?>" class="card-img-top" alt="<?php echo e($video->vod_name); ?>" style="height: 180px; object-fit: cover;">
            </a>
            <div class="card-body text-center p-2">
                <h6 class="card-title mb-0" style="font-size: 0.9rem;">
                    <a href="<?php echo e(route('vod.detail', $video->vod_id)); ?>" class="text-decoration-none text-dark"><?php echo e($video->vod_name); ?></a>
                </h6>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>

<?php if($art_list->isNotEmpty()): ?>
<h3 class="mb-3">Related Articles</h3>
<div class="list-group mb-4">
    <?php $__currentLoopData = $art_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <a href="<?php echo e(route('art.detail', $article->art_id)); ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
        <span><?php echo e($article->art_name); ?></span>
        <small class="text-muted"><?php echo e(date('Y-m-d', $article->art_time)); ?></small>
    </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>

<div id="comment-container"></div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    $(document).ready(function() {
        // Load comments for topic (mid=3) - assuming mid=3 for topic based on maccms logic
        var commentUrl = "<?php echo e(route('comment.index', ['mid' => 3, 'rid' => $topic->topic_id])); ?>";
        loadComments(commentUrl);
    });

    function loadComments(url) {
        $.get(url, function(data) {
            $('#comment-container').html(data);
        });
    }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\topic\detail.blade.php ENDPATH**/ ?>