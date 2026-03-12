<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="page-container">
    <form class="layui-form layui-form-pane" action="">
        <input type="hidden" name="__token__" value="<?php echo e($Request.token); ?>" />
        <div class="layui-tab">
            <ul class="layui-tab-title">
                <li class="layui-this"><?php echo e(__('admin.admin/database/sql')); ?></li>
            </ul>
            <div class="layui-tab-content">
                <div class="layui-tab-item layui-show">

                    <div class="layui-input-block" >
                    <blockquote class="layui-elem-quote layui-quote-nm">
                        <?php echo e(__('admin.admin/database/sql_tip')); ?>

                    </blockquote>
                    </div>

                <div class="layui-form-item">
                    <div class="layui-input-block" >
                        <textarea name="sql" class="layui-textarea" rows="10" placeholder="" ></textarea>
                    </div>
                </div>

            </div>
            </div>
        </div>
        <div class="layui-form-item center">
            <div class="layui-input-block">
                <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit"><?php echo e(__('admin.start_exec')); ?></button>
                <button class="layui-btn layui-btn-warm" type="reset"><?php echo e(__('admin.btn_reset')); ?></button>
            </div>
        </div>
    </form>
</div>

<?php echo $__env->make('../../../application/admin/view/public/foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script type="text/javascript">

</script>

</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\database\sql.blade.php ENDPATH**/ ?>