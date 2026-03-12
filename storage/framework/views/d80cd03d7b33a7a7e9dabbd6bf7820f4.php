<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">
    <div class="my-btn-box lh30">
        <div class="layui-btn-group fl">
            <a data-full="1" data-href="<?php echo e(route('admin.template.info', ['fpath' => $curpath])); ?>" class="layui-btn layui-btn-primary j-iframe">
                <i class="layui-icon">&#xe654;</i><?php echo e(__('admin.add')); ?>

            </a>
        </div>
    </div>

    <form class="layui-form layui-form-pane" action="">
        <table class="layui-table mt10">
            <thead>
            <tr>
                <th><?php echo e(__('admin.file_name')); ?></th>
                <th width="200"><?php echo e(__('admin.file_des')); ?></th>
                <th width="200"><?php echo e(__('admin.file_size')); ?></th>
                <th width="200"><?php echo e(__('admin.file_time')); ?></th>
                <th width="100"><?php echo e(__('admin.opt')); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if($ischild == 1): ?>
                <tr>
                    <td colspan="5">
                        <a href="<?php echo e(route('admin.template.index', ['path' => $uppath])); ?>">...<?php echo e(__('admin.return_parent_dir')); ?></a>
                    </td>
                </tr>
            <?php endif; ?>
            <?php $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <?php if($vo['isfile'] == 1): ?>
                        <td><?php echo e($vo['name']); ?></td>
                        <td><?php echo e($vo['note']); ?></td>
                        <td><?php echo e($vo['size']); ?></td>
                        <td><?php echo e($vo['time'] ? date('Y-m-d H:i:s', $vo['time']) : ''); ?></td>
                        <td>
                            <a class="layui-badge-rim j-iframe" data-full="1" data-href="<?php echo e(route('admin.template.info', ['fpath' => $vo['path'], 'fname' => $vo['name']])); ?>" href="javascript:;" title="<?php echo e(__('admin.edit')); ?>"><?php echo e(__('admin.edit')); ?></a>
                            <a class="layui-badge-rim j-tr-del" data-href="<?php echo e(route('admin.template.del', ['fname' => $vo['fullname']])); ?>" href="javascript:;" title="<?php echo e(__('admin.del')); ?>"><?php echo e(__('admin.del')); ?></a>
                        </td>
                    <?php else: ?>
                        <td><a href="<?php echo e(route('admin.template.index', ['path' => $vo['path']])); ?>"><?php echo e($vo['name']); ?></a></td>
                        <td><?php echo e($vo['note']); ?></td>
                        <td></td>
                        <td><?php echo e($vo['time'] ? date('Y-m-d H:i:s', $vo['time']) : ''); ?></td>
                        <td></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="5">
                    <?php echo e(__('admin.admin/template/current_dir')); ?>：<?php echo e(str_replace('@', '/', $curpath)); ?>，
                    <?php echo e(__('admin.sum')); ?><b class="red"><?php echo e($num_path); ?></b><?php echo e(__('admin.dir')); ?>，
                    <b class="red"><?php echo e($num_file); ?></b><?php echo e(__('admin.file')); ?>，
                    <?php echo e(__('admin.occupies')); ?><b class="red"><?php echo e($sum_size); ?></b><?php echo e(__('admin.space')); ?>

                </td>
            </tr>
            </tfoot>
        </table>
    </form>
</div>
<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\template\index.blade.php ENDPATH**/ ?>