<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="page-container p10">
    <form class="layui-form layui-form-pane" method="post" action="">

        <div class="layui-form-item">
            <label class="layui-form-label">url：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.url); ?>" lay-verify="url" placeholder="" id="url" name="url">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">title：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e($info.title); ?>" lay-verify="title" placeholder="" id="title" name="title">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">other：</label>
            <div class="layui-input-block">
                <?php $__currentLoopData = $$info.data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php echo e($key); ?><input type="text" class="layui-input" value="<?php echo e($vo); ?>"><br>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div class="layui-form-item center">
            <div class="layui-input-block">

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
                    return "<?php echo e(__('admin.link_empty')); ?>";
                }
            }
        });


    });
</script>

</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\cj\show.blade.php ENDPATH**/ ?>