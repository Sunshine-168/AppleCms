<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">New Videos</h2>
            <div class="row">
                <?php $__currentLoopData = $new_videos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $video): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                            <a href="<?php echo e(route('vod.detail', $video->vod_id)); ?>" class="btn btn-primary btn-sm">Watch</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <h2 class="mb-4 mt-5">Hot Videos</h2>
            <div class="row">
                <?php $__currentLoopData = $hot_videos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $video): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-3 col-sm-6 vod-item">
                    <div class="card h-100">
                        <a href="<?php echo e(route('vod.detail', $video->vod_id)); ?>">
                            <img src="<?php echo e($video->vod_pic); ?>" class="card-img-top" alt="<?php echo e($video->vod_name); ?>">
                        </a>
                        <div class="card-body">
                            <h5 class="card-title vod-title">
                                <a href="<?php echo e(route('vod.detail', $video->vod_id)); ?>" class="text-decoration-none text-dark"><?php echo e($video->vod_name); ?></a>
                            </h5>
                            <p class="card-text text-muted">Hits: <?php echo e($video->vod_hits); ?></p>
                            <a href="<?php echo e(route('vod.detail', $video->vod_id)); ?>" class="btn btn-primary btn-sm">Watch</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <?php if(count($new_articles) > 0): ?>
            <h2 class="mb-4 mt-5">Latest Articles</h2>
            <div class="list-group">
                <?php $__currentLoopData = $new_articles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('art.detail', $article->art_id)); ?>" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1"><?php echo e($article->art_name); ?></h5>
                        <small><?php echo e(date('Y-m-d', $article->art_time)); ?></small>
                    </div>
                    <p class="mb-1"><?php echo e($article->art_remarks); ?></p>
                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\index\index.blade.php ENDPATH**/ ?>