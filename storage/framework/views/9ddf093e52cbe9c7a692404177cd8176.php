<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">

    <div class="my-toolbar-box">

        <div class="layui-btn-group">
            <a data-href="<?php echo e(url('info')); ?>" class="layui-btn layui-btn-primary j-iframe"><i class="layui-icon">&#xe654;</i><?php echo e(__('admin.add')); ?></a>
            <a data-href="<?php echo e(url('del')); ?>" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i><?php echo e(__('admin.del')); ?></a>
        </div>

    </div>

    <form class="layui-form " method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="100"><?php echo e(__('admin.id')); ?></th>
                <th ><?php echo e(__('admin.name')); ?></th>
                <th width="100"><?php echo e(__('admin.status')); ?></th>
                <th width="100"><?php echo e(__('admin.admin/group/pack_day')); ?></th>
                <th width="100"><?php echo e(__('admin.admin/group/pack_week')); ?></th>
                <th width="100"><?php echo e(__('admin.admin/group/pack_month')); ?></th>
                <th width="100"><?php echo e(__('admin.admin/group/pack_year')); ?></th>
                <th width="100"><?php echo e(__('admin.opt')); ?></th>
            </tr>
            </thead>

            <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td>
                    <?php if(condition="$vo['group_id'] > 2"): ?>
                    <input type="checkbox" name="ids[]" value="<?php echo e($vo.group_id); ?>" class="layui-checkbox checkbox-ids" lay-skin="primary">
                    <?php endif; ?>
                </td>
                <td><?php echo e($vo.group_id); ?></td>
                <td><?php echo e($vo.group_name|htmlspecialchars); ?></td>
                <td>
                    <?php if(condition="$vo['group_id'] > 2"): ?>
                    <input type="checkbox" name="status" <?php if(condition="$vo['group_status'] == 1"): ?>checked<?php endif; ?> value="<?php echo e($vo['group_status']); ?>" lay-skin="switch" lay-filter="switchStatus" lay-text="<?php echo e(__('admin.open')); ?>|<?php echo e(__('admin.close')); ?>" data-href="<?php echo e(url('field?col=group_status&ids='.$vo['group_id'])); ?>">
                    @endif
                </td>
                <td><?php echo e($vo.group_points_day); ?></td>
                <td><?php echo e($vo.group_points_week); ?></td>
                <td><?php echo e($vo.group_points_month); ?></td>
                <td><?php echo e($vo.group_points_year); ?></td>
                <td>
                    <a class="layui-badge-rim j-iframe" data-href="<?php echo e(url('info?id='.$vo['group_id'])); ?>" href="javascript:;" title="<?php echo e(__('admin.edit')); ?>"><?php echo e(__('admin.edit')); ?></a>
                    <?php if(condition="$vo['group_id'] > 2"): ?>
                    <a class="layui-badge-rim j-tr-del" data-href="<?php echo e(url('del?ids='.$vo['group_id'])); ?>" href="javascript:;" title="<?php echo e(__('admin.del')); ?>"><?php echo e(__('admin.del')); ?></a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

    </form>

    <blockquote class="layui-elem-quote layui-quote-nm">
        <?php echo e(__('admin.admin/group/help_tip')); ?>

    </blockquote>
</div>

<?php echo $__env->make('../../../application/admin/view/public/foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script type="text/javascript">

    layui.use(['laypage', 'layer'], function() {
        var laypage = layui.laypage
                , layer = layui.layer;


    });
</script>
</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\group\index.blade.php ENDPATH**/ ?>