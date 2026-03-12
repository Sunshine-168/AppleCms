<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">

    <div class="layui-tab layui-tab-brief" lay-filter="tabs">
        <ul class="layui-tab-title">
            <li class="btn-local" ><a href="<?php echo e(url('index')); ?>"><?php echo e(__('admin.local_app')); ?></a></li>
            <li class="layui-this"><a href="<?php echo e(url('add')); ?>"><?php echo e(__('admin.local_setup')); ?></a></li>
        </ul>
        <div class="layui-tab-content">
            <blockquote class="layui-elem-quote layui-quote-nm">
                <?php echo e(__('admin.admin/addon/add_tip')); ?>

            </blockquote>
            <input type="hidden" id="token" name="__token__" value="<?php echo e($Request.token); ?>" />
            <button type="button" class="layui-btn layui-upload" id="upload1"><?php echo e(__('admin.upload')); ?></button>
        </div>
    </div>
</div>

<?php echo $__env->make('../../../application/admin/view/public/foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<script type="text/javascript">
    var url='';
    layui.use(['form','laypage', 'layer','upload','element'], function() {
        // 操作对象
        var form = layui.form
            , layer = layui.layer
            , upload = layui.upload
            ,element = layui.element;

        upload.render({
            elem: '.layui-upload'
            ,url: "<?php echo e(url('addon/local')); ?>?__token__=" + $('#token').val()
            ,method: 'post'
            ,exts:'zip'
            ,before: function(input) {
                layer.msg("<?php echo e(__('admin.upload_ing')); ?>", {time:3000000});
            },done: function(res, index, upload) {
                var obj = this.item;
                if (res.code == 0) {
                    layer.msg(res.msg);
                }

                setTimeout(function () {
                    layer.closeAll();
                    location.reload();
                },2000);
            }
        });

    });


</script>
</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\addon\add.blade.php ENDPATH**/ ?>