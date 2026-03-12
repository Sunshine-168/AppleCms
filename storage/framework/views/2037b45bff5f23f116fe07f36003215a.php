<?php $__env->startSection('title', $video->vod_name); ?>

<?php $__env->startSection('content'); ?>
<div class="card mb-4">
    <div class="row g-0">
        <div class="col-md-4">
            <img src="<?php echo e($video->vod_pic); ?>" class="img-fluid rounded-start" alt="<?php echo e($video->vod_name); ?>">
        </div>
        <div class="col-md-8">
            <div class="card-body">
                <h2 class="card-title"><?php echo e($video->vod_name); ?></h2>
                <p class="card-text"><small class="text-muted"><?php echo e($video->type->type_name); ?> | <?php echo e($video->vod_year); ?> | <?php echo e($video->vod_area); ?></small></p>
                <p class="card-text"><strong>Director:</strong> <?php echo e($video->vod_director); ?></p>
                <p class="card-text"><strong>Actor:</strong> <?php echo e($video->vod_actor); ?></p>
                <p class="card-text"><strong>Description:</strong> <?php echo e(strip_tags($video->vod_content)); ?></p>
                
                <?php $__currentLoopData = $playList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sid => $player): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="mt-3">
                    <h5><?php echo e($player['player_name']); ?></h5>
                    <div class="btn-group flex-wrap" role="group">
                        <?php $__currentLoopData = $player['urls']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nid => $episode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('vod.play', ['id' => $video->vod_id, 'sid' => $sid + 1, 'nid' => $nid + 1])); ?>" class="btn btn-outline-primary m-1"><?php echo e($episode['name']); ?></a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
</div>

<div id="comment-container"></div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    $(document).ready(function() {
        var commentUrl = "<?php echo e(route('comment.index', ['mid' => 1, 'rid' => $video->vod_id])); ?>";
        loadComments(commentUrl);
    });

    function loadComments(url) {
        $.get(url, function(data) {
            $('#comment-container').html(data);
        });
    }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\vod\detail.blade.php ENDPATH**/ ?>