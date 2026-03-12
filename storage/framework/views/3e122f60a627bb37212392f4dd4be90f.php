<form class="layui-form m10" method="post" action="<?php echo e($url); ?>">
    <input type="hidden" name="col" value="<?php echo e($col); ?>">
    <input type="hidden" name="ids" value="<?php echo e($ids); ?>">

    <div class="layui-input-inline w150">
        <select name="val">
            <option value=""><?php echo e(__('admin.select_opt')); ?></option>
            <option value="0" ><?php echo e(__('admin.disable')); ?></option>
            <option value="1" ><?php echo e(__('admin.enable')); ?></option>
        </select>
    </div>
    <div class="layui-input-inline">
        <button type="submit" class="layui-btn" lay-submit="" refresh="<?php echo e($refresh); ?>" lay-filter="formSubmit"><?php echo e(__('admin.btn_save')); ?></button>
    </div>
</form>

<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\public\select_state.blade.php ENDPATH**/ ?>