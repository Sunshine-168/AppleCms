<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">
    <form class="layui-form layui-form-pane" method="post" action="">
        <input id="collect_id" name="collect_id" type="hidden" value="<?php echo e($info.collect_id); ?>">
        <input type="hidden" name="__token__" value="<?php echo e($Request.token); ?>" />
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.admin/collect/name')); ?>：</label>
            <div class="layui-input-block  ">
                <input type="text" class="layui-input" value="<?php echo e($info.collect_name); ?>" placeholder="" id="collect_name" name="collect_name">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.admin/collect/api_url')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.collect_url); ?>" placeholder="" id="collect_url" name="collect_url">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.admin/collect/attach_param')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.collect_param); ?>" placeholder="" id="collect_param" name="collect_param">
            </div>
            <div class="layui-form-mid layui-word-aux" style="margin-left:110px; "><?php echo e(__('admin.admin/collect/attach_param_tip')); ?></div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.admin/collect/api_type')); ?>：</label>
            <div class="layui-input-block">
                <input name="collect_type" type="radio" value="1" title="xml" <?php if(condition="$info['collect_type'] == 1"): ?>checked <?php endif; ?>>
                <input name="collect_type" type="radio" value="2" title="json" <?php if(condition="$info['collect_type'] != 1"): ?>checked <?php endif; ?>>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.admin/collect/data_type')); ?>：</label>
            <div class="layui-input-block">
                <input name="collect_mid" lay-filter="collect_mid" type="radio" value="1" title="<?php echo e(__('admin.vod')); ?>" <?php if(condition="$info['collect_mid'] == 1"): ?>checked <?php endif; ?>>
                <input name="collect_mid" lay-filter="collect_mid" type="radio" value="2" title="<?php echo e(__('admin.art')); ?>" <?php if(condition="$info['collect_mid'] == 2"): ?>checked <?php endif; ?>>
                <input name="collect_mid" lay-filter="collect_mid" type="radio" value="8" title="<?php echo e(__('admin.actor')); ?>" <?php if(condition="$info['collect_mid'] == 8"): ?>checked <?php endif; ?>>
                <input name="collect_mid" lay-filter="collect_mid" type="radio" value="9" title="<?php echo e(__('admin.role')); ?>" <?php if(condition="$info['collect_mid'] == 9"): ?>checked <?php endif; ?>>
                <input name="collect_mid" lay-filter="collect_mid" type="radio" value="11" title="<?php echo e(__('admin.website')); ?>" <?php if(condition="$info['collect_mid'] == 11"): ?>checked <?php endif; ?>>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.admin/collect/data_opt')); ?>：</label>
            <div class="layui-input-block">
                <input name="collect_opt" type="radio" value="0" title="<?php echo e(__('admin.admin/collect/add_update')); ?>" <?php if(condition="$info['collect_opt'] == 0"): ?>checked <?php endif; ?>>
                <input name="collect_opt" type="radio" value="1" title="<?php echo e(__('admin.admin/collect/add')); ?>" <?php if(condition="$info['collect_opt'] == 1"): ?>checked <?php endif; ?>>
                <input name="collect_opt" type="radio" value="2" title="<?php echo e(__('admin.admin/collect/update')); ?>" <?php if(condition="$info['collect_opt'] == 2"): ?>checked <?php endif; ?>>
            </div>
            <div class="layui-form-mid layui-word-aux" style=""><?php echo e(__('admin.admin/collect/data_opt_tip')); ?></div>
        </div>

        <div class="layui-form-item row_filer" <?php if(condition="$info['collect_mid'] != '1'"): ?> style="display:none;" <?php endif; ?>>
            <label class="layui-form-label"><?php echo e(__('admin.admin/collect/url_filter')); ?>：</label>
            <div class="layui-input-block">
                <input name="collect_filter" type="radio" value="0" title="<?php echo e(__('admin.admin/collect/no_filter')); ?>" <?php if(condition="$info['collect_filter'] == 0"): ?>checked <?php endif; ?>>
                <input name="collect_filter" type="radio" value="1" title="<?php echo e(__('admin.admin/collect/add_update')); ?>" <?php if(condition="$info['collect_filter'] == 1"): ?>checked <?php endif; ?>>
                <input name="collect_filter" type="radio" value="2" title="<?php echo e(__('admin.admin/collect/add')); ?>" <?php if(condition="$info['collect_filter'] == 2"): ?>checked <?php endif; ?>>
                <input name="collect_filter" type="radio" value="3" title="<?php echo e(__('admin.admin/collect/update')); ?>" <?php if(condition="$info['collect_filter'] == 3"): ?>checked <?php endif; ?>>
            </div>
        </div>
        <div class="layui-form-item row_filer" <?php if(condition="$info['collect_mid'] != '1'"): ?> style="display:none;" <?php endif; ?>>
            <label class="layui-form-label"><?php echo e(__('admin.admin/collect/filter_code')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.collect_filter_from); ?>" placeholder="<?php echo e(__('admin.admin/collect/filter_code_tip')); ?>" id="collect_filter_from" name="collect_filter_from">
            </div>
        </div>
        <div class="layui-form-item row_filer" <?php if(condition="$info['collect_mid'] != '1'"): ?> style="display:none;" <?php endif; ?>>
            <label class="layui-form-label"><?php echo e(__('admin.admin/collect/filter_year')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.collect_filter_year); ?>" placeholder="<?php echo e(__('admin.admin/collect/filter_year_tip')); ?>" id="collect_filter_year" name="collect_filter_year">
            </div>
        </div>
        <div class="layui-form-item row_filer">
            <label class="layui-form-label"><?php echo e(__('admin.pic_sync')); ?>：</label>
            <div class="layui-input-block">
                <input name="collect_sync_pic_opt" type="radio" value="0" title="<?php echo e(__('admin.follow_global')); ?>" <?php if(condition="$info['collect_sync_pic_opt'] == 0"): ?>checked <?php endif; ?>>
                <input name="collect_sync_pic_opt" type="radio" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if(condition="$info['collect_sync_pic_opt'] == 1"): ?>checked <?php endif; ?>>
                <input name="collect_sync_pic_opt" type="radio" value="2" title="<?php echo e(__('admin.close')); ?>" <?php if(condition="$info['collect_sync_pic_opt'] == 2"): ?>checked <?php endif; ?>>
            </div>
        </div>

        <br>
        <div class="layui-form-item center">
            <div class="layui-input-block">
                <button class="layui-btn layui-btn-normal" type="button" id="btnTest" ><?php echo e(__('admin.test')); ?></button>
                <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit" data-child="true"><?php echo e(__('admin.btn_save')); ?></button>
                <button class="layui-btn layui-btn-warm" type="reset"><?php echo e(__('admin.btn_reset')); ?></button>
            </div>
        </div>
    </form>

