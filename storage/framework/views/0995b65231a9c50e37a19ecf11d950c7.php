<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container">
    <form class="layui-form layui-form-pane" method="post" action="<?php echo e(route('admin.system.configconnect')); ?>">
        <?php echo csrf_field(); ?>
        <blockquote class="layui-elem-quote layui-quote-nm">
            <?php echo e(__('admin.admin/system/configconnect/tip')); ?>

        </blockquote>

        <fieldset class="layui-elem-field layui-field-title" style="margin-top: 30px;">
            <legend><?php echo e(__('admin.admin/system/configconnect/qq')); ?> <a target="_blank" href="http://connect.qq.com/?maccms" class="layui-btn layui-btn-primary"><?php echo e(__('admin.admin/system/configconnect/go_reg')); ?></a></legend>
        </fieldset>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.status')); ?>：</label>
            <div class="layui-input-block">
                <input type="radio" name="connect[qq][status]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'connect.qq.status', '0') !== '1'): echo 'checked'; endif; ?>>
                <input type="radio" name="connect[qq][status]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'connect.qq.status', '0') === '1'): echo 'checked'; endif; ?>>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">APP_KEY：</label>
            <div class="layui-input-block">
                <input type="text" name="connect[qq][key]" value="<?php echo e(data_get($config, 'connect.qq.key', '')); ?>" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">APP_SECRET：</label>
            <div class="layui-input-block">
                <input type="text" name="connect[qq][secret]" value="<?php echo e(data_get($config, 'connect.qq.secret', '')); ?>" class="layui-input">
            </div>
        </div>

        <fieldset class="layui-elem-field layui-field-title" style="margin-top: 30px;">
            <legend><?php echo e(__('admin.admin/system/configconnect/wx')); ?> <a target="_blank" href="https://open.weixin.qq.com/?maccms" class="layui-btn layui-btn-primary"><?php echo e(__('admin.admin/system/configconnect/go_reg')); ?></a></legend>
        </fieldset>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.status')); ?>：</label>
            <div class="layui-input-block">
                <input type="radio" name="connect[weixin][status]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'connect.weixin.status', '0') !== '1'): echo 'checked'; endif; ?>>
                <input type="radio" name="connect[weixin][status]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'connect.weixin.status', '0') === '1'): echo 'checked'; endif; ?>>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">APP_KEY：</label>
            <div class="layui-input-block">
                <input type="text" name="connect[weixin][key]" value="<?php echo e(data_get($config, 'connect.weixin.key', '')); ?>" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">APP_SECRET：</label>
            <div class="layui-input-block">
                <input type="text" name="connect[weixin][secret]" value="<?php echo e(data_get($config, 'connect.weixin.secret', '')); ?>" class="layui-input">
            </div>
        </div>

        <div class="layui-form-item center">
            <div class="layui-input-block">
                <button type="submit" class="layui-btn" lay-submit lay-filter="formSubmit"><?php echo e(__('admin.btn_save')); ?></button>
                <button class="layui-btn layui-btn-warm" type="reset"><?php echo e(__('admin.btn_reset')); ?></button>
            </div>
        </div>
    </form>
</div>
<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\system\configconnect.blade.php ENDPATH**/ ?>