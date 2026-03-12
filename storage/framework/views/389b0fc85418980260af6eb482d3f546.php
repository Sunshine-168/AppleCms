<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">
    <div class="my-toolbar-box">
        <div class="center mb10">
            <form class="layui-form" method="get" action="<?php echo e(route('admin.user.reward')); ?>">
                <div class="layui-input-inline w150">
                    <select name="level">
                        <option value=""><?php echo e(__('admin/user/reward/select_level')); ?></option>
                        <option value="1" <?php if(($param['level'] ?? '') === '1'): echo 'selected'; endif; ?>><?php echo e(__('admin/user/reward/one_distribution')); ?></option>
                        <option value="2" <?php if(($param['level'] ?? '') === '2'): echo 'selected'; endif; ?>><?php echo e(__('admin/user/reward/two_distribution')); ?></option>
                        <option value="3" <?php if(($param['level'] ?? '') === '3'): echo 'selected'; endif; ?>><?php echo e(__('admin/user/reward/three_distribution')); ?></option>
                    </select>
                </div>
                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" placeholder="<?php echo e(__('wd')); ?>" class="layui-input" name="wd" value="<?php echo e($param['wd'] ?? ''); ?>">
                </div>
                <input type="hidden" name="uid" value="<?php echo e($param['uid'] ?? 0); ?>">
                <button class="layui-btn mgl-20 j-search" ><?php echo e(__('btn_search')); ?></button>
            </form>
        </div>

        <div class="layui-btn-group">
            <a class="layui-btn"><?php echo e(__('admin/user/reward/one_people_num')); ?>【<?php echo e($data['level_cc_1']); ?>】<?php echo e(__('admin/user/reward/total_commission_points')); ?>【<?php echo e($data['points_cc_1']); ?>】</a>
            <a class="layui-btn layui-btn-normal"><?php echo e(__('admin/user/reward/two_people_num')); ?>【<?php echo e($data['level_cc_2']); ?>】<?php echo e(__('admin/user/reward/total_commission_points')); ?>【<?php echo e($data['points_cc_2']); ?>】</a>
            <a class="layui-btn layui-btn-warm"><?php echo e(__('admin/user/reward/three_people_num')); ?>【<?php echo e($data['level_cc_3']); ?>】<?php echo e(__('admin/user/reward/total_commission_points')); ?>【<?php echo e($data['points_cc_3']); ?>】</a>
        </div>
    </div>

    <form class="layui-form" method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="100"><?php echo e(__('id')); ?></th>
                <th><?php echo e(__('name')); ?></th>
                <th width="120"><?php echo e(__('group')); ?></th>
                <th width="120"><?php echo e(__('status')); ?></th>
                <th width="120"><?php echo e(__('admin/user/reward/distribution_level')); ?></th>
                <th width="130"><?php echo e(__('reg_time')); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><input type="checkbox" name="ids[]" value="<?php echo e($vo.user_id); ?>" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                    <td><?php echo e($vo.user_id); ?></td>
                    <td><?php echo e($vo.user_name); ?></td>
                    <td><?php echo e($vo->group->group_name ?? ''); ?></td>
                    <td><?php if((int) $vo.user_status === 1): ?><span class="layui-badge layui-bg-green"><?php echo e(__('open')); ?></span><?php else: ?><span class="layui-badge"><?php echo e(__('close')); ?></span><?php endif; ?></td>
                    <td>
                        <?php if((int) $vo.user_pid === (int) ($param['uid'] ?? 0)): ?>
                            <?php echo e(__('admin/user/reward/one_distribution')); ?>

                        <?php elseif((int) $vo.user_pid_2 === (int) ($param['uid'] ?? 0)): ?>
                            <?php echo e(__('admin/user/reward/two_distribution')); ?>

                        <?php else: ?>
                            <?php echo e(__('admin/user/reward/three_distribution')); ?>

                        <?php endif; ?>
                    </td>
                    <td><?php echo e(mac_day($vo.user_reg_time, 'color')); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" class="center"><?php echo e(__('empty_data')); ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        <div id="pages" class="center"></div>
    </form>
</div>
<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script type="text/javascript">
    var curUrl = <?php echo json_encode(route('admin.user.reward', $param), 512) ?>;
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
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\user\reward.blade.php ENDPATH**/ ?>