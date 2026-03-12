<?php $__env->startSection('title', '权限查看'); ?>

<?php $__env->startSection('user_content'); ?>
<div class="card">
    <div class="card-header">权限查看</div>
    <div class="card-body">
        <?php $__empty_1 = true; $__currentLoopData = $tree; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $node): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="mb-4">
                <h5><?php echo e($node['type']->type_name); ?></h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>分类</th>
                                <?php $__currentLoopData = $node['popedom']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $allowed): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <th><?php echo e($label); ?></th>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo e($node['type']->type_name); ?></td>
                                <?php $__currentLoopData = $node['popedom']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $allowed): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <td><?php echo e($allowed ? '是' : '否'); ?></td>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                            <?php $__currentLoopData = $node['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($child['type']->type_name); ?></td>
                                    <?php $__currentLoopData = $child['popedom']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $allowed): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <td><?php echo e($allowed ? '是' : '否'); ?></td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p>暂无权限数据。</p>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\user\popedom.blade.php ENDPATH**/ ?>