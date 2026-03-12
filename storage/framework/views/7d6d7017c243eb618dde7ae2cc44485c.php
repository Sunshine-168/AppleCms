<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">
    <form class="layui-form layui-form-pane" method="post" action="">
        <input type="hidden" name="__token__" value="<?php echo e($Request.token); ?>" />
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(lang('status')); ?>：</label>
                        <div class="layui-input-inline">
                            <input name="status" type="radio" id="rad-1" value="0" title="<?php echo e(lang('disable')); ?>" <?php if(condition="$info['status'] neq 1"): ?>checked <?php endif; ?>>
                            <input name="status" type="radio" id="rad-2" value="1" title="<?php echo e(lang('enable')); ?>" <?php if(condition="$info['status'] eq 1"): ?>checked <?php endif; ?>>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(lang('name')); ?>：</label>
                        <div class="layui-input-inline w500">
                            <input type="text" class="layui-input" value="<?php echo e($info.name); ?>" placeholder="<?php echo e(lang('admin/timming/unique_id')); ?>" id="name" name="name" <?php if(condition="$info.name neq ''"): ?> readonly="readonly"<?php endif; ?>>
                        </div>
                        <div class="layui-form-mid layui-word-aux"><?php echo e(lang('admin/timming/call_method')); ?>/api.php/timming/index?name=<?php echo e($info.name); ?></div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(lang('remarks')); ?>：</label>
                        <div class="layui-input-inline w500">
                            <input type="text" class="layui-input" value="<?php echo e($info.des); ?>" placeholder="" id="des" name="des">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(lang('admin/timming/exec_file')); ?>：</label>
                        <div class="layui-input-inline w500">
                            <select class="" name="file">
                                <option value="collect" <?php if(condition="$info['file'] eq 'collect'"): ?>selected@endif><?php echo e(lang('admin/timming/collect')); ?></option>
                                <option value="make" <?php if(condition="$info['file'] eq 'make'"): ?>selected@endif><?php echo e(lang('admin/timming/make')); ?></option>
                                <option value="cj" <?php if(condition="$info['file'] eq 'cj'"): ?>selected@endif><?php echo e(lang('admin/timming/cj')); ?></option>
                                <option value="cache" <?php if(condition="$info['file'] eq 'cache'"): ?>selected@endif><?php echo e(lang('admin/timming/cache')); ?></option>
                                <option value="urlsend" <?php if(condition="$info['file'] eq 'urlsend'"): ?>selected@endif><?php echo e(lang('admin/timming/urlsend')); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(lang('admin/timming/attach_param')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="text" class="layui-input" value="<?php echo e($info.param); ?>" placeholder="<?php echo e(lang('admin/timming/attach_param_tip')); ?>" id="param" name="param">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(lang('admin/timming/exec_cycle')); ?>：</label>
                        <div class="layui-input-block role-list-form">
                            <input type="checkbox" name="weeks[]" class="layui-checkbox" lay-skin="primary" value="1" title="<?php echo e(lang('monday')); ?>" <?php if(condition="strpos($info['weeks'],'1')!==false"): ?>checked@endif>
                            <input type="checkbox" name="weeks[]" class="layui-checkbox" lay-skin="primary" value="2" title="<?php echo e(lang('tuesday')); ?>" <?php if(condition="strpos($info['weeks'],'2')!==false"): ?>checked@endif>
                            <input type="checkbox" name="weeks[]" class="layui-checkbox" lay-skin="primary" value="3" title="<?php echo e(lang('wednesday')); ?>" <?php if(condition="strpos($info['weeks'],'3')!==false"): ?>checked@endif>
                            <input type="checkbox" name="weeks[]" class="layui-checkbox" lay-skin="primary" value="4" title="<?php echo e(lang('thursday')); ?>" <?php if(condition="strpos($info['weeks'],'4')!==false"): ?>checked@endif>
                            <input type="checkbox" name="weeks[]" class="layui-checkbox" lay-skin="primary" value="5" title="<?php echo e(lang('friday')); ?>" <?php if(condition="strpos($info['weeks'],'5')!==false"): ?>checked@endif>
                            <input type="checkbox" name="weeks[]" class="layui-checkbox" lay-skin="primary" value="6" title="<?php echo e(lang('saturday')); ?>" <?php if(condition="strpos($info['weeks'],'6')!==false"): ?>checked@endif>
                            <input type="checkbox" name="weeks[]" class="layui-checkbox" lay-skin="primary" value="0" title="<?php echo e(lang('sunday')); ?>" <?php if(condition="strpos($info['weeks'],'0')!==false"): ?>checked@endif>

                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(lang('admin/timming/exec_time')); ?>：</label>
                        <div class="layui-input-block role-list-form">
                            <input type="checkbox" name="hours[]" class="layui-checkbox" lay-skin="primary" value="00" title="00" <?php if(condition="strpos($info['hours'],'00')!==false"): ?>checked@endif>
                            <input type="checkbox" name="hours[]" class="layui-checkbox" lay-skin="primary" value="01" title="01" <?php if(condition="strpos($info['hours'],'01')!==false"): ?>checked@endif>
                            <input type="checkbox" name="hours[]" class="layui-checkbox" lay-skin="primary" value="02" title="02" <?php if(condition="strpos($info['hours'],'02')!==false"): ?>checked@endif>
                            <input type="checkbox" name="hours[]" class="layui-checkbox" lay-skin="primary" value="03" title="03" <?php if(condition="strpos($info['hours'],'03')!==false"): ?>checked@endif>
                            <input type="checkbox" name="hours[]" class="layui-checkbox" lay-skin="primary" value="04" title="04" <?php if(condition="strpos($info['hours'],'04')!==false"): ?>checked@endif>
                            <input type="checkbox" name="hours[]" class="layui-checkbox" lay-skin="primary" value="05" title="05" <?php if(condition="strpos($info['hours'],'05')!==false"): ?>checked@endif>
                            <input type="checkbox" name="hours[]" class="layui-checkbox" lay-skin="primary" value="06" title="06" <?php if(condition="strpos($info['hours'],'06')!==false"): ?>checked@endif>
                            <input type="checkbox" name="hours[]" class="layui-checkbox" lay-skin="primary" value="07" title="07" <?php if(condition="strpos($info['hours'],'07')!==false"): ?>checked@endif>
                            <input type="checkbox" name="hours[]" class="layui-checkbox" lay-skin="primary" value="08" title="08" <?php if(condition="strpos($info['hours'],'08')!==false"): ?>checked@endif>
                            <input type="checkbox" name="hours[]" class="layui-checkbox" lay-skin="primary" value="09" title="09" <?php if(condition="strpos($info['hours'],'09')!==false"): ?>checked@endif>
                            <input type="checkbox" name="hours[]" class="layui-checkbox" lay-skin="primary" value="10" title="10" <?php if(condition="strpos($info['hours'],'10')!==false"): ?>checked@endif>
                            <input type="checkbox" name="hours[]" class="layui-checkbox" lay-skin="primary" value="11" title="11" <?php if(condition="strpos($info['hours'],'11')!==false"): ?>checked@endif>
                            <input type="checkbox" name="hours[]" class="layui-checkbox" lay-skin="primary" value="12" title="12" <?php if(condition="strpos($info['hours'],'12')!==false"): ?>checked@endif>
                            <input type="checkbox" name="hours[]" class="layui-checkbox" lay-skin="primary" value="13" title="13" <?php if(condition="strpos($info['hours'],'13')!==false"): ?>checked@endif>
                            <input type="checkbox" name="hours[]" class="layui-checkbox" lay-skin="primary" value="14" title="14" <?php if(condition="strpos($info['hours'],'14')!==false"): ?>checked@endif>
                            <input type="checkbox" name="hours[]" class="layui-checkbox" lay-skin="primary" value="15" title="15" <?php if(condition="strpos($info['hours'],'15')!==false"): ?>checked@endif>
                            <input type="checkbox" name="hours[]" class="layui-checkbox" lay-skin="primary" value="16" title="16" <?php if(condition="strpos($info['hours'],'16')!==false"): ?>checked@endif>
                            <input type="checkbox" name="hours[]" class="layui-checkbox" lay-skin="primary" value="17" title="17" <?php if(condition="strpos($info['hours'],'17')!==false"): ?>checked@endif>
                            <input type="checkbox" name="hours[]" class="layui-checkbox" lay-skin="primary" value="18" title="18" <?php if(condition="strpos($info['hours'],'18')!==false"): ?>checked@endif>
                            <input type="checkbox" name="hours[]" class="layui-checkbox" lay-skin="primary" value="19" title="19" <?php if(condition="strpos($info['hours'],'19')!==false"): ?>checked@endif>
                            <input type="checkbox" name="hours[]" class="layui-checkbox" lay-skin="primary" value="20" title="20" <?php if(condition="strpos($info['hours'],'20')!==false"): ?>checked@endif>
                            <input type="checkbox" name="hours[]" class="layui-checkbox" lay-skin="primary" value="21" title="21" <?php if(condition="strpos($info['hours'],'21')!==false"): ?>checked@endif>
                            <input type="checkbox" name="hours[]" class="layui-checkbox" lay-skin="primary" value="22" title="22" <?php if(condition="strpos($info['hours'],'22')!==false"): ?>checked@endif>
                            <input type="checkbox" name="hours[]" class="layui-checkbox" lay-skin="primary" value="23" title="23" <?php if(condition="strpos($info['hours'],'23')!==false"): ?>checked@endif>
                        </div>
                    </div>
        

        <div class="layui-form-item center">
            <div class="layui-input-block">
                <button type="button" class="layui-btn layui-btn-normal formCheckAll" lay-filter="formCheckAll" ><?php echo e(lang('check_all')); ?></button>
                <button type="button" class="layui-btn layui-btn-normal formCheckOther" lay-filter="formCheckOther"><?php echo e(lang('check_other')); ?></button>

                <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit" data-child="true"><?php echo e(lang('btn_save')); ?></button>
                <button class="layui-btn layui-btn-warm" type="reset"><?php echo e(lang('btn_reset')); ?></button>
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
            show: function (value) {
                if (value == "") {
                    return "<?php echo e(lang('name_empty')); ?>";
                }
            }
        });

        $('.formCheckAll').click(function(){
            var child = $('.role-list-form').find('input');
            /* 自动选中子节点 */
            child.each(function(index, item) {
                item.checked = true;
            });
            form.render('checkbox');
        });
        $('.formCheckOther').click(function(){
            var child = $('.role-list-form').find('input');
            /* 自动选中子节点 */
            child.each(function(index, item) {
                item.checked = (item.checked  ? false : true);
            });
            form.render('checkbox');
        });


    });
</script>

</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\timming\info.blade.php ENDPATH**/ ?>