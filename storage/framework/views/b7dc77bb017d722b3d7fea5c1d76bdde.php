<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container">
    <form class="layui-form layui-form-pane" action="">
        <div class="layui-tab">
            <ul class="layui-tab-title">
                <li class="<?php echo e(empty($param['status']) ? 'layui-this' : ''); ?>"><a href="<?php echo e(route('admin.cj.publish', ['id' => $param['id']])); ?>"><?php echo e(__('admin.all')); ?></a></li>
                <li class="<?php echo e(($param['status'] ?? '') === '1' ? 'layui-this' : ''); ?>"><a href="<?php echo e(route('admin.cj.publish', ['id' => $param['id']])); ?>?status=1"><?php echo e(__('admin.admin/cj/collected_not')); ?></a></li>
                <li class="<?php echo e(($param['status'] ?? '') === '2' ? 'layui-this' : ''); ?>"><a href="<?php echo e(route('admin.cj.publish', ['id' => $param['id']])); ?>?status=2"><?php echo e(__('admin.admin/cj/collected')); ?></a></li>
                <li class="<?php echo e(($param['status'] ?? '') === '3' ? 'layui-this' : ''); ?>"><a href="<?php echo e(route('admin.cj.publish', ['id' => $param['id']])); ?>?status=3"><?php echo e(__('admin.admin/cj/published')); ?></a></li>
            </ul>

            <div class="layui-tab-content">
                <div class="layui-tab-item layui-show">
                    <div class="layui-btn-group">
                        <select id="collect-opt" class="layui-input" style="display:inline-block;width:120px;height:38px;">
                            <option value="0" <?php echo e((string)($param['opt'] ?? '0') === '0' ? 'selected' : ''); ?>>新增+更新</option>
                            <option value="1" <?php echo e((string)($param['opt'] ?? '') === '1' ? 'selected' : ''); ?>>仅新增</option>
                            <option value="2" <?php echo e((string)($param['opt'] ?? '') === '2' ? 'selected' : ''); ?>>仅更新</option>
                        </select>
                        <select id="collect-filter" class="layui-input" style="display:inline-block;width:140px;height:38px;">
                            <option value="0" <?php echo e((string)($param['filter'] ?? '0') === '0' ? 'selected' : ''); ?>>不过滤资源组</option>
                            <option value="1" <?php echo e((string)($param['filter'] ?? '') === '1' ? 'selected' : ''); ?>>新增更新都过滤</option>
                            <option value="2" <?php echo e((string)($param['filter'] ?? '') === '2' ? 'selected' : ''); ?>>仅新增过滤</option>
                            <option value="3" <?php echo e((string)($param['filter'] ?? '') === '3' ? 'selected' : ''); ?>>仅更新过滤</option>
                        </select>
                        <input id="collect-filter-from" class="layui-input" style="display:inline-block;width:180px;height:38px;" value="<?php echo e($param['filter_from'] ?? ''); ?>" placeholder="资源组,逗号分隔">
                        <a data-href="<?php echo e(route('admin.cj.content_del')); ?>" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i><?php echo e(__('admin.del')); ?></a>
                        <a data-href="<?php echo e(route('admin.cj.content_del')); ?>?ids=1&all=1" class="layui-btn layui-btn-primary j-ajax" confirm="<?php echo e(__('admin.clear_confirm')); ?>"><i class="layui-icon">&#xe640;</i><?php echo e(__('admin.clear')); ?></a>
                        <a data-base-href="<?php echo e(route('admin.cj.content_into', ['id' => $param['id']])); ?>" data-ajax="no" class="layui-btn layui-btn-primary j-page-btns confirm j-import-btn"><i class="layui-icon">&#xe654;</i><?php echo e(__('admin.import')); ?></a>
                        <a data-base-href="<?php echo e(route('admin.cj.content_into', ['id' => $param['id']])); ?>?all=1" data-ajax="no" data-checkbox="no" class="layui-btn layui-btn-primary j-page-btns confirm j-import-btn"><i class="layui-icon">&#xe654;</i><?php echo e(__('admin.import_all')); ?></a>
                    </div>
                    <table class="layui-table" lay-size="sm">
                        <thead>
                        <tr>
                            <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                            <th width="50"><?php echo e(__('admin.id')); ?></th>
                            <th width="50"><?php echo e(__('admin.status')); ?></th>
                            <th width="250"><?php echo e(__('admin.name')); ?></th>
                            <th ><?php echo e(__('admin.url')); ?></th>
                            <th width="40"><?php echo e(__('admin.opt')); ?></th>
                        </tr>
                        </thead>
                        <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="<?php echo e($vo->id); ?>" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                            <td><?php echo e($vo->id); ?></td>
                            <td>
                                <?php if($vo->status == 1): ?>
                                    <?php echo e(__('admin.admin/cj/collected_not')); ?>

                                <?php elseif($vo->status == 2): ?>
                                    <?php echo e(__('admin.admin/cj/collected')); ?>

                                <?php else: ?>
                                    <?php echo e(__('admin.admin/cj/published')); ?>

                                <?php endif; ?>
                            </td>
                            <td><?php echo e($vo->title); ?></td>
                            <td><?php echo e($vo->url); ?></td>
                            <td>
                                <a class="layui-badge-rim j-iframe" data-href="<?php echo e(route('admin.cj.show', ['id' => $vo->id])); ?>" href="javascript:;" title="<?php echo e(__('admin.view')); ?>"><?php echo e(__('admin.view')); ?></a>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>

                    <div id="pages" class="center"></div>

                </div>

            </div>
        </div>

    </form>
