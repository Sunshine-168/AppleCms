<?php $__env->startSection('title', '充值卡记录'); ?>

<?php $__env->startSection('user_content'); ?>
<div class="card">
    <div class="card-header">充值卡记录</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>卡号</th>
                        <th>积分</th>
                        <th>使用状态</th>
                        <th>使用时间</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($card->card_no); ?></td>
                            <td><?php echo e($card->card_points); ?></td>
                            <td><?php echo e($card->card_use_status == 1 ? '已使用' : '未使用'); ?></td>
                            <td><?php echo e($card->card_use_time ? date('Y-m-d H:i:s', $card->card_use_time) : '-'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="text-center">暂无记录</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($cards->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\user\cards.blade.php ENDPATH**/ ?>