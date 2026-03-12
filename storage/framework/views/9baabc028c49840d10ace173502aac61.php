<?php $__env->startSection('title', $video->vod_name . ' 剧情'); ?>

<?php $__env->startSection('content'); ?>
<div class="card mb-4">
    <div class="card-body">
        <h2 class="card-title"><?php echo e($video->vod_name); ?></h2>
        <p class="text-muted">剧情更新时间：<?php echo e(date('Y-m-d H:i', $video->vod_time)); ?></p>
        <a class="btn btn-outline-primary btn-sm mb-3" href="<?php echo e(route('vod.detail', $video->vod_id)); ?>">查看视频详情</a>

        <?php $__empty_1 = true; $__currentLoopData = $plotList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="mb-4">
            <h5><?php echo e($plot['name']); ?></h5>
            <div class="text-muted"><?php echo e(strip_tags($plot['detail']) ?: '暂无内容'); ?></div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="alert alert-info">暂无剧情内容</div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\plot\detail.blade.php ENDPATH**/ ?>