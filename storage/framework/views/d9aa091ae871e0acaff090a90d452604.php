<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="page-container">
    <form class="layui-form layui-form-pane" action="">
        <input type="hidden" name="__token__" value="<?php echo e($Request.token); ?>" />
        <div class="layui-tab">
            <ul class="layui-tab-title">
                <li class="layui-this" lay-id="configsms_1"><?php echo e(lang('admin/system/configsms/title')); ?></li>
                {volist name="$extends['ext_list']" id="vo"}
                <li data-key="<?php echo e($key); ?>" lay-id="configsms_<?php echo e($i+1); ?>"><?php echo e($vo); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
            <div class="layui-tab-content">
                <div class="layui-tab-item layui-show">

                    <blockquote class="layui-elem-quote layui-quote-nm">
                        <?php echo e(lang('admin/system/configsms/tip')); ?>

                    </blockquote>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(lang('admin/system/configsms/type')); ?>：</label>
                        <div class="layui-input-inline">
                            <select  name="sms[type]">
                                <option value="" ><?php echo e(lang('select_please')); ?>...</option>
                                {volist name="$extends['ext_list']" id="vo"}
                                <option value="<?php echo e($key); ?>" <?php if(condition="$config['sms']['type'] eq $key"): ?>selected <?php endif; ?>><?php echo e($vo); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="layui-form-mid layui-word-aux"></div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(lang('admin/system/configsms/sign')); ?>：</label>
                        <div class="layui-input-inline w400">
                            <input type="text" id="sign" name="sms[sign]" placeholder="" value="<?php echo e($config['sms']['sign']); ?>" class="layui-input "  >
                        </div>
                        <div class="layui-form-mid layui-word-aux"></div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(lang('admin/system/configsms/tpl_code_reg')); ?>：</label>
                        <div class="layui-input-inline w400">
                            <input type="text" id="tpl_code_reg" name="sms[tpl_code_reg]" placeholder="" value="<?php echo e($config['sms']['tpl_code_reg']); ?>" class="layui-input "  >
                        </div>
                        <div class="layui-form-mid layui-word-aux"><?php echo e(lang('admin/system/configsms/tpl_code_tip')); ?></div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(lang('admin/system/configsms/tpl_code_bind')); ?>：</label>
                        <div class="layui-input-inline w400">
                            <input type="text" id="tpl_code_bind" name="sms[tpl_code_bind]" placeholder="" value="<?php echo e($config['sms']['tpl_code_bind']); ?>" class="layui-input "  >
                        </div>
                        <div class="layui-form-mid layui-word-aux"><?php echo e(lang('admin/system/configsms/tpl_code_tip')); ?></div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(lang('admin/system/configsms/tpl_code_findpass')); ?>：</label>
                        <div class="layui-input-inline w400">
                            <input type="text" id="tpl_code_findpass" name="sms[tpl_code_findpass]" placeholder="" value="<?php echo e($config['sms']['tpl_code_findpass']); ?>" class="layui-input "  >
                        </div>
                        <div class="layui-form-mid layui-word-aux"><?php echo e(lang('admin/system/configsms/tpl_code_tip')); ?></div>
                    </div>
                </div>
                <?php echo e($extends['ext_html']); ?>

            </div>
        </div>
        <div class="layui-form-item center">
            <div class="layui-input-block">
                <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit"><?php echo e(lang('btn_save')); ?></button>
                <button class="layui-btn layui-btn-warm" type="reset"><?php echo e(lang('btn_reset')); ?></button>
            </div>
        </div>
    </form>
</div>

<?php echo $__env->make('../../../application/admin/view/public/foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script type="text/javascript">
    function test_email() {
        var host = $("#host").val();
        var username = $("#username").val();
        var password = $("#password").val();
        var test = $("#test").val();
        var port = $('#port').val();

        layer.msg("<?php echo e(lang('wait_submit')); ?>",{time:500000});
        $.ajax({
            url: "<?php echo e(url('system/test_email')); ?>",
            type: "post",
            dataType: "json",
            data: {host:host,username:username,password:password,port:port,test:test},
            beforeSend: function () {
            },
            error:function(r){
                layer.msg("<?php echo e(lang('admin/system/configsms/test_err')); ?>",{time:1800});
            },
            success: function (r) {
                layer.msg(r.msg,{time:1800});
            },
            complete: function () {
            }
        });
    }
</script>

</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\system\configsms.blade.php ENDPATH**/ ?>