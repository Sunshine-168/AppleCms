<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">
    <div class="update">
        <h1 class="layui-font-20"><?php echo e($title); ?></h1>
        <textarea rows="25" class="layui-textarea" readonly><?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php echo e($line); ?>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></textarea>
        <?php if($nextUrl): ?>
            <div style="margin-top: 12px;">
                <a href="<?php echo e($nextUrl); ?>" class="layui-btn"><?php echo e($nextText ?? '下一步'); ?></a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\update\result.blade.php ENDPATH**/ ?>