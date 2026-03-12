<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">
    <form class="layui-form layui-form-pane" method="post" action="">
        <input type="hidden" name="__token__" value="<?php echo e($Request.token); ?>" />
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(lang('status')); ?>：</label>
            <div class="layui-input-block">
                <input name="status" type="radio" id="rad-1" value="0" title="<?php echo e(lang('disable')); ?>" <?php if(condition="$info['status'] neq 1"): ?>checked <?php endif; ?>>
                <input name="status" type="radio" id="rad-2" value="1" title="<?php echo e(lang('enable')); ?>" <?php if(condition="$info['status'] eq 1"): ?>checked <?php endif; ?>>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(lang('code')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.from); ?>" placeholder="<?php echo e(lang('admin/vodplayer/code_tip')); ?>" id="from" name="from" <?php if(condition="$info.from neq ''"): ?> readonly="readonly"<?php endif; ?>>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(lang('name')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.show); ?>" placeholder="" id="show" name="show">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(lang('remarks')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.des); ?>" placeholder="" id="des" name="des">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(lang('target')); ?>：</label>
            <div class="layui-input-block">
                <input name="target" type="radio" value="_self" title="<?php echo e(lang('current')); ?>" <?php if(condition="$info['target'] neq '_blank'"): ?>checked <?php endif; ?>>
                <input name="target" type="radio" value="_blank" title="<?php echo e(lang('blank')); ?>" <?php if(condition="$info['target'] eq '_blank'"): ?>checked <?php endif; ?>>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(lang('status_parse')); ?>：</label>
            <div class="layui-input-block">
                <input name="ps" type="radio" value="0" title="<?php echo e(lang('disable')); ?>" <?php if(condition="$info['ps'] neq 1"): ?>checked <?php endif; ?>>
                <input name="ps" type="radio" value="1" title="<?php echo e(lang('enable')); ?>" <?php if(condition="$info['ps'] eq 1"): ?>checked <?php endif; ?>>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(lang('admin/vodplayer/api_url_tip')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.parse); ?>" placeholder="<?php echo e(lang('admin/vodplayer/api_url_tip')); ?>" id="parse" name="parse">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(lang('sort')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.sort); ?>" placeholder="<?php echo e(lang('admin/vodplayer/sort_tip')); ?>" id="sort" name="sort">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(lang('tip')); ?>：</label>
            <div class="layui-input-block">
                <textarea name="tip" cols="" rows="" class="layui-textarea"  placeholder="" ><?php echo e($info.tip); ?></textarea>
            </div>
        </div>

        <div class="layui-form-item center">
            <div class="layui-input-block">
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
            from: function (value) {
                if (value == "") {
                    return "<?php echo e(lang('admin/vodplayer/code_empty')); ?>";
                }
            },
            show: function (value) {
                if (value == "") {
                    return "<?php echo e(lang('name_empty')); ?>";
                }
            }
        });


    });
</script>

</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\voddowner\info.blade.php ENDPATH**/ ?>