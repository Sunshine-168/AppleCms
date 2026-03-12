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
                <th width="150"><?php echo e(__('admin.file_des')); ?></th>
                <th width="150"><?php echo e(__('admin.file_size')); ?></th>
                <th width="150"><?php echo e(__('admin.file_time')); ?></th>
                <th width="260"><?php echo e(__('admin.admin/template/call_code')); ?></th>
                <th width="130"><?php echo e(__('admin.opt')); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($vo['name']); ?></td>
                    <td><?php echo e($vo['note']); ?></td>
                    <td><?php echo e($vo['size']); ?></td>
                    <td><?php echo e($vo['time'] ? date('Y-m-d H:i:s', $vo['time']) : ''); ?></td>
                    <td>
                        <input id="txt<?php echo e($index); ?>" type="text" class="layui-input" value='<script src="<?php echo e($pathAds); ?>/<?php echo e($vo['name']); ?>"></script>' readonly>
                    </td>
                    <td>
                        <a class="layui-badge-rim j-clipboard" data-clipboard-target="#txt<?php echo e($index); ?>" href="javascript:;" title="<?php echo e(__('admin.copy')); ?>"><?php echo e(__('admin.copy')); ?></a>
                        <a class="layui-badge-rim j-iframe" data-full="1" data-href="<?php echo e(route('admin.template.info', ['fpath' => $vo['path'], 'fname' => $vo['name']])); ?>" href="javascript:;" title="<?php echo e(__('admin.edit')); ?>"><?php echo e(__('admin.edit')); ?></a>
                        <a class="layui-badge-rim j-tr-del" data-href="<?php echo e(route('admin.template.del', ['fname' => $vo['fullname']])); ?>" href="javascript:;" title="<?php echo e(__('admin.del')); ?>"><?php echo e(__('admin.del')); ?></a>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="6">
                    <?php echo e(__('admin.admin/template/current_dir')); ?>：<?php echo e(str_replace('@', '/', $curpath)); ?>，
                    <?php echo e(__('admin.sum')); ?><b class="red"><?php echo e($num_file); ?></b><?php echo e(__('admin.file')); ?>，
                    <?php echo e(__('admin.occupies')); ?><b class="red"><?php echo e($sum_size); ?></b><?php echo e(__('admin.space')); ?>

                </td>
            </tr>
            </tfoot>
        </table>
    </form>
</div>
<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script type="text/javascript" src="<?php echo e(asset('static/js/jquery.clipboard.js')); ?>"></script>
<script type="text/javascript">
    var clipboard = new ClipboardJS('.j-clipboard');
    clipboard.on('success', function () {
        layer.msg('copy ok');
    });
</script><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\template\ads.blade.php ENDPATH**/ ?>