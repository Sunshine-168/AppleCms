<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">
    <div class="my-toolbar-box">

        <div class="layui-btn-group">
            <a data-href="<?php echo e(url('info')); ?>" class="layui-btn layui-btn-primary j-iframe"><i class="layui-icon">&#xe654;</i><?php echo e(lang('add')); ?></a>
        </div>

    </div>

    <form class="layui-form " method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="80"><?php echo e(lang('sort')); ?></th>
                <th width="80"><?php echo e(lang('code')); ?></th>
                <th width="80"><?php echo e(lang('status')); ?></th>
                <th width="150"><?php echo e(lang('name')); ?></th>
                <th><?php echo e(lang('remarks')); ?></th>
                <th><?php echo e(lang('tip')); ?></th>
                <th width="100"><?php echo e(lang('opt')); ?></th>
            </tr>
            </thead>

            <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($vo.sort); ?></td>
                <td><?php echo e($vo.from); ?></td>
                <td><?php if($vo.status eq 1): ?><span class="layui-badge layui-bg-green"><?php echo e(lang('enable')); ?></span>{else}<span class="layui-badge"><?php echo e(lang('disable')); ?></span><?php endif; ?> </td>
                <td><?php echo e($vo.show|mac_filter_xss); ?></td>
                <td><?php echo e($vo.des|mac_filter_xss); ?></td>
                <td><?php echo e($vo.tip|mac_filter_xss); ?></td>
                <td>
                    <a class="layui-badge-rim j-iframe" data-href="<?php echo e(url('info?id='.$vo['from'])); ?>" href="javascript:;" title="<?php echo e(lang('edit')); ?>"><?php echo e(lang('edit')); ?></a>
                    <a class="layui-badge-rim j-tr-del" data-href="<?php echo e(url('del?ids='.$vo['from'])); ?>" href="javascript:;" title="<?php echo e(lang('del')); ?>"><?php echo e(lang('del')); ?></a>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

    </form>
</div>
<?php echo $__env->make('../../../application/admin/view/public/foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script type="text/javascript">

</script>
</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\vodserver\index.blade.php ENDPATH**/ ?>