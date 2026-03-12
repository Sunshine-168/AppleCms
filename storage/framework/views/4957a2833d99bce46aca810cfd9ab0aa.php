<?php $__env->startSection('title', $website->website_name); ?>

<?php $__env->startSection('content'); ?>
<div class="card mb-4">
    <div class="row g-0">
        <div class="col-md-4">
            <img src="<?php echo e($website->website_pic ?: asset('static/images/nopic.gif')); ?>" class="img-fluid rounded-start" alt="<?php echo e($website->website_name); ?>" style="width: 100%; max-height: 320px; object-fit: cover;">
        </div>
        <div class="col-md-8">
            <div class="card-body">
                <h2 class="card-title"><?php echo e($website->website_name); ?></h2>
                <p class="card-text"><strong>地区：</strong><?php echo e($website->website_area ?: '-'); ?></p>
                <p class="card-text"><strong>语言：</strong><?php echo e($website->website_lang ?: '-'); ?></p>
                <p class="card-text"><strong>备注：</strong><?php echo e($website->website_remarks ?: '-'); ?></p>
                <p class="card-text"><strong>简介：</strong><?php echo e(strip_tags($website->website_content) ?: '暂无简介'); ?></p>
                <?php if($website->website_jumpurl): ?>
                <a class="btn btn-primary" href="<?php echo e($website->website_jumpurl); ?>" target="_blank" rel="noopener noreferrer">访问网站</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\website\detail.blade.php ENDPATH**/ ?>