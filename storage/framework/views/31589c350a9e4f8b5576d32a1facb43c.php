<?php $__env->startSection('title', '订单记录'); ?>

<?php $__env->startSection('user_content'); ?>
<div class="card">
    <div class="card-header">订单记录</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>订单号</th>
                        <th>金额</th>
                        <th>积分</th>
                        <th>状态</th>
                        <th>时间</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($order->order_code); ?></td>
                            <td><?php echo e($order->order_price); ?></td>
                            <td><?php echo e($order->order_points); ?></td>
                            <td><?php echo e($order->order_status == 1 ? '已支付' : '待支付'); ?></td>
                            <td><?php echo e($order->order_time ? date('Y-m-d H:i:s', $order->order_time) : '-'); ?></td>
                            <td><a href="<?php echo e(route('user.pay', ['order_code' => $order->order_code])); ?>">查看</a></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6" class="text-center">暂无订单</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($orders->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\user\orders.blade.php ENDPATH**/ ?>