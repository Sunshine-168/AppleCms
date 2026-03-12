<?php $__env->startSection('title', '我的留言'); ?>

<?php $__env->startSection('user_content'); ?>
<div class="card">
    <div class="card-header">我的留言</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>内容</th>
                        <th>状态</th>
                        <th>时间</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $gbooks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gbook): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($gbook->gbook_id); ?></td>
                            <td><?php echo e($gbook->gbook_content); ?></td>
                            <td><?php echo e($gbook->gbook_status == 1 ? '已通过' : '待审核'); ?></td>
                            <td><?php echo e($gbook->gbook_time ? date('Y-m-d H:i:s', $gbook->gbook_time) : '-'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="text-center">暂无留言</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($gbooks->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\user\gbook.blade.php ENDPATH**/ ?>