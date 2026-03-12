<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">

    <div class="my-toolbar-box" >
        <div class="layui-btn-group">
            <a data-href="<?php echo e(url('info')); ?>" data-full="1" class="layui-btn layui-btn-primary j-iframe" data-width="600px" data-height="400px"><i class="layui-icon">&#xe654;</i><?php echo e(__('admin.add')); ?></a>
            <a data-href="<?php echo e(url('import')); ?>" class="layui-btn layui-btn-primary layui-upload" ><i class="layui-icon">&#xe654;</i><?php echo e(__('admin.import')); ?></a>
        </div>
    </div>

    <form method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="50"><?php echo e(__('admin.id')); ?></th>
                <th ><?php echo e(__('admin.name')); ?></th>
                <th width="120"><?php echo e(__('admin.cj_time')); ?></th>
                <th width="250"><?php echo e(__('admin.opt_content')); ?></th>
                <th width="250"><?php echo e(__('admin.opt')); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><input type="checkbox" name="ids[]" value="<?php echo e($vo.nodeid); ?>" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                <td><?php echo e($vo.nodeid); ?></td>
                <td><?php echo e($vo.name); ?></td>
                <td><?php echo e($vo.lastdate|mac_day); ?></td>
                <td>
                    <a class="layui-btn layui-btn-primary layui-btn-xs j-iframe" data-href="<?php echo e(url('col_url')); ?>?id=<?php echo e($vo.nodeid); ?>"><?php echo e(__('admin.admin/cj/cj_url')); ?></a>
                    <a class="layui-btn layui-btn-primary layui-btn-xs j-iframe" data-href="<?php echo e(url('col_content')); ?>?id=<?php echo e($vo.nodeid); ?>"><?php echo e(__('admin.admin/cj/cj_content')); ?></a>
                    <a class="layui-btn layui-btn-primary layui-btn-xs j-iframe" data-href="<?php echo e(url('publish')); ?>?id=<?php echo e($vo.nodeid); ?>&status=2"><?php echo e(__('admin.admin/cj/content_publish')); ?></a>
                </td>
                <td>
                    <a class="layui-btn layui-btn-primary layui-btn-xs j-iframe" data-full="1" data-href="<?php echo e(url('info')); ?>?id=<?php echo e($vo.nodeid); ?>" title="<?php echo e(__('admin.edit')); ?>"><?php echo e(__('admin.edit')); ?></a>
                    <a class="layui-btn layui-btn-primary layui-btn-xs j-iframe" data-href="<?php echo e(url('program')); ?>?id=<?php echo e($vo.nodeid); ?>" title="<?php echo e(__('admin.admin/cj/publish_plan')); ?>"><?php echo e(__('admin.admin/cj/publish_plan')); ?></a>
                    <a class="layui-btn layui-btn-primary layui-btn-xs" href="<?php echo e(url('export')); ?>?id=<?php echo e($vo.nodeid); ?>" title="<?php echo e(__('admin.export')); ?>"><?php echo e(__('admin.export')); ?></a>
                    <a class="layui-btn layui-btn-primary layui-btn-xs j-tr-del" href="<?php echo e(url('del')); ?>?ids=<?php echo e($vo.nodeid); ?>" title="<?php echo e(__('admin.del')); ?>"><?php echo e(__('admin.del')); ?></a>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <div id="pages" class="center"></div>
    </form>

</div>
<?php echo $__env->make('../../../application/admin/view/public/foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<!-- 注意：如果你直接复制所有代码到本地，上述js路径需要改成你本地的 -->
<script>
    layui.use(['form','laypage', 'layer','upload'], function() {
        // 操作对象
        var form = layui.form
                , layer = layui.layer
                , $ = layui.jquery
                , upload = layui.upload;

        upload.render({
            elem: '.layui-upload'
            ,url: "<?php echo e(url('cj/import')); ?>"
            ,method: 'post'
            ,exts:'txt'
            ,before: function(input) {
                layer.msg("<?php echo e(__('admin.upload_ing')); ?>", {time:3000000});
            },done: function(res, index, upload) {
                var obj = this.item;
                if (res.code == 0) {
                    layer.msg(res.msg);
                    return false;
                }
                location.reload();
            }
        });

    });
</script>
</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\cj\index.blade.php ENDPATH**/ ?>