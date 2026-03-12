<?php $__env->startSection('title', 'Articles'); ?>

<?php $__env->startSection('content'); ?>
<div class="list-group">
    <?php $__currentLoopData = $articles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <a href="<?php echo e(route('art.detail', $article->art_id)); ?>" class="list-group-item list-group-item-action">
        <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1"><?php echo e($article->art_name); ?></h5>
            <small><?php echo e(date('Y-m-d', $article->art_time)); ?></small>
        </div>
        <p class="mb-1"><?php echo e($article->art_remarks); ?></p>
        <small><?php echo e($article->type ? $article->type->type_name : ''); ?></small>
    </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="d-flex justify-content-center mt-4">
    <?php echo e($articles->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\art\index.blade.php ENDPATH**/ ?>