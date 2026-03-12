<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">
    <div class="my-toolbar-box" >
        <div class="center mb10">
            <form class="layui-form" method="get" id="searchForm" action="<?php echo e(route('admin.card.index')); ?>">
                <div class="layui-input-inline w150">
                    <select name="sale_status">
                        <option value=""><?php echo e(__('admin.select_sale_status')); ?></option>
                        <option value="0" <?php if(($param['sale_status'] ?? '') === '0'): echo 'selected'; endif; ?>><?php echo e(__('admin.not_sale')); ?></option>
                        <option value="1" <?php if(($param['sale_status'] ?? '') === '1'): echo 'selected'; endif; ?>><?php echo e(__('admin.sold')); ?></option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="use_status">
                        <option value=""><?php echo e(__('admin.select_use_status')); ?></option>
                        <option value="0" <?php if(($param['use_status'] ?? '') === '0'): echo 'selected'; endif; ?>><?php echo e(__('admin.not_used')); ?></option>
                        <option value="1" <?php if(($param['use_status'] ?? '') === '1'): echo 'selected'; endif; ?>><?php echo e(__('admin.used')); ?></option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="time">
                        <option value=""><?php echo e(__('admin.select_time')); ?></option>
                        <option value="1" <?php if(($param['time'] ?? '') === '1'): echo 'selected'; endif; ?>><?php echo e(__('admin.the_last_time')); ?></option>
                        <option value="0" <?php if(($param['time'] ?? '') === '0'): echo 'selected'; endif; ?>><?php echo e(__('admin.that_day')); ?></option>
                        <option value="7" <?php if(($param['time'] ?? '') === '7'): echo 'selected'; endif; ?>><?php echo e(__('admin.in_a_week')); ?></option>
                        <option value="30" <?php if(($param['time'] ?? '') === '30'): echo 'selected'; endif; ?>><?php echo e(__('admin.in_a_month')); ?></option>
                    </select>
                </div>
                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" placeholder="<?php echo e(__('admin.wd')); ?>" class="layui-input" name="wd" value="<?php echo e($param['wd'] ?? ''); ?>">
                </div>
                <button class="layui-btn mgl-20 j-search" ><?php echo e(__('admin.btn_search')); ?></button>
                <button class="layui-btn mgl-20" type="button" id="btnExport"><?php echo e(__('admin.export')); ?></button>
            </form>
        </div>

        <div class="layui-btn-group">
            <a data-href="<?php echo e(route('admin.card.info')); ?>" class="layui-btn layui-btn-primary j-iframe" data-width="600px" data-height="400px"><i class="layui-icon">&#xe654;</i><?php echo e(__('admin.add')); ?></a>
            <a data-href="<?php echo e(route('admin.card.del')); ?>" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i><?php echo e(__('admin.del')); ?></a>
            <a data-href="<?php echo e(route('admin.card.del', ['ids' => 1, 'all' => 1])); ?>" class="layui-btn layui-btn-primary j-ajax" confirm="<?php echo e(__('admin.clear_confirm')); ?>"><i class="layui-icon">&#xe640;</i><?php echo e(__('admin.clear')); ?></a>
        </div>
    </div>

    <form class="layui-form" method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="80"><?php echo e(__('admin.id')); ?></th>
                <th width="150"><?php echo e(__('admin.card_no')); ?></th>
                <th width="100"><?php echo e(__('admin.pass')); ?></th>
                <th width="100"><?php echo e(__('admin.money')); ?></th>
                <th width="100"><?php echo e(__('admin.points')); ?></th>
                <th width="100"><?php echo e(__('admin.add_time')); ?></th>
                <th width="100"><?php echo e(__('admin.user')); ?></th>
                <th width="150"><?php echo e(__('admin.use_time')); ?></th>
                <th width="50"><?php echo e(__('admin.opt')); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><input type="checkbox" name="ids[]" value="<?php echo e($vo.card_id); ?>" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                    <td><?php echo e($vo.card_id); ?></td>
                    <td><?php echo e($vo.card_no); ?></td>
                    <td><?php echo e($vo.card_pwd); ?></td>
                    <td><?php echo e($vo.card_money); ?></td>
                    <td><?php echo e($vo.card_points); ?></td>
                    <td><?php echo e(mac_day($vo.card_add_time, 'color')); ?></td>
                    <td><?php echo e($vo.user_id); ?>、<?php echo e($vo->user->user_name ?? ''); ?></td>
                    <td><?php echo e($vo.card_use_time ? mac_day($vo.card_use_time, 'color') : ''); ?></td>
                    <td>
                        <a class="layui-badge-rim j-tr-del" data-href="<?php echo e(route('admin.card.del', ['ids' => $vo['card_id']])); ?>" href="javascript:;" title="<?php echo e(__('admin.del')); ?>"><?php echo e(__('admin.del')); ?></a>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="10" class="center"><?php echo e(__('admin.empty_data')); ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>

        <div id="pages" class="center"></div>
    </form>
    <iframe id="if" width="0" height="0"></iframe>
</div>
<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script type="text/javascript">
    var curUrl = <?php echo json_encode(route('admin.card.index', $param), 512) ?>;
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

        $('#btnExport').click(function(){
            var par = $('#searchForm').serialize() + '&export=1';

            $('#if').attr('src', <?php echo json_encode(route('admin.card.index'), 15, 512) ?> + '?' + par);
        });
    });
</script>
</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\card\index.blade.php ENDPATH**/ ?>