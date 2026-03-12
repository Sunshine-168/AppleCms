<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">

    <div class="my-toolbar-box" >
        <ul class="layui-tab-title mb10">
            <li class="layui-this"><a href="<?php echo e(url('index')); ?>"><?php echo e(__('admin.admin/database/backup_db')); ?></a></li>
            <li><a href="<?php echo e(url('index')); ?>?group=import"><?php echo e(__('admin.admin/database/import_db')); ?></a></li>
        </ul>

        <div class="layui-btn-group">
            <a data-href="<?php echo e(url('export')); ?>" class="layui-btn layui-btn-primary j-page-btns"><i class="layui-icon">&#xe62d;</i><?php echo e(__('admin.admin/database/backup_db')); ?></a>
            <a data-href="<?php echo e(url('optimize')); ?>" class="layui-btn layui-btn-primary j-page-btns"><i class="layui-icon">&#xe631;</i><?php echo e(__('admin.admin/database/optimize_db')); ?></a>
            <a data-href="<?php echo e(url('repair')); ?>" class="layui-btn layui-btn-primary j-page-btns"><i class="layui-icon">&#xe60c;</i><?php echo e(__('admin.admin/database/repair_db')); ?></a>
        </div>
    </div>

    <form id="pageListForm" class="layui-form">
        <table class="layui-table mt10" lay-even="" lay-skin="row">
            <colgroup>
                <col width="50">
            </colgroup>
            <thead>
            <tr>
                <th><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th><?php echo e(__('admin.admin/database/table')); ?></th>
                <th><?php echo e(__('admin.admin/database/count')); ?></th>
                <th><?php echo e(__('admin.admin/database/size')); ?></th>
                <th><?php echo e(__('admin.admin/database/redundancy')); ?></th>
                <th><?php echo e(__('admin.remarks')); ?></th>
                <th width="90"><?php echo e(__('admin.opt')); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><input type="checkbox" name="ids[]" class="layui-checkbox checkbox-ids" value="<?php echo e($vo['Name']); ?>" lay-skin="primary"></td>
                <td><?php echo e($vo['Name']); ?></td>
                <td><?php echo e($vo['Rows']); ?></td>
                <td><?php echo e($vo['Data_length']/1024|round=###,2); ?> kb</td>
                <td><?php echo e($vo['Data_free']/1024|round=###,2); ?> kb</td>
                <td><?php echo e($vo['Comment']); ?></td>
                <td>
                        <a data-href="<?php echo e(url('optimize?ids='.$vo['Name'])); ?>" class="layui-badge-rim j-ajax"><?php echo e(__('admin.admin/database/optimize')); ?></a>
                        <a data-href="<?php echo e(url('repair?ids='.$vo['Name'])); ?>" class="layui-badge-rim  j-ajax"><?php echo e(__('admin.admin/database/repair')); ?></a>
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
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\database\export.blade.php ENDPATH**/ ?>