<?php $__env->startSection('title', '订单详情'); ?>

<?php $__env->startSection('user_content'); ?>
<div class="card">
    <div class="card-header">订单详情</div>
    <div class="card-body">
        <?php if($order): ?>
            <p>订单号：<?php echo e($order->order_code); ?></p>
            <p>订单金额：<?php echo e($order->order_price); ?></p>
            <p>兑换积分：<?php echo e($order->order_points); ?></p>
            <p>支付状态：<?php echo e($order->order_status == 1 ? '已支付' : '待支付'); ?></p>
            <p>支付方式：<?php echo e($order->order_pay_type ?: '-'); ?></p>
        <?php else: ?>
            <p>未找到订单。</p>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\user\order_info.blade.php ENDPATH**/ ?>