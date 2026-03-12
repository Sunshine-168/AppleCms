<form class="layui-form m10" method="post" action="<?php echo e($url); ?>">
    <input type="hidden" name="col" value="<?php echo e($col); ?>">
    <input type="hidden" name="ids" value="<?php echo e($ids); ?>">

    <div class="layui-input-inline w150">
        <select name="val">
            <option value=""><?php echo e(__('admin.select_level')); ?></option>
            <option value="0"><?php echo e(__('admin.cancel_level')); ?></option>
            <?php $__currentLoopData = $level_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($vo); ?>"><?php echo e(__('admin.level')); ?><?php echo e($vo); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="layui-input-inline">
        <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit"><?php echo e(__('admin.btn_save')); ?></button>
    </div>
</form>

<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\public\select_level.blade.php ENDPATH**/ ?>