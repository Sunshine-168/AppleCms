<?php $__env->startSection('title', $type->type_name); ?>

<?php $__env->startSection('content'); ?>
<h2 class="mb-4">分类：<?php echo e($type->type_name); ?></h2>

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

<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\website\type.blade.php ENDPATH**/ ?>