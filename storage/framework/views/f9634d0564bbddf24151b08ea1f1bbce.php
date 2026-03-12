<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">
    <div class="my-toolbar-box">
        <div class="center mb10">
            <form class="layui-form" method="get" action="<?php echo e(route('admin.user.index')); ?>">
                <div class="layui-input-inline w150">
                    <select name="status">
                        <option value=""><?php echo e(__('select_status')); ?></option>
                        <option value="0" <?php if(($param['status'] ?? '') === '0'): echo 'selected'; endif; ?>><?php echo e(__('reviewed_not')); ?></option>
                        <option value="1" <?php if(($param['status'] ?? '') === '1'): echo 'selected'; endif; ?>><?php echo e(__('reviewed')); ?></option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="group">
                        <option value=""><?php echo e(__('select_group')); ?></option>
                        <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($group->group_id); ?>" <?php if((string) ($param['group'] ?? '') === (string) $group->group_id): echo 'selected'; endif; ?>><?php echo e($group->group_name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" placeholder="<?php echo e(__('wd')); ?>" class="layui-input" name="wd" value="<?php echo e($param['wd'] ?? ''); ?>">
                </div>
                <button class="layui-btn mgl-20 j-search"><?php echo e(__('btn_search')); ?></button>
            </form>
        </div>

        <div class="layui-btn-group">
            <a data-href="<?php echo e(route('admin.user.info')); ?>" class="layui-btn layui-btn-primary j-iframe"><i class="layui-icon">&#xe654;</i><?php echo e(__('add')); ?></a>
            <a data-href="<?php echo e(route('admin.user.del')); ?>" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i><?php echo e(__('del')); ?></a>
        </div>
    </div>

    <form class="layui-form" method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="100"><?php echo e(__('id')); ?></th>
                <th><?php echo e(__('name')); ?></th>
                <th width="100"><?php echo e(__('group')); ?></th>
                <th width="80"><?php echo e(__('status')); ?></th>
                <th width="80"><?php echo e(__('points')); ?></th>
                <th width="130"><?php echo e(__('last_login_time')); ?></th>
                <th width="130"><?php echo e(__('last_login_ip')); ?></th>
                <th width="80"><?php echo e(__('login_num')); ?></th>
                <th width="260"><?php echo e(__('related_data')); ?></th>
                <th width="100"><?php echo e(__('opt')); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><input type="checkbox" name="ids[]" value="<?php echo e($user->user_id); ?>" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                    <td><?php echo e($user->user_id); ?></td>
                    <td><?php echo e($user->user_name); ?></td>
                    <td><?php echo e($user->group->group_name ?? ''); ?></td>
                    <td>
                        <?php if((int) $user->user_status === 1): ?>
                            <span class="layui-badge layui-bg-green"><?php echo e(__('open')); ?></span>
                        <?php else: ?>
                            <span class="layui-badge"><?php echo e(__('close')); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($user->user_points); ?></td>
                    <td><?php echo e(mac_day($user->user_login_time, 'color')); ?></td>
                    <td><?php echo e($user->user_login_ip ? long2ip($user->user_login_ip) : ''); ?></td>
                    <td><?php echo e($user->user_login_num); ?></td>
                    <td>
                        <a class="layui-badge-rim j-iframe" data-full="1" data-href="<?php echo e(route('admin.order.index', ['uid' => $user->user_id])); ?>" href="javascript:;" title="<?php echo e(__('admin/user/order_record')); ?>"><?php echo e(__('admin/user/order_record')); ?></a>
                        <a class="layui-badge-rim j-iframe" data-full="1" data-href="<?php echo e(route('admin.visit.index', ['uid' => $user->user_id])); ?>" href="javascript:;" title="<?php echo e(__('admin/user/visit_record')); ?>"><?php echo e(__('admin/user/visit_record')); ?></a>
                        <a class="layui-badge-rim j-iframe" data-full="1" data-href="<?php echo e(route('admin.plog.index', ['uid' => $user->user_id])); ?>" href="javascript:;" title="<?php echo e(__('admin/user/point_record')); ?>"><?php echo e(__('admin/user/point_record')); ?></a>
                        <a class="layui-badge-rim j-iframe" data-full="1" data-href="<?php echo e(route('admin.cash.index', ['uid' => $user->user_id])); ?>" href="javascript:;" title="<?php echo e(__('admin/user/withdrawals_record')); ?>"><?php echo e(__('admin/user/withdrawals_record')); ?></a>
                        <a class="layui-badge-rim j-iframe" data-full="1" data-href="<?php echo e(route('admin.user.reward', ['uid' => $user->user_id])); ?>" href="javascript:;" title="<?php echo e(__('admin/user/three_distribution')); ?>"><?php echo e(__('admin/user/three_distribution')); ?></a>
                    </td>
                    <td>
                        <a class="layui-badge-rim j-iframe" data-href="<?php echo e(route('admin.user.info', ['id' => $user->user_id])); ?>" href="javascript:;" title="<?php echo e(__('edit')); ?>"><?php echo e(__('edit')); ?></a>
                        <a class="layui-badge-rim j-tr-del" data-href="<?php echo e(route('admin.user.del', ['ids' => $user->user_id])); ?>" href="javascript:;" title="<?php echo e(__('del')); ?>"><?php echo e(__('del')); ?></a>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="11" class="center"><?php echo e(__('empty_data')); ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        <div id="pages" class="center"></div>
    </form>
</div>

<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script type="text/javascript">
    var curUrl = <?php echo json_encode(route('admin.user.index', $param), 512) ?>;
    layui.use(['laypage', 'layer'], function() {
        var laypage = layui.laypage, layer = layui.layer;

        laypage.render({
            elem: 'pages'
            ,count: <?php echo e($total); ?>

            ,limit: <?php echo e($limit); ?>

            ,curr: <?php echo e($page); ?>

            ,layout: ['count', 'prev', 'page', 'next', 'limit', 'skip']
            ,jump: function(obj, first){
                if(!first){
                    location.href = curUrl.replace('%7Bpage%7D', obj.curr).replace('%7Blimit%7D', obj.limit);
                }
            }
        });
    });
</script>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\user\index.blade.php ENDPATH**/ ?>