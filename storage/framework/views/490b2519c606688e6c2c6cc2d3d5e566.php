<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="page-container">
    <form class="layui-form layui-form-pane" method="get" action="">
        <input name="ck" value="1" type="hidden">
        <div class="layui-tab">
            <ul class="layui-tab-title">
                <li class="layui-this"><?php echo e(__('admin.admin/safety/data_inspect')); ?></li>
            </ul>
            <div class="layui-tab-content">
                <div class="layui-tab-item layui-show">

                    <div class="layui-input-block" >
                        <blockquote class="layui-elem-quote layui-quote-nm">
                            <?php echo e(__('admin.admin/safety/data_inspect_tip')); ?>

                        </blockquote>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-form-item center">
            <div class="layui-input-block">
                <button type="submit" class="layui-btn" ><?php echo e(__('admin.admin/safety/exec')); ?></button>
            </div>
        </div>
    </form>
</div>

<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script type="text/javascript">
    $(function(){
        $('.layui-btn').click(function(){
            layer.msg("<?php echo e(__('admin.wait_submit')); ?>");
        });
    });
</script>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\safety\data.blade.php ENDPATH**/ ?>