<form class="layui-form m10" method="post" action="<?php echo e($url); ?>">
    <input type="hidden" name="col" value="<?php echo e($col); ?>">
    <input type="hidden" name="ids" value="<?php echo e($ids); ?>">

    <div class="layui-input-inline w150">
        <select name="val">
            <option value=""><?php echo e(__('admin.select_type')); ?></option>
            <?php $__currentLoopData = $type_tree; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($vo.type_mid == $mid): ?>
            <option value="<?php echo e($vo.type_id); ?>" ><?php echo e($vo.type_name); ?></option>
            <?php $__currentLoopData = $vo.child; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($ch.type_id); ?>" <?php if($ch.type_id == $val): ?>selected@endif>&nbsp;&nbsp;&nbsp;&nbsp;├&nbsp;<?php echo e($ch.type_name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="layui-input-inline">
        <button type="submit" class="layui-btn" lay-submit="" refresh="<?php echo e($refresh); ?>" lay-filter="formSubmit"><?php echo e(__('admin.btn_save')); ?></button>
    </div>
</form>

<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\public\select_type.blade.php ENDPATH**/ ?>