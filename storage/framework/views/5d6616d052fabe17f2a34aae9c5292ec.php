<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">

    <form class="layui-form " method="post" action="<?php echo e(route('admin.images.sync')); ?>">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="tab" value="<?php echo e($tab); ?>">
        <fieldset class="layui-elem-field">
            <legend><?php echo e(__('admin.admin/images/sync_range')); ?></legend>
            <div class="layui-field-box">
                <div class="layui-form-item">
                        <div class="layui-input-inline" style="width: 110px;">
                            <input type="radio" checked value="1" name="range" title="<?php echo e(__('admin.all')); ?>">
                        </div>
                        <div class="layui-input-inline" style="width: 110px;">
                            <input type="radio" value="2" name="range" title="<?php echo e(__('admin.admin/images/date')); ?>">
                        </div>
                        <div class="layui-input-inline" style="width: 100px;">
                            <input type="text" name="date" required  placeholder="" autocomplete="off" class="layui-input" value="<?php echo e(date('Y-m-d')); ?>">
                        </div>
                </div>
            </div>
        </fieldset>

        <fieldset class="layui-elem-field">
            <legend><?php echo e(__('admin.admin/images/sync_option')); ?></legend>
            <div class="layui-field-box">
                <div class="layui-form-item">
                    <div class="layui-input-inline" style="width: 110px;">
                        <input type="radio"  value="0" name="opt" title="<?php echo e(__('admin.all')); ?>">
                    </div>
                    <div class="layui-input-inline" style="width: 110px;">
                        <input type="radio" value="1" name="opt" title="<?php echo e(__('admin.not_pic_sync_err')); ?>">
                    </div>
                    <div class="layui-input-inline" style="width: 120px;">
                        <input type="radio" checked value="2" name="opt" title="<?php echo e(__('admin.not_pic_sync_today_err')); ?>">
                    </div>
                    <div class="layui-input-inline" style="width: 110px;">
                        <input type="radio" value="3" name="opt" title="<?php echo e(__('admin.pic_err')); ?>">
                    </div>
                </div>
            </div>
        </fieldset>

        <fieldset class="layui-elem-field">
            <legend><?php echo e(__('admin.admin/images/opt/tip1')); ?></legend>
            <div class="layui-field-box">
                <div class="layui-form-item">
                    <div class="layui-input-inline" style="width: 110px;">
                        <input type="radio" checked value="1" name="col" title="<?php echo e(__('admin.admin/images/opt/pic')); ?>">
                    </div>
                    <div class="layui-input-inline" style="width: 110px;">
                        <input type="radio" value="2" name="col" title="<?php echo e(__('admin.admin/images/pic_content')); ?>">
                    </div>
                </div>
            </div>
        </fieldset>

        <fieldset class="layui-elem-field">
            <legend><?php echo e(__('admin.admin/images/opt/tip2')); ?></legend>
            <div class="layui-field-box">
                <div class="layui-form-item">
                    <div class="layui-input-inline" style="width: 110px;">
                        <input type="text" name="limit" required placeholder="" autocomplete="off" class="layui-input" value="10">
                    </div>
                </div>
            </div>
        </fieldset>

        <div class="layui-form-item">
            <button type="submit" class="layui-btn btn_submit"><?php echo e(__('admin.start_exec')); ?></button>
        </div>

    </form>
</div>
<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script type="text/javascript">
    layui.use(['element', 'layer'], function() {

    });

    $('.btn_submit').click(function(){
        $('form').submit();
    })

</script><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\images\opt.blade.php ENDPATH**/ ?>