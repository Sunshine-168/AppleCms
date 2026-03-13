<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container" style="padding:16px;">
    <blockquote class="layui-elem-quote layui-quote-nm mt10">
        <p class="f-20 text-success"><?php echo e(__('admin/index/welcome/tip_warn')); ?></p>
    </blockquote>
    <table class="layui-table">
        <tbody>
        <tr>
            <td width="160"><?php echo e(__('admin/index/welcome/filed_os')); ?></td>
            <td><?php echo e(PHP_OS); ?> (<?php echo e($_SERVER['SERVER_SOFTWARE'] ?? ''); ?>)</td>
        </tr>
        <tr>
            <td><?php echo e(__('admin/index/welcome/filed_host')); ?></td>
            <td><?php echo e(request()->getHost()); ?></td>
        </tr>
        <tr>
            <td><?php echo e(__('admin/index/welcome/filed_max_upload')); ?></td>
            <td><?php echo e(ini_get('file_uploads') ? ini_get('upload_max_filesize') : '×'); ?></td>
        </tr>
        <tr>
            <td><?php echo e(__('admin/index/welcome/filed_date')); ?></td>
            <td><?php echo e(date('Y-m-d')); ?></td>
        </tr>
        <tr>
            <td><?php echo e(__('admin/index/welcome/filed_php_ver')); ?></td>
            <td><?php echo e(PHP_VERSION); ?></td>
        </tr>
        <tr>
            <td>Laravel</td>
            <td><?php echo e(app()->version()); ?></td>
        </tr>
        </tbody>
    </table>
</div>
<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views/admin/index/welcome.blade.php ENDPATH**/ ?>