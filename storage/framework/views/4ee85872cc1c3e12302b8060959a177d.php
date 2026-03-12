<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">

    <div class="my-toolbar-box" >
        <ul class="layui-tab-title mb10">
            <li ><a href="<?php echo e(url('index')); ?>"><?php echo e(__('admin.admin/database/backup_db')); ?></a></li>
            <li class="layui-this"><a href="<?php echo e(url('index')); ?>?group=import"><?php echo e(__('admin.admin/database/import_db')); ?></a></li>
        </ul>
    </div>

    <form id="pageListForm" class="layui-form">
        <table class="layui-table mt10" lay-even="" lay-skin="row">
            <thead>
            <tr>
                <th><?php echo e(__('admin.admin/database/backup_name')); ?></th>
                <th><?php echo e(__('admin.admin/database/backup_num')); ?></th>
                <th><?php echo e(__('admin.admin/database/backup_zip')); ?></th>
                <th><?php echo e(__('admin.admin/database/backup_size')); ?></th>
                <th><?php echo e(__('admin.admin/database/backup_time')); ?></th>
                <th width="80"><?php echo e(__('admin.opt')); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e(date('Ymd-His', $vo['time'])); ?></td>
                <td><?php echo e($vo['part']); ?></td>
                <td><?php echo e($vo['compress']); ?></td>
                <td><?php echo e(round($vo['size']/1024, 2)); ?> K</td>
                <td><?php echo e(date('Y-m-d H:i:s', $vo['time'])); ?></td>
                <td>
                    <div class="layui-btn-group">
                        <a data-href="<?php echo e(url('import?id='.strtotime($key))); ?>" class="layui-badge-rim layui-btn-small j-ajax" confirm="<?php echo e(__('admin.admin/database/import_confirm')); ?>"><?php echo e(__('admin.admin/database/import')); ?></a>
                        <a data-href="<?php echo e(url('del?id='.strtotime($key))); ?>" class="layui-badge-rim layui-btn-small j-tr-del"><?php echo e(__('admin.del')); ?></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </form>

</div>
<?php echo $__env->make('../../../application/admin/view/public/foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<script type="text/javascript">
    layui.use(['form', 'layer'], function () {
        // 操作对象
        var form = layui.form
                , layer = layui.layer
                , $ = layui.jquery;



    });
</script>
</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\database\import.blade.php ENDPATH**/ ?>