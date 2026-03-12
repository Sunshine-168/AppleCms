<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">
    <div class="my-toolbar-box">

        <div class="layui-btn-group">
            <a data-href="<?php echo e(url('info')); ?>" class="layui-btn layui-btn-primary j-iframe"><i class="layui-icon">&#xe654;</i><?php echo e(lang('add')); ?></a>
            <a data-href="<?php echo e(url('index/select')); ?>?tab=vod&col=status&tpl=select_state&url=timming/field" data-width="470" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i><?php echo e(lang('status')); ?></a>
        </div>

    </div>

    <form class="layui-form " method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>

                <th width="80"><?php echo e(lang('name')); ?></th>
                <th width="150"><?php echo e(lang('description')); ?></th>
                <th width="80"><?php echo e(lang('run')); ?></th>
                <th width="80"><?php echo e(lang('status')); ?></th>
                <th width="80"><?php echo e(lang('last_run_time')); ?></th>
                <th width="100"><?php echo e(lang('opt')); ?></th>
            </tr>
            </thead>
            <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><input type="checkbox" name="ids[]" value="<?php echo e($vo.name); ?>" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                <td><?php echo e($vo.name|htmlspecialchars); ?></td>
                <td><?php echo e($vo.des|htmlspecialchars); ?></td>
                <td><?php echo e($vo.file); ?></td>
                <td>
                    <input type="checkbox" name="status" <?php if(condition="$vo['status'] eq 1"): ?>checked@endif value="<?php echo e($vo['status']); ?>" lay-skin="switch" lay-filter="switchStatus" lay-text="<?php echo e(lang('open')); ?>|<?php echo e(lang('close')); ?>" data-href="<?php echo e(url('field?col=status&ids='.$vo['name'])); ?>">
                </td>
                <td><?php echo e($vo.runtime|mac_day); ?></td>
                <td>
                    <a class="layui-badge-rim" target="_blank" href="{php}echo $GLOBALS['config']['site']['install_dir'];{/php}api.php/timming/index.html?enforce=1&name=<?php echo e($vo['name']|rawurlencode); ?>" title="<?php echo e(lang('test')); ?>"><?php echo e(lang('test')); ?></a>
                    <a class="layui-badge-rim j-iframe" data-href="<?php echo e(url('info')); ?>?id=<?php echo e($vo['name']|rawurlencode); ?>" href="javascript:;" title="<?php echo e(lang('edit')); ?>"><?php echo e(lang('edit')); ?></a>
                    <a class="layui-badge-rim j-tr-del" data-href="<?php echo e(url('del')); ?>?ids=<?php echo e($vo['name']|rawurlencode); ?>" href="javascript:;" title="<?php echo e(lang('del')); ?>"><?php echo e(lang('del')); ?></a>
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
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\timming\index.blade.php ENDPATH**/ ?>