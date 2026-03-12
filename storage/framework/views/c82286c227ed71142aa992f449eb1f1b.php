<?php $__env->startSection('title', $title); ?>

<?php $__env->startSection('user_content'); ?>
<div class="card">
    <div class="card-header"><?php echo e($title); ?></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>模块</th>
                        <th>关联ID</th>
                        <th>类型</th>
                        <th>时间</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($log->ulog_id); ?></td>
                            <td><?php echo e($log->ulog_mid); ?></td>
                            <td><?php echo e($log->ulog_rid); ?></td>
                            <td><?php echo e($log->ulog_type); ?></td>
                            <td><?php echo e($log->ulog_time ? date('Y-m-d H:i:s', $log->ulog_time) : '-'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="text-center">暂无记录</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($logs->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\user\ulog_records.blade.php ENDPATH**/ ?>