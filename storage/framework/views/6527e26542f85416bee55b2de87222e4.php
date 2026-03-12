<?php $__env->startSection('title', '网址列表'); ?>

<?php $__env->startSection('content'); ?>
<h2 class="mb-4">网址列表</h2>

<form class="d-flex mb-4" action="<?php echo e(route('website.search')); ?>" method="GET">
    <input class="form-control me-2" type="search" name="wd" placeholder="搜索网址">
    <button class="btn btn-outline-success" type="submit">搜索</button>
</form>

<div class="row">
    <?php $__currentLoopData = $websites; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $website): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="card h-100">
            <a href="<?php echo e(route('website.detail', $website->website_id)); ?>">
                <img src="<?php echo e($website->website_pic ?: asset('static/images/nopic.gif')); ?>" class="card-img-top" alt="<?php echo e($website->website_name); ?>" style="height: 180px; object-fit: cover;">
            </a>
            <div class="card-body">
                <h5 class="card-title">
                    <a href="<?php echo e(route('website.detail', $website->website_id)); ?>" class="text-decoration-none text-dark"><?php echo e($website->website_name); ?></a>
                </h5>
                <p class="card-text text-muted"><?php echo e(\Illuminate\Support\Str::limit(strip_tags($website->website_blurb), 60)); ?></p>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="d-flex justify-content-center mt-4">
    <?php echo e($websites->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\website\index.blade.php ENDPATH**/ ?>