<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="page-container p10">

    <div class="my-toolbar-box">
        <div class="layui-btn-group">
            <?php if(condition="$collect_break_vod != ''"): ?>
            <a href="<?php echo e(url('load')); ?>?flag=vod" class="layui-btn layui-btn-danger ">【进入视频断点采集】</a>
            <?php endif; ?>
            <?php if(condition="$collect_break_art != ''"): ?>
            <a href="<?php echo e(url('load')); ?>?flag=art" class="layui-btn layui-btn-danger ">【进入文章断点采集】</a>
            <?php endif; ?>
            <?php if(condition="$collect_break_actor != ''"): ?>
            <a href="<?php echo e(url('load')); ?>?flag=actor" class="layui-btn layui-btn-danger ">【进入明星断点采集】</a>
            <?php endif; ?>
            <?php if(condition="$collect_break_role != ''"): ?>
            <a href="<?php echo e(url('load')); ?>?flag=role" class="layui-btn layui-btn-danger ">【进入角色断点采集】</a>
            <?php endif; ?>
            <?php if(condition="$collect_break_website != ''"): ?>
            <a href="<?php echo e(url('load')); ?>?flag=website" class="layui-btn layui-btn-danger ">【进入网址断点采集】</a>
            <?php endif; ?>
        </div>
    </div>
    <hr>

    <script src="" charset="utf-8"></script>

</div>

<?php echo $__env->make('../../../application/admin/view/public/foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script type="text/javascript">
    layui.use(['laypage', 'layer'], function() {
        var laypage = layui.laypage
                , layer = layui.layer;


    });
</script>
</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\collect\union.blade.php ENDPATH**/ ?>