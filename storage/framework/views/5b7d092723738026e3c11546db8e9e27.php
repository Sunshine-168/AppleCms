<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">
    <div class="my-toolbar-box" >
        <div class="center mb10">
            <form class="layui-form" method="get" action="<?php echo e(route('admin.visit.index')); ?>">
                <div class="layui-input-inline w150">
                    <select name="mid">
                        <option value=""><?php echo e(__('select_model')); ?></option>
                        <option value="6" <?php if(($param['mid'] ?? '') === '6'): echo 'selected'; endif; ?>><?php echo e(__('user')); ?></option>
                        <option value="11" <?php if(($param['mid'] ?? '') === '11'): echo 'selected'; endif; ?>><?php echo e(__('website')); ?></option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="time">
                        <option value=""><?php echo e(__('select_time')); ?></option>
                        <option value="0" <?php if(($param['time'] ?? '') === '0'): echo 'selected'; endif; ?>><?php echo e(__('that_day')); ?></option>
                        <option value="7" <?php if(($param['time'] ?? '') === '7'): echo 'selected'; endif; ?>><?php echo e(__('in_a_week')); ?></option>
                        <option value="30" <?php if(($param['time'] ?? '') === '30'): echo 'selected'; endif; ?>><?php echo e(__('in_a_month')); ?></option>
                    </select>
                </div>
                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" placeholder="<?php echo e(__('wd')); ?>" class="layui-input" name="wd" value="<?php echo e($param['wd'] ?? ''); ?>">
                </div>
                <button class="layui-btn mgl-20 j-search" ><?php echo e(__('btn_search')); ?></button>
            </form>
        </div>

        <div class="layui-btn-group">
            <a data-href="<?php echo e(route('admin.visit.del')); ?>" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i><?php echo e(__('del')); ?></a>
            <a data-href="<?php echo e(route('admin.visit.del', ['ids' => 1, 'all' => 1])); ?>" class="layui-btn layui-btn-primary j-ajax" confirm="<?php echo e(__('clear_confirm')); ?>"><i class="layui-icon">&#xe640;</i><?php echo e(__('clear')); ?></a>
        </div>
    </div>

    <form class="layui-form" method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="80"><?php echo e(__('id')); ?></th>
                <th width="100"><?php echo e(__('user')); ?></th>
                <th width="50"><?php echo e(__('model')); ?></th>
                <th><?php echo e(__('from')); ?></th>
                <th width="130"><?php echo e(__('time')); ?></th>
                <th width="50"><?php echo e(__('opt')); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><input type="checkbox" name="ids[]" value="<?php echo e($vo.visit_id); ?>" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                    <td><?php echo e($vo.visit_id); ?></td>
                    <td>[<?php echo e($vo.user_id); ?>]<?php echo e($vo->user->user_name ?? ''); ?></td>
                    <td><?php echo e(mac_get_mid_text($vo.visit_mid)); ?></td>
                    <td><?php echo e($vo.visit_ly); ?></td>
                    <td><?php echo e(mac_day($vo.visit_time, 'color')); ?></td>
                    <td>
                        <a class="layui-badge-rim j-tr-del" data-href="<?php echo e(route('admin.visit.del', ['ids' => $vo['visit_id']])); ?>" href="javascript:;" title="<?php echo e(__('del')); ?>"><?php echo e(__('del')); ?></a>
                    </td>
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
    var curUrl = <?php echo json_encode(route('admin.visit.index', $param), 512) ?>;
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
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\visit\index.blade.php ENDPATH**/ ?>