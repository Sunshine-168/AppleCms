<?php $__env->startSection('title', '订单支付'); ?>

<?php $__env->startSection('user_content'); ?>
<div class="card">
    <div class="card-header">订单支付</div>
    <div class="card-body">
        <p>订单号：<?php echo e($order->order_code); ?></p>
        <p>订单金额：<?php echo e($order->order_price); ?></p>
        <p>兑换积分：<?php echo e($order->order_points); ?></p>
        <p>状态：<?php echo e($order->order_status == 1 ? '已支付' : '待支付'); ?></p>
        <?php if($order->order_status != 1): ?>
            <?php if(!empty($paymentMethods)): ?>
                <form method="post" action="<?php echo e(route('user.gopay')); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="order_id" value="<?php echo e($order->order_id); ?>">
                    <div class="mb-3">
                        <label class="form-label">支付方式</label>
                        <select class="form-select" name="payment">
                            <?php $__currentLoopData = $paymentMethods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $paymentKey => $paymentLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($paymentKey); ?>"><?php echo e($paymentLabel); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="form-text mb-3">仅显示当前已在配置中启用的支付接口。</div>
                    <button type="submit" class="btn btn-primary">前往支付</button>
                </form>
            <?php else: ?>
                <div class="alert alert-warning mb-0">当前没有已启用的支付接口，请先在后台配置支付参数。</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\user\pay.blade.php ENDPATH**/ ?>