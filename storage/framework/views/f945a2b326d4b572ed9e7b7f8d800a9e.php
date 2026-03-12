<?php $__env->startSection('title', $actor->actor_name); ?>

<?php $__env->startSection('content'); ?>
<div class="card mb-4">
    <div class="row g-0">
        <div class="col-md-3">
            <img src="<?php echo e($actor->actor_pic); ?>" class="img-fluid rounded-start" alt="<?php echo e($actor->actor_name); ?>" style="max-height: 400px; object-fit: cover; width: 100%;">
        </div>
        <div class="col-md-9">
            <div class="card-body">
                <h2 class="card-title"><?php echo e($actor->actor_name); ?></h2>
                <p class="card-text"><strong>Alias:</strong> <?php echo e($actor->actor_en); ?></p>
                <p class="card-text"><strong>Area:</strong> <?php echo e($actor->actor_area); ?></p>
                <p class="card-text"><strong>Birthday:</strong> <?php echo e($actor->actor_birthday); ?></p>
                <p class="card-text"><strong>Height:</strong> <?php echo e($actor->actor_height); ?></p>
                <p class="card-text"><strong>Weight:</strong> <?php echo e($actor->actor_weight); ?></p>
                <div class="card-text mt-3">
                    <strong>Description:</strong> 
                    <p><?php echo e(strip_tags($actor->actor_content)); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<h3 class="mb-4">Works</h3>
<?php if($related_vods->isEmpty()): ?>
    <div class="alert alert-info">No related works found.</div>
<?php else: ?>
    <div class="row">
        <?php $__currentLoopData = $related_vods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $video): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-md-2 col-sm-4 mb-4">
            <div class="card h-100">
                <a href="<?php echo e(route('vod.detail', $video->vod_id)); ?>">
                    <img src="<?php echo e($video->vod_pic); ?>" class="card-img-top" alt="<?php echo e($video->vod_name); ?>" style="height: 200px; object-fit: cover;">
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

<div id="comment-container"></div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    $(document).ready(function() {
        // Load comments for actor (mid=8)
        var commentUrl = "<?php echo e(route('comment.index', ['mid' => 8, 'rid' => $actor->actor_id])); ?>";
        loadComments(commentUrl);
    });

    function loadComments(url) {
        $.get(url, function(data) {
            $('#comment-container').html(data);
        });
    }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\actor\detail.blade.php ENDPATH**/ ?>