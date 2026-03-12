<form class="layui-form m10" method="post" action="<?php echo e($url); ?>">
    <input type="hidden" name="col" value="<?php echo e($col); ?>">
    <input type="hidden" name="ids" value="<?php echo e($ids); ?>">

    <div class="layui-form-item">
        <label class="layui-form-label w50"><?php echo e(__('admin.start')); ?>：</label>
        <div class="layui-input-inline w70">
            <input type="text" class="layui-input" value="" placeholder="" name="start">
        </div>
        <label class="layui-form-label w50"><?php echo e(__('admin.end')); ?>：</label>
        <div class="layui-input-inline w70">
            <input type="text" class="layui-input" value="" placeholder="" name="end">
        </div>
        <div class="layui-input-inline w70">
            <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit"><?php echo e(__('admin.btn_save')); ?></button>
        </div>
    </div>

</form>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\public\select_hits.blade.php ENDPATH**/ ?>