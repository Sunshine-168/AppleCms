<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">
    <div class="my-toolbar-box" >
        <div class="center mb10">
            <form class="layui-form" method="get" action="<?php echo e(route('admin.order.index')); ?>">
                <div class="layui-input-inline w150">
                    <select name="status">
                        <option value=""><?php echo e(__('admin.select_order_status')); ?></option>
                        <option value="0" <?php if(($param['status'] ?? '') === '0'): echo 'selected'; endif; ?>><?php echo e(__('admin.not_paid')); ?></option>
                        <option value="1" <?php if(($param['status'] ?? '') === '1'): echo 'selected'; endif; ?>><?php echo e(__('admin.paid')); ?></option>
                    </select>
                </div>
                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" placeholder="<?php echo e(__('admin.wd')); ?>" class="layui-input" name="wd" value="<?php echo e($param['wd'] ?? ''); ?>">
                </div>
                <button class="layui-btn mgl-20 j-search" ><?php echo e(__('admin.btn_search')); ?></button>
            </form>
        </div>

        <div class="layui-btn-group">
            <a data-href="<?php echo e(route('admin.order.del')); ?>" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i><?php echo e(__('admin.del')); ?></a>
            <a data-href="<?php echo e(route('admin.order.del', ['ids' => 1, 'all' => 1])); ?>" class="layui-btn layui-btn-primary j-ajax" confirm="<?php echo e(__('admin.clear_confirm')); ?>"><i class="layui-icon">&#xe640;</i><?php echo e(__('admin.clear')); ?></a>
        </div>
    </div>

    <form class="layui-form" method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="50"><?php echo e(__('admin.id')); ?></th>
                <th width="100"><?php echo e(__('admin.admin/order/order_no')); ?></th>
                <th width="80"><?php echo e(__('admin.admin/order/order_money')); ?></th>
                <th width="80"><?php echo e(__('admin.admin/order/order_status')); ?></th>
                <th width="130"><?php echo e(__('admin.admin/order/order_time')); ?></th>
                <th width="100"><?php echo e(__('admin.admin/order/pay_type')); ?></th>
                <th width="130"><?php echo e(__('admin.admin/order/pay_time')); ?></th>
                <th width="80"><?php echo e(__('admin.user')); ?></th>
                <th width="50"><?php echo e(__('admin.opt')); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><input type="checkbox" name="ids[]" value="<?php echo e($vo.order_id); ?>" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                <td><?php echo e($vo.order_id); ?></td>
                <td><?php echo e($vo.order_code); ?></td>
                <td><?php echo e($vo.order_price); ?></td>
                <td><?php echo e(mac_get_order_status_text($vo.order_status)); ?></td>
                <td><?php echo e(mac_day($vo.order_time, 'color')); ?></td>
                <td><?php echo e($vo.order_pay_type ?: $vo.order_type); ?></td>
                <td><?php echo e(mac_day($vo.order_pay_time, 'color')); ?></td>
                <td><?php echo e($vo.user_id); ?>、<?php echo e($vo->user->user_name ?? ''); ?></td>
                <td>
                    <a class="layui-badge-rim j-tr-del" data-href="<?php echo e(route('admin.order.del', ['ids' => $vo['order_id']])); ?>" href="javascript:;" title="<?php echo e(__('admin.del')); ?>"><?php echo e(__('admin.del')); ?></a>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($list->isEmpty()): ?>
                <tr><td colspan="10" class="center"><?php echo e(__('admin.empty_data')); ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>

        <div id="pages" class="center"></div>
    </form>
</div>
<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script type="text/javascript">
    var curUrl = <?php echo json_encode(route('admin.order.index', $param), 512) ?>;
    layui.use(['laypage', 'layer'], function() {
        var laypage = layui.laypage, layer = layui.layer;

        laypage.render({
            elem: 'pages'
            ,count: <?php echo e($total); ?>

            ,limit: <?php echo e($limit); ?>

            ,curr: <?php echo e($page); ?>

            ,layout: ['count', 'prev', 'page', 'next', 'limit', 'skip']
            ,jump: function(obj,first){
                if(!first){
                    location.href = curUrl.replace('%7Bpage%7D',obj.curr).replace('%7Blimit%7D',obj.limit);
                }
            }
        });
    });
</script>
</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\order\index.blade.php ENDPATH**/ ?>