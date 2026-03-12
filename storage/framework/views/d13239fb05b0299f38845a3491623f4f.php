<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">
    <div class="layui-textarea" style="height:auto; min-height:420px; line-height:22px;"><?php echo implode('<br>', $logs); ?></div>
    <?php if(!empty($nextUrl)): ?>
        <div style="margin-top: 12px;">
            <a href="<?php echo e($nextUrl); ?>" class="layui-btn">下一页继续推送</a>
        </div>
        <script type="text/javascript">
            setTimeout(function () {
                location.href = <?php echo json_encode($nextUrl, 15, 512) ?>;
            }, 3000);
        </script>
    <?php endif; ?>
</div>
<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\urlsend\result.blade.php ENDPATH**/ ?>