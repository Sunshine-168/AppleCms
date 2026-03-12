<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="page-container p10">
    <div class="layui-card">
        <div class="layui-card-header">图片同步结果</div>
        <div class="layui-card-body">
            <blockquote class="layui-elem-quote">
                当前模型：<?php echo e($tab); ?>，处理页码：<?php echo e($page); ?>，每页 <?php echo e($limit); ?> 条，成功 <?php echo e($successCount); ?> 张，失败 <?php echo e($failedCount); ?> 张。
            </blockquote>

            <table class="layui-table" lay-size="sm">
                <thead>
                <tr>
                    <th width="80">ID</th>
                    <th>名称</th>
                    <th width="90">处理数</th>
                    <th width="90">成功</th>
                    <th width="90">失败</th>
                    <th>说明</th>
                </tr>
                </thead>
                <tbody>
                <?php $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($result['id']); ?></td>
                        <td><?php echo e($result['name']); ?></td>
                        <td><?php echo e($result['processed']); ?></td>
                        <td><?php echo e($result['success']); ?></td>
                        <td><?php echo e($result['failed']); ?></td>
                        <td><?php echo e($result['message'] ?? '-'); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>

            <a href="<?php echo e(route('admin.images.opt', ['tab' => $tab])); ?>" class="layui-btn">返回图片同步</a>
        </div>
    </div>
</div>

<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\images\result.blade.php ENDPATH**/ ?>