</div>

<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script type="text/javascript">
    var curUrl = "<?php echo e(route('admin.cj.publish', ['id' => $param['id']])); ?>";
    var curStatus = "<?php echo e($param['status'] ?? ''); ?>";
    var curOpt = "<?php echo e($param['opt'] ?? '0'); ?>";
    var curFilter = "<?php echo e($param['filter'] ?? '0'); ?>";
    var curFilterFrom = "<?php echo e($param['filter_from'] ?? ''); ?>";
    layui.use(['laypage', 'layer'], function() {
        var laypage = layui.laypage
                , layer = layui.layer;

        laypage.render({
            elem: 'pages'
            ,count: <?php echo e($total); ?>

            ,limit: <?php echo e($limit); ?>

            ,curr: <?php echo e($page); ?>

            ,layout: ['count', 'prev', 'page', 'next', 'limit', 'skip']
            ,jump: function(obj,first){
                if(!first){
                    var query = '?page=' + obj.curr + '&limit=' + obj.limit;
                    if (curStatus !== '') {
                        query += '&status=' + encodeURIComponent(curStatus);
                    }
                    if (curOpt !== '' && curOpt !== '0') {
                        query += '&opt=' + encodeURIComponent(curOpt);
                    }
                    if (curFilter !== '' && curFilter !== '0') {
                        query += '&filter=' + encodeURIComponent(curFilter);
                    }
                    if (curFilterFrom !== '') {
                        query += '&filter_from=' + encodeURIComponent(curFilterFrom);
                    }
                    location.href = curUrl + query;
                }
            }
        });
    });

    document.querySelectorAll('.j-import-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            var baseHref = button.getAttribute('data-base-href') || '';
            var separator = baseHref.indexOf('?') === -1 ? '?' : '&';
            var query = 'opt=' + encodeURIComponent(document.getElementById('collect-opt').value)
                + '&filter=' + encodeURIComponent(document.getElementById('collect-filter').value)
                + '&filter_from=' + encodeURIComponent(document.getElementById('collect-filter-from').value);
            button.setAttribute('data-href', baseHref + separator + query);
        });
    });
</script><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\cj\publish.blade.php ENDPATH**/ ?>