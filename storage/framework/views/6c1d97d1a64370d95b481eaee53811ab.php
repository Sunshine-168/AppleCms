<?php $__env->startSection('title', '剧情列表'); ?>

<?php $__env->startSection('content'); ?>
<h2 class="mb-4">剧情列表</h2>

<form class="d-flex mb-4" action="<?php echo e(route('plot.search')); ?>" method="GET">
    <input class="form-control me-2" type="search" name="wd" placeholder="搜索剧情">
    <button class="btn btn-outline-success" type="submit">搜索</button>
</form>

<div class="list-group">
    <?php $__currentLoopData = $videos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $video): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <a href="<?php echo e(route('plot.detail', $video->vod_id)); ?>" class="list-group-item list-group-item-action">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <strong><?php echo e($video->vod_name); ?></strong>
                <div class="text-muted small"><?php echo e(\Illuminate\Support\Str::limit(strip_tags($video->vod_plot_name), 80)); ?></div>
            </div>
            <span class="badge bg-secondary"><?php echo e(date('Y-m-d', $video->vod_time)); ?></span>
        </div>
    </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="d-flex justify-content-center mt-4">
    <?php echo e($videos->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\plot\index.blade.php ENDPATH**/ ?>