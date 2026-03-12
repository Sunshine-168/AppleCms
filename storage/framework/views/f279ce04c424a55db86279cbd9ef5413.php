<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="page-container p10">
    <form class="layui-form layui-form-pane" method="post" action="">
        <input id="link_id" name="link_id" type="hidden" value="<?php echo e($info.link_id); ?>">
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.name')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.link_name); ?>" lay-verify="link_name" placeholder="" id="link_name" name="link_name">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.url')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.link_url); ?>" lay-verify="link_url" placeholder="" id="link_url" name="link_url">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.logo')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.link_logo); ?>" placeholder="" id="link_logo" name="link_logo">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.sort')); ?>：</label>
            <div class="layui-input-inline w100">
                <input type="text" class="layui-input" value="<?php echo e($info.link_sort); ?>" placeholder="" id="link_sort" name="link_sort">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.genre')); ?>：</label>
            <div class="layui-input-inline">
                <select class="w100" name="link_type">
                    <option value="0" <?php if(condition="$vo['link_type'] == 0"): ?>selected <?php endif; ?>><?php echo e(__('admin.admin/link/text_link')); ?></option>
                    <option value="1" <?php if(condition="$vo['link_type'] == 1"): ?>selected <?php endif; ?>><?php echo e(__('admin.admin/link/pic_link')); ?></option>
                </select>
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

<?php echo $__env->make('../../../application/admin/view/public/foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script type="text/javascript">
    layui.use(['form', 'layer'], function () {
        // 操作对象
        var form = layui.form
                , layer = layui.layer
                , $ = layui.jquery;

        // 验证
        form.verify({
            link_name: function (value) {
                if (value == "") {
                    return "<?php echo e(__('admin.name_empty')); ?>";
                }
            },
            link_url: function (value) {
                if (value == "") {
                    return "<?php echo e(__('admin.url_empty')); ?>";
                }
            }
        });


    });
</script>

</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\link\info.blade.php ENDPATH**/ ?>