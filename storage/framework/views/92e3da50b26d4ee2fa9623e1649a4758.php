<?php $__env->startSection('title', '支付跳转'); ?>

<?php $__env->startSection('user_content'); ?>
<div class="card">
    <div class="card-header">支付跳转</div>
    <div class="card-body">
        <p>订单号：<?php echo e($order->order_code); ?></p>
        <p>支付方式：<?php echo e($payment ?: '未选择'); ?></p>
        <p>当前 Laravel 版本已保留订单入口，但第三方支付页面尚未完全迁移。</p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\user\gopay.blade.php ENDPATH**/ ?>