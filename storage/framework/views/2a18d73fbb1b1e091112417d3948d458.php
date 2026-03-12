<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container">
    <form class="layui-form layui-form-pane" method="post" action="">
        <?php echo csrf_field(); ?>
        <blockquote class="layui-elem-quote layui-quote-nm">
            提示信息：<br>
            为了安全考量避免通过模板写入后门文件，文件内出现以下任意字符串时禁止在线保存修改，如需修改请使用其他方式。<br>
            <?php echo e($filter); ?>

        </blockquote>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.path')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($fpath); ?>" id="fpath" name="fpath" readonly>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.file_name')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($fname); ?>" placeholder="<?php echo e(__('admin.admin/template/name_tip')); ?>" id="fname" name="fname" <?php echo e($fname !== '' ? 'readonly' : ''); ?>>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.content')); ?>：</label>
            <div class="layui-input-block">
                <textarea name="fcontent" class="layui-textarea" style="height:550px;"><?php echo e($fcontent); ?></textarea>
            </div>
        </div>

        <div class="layui-form-item center">
            <div class="layui-input-block">
                <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit" data-child="true"><?php echo e(__('admin.btn_save')); ?></button>
                <button class="layui-btn layui-btn-warm" type="reset"><?php echo e(__('admin.btn_reset')); ?></button>
            </div>
        </div>
    </form>
</div>
<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\template\info.blade.php ENDPATH**/ ?>