</div>
<?php echo $__env->make('../../../application/admin/view/public/foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script type="text/javascript">
    layui.use(['form', 'layer'], function () {
        // 操作对象
        var form = layui.form
                , layer = layui.layer
                , $ = layui.jquery;

        // 验证
        form.verify({
            collect_name: function (value) {
                if (value == "") {
                    return "<?php echo e(__('admin.name_empty')); ?>";
                }
            },
            collect_url: function (value) {
                if (value == "") {
                    return "<?php echo e(__('admin.url_empty')); ?>";
                }
            }
        });


        $('#btnTest').click(function() {
            var that = $(this);
            var data = 'cjurl='+ $('#collect_url').val() + '&cjflag='+ '&ac=list';

            $.post("<?php echo e(url('test')); ?>",data,function(r){
                if(r.code==1){
                    layer.msg( "<?php echo e(__('admin.admin/collect/test_ok')); ?>" + '：'+ r.msg ,{time:1800});
                    if(r.msg=='json'){
                        $("input[name='collect_type'][value=2]").attr("checked",true);
                    }
                    else{
                        $("input[name='collect_type'][value=1]").attr("checked",true);
                    }
                    form.render('radio');
                }
                else{
                    layer.msg(r.msg,{time:1800});
                }
            });

        });

        form.on('radio(collect_mid)',function(data){
            $('.row_filer').hide();
            if(data.value=='1'){
                $('.row_filer').show();
            }
        });

    });




</script>

</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\collect\info.blade.php ENDPATH**/ ?>