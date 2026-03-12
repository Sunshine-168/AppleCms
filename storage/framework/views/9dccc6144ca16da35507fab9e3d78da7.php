<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="card">
        <div class="card-header"><?php echo e($title); ?></div>
        <div class="card-body">
            <div class="alert alert-info"><?php echo e($summary); ?></div>

            <?php if(!empty($items)): ?>
                <ul class="list-group mb-3">
                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="list-group-item"><?php echo e($item); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            <?php endif; ?>

            <a href="<?php echo e(route('admin.annex.index')); ?>" class="btn btn-primary">返回附件管理</a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\annex\result.blade.php ENDPATH**/ ?>