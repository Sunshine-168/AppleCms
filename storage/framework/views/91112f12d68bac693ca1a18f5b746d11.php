<?php $__env->startSection('title', $role->role_name); ?>

<?php $__env->startSection('content'); ?>
<div class="card mb-4">
    <div class="row g-0">
        <div class="col-md-3">
            <img src="<?php echo e($role->role_pic ?: asset('static/images/nopic.gif')); ?>" class="img-fluid rounded-start" alt="<?php echo e($role->role_name); ?>" style="width: 100%; max-height: 360px; object-fit: cover;">
        </div>
        <div class="col-md-9">
            <div class="card-body">
                <h2 class="card-title"><?php echo e($role->role_name); ?></h2>
                <p class="card-text"><strong>演员：</strong><?php echo e($role->role_actor ?: '-'); ?></p>
                <p class="card-text"><strong>更新时间：</strong><?php echo e(date('Y-m-d H:i', $role->role_time)); ?></p>
                <p class="card-text"><strong>简介：</strong><?php echo e(strip_tags($role->role_content) ?: '暂无简介'); ?></p>
            </div>
        </div>
    </div>
</div>

<h3 class="mb-3">相关作品</h3>
<?php if($relatedVod->isEmpty()): ?>
    <div class="alert alert-info">暂无相关作品</div>
<?php else: ?>
<div class="row">
    <?php $__currentLoopData = $relatedVod; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $video): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="col-md-2 col-sm-4 mb-4">
        <div class="card h-100">
            <a href="<?php echo e(route('vod.detail', $video->vod_id)); ?>">
                <img src="<?php echo e($video->vod_pic ?: asset('static/images/nopic.gif')); ?>" class="card-img-top" alt="<?php echo e($video->vod_name); ?>" style="height: 180px; object-fit: cover;">
            </a>
            <div class="card-body text-center p-2">
                <a href="<?php echo e(route('vod.detail', $video->vod_id)); ?>" class="text-decoration-none text-dark"><?php echo e($video->vod_name); ?></a>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\role\detail.blade.php ENDPATH**/ ?>