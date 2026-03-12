<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">
    <form class="layui-form layui-form-pane" method="post" action="">
        <input id="gbook_id" name="gbook_id" type="hidden" value="<?php echo e($info.gbook_id); ?>">
        <input id="gbook_rid" name="gbook_rid" type="hidden" value="<?php echo e($info.gbook_rid); ?>">
        <div class="layui-form-item">

            <label class="layui-form-label">昵称：</label>
            <div class="layui-input-inline w80">
                <input type="text" class="layui-input" value="<?php echo e($info.gbook_name); ?>" readonly="readonly" name="gbook_name" >
            </div>
            <label class="layui-form-label">留言时间：</label>
            <div class="layui-input-inline w130">
                <input type="text" class="layui-input" value="<?php echo e($info.gbook_time|date='Y-m-d H:i:s',###); ?>" readonly="readonly">
            </div>
            <label class="layui-form-label">回复时间：</label>
            <div class="layui-input-inline w130">
                <input type="text" class="layui-input" value="<?php echo e($info.gbook_reply_time|date='Y-m-d H:i:s',###); ?>" readonly="readonly">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">留言内容：</label>
            <div class="layui-input-block">
                <textarea type="text" class="layui-textarea" lay-verify="gbook_content" placeholder="请输入评论内容" name="gbook_content"><?php echo e($info.gbook_content); ?></textarea>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">回复内容：</label>
            <div class="layui-input-block">
                <textarea type="text" class="layui-textarea" lay-verify="gbook_reply" placeholder="请输入回复内容" name="gbook_reply"><?php echo e($info.gbook_reply); ?></textarea>
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
            gbook_content: function (value) {
                if (value == "") {
                    return "请输入留言内容";
                }
            }
        });


    });
</script>

</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\gbook\info.blade.php ENDPATH**/ ?>