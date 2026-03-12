<?php $__env->startSection('title', '提现记录'); ?>

<?php $__env->startSection('user_content'); ?>
<div class="card mb-4">
    <div class="card-header">申请提现</div>
    <div class="card-body">
        <p>当前积分：<?php echo e($user->user_points); ?>，冻结积分：<?php echo e($user->user_points_froze); ?></p>
        <form method="post" action="<?php echo e(route('user.cash')); ?>">
            <?php echo csrf_field(); ?>
            <div class="mb-3">
                <label class="form-label">提现金额</label>
                <input type="number" step="0.01" class="form-control" name="cash_money">
            </div>
            <div class="mb-3">
                <label class="form-label">银行名称</label>
                <input type="text" class="form-control" name="cash_bank_name">
            </div>
            <div class="mb-3">
                <label class="form-label">银行卡号</label>
                <input type="text" class="form-control" name="cash_bank_no">
            </div>
            <div class="mb-3">
                <label class="form-label">收款人</label>
                <input type="text" class="form-control" name="cash_payee_name">
            </div>
            <button type="submit" class="btn btn-primary">提交申请</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">提现记录</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>金额</th>
                        <th>积分</th>
                        <th>银行</th>
                        <th>状态</th>
                        <th>时间</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($record->cash_money); ?></td>
                            <td><?php echo e($record->cash_points); ?></td>
                            <td><?php echo e($record->cash_bank_name); ?></td>
                            <td><?php echo e($record->cash_status == 1 ? '已审核' : '待审核'); ?></td>
                            <td><?php echo e($record->cash_time ? date('Y-m-d H:i:s', $record->cash_time) : '-'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="text-center">暂无记录</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($records->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\user\cash.blade.php ENDPATH**/ ?>