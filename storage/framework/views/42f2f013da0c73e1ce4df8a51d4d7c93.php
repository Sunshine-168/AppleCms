<?php $__env->startSection('title', '微信支付'); ?>

<?php $__env->startSection('user_content'); ?>
<div class="card">
    <div class="card-header"><?php echo e($paymentLabel ?? '微信支付'); ?></div>
    <div class="card-body">
        <p>订单号：<?php echo e($order->order_code); ?></p>
        <p>订单金额：<?php echo e($order->order_price); ?></p>
        <p>请使用微信扫描下方二维码完成支付。</p>
        <div class="text-center my-4">
            <img src="<?php echo e(route('user.qrcode', ['data' => $paymentData['code_url'] ?? ''])); ?>" alt="微信支付二维码" class="img-fluid" style="max-width: 260px;">
        </div>
        <p class="text-muted small mb-0">支付完成后可返回订单页刷新状态。</p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\user\payment_weixin.blade.php ENDPATH**/ ?>