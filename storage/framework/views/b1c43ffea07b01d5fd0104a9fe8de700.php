<?php $__env->startSection('title', '网址搜索'); ?>

<?php $__env->startSection('content'); ?>
<h2 class="mb-4">网址搜索：<?php echo e($wd); ?></h2>

<div class="row">
    <?php $__empty_1 = true; $__currentLoopData = $websites; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $website): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="card h-100">
            <a href="<?php echo e(route('website.detail', $website->website_id)); ?>">
                <img src="<?php echo e($website->website_pic ?: asset('static/images/nopic.gif')); ?>" class="card-img-top" alt="<?php echo e($website->website_name); ?>" style="height: 180px; object-fit: cover;">
            </a>
            <div class="card-body">
                <h5 class="card-title">
                    <a href="<?php echo e(route('website.detail', $website->website_id)); ?>" class="text-decoration-none text-dark"><?php echo e($website->website_name); ?></a>
                </h5>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="alert alert-warning">没有找到相关网址</div>
    <?php endif; ?>
</div>

<div class="d-flex justify-content-center mt-4">
    <?php echo e($websites->appends(['wd' => $wd])->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\website\search.blade.php ENDPATH**/ ?>