<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">

    <div class="my-toolbar-box">
        <div class="layui-btn-group">
            <a data-full="1" data-href="<?php echo e(url('info')); ?>" class="layui-btn layui-btn-primary j-iframe"><i class="layui-icon">&#xe654;</i><?php echo e(lang('add')); ?></a>
            <a data-href="<?php echo e(url('batch')); ?>" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe642;</i><?php echo e(lang('edit')); ?></a>
            <a data-href="<?php echo e(url('del')); ?>" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i><?php echo e(lang('del')); ?></a>
            <a data-href="<?php echo e(url('index/select')); ?>?tab=type&col=type_status&tpl=select_status&url=type/field" data-width="470" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i><?php echo e(lang('status')); ?></a>
            <a data-href="<?php echo e(url('index/select')); ?>?tab=type&col=type_status&tpl=select_type&url=type/move" data-width="470" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i><?php echo e(lang('transfer')); ?></a>
        </div>

    </div>

    <form class="layui-form " method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
        <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th><?php echo e(lang('name')); ?></th>
                <th width="50"><?php echo e(lang('status')); ?></th>
                <th width="40"><?php echo e(lang('genre')); ?></th>
                <th width="40"><?php echo e(lang('sort')); ?></th>
                <th width="80"><?php echo e(lang('name')); ?></th>
                <th width="120"><?php echo e(lang('en')); ?></th>
                <th width="100"><?php echo e(lang('admin/type/type_tpl')); ?></th>
                <th width="100"><?php echo e(lang('admin/type/show_tpl')); ?></th>
                <th width="100"><?php echo e(lang('admin/type/detail_tpl')); ?></th>
                <th width="130"><?php echo e(lang('opt')); ?></th>
            </tr>
            </thead>

            <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><input type="checkbox" name="ids[]" value="<?php echo e($vo.type_id); ?>" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                <td><?php echo e($vo.type_id); ?>、<a target="_blank" class="layui-badge-rim " href="<?php echo e(mac_url_type($vo)); ?>"><?php echo e($vo.type_name); ?></a> <span class="layui-badge"><?php echo e($vo.cc); ?></span></td>
                <td>
                    <input type="checkbox" name="status" <?php if(condition="$vo['type_status'] eq 1"): ?>checked@endif value="<?php echo e($vo['type_status']); ?>" lay-skin="switch" lay-filter="switchStatus" lay-text="<?php echo e(lang('open')); ?>|<?php echo e(lang('close')); ?>" data-href="<?php echo e(url('field?col=type_status&ids='.$vo['type_id'])); ?>">
                </td>
                <td>
                    <span class="label label-success radius	"><?php echo e($vo.type_mid|mac_get_mid_text); ?></span>
                </td>
                <td><input type="input" name="type_sort_<?php echo e($vo.type_id); ?>" value="<?php echo e($vo.type_sort); ?>" class="layui-input"></td>
                <td><input type="input" name="type_name_<?php echo e($vo.type_id); ?>" value="<?php echo e($vo.type_name); ?>" class="layui-input"></td>
                <td><input type="input" name="type_en_<?php echo e($vo.type_id); ?>" value="<?php echo e($vo.type_en); ?>" class="layui-input"></td>
                <td><input type="input" name="type_tpl_<?php echo e($vo.type_id); ?>" value="<?php echo e($vo.type_tpl); ?>" class="layui-input"></td>
                <td><input type="input" name="type_tpl_list_<?php echo e($vo.type_id); ?>" value="<?php echo e($vo.type_tpl_list); ?>" class="layui-input"></td>
                <td><input type="input" name="type_tpl_detail_<?php echo e($vo.type_id); ?>" value="<?php echo e($vo.type_tpl_detail); ?>" class="layui-input"></td>
                <td>
                    <a class="layui-badge-rim j-iframe" data-full="1" data-href="<?php echo e(url('info?id='.$vo['type_id'])); ?>" href="javascript:;" title="<?php echo e(lang('edit')); ?>"><?php echo e(lang('edit')); ?></a>
                    <a class="layui-badge-rim j-tr-del" data-href="<?php echo e(url('del?ids='.$vo['type_id'])); ?>" href="javascript:;" title="<?php echo e(lang('del')); ?>"><?php echo e(lang('del')); ?></a>
                    <a class="layui-badge-rim j-iframe" data-full="1" data-href="<?php echo e(url('info')); ?>?pid=<?php echo e($vo.type_id); ?>" href="javascript:;" title="<?php echo e(lang('add')); ?>"><?php echo e(lang('add')); ?></a>
                </td>
            </tr>
            <?php $__currentLoopData = $vo.child; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><input type="checkbox" name="ids[]" value="<?php echo e($ch.type_id); ?>" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;├&nbsp;<?php echo e($ch.type_id); ?>、<a target="_blank" class="layui-badge-rim " href="<?php echo e(mac_url_type($ch)); ?>"><?php echo e($ch.type_name); ?></a> <span class="layui-badge"><?php echo e($ch.cc); ?></span></td>
                <td>
                    <input type="checkbox" name="status" <?php if(condition="$ch['type_status'] eq 1"): ?>checked@endif value="<?php echo e($ch['type_status']); ?>" lay-skin="switch" lay-filter="switchStatus" lay-text="<?php echo e(lang('open')); ?>|<?php echo e(lang('close')); ?>" data-href="<?php echo e(url('field?col=type_status&ids='.$ch['type_id'])); ?>">
                </td>
                <td>
                    <span class="label label-success radius	"><?php echo e($ch.type_mid|mac_get_mid_text); ?></span>
                </td>
                <td><input type="input" name="type_sort_<?php echo e($ch.type_id); ?>" value="<?php echo e($ch.type_sort); ?>" class="layui-input"></td>
                <td><input type="input" name="type_name_<?php echo e($ch.type_id); ?>" value="<?php echo e($ch.type_name); ?>" class="layui-input"></td>
                <td><input type="input" name="type_en_<?php echo e($ch.type_id); ?>" value="<?php echo e($ch.type_en); ?>" class="layui-input"></td>
                <td><input type="input" name="type_tpl_<?php echo e($ch.type_id); ?>" value="<?php echo e($ch.type_tpl); ?>" class="layui-input"></td>
                <td><input type="input" name="type_tpl_list_<?php echo e($ch.type_id); ?>" value="<?php echo e($ch.type_tpl_list); ?>" class="layui-input"></td>
                <td><input type="input" name="type_tpl_detail_<?php echo e($ch.type_id); ?>" value="<?php echo e($ch.type_tpl_detail); ?>" class="layui-input"></td>
                <td>
                    <a class="layui-badge-rim j-iframe" data-full="1" data-href="<?php echo e(url('info?id='.$ch['type_id'])); ?>" href="javascript:;" title="<?php echo e(lang('edit')); ?>"><?php echo e(lang('edit')); ?></a>
                    <a class="layui-badge-rim j-tr-del" data-href="<?php echo e(url('del?ids='.$ch['type_id'])); ?>" href="javascript:;" title="<?php echo e(lang('del')); ?>"><?php echo e(lang('del')); ?></a>
                </td>
            </tr>


            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

    </form>
</div>

<?php echo $__env->make('../../../application/admin/view/public/foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script type="text/javascript">

    layui.use(['laypage', 'layer'], function() {
        var laypage = layui.laypage
                , layer = layui.layer;


    });
</script>
</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\type\index.blade.php ENDPATH**/ ?>