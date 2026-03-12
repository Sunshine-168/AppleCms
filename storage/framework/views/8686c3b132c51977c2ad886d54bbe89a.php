<?php $__env->startSection('title', 'Search: ' . $wd); ?>

<?php $__env->startSection('content'); ?>
<h2>Search Results for: "<?php echo e($wd); ?>"</h2>

<?php if($videos->isEmpty()): ?>
    <div class="alert alert-warning">No videos found.</div>
<?php else: ?>
    <div class="row">
        <?php $__currentLoopData = $videos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $video): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-md-3 col-sm-6 vod-item">
            <div class="card h-100">
                <a href="<?php echo e(route('vod.detail', $video->vod_id)); ?>">
                    <img src="<?php echo e($video->vod_pic); ?>" class="card-img-top" alt="<?php echo e($video->vod_name); ?>">
                </a>
                <div class="card-body">
                    <h5 class="card-title vod-title">
                        <a href="<?php echo e(route('vod.detail', $video->vod_id)); ?>" class="text-decoration-none text-dark"><?php echo e($video->vod_name); ?></a>
                    </h5>
                    <p class="card-text text-muted"><?php echo e($video->vod_remarks); ?></p>
                    <a href="<?php echo e(route('vod.detail', $video->vod_id)); ?>" class="btn btn-primary btn-sm">Watch Now</a>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="d-flex justify-content-center mt-4">
        <?php echo e($videos->links()); ?>

    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\vod\search.blade.php ENDPATH**/ ?>