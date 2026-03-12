<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">
    <div class="my-toolbar-box">

        <div class="layui-btn-group">
            <a data-href="<?php echo e(url('info')); ?>" class="layui-btn layui-btn-primary j-iframe"><i class="layui-icon">&#xe654;</i><?php echo e(lang('add')); ?></a>
            <a href="<?php echo e(url('import')); ?>" class="layui-btn layui-btn-primary" ><i class="layui-icon">&#xe654;</i><?php echo e(lang('import')); ?></a>
            <a data-href="<?php echo e(url('index/select')); ?>?tab=vod&col=status&tpl=select_state&url=vodplayer/field" data-width="470" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i><?php echo e(lang('status')); ?></a>
            <a data-href="<?php echo e(url('index/select')); ?>?tab=vod&col=ps&tpl=select_state&url=vodplayer/field" data-width="470" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i><?php echo e(lang('status_parse')); ?></a>
        </div>

    </div>

    <form class="layui-form " method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="40"><?php echo e(lang('sort')); ?></th>
                <th width="40"><?php echo e(lang('code')); ?></th>
                <th width="130"><?php echo e(lang('name')); ?></th>
                <th width="50"><?php echo e(lang('status')); ?></th>
                <th width="50"><?php echo e(lang('status_parse')); ?></th>
                <th width="50"><?php echo e(lang('target')); ?></th>
                <th width="130"><?php echo e(lang('admin/vodplayer/alone_api_url')); ?></th>
                <th width="130"><?php echo e(lang('remarks')); ?></th>
                <th ><?php echo e(lang('tip')); ?></th>
                <th width="130"><?php echo e(lang('opt')); ?></th>
            </tr>
            </thead>

            <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><input type="checkbox" name="ids[]" value="<?php echo e($vo.from); ?>" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                <td><?php echo e($vo.sort); ?></td>
                <td><?php echo e($vo.from); ?></td>
                <td><?php echo e($vo.show); ?></td>
                <td><?php if($vo.status eq 1): ?><span class="layui-badge layui-bg-green"><?php echo e(lang('enable')); ?></span>{else}<span class="layui-badge"><?php echo e(lang('disable')); ?></span><?php endif; ?> </td>
                <td><?php if($vo.ps eq 1): ?><span class="layui-badge layui-bg-green"><?php echo e(lang('enable')); ?></span>{else}<span class="layui-badge"><?php echo e(lang('disable')); ?></span><?php endif; ?> </td>
                <td><?php if(condition="$vo.target neq '_blank'"): ?><span class="layui-badge layui-bg-green"><?php echo e(lang('current')); ?></span>{else}<span class="layui-badge"><?php echo e(lang('blank')); ?></span><?php endif; ?> </td>
                <td><?php echo e($vo.parse); ?></td>
                <td><?php echo e($vo.des); ?></td>
                <td><?php echo e($vo.tip); ?></td>
                <td>
                    <a class="layui-badge-rim" href="<?php echo e(url('export?id='.$vo['from'])); ?>" title="<?php echo e(lang('export')); ?>"><?php echo e(lang('export')); ?></a>
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
    layui.use(['form','laypage', 'layer'], function() {
        // 操作对象
        var form = layui.form
                , layer = layui.layer
                , $ = layui.jquery;


    });
</script>
</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\vodplayer\index.blade.php ENDPATH**/ ?>