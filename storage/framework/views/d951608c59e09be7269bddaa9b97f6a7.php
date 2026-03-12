<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="page-container">
    <form class="layui-form layui-form-pane" action="">
        <input type="hidden" name="__token__" value="<?php echo e($Request.token); ?>" />
        <div class="layui-tab" lay-filter="tb1">
            <ul class="layui-tab-title">
                <li class="layui-this" lay-id="configpay_1"><?php echo e(__('admin.admin/system/configpay/title')); ?></li>
                <li lay-id="configpay_2"><?php echo e(__('admin.admin/system/configpay/card')); ?></li>
                {volist name="$extends['ext_list']" id="vo"}
                <li data-key="<?php echo e($key); ?>" lay-id="configpay_<?php echo e($i+2); ?>"><?php echo e($vo); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                
            </ul>
            <div class="layui-tab-content">

                <div class="layui-tab-item layui-show">

                    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 30px;">
                        <legend><?php echo e(__('admin.admin/system/configpay/config')); ?></legend>
                    </fieldset>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configpay/notify')); ?>：</label>
                        <div class="layui-input-inline w400">
                            <input type="text" readonly="readonly" value="<?php echo e($http_type); ?><?php echo e($config['site']['site_url']); ?>/index.php/payment/notify.html" class="layui-input">
                        </div>
                        <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin.admin/system/configpay/notify_tip')); ?></div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configpay/min')); ?>：</label>
                        <div class="layui-input-inline w400">
                            <input type="text" name="pay[min]" placeholder="" value="<?php echo e($config['pay']['min']); ?>" class="layui-input">
                        </div>
                        <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin.admin/system/configpay/min_tip')); ?></div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configpay/scale')); ?>：</label>
                        <div class="layui-input-inline w400">
                            <input type="text" name="pay[scale]" placeholder="" value="<?php echo e($config['pay']['scale']); ?>" class="layui-input">
                        </div>
                        <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin.admin/system/configpay/scale_tip')); ?></div>
                    </div>
                </div>

                <div class="layui-tab-item ">

                    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 30px;">
                        <legend><?php echo e(__('admin.admin/system/configpay/card_config')); ?></legend>
                    </fieldset>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin.admin/system/configpay/card_url')); ?>：</label>
                        <div class="layui-input-inline w400">
                            <input type="text" name="pay[card][url]" placeholder="" value="<?php echo e($config['pay']['card']['url']); ?>" class="layui-input">
                        </div>
                        <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin.admin/system/configpay/card_url_tip')); ?></div>
                    </div>
                </div>

                <?php echo e($extends['ext_html']); ?>


            </div>
        </div>
        <div class="layui-form-item center">
            <div class="layui-input-block">
                <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit"><?php echo e(__('admin.btn_save')); ?></button>
                <button class="layui-btn layui-btn-warm" type="reset"><?php echo e(__('admin.btn_reset')); ?></button>
            </div>
        </div>
    </form>
</div>

<?php echo $__env->make('../../../application/admin/view/public/foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script type="text/javascript" src="<?php echo e(asset('static')); ?>/js/jquery.cookie.js"></script>
<script type="text/javascript">
    layui.use(['element', 'form', 'layer'], function() {
        var element = layui.element
            ,form = layui.form
            , layer = layui.layer;


        element.on('tab(tb1)', function(){
            $.cookie('configpay_tab', this.getAttribute('lay-id'));
        });

        if( $.cookie('configpay_tab') !=null ) {
            element.tabChange('tb1', $.cookie('configpay_tab'));
        }

    });
</script>

</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\system\configpay.blade.php ENDPATH**/ ?>