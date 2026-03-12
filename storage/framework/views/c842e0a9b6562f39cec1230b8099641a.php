<?php $__env->startSection('title', '推广记录'); ?>

<?php $__env->startSection('user_content'); ?>
<div class="card">
    <div class="card-header">推广记录</div>
    <div class="card-body">
        <form method="get" class="row g-2 mb-3">
            <div class="col-auto">
                <select class="form-select" name="level">
                    <option value="1" <?php echo e($level === '1' ? 'selected' : ''); ?>>一级分销</option>
                    <option value="2" <?php echo e($level === '2' ? 'selected' : ''); ?>>二级分销</option>
                    <option value="3" <?php echo e($level === '3' ? 'selected' : ''); ?>>三级分销</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">筛选</button>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>用户ID</th>
                        <th>用户名</th>
                        <th>积分</th>
                        <th>注册时间</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($item->user_id); ?></td>
                            <td><?php echo e($item->user_name); ?></td>
                            <td><?php echo e($item->user_points); ?></td>
                            <td><?php echo e($item->user_reg_time ? date('Y-m-d H:i:s', $item->user_reg_time) : '-'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="text-center">暂无推广记录</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($users->appends(['level' => $level])->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\user\reward.blade.php ENDPATH**/ ?>