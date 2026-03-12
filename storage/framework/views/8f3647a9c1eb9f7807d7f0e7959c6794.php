<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">
    <fieldset class="layui-elem-field">
        <legend><?php echo e(__('admin.base_info')); ?></legend>
        <div class="layui-field-box">
            <?php $__empty_1 = true; $__currentLoopData = $urls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <p><?php echo e($vo); ?></p>
            <hr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p>未生成可预览的网址。</p>
            <?php endif; ?>
        </div>
    </fieldset>
</div>
<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script type="text/javascript">
    layui.use(['form', 'layer'], function () {
        // 操作对象
        var form = layui.form
                , layer = layui.layer
                , $ = layui.jquery;



    });

</script>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\cj\show_url.blade.php ENDPATH**/ ?>