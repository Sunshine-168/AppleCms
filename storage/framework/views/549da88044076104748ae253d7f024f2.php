<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container">
    <form class="layui-form layui-form-pane" method="post" action="<?php echo e(route('admin.system.configinterface')); ?>">
        <?php echo csrf_field(); ?>
        <blockquote class="layui-elem-quote layui-quote-nm" style="color:#01AAED;">
            <?php echo __('admin.admin/system/configinterface/tip'); ?>

        </blockquote>

        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.admin/system/configinterface/status')); ?>：</label>
            <div class="layui-input-block">
                <input type="radio" name="interface[status]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'interface.status', '0') !== '1'): echo 'checked'; endif; ?>>
                <input type="radio" name="interface[status]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'interface.status', '0') === '1'): echo 'checked'; endif; ?>>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.admin/system/configinterface/pass')); ?>：</label>
            <div class="layui-input-inline w400">
                <input type="text" name="interface[pass]" value="<?php echo e(data_get($config, 'interface.pass', '')); ?>" class="layui-input">
            </div>
            <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin.admin/system/configinterface/pass_tip')); ?></div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.admin/system/configinterface/vod_type')); ?>：</label>
            <div class="layui-input-inline" style="width:230px;">
                <textarea name="interface[vodtype]" class="layui-textarea" rows="20"><?php echo e(mac_replace_text((string) data_get($config, 'interface.vodtype', ''))); ?></textarea>
            </div>
            <label class="layui-form-label"><?php echo e(__('admin.admin/system/configinterface/art_type')); ?>：</label>
            <div class="layui-input-inline" style="width:230px;">
                <textarea name="interface[arttype]" class="layui-textarea" rows="20"><?php echo e(mac_replace_text((string) data_get($config, 'interface.arttype', ''))); ?></textarea>
            </div>
            <label class="layui-form-label"><?php echo e(__('admin.admin/system/configinterface/actor_type')); ?>：</label>
            <div class="layui-input-inline" style="width:230px;">
                <textarea name="interface[actortype]" class="layui-textarea" rows="20"><?php echo e(mac_replace_text((string) data_get($config, 'interface.actortype', ''))); ?></textarea>
            </div>
            <label class="layui-form-label"><?php echo e(__('admin.admin/system/configinterface/website_type')); ?>：</label>
            <div class="layui-input-inline" style="width:230px;">
                <textarea name="interface[websitetype]" class="layui-textarea" rows="20"><?php echo e(mac_replace_text((string) data_get($config, 'interface.websitetype', ''))); ?></textarea>
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
<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views/admin/system/configinterface.blade.php ENDPATH**/ ?>