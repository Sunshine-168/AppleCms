<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="page-container p10">
    <div class="layui-card">
        <div class="layui-card-header"><?php echo e($title); ?></div>
        <div class="layui-card-body">
            <blockquote class="layui-elem-quote">
                共处理 <?php echo e(count($results)); ?> 个文件，成功 <?php echo e($successCount); ?> 个，失败 <?php echo e($failedCount); ?> 个。
            </blockquote>

            <table class="layui-table" lay-size="sm">
                <thead>
                <tr>
                    <th width="90">状态</th>
                    <th>来源</th>
                    <th>目标文件</th>
                    <th>结果</th>
                </tr>
                </thead>
                <tbody>
                <?php $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                            <?php if($result['ok']): ?>
                                <span style="color:#16b777;">成功</span>
                            <?php else: ?>
                                <span style="color:#ff5722;">失败</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($result['source'] ?: '-'); ?></td>
                        <td><?php echo e($result['target'] ?: '-'); ?></td>
                        <td><?php echo e($result['message']); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>

            <a href="<?php echo e($backUrl); ?>" class="layui-btn">返回生成管理</a>
        </div>
    </div>
</div>

<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\make\result.blade.php ENDPATH**/ ?>