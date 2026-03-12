<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">
    <div class="my-toolbar-box" >
        <div class="center mb10">
            <form class="layui-form" method="get" action="<?php echo e(route('admin.plog.index')); ?>">
                <div class="layui-input-inline w150">
                    <select name="type">
                        <option value=""><?php echo e(__('admin.select_genre')); ?></option>
                        <option value="1" <?php if(($param['type'] ?? '') === '1'): echo 'selected'; endif; ?>><?php echo e(__('admin.admin/plog/points_recharge')); ?></option>
                        <option value="2" <?php if(($param['type'] ?? '') === '2'): echo 'selected'; endif; ?>><?php echo e(__('admin.admin/plog/reg_promote')); ?></option>
                        <option value="3" <?php if(($param['type'] ?? '') === '3'): echo 'selected'; endif; ?>><?php echo e(__('admin.admin/plog/visit_promote')); ?></option>
                        <option value="4" <?php if(($param['type'] ?? '') === '4'): echo 'selected'; endif; ?>><?php echo e(__('admin.one_level_distribution')); ?></option>
                        <option value="5" <?php if(($param['type'] ?? '') === '5'): echo 'selected'; endif; ?>><?php echo e(__('admin.two_level_distribution')); ?></option>
                        <option value="6" <?php if(($param['type'] ?? '') === '6'): echo 'selected'; endif; ?>><?php echo e(__('admin.three_level_distribution')); ?></option>
                        <option value="7" <?php if(($param['type'] ?? '') === '7'): echo 'selected'; endif; ?>><?php echo e(__('admin.admin/plog/points_upgrade')); ?></option>
                        <option value="8" <?php if(($param['type'] ?? '') === '8'): echo 'selected'; endif; ?>><?php echo e(__('admin.admin/plog/points_buy')); ?></option>
                        <option value="9" <?php if(($param['type'] ?? '') === '9'): echo 'selected'; endif; ?>><?php echo e(__('admin.admin/plog/points_withdrawal')); ?></option>
                    </select>
                </div>
                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" placeholder="<?php echo e(__('admin.wd')); ?>" class="layui-input" name="wd" value="<?php echo e($param['wd'] ?? ''); ?>">
                </div>
                <button class="layui-btn mgl-20 j-search" ><?php echo e(__('admin.btn_search')); ?></button>
            </form>
        </div>

        <div class="layui-btn-group">
            <a data-href="<?php echo e(route('admin.plog.del')); ?>" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i><?php echo e(__('admin.del')); ?></a>
            <a data-href="<?php echo e(route('admin.plog.del', ['ids' => 1, 'all' => 1])); ?>" class="layui-btn layui-btn-primary j-ajax" confirm="<?php echo e(__('admin.clear_confirm')); ?>"><i class="layui-icon">&#xe640;</i><?php echo e(__('admin.clear')); ?></a>
        </div>
    </div>

    <form class="layui-form" method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="80"><?php echo e(__('admin.id')); ?></th>
                <th width="100"><?php echo e(__('admin.user')); ?></th>
                <th width="50"><?php echo e(__('admin.genre')); ?></th>
                <th width="50"><?php echo e(__('admin.points')); ?></th>
                <th width="200"><?php echo e(__('admin.remarks')); ?></th>
                <th width="140"><?php echo e(__('admin.admin/plog/log_time')); ?></th>
                <th width="50"><?php echo e(__('admin.opt')); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><input type="checkbox" name="ids[]" value="<?php echo e($vo.plog_id); ?>" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                <td><?php echo e($vo.plog_id); ?></td>
                <td>[<?php echo e($vo.user_id); ?>]<?php echo e($vo->user->user_name ?? ''); ?></td>
                <td><?php echo e(mac_get_plog_type_text($vo.plog_type)); ?></td>
                <td><?php if(in_array((int) $vo.plog_type, [1, 2, 3, 4, 5, 6], true)): ?>+<?php else: ?>-<?php endif; ?><?php echo e($vo.plog_points); ?></td>
                <td><?php echo e($vo.plog_remarks); ?></td>
                <td><?php echo e(mac_day($vo.plog_time, 'color')); ?></td>
                <td>
                    <a class="layui-badge-rim j-tr-del" data-href="<?php echo e(route('admin.plog.del', ['ids' => $vo['plog_id']])); ?>" href="javascript:;" title="<?php echo e(__('admin.del')); ?>"><?php echo e(__('admin.del')); ?></a>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($list->isEmpty()): ?>
                <tr><td colspan="8" class="center"><?php echo e(__('admin.empty_data')); ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>

        <div id="pages" class="center"></div>
    </form>
</div>
<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script type="text/javascript">
    var curUrl = <?php echo json_encode(route('admin.plog.index', $param), 512) ?>;
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
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\plog\index.blade.php ENDPATH**/ ?>