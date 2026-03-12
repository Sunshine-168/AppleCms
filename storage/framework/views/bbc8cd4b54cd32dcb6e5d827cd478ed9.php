<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">
    <form class="layui-form layui-form-pane" method="post" action="">
        <input id="comment_id" name="comment_id" type="hidden" value="<?php echo e($info.comment_id); ?>">
        <input id="comment_mid" name="comment_mid" type="hidden" value="<?php echo e($info.comment_mid); ?>">
        <input id="comment_rid" name="comment_rid" type="hidden" value="<?php echo e($info.comment_rid); ?>">
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.model')); ?>：</label>
            <div class="layui-input-inline w80">
                <input type="text" class="layui-input" value="<?php echo e($info.comment_mid|mac_get_mid_text); ?>" readonly="readonly">
            </div>
            <label class="layui-form-label"><?php echo e(__('admin.nickname')); ?>：</label>
            <div class="layui-input-inline w80">
                <input type="text" class="layui-input" value="<?php echo e($info.comment_name); ?>" readonly="readonly" name="comment_name" >
            </div>
            <label class="layui-form-label"><?php echo e(__('admin.time')); ?>：</label>
            <div class="layui-input-inline w130">
                <input type="text" class="layui-input" value="<?php echo e($info.comment_time|date='Y-m-d H:i:s',###); ?>" readonly="readonly">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.up')); ?>：</label>
            <div class="layui-input-inline w80">
                <input type="text" class="layui-input" value="<?php echo e($info.comment_up); ?>" name="comment_up">
            </div>
            <label class="layui-form-label"><?php echo e(__('admin.hate')); ?>：</label>
            <div class="layui-input-inline w80">
                <input type="text" class="layui-input" value="<?php echo e($info.comment_down); ?>" name="comment_down">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.content')); ?>：</label>
            <div class="layui-input-block">
                <textarea type="text" class="layui-textarea" lay-verify="comment_content" placeholder="" name="comment_content"><?php echo e($info.comment_content); ?></textarea>
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
            comment_content: function (value) {
                if (value == "") {
                    return "<?php echo e(__('admin.content_empty')); ?>";
                }
            }
        });


    });
</script>

</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\comment\info.blade.php ENDPATH**/ ?>