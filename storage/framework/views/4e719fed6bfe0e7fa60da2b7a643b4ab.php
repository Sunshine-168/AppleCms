<?php $__env->startSection('title', '我的评论'); ?>

<?php $__env->startSection('user_content'); ?>
<div class="card">
    <div class="card-header">我的评论</div>
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
                    <?php $__empty_1 = true; $__currentLoopData = $comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($comment->comment_id); ?></td>
                            <td><?php echo e($comment->comment_content); ?></td>
                            <td><?php echo e($comment->comment_status == 1 ? '已通过' : '待审核'); ?></td>
                            <td><?php echo e($comment->comment_time ? date('Y-m-d H:i:s', $comment->comment_time) : '-'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="text-center">暂无评论</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($comments->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\user\comment.blade.php ENDPATH**/ ?>