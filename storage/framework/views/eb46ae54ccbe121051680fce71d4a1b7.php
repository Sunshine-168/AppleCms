<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">

    <div class="my-toolbar-box">
        <div class="center mb10">
            <form class="layui-form " method="post" action="<?php echo e(url('index')); ?>">
                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" placeholder="<?php echo e(__('admin.wd')); ?>" class="layui-input" name="wd" value="<?php echo e($param['wd']); ?>">
                </div>
                <button class="layui-btn mgl-20 j-search" ><?php echo e(__('admin.btn_search')); ?></button>
            </form>
        </div>

        <div class="layui-btn-group">
            <a data-href="<?php echo e(url('info')); ?>" class="layui-btn layui-btn-primary j-iframe"><i class="layui-icon">&#xe654;</i><?php echo e(__('admin.add')); ?></a>
            <a data-href="<?php echo e(url('batch')); ?>" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe642;</i><?php echo e(__('admin.edit')); ?></a>
            <a data-href="<?php echo e(url('del')); ?>" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i><?php echo e(__('admin.del')); ?></a>
        </div>

    </div>

    <form class="layui-form " method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
        <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="100"><?php echo e(__('admin.id')); ?></th>
                <th width="100"><?php echo e(__('admin.sort')); ?></th>
                <th width="150"><?php echo e(__('admin.genre')); ?></th>
                <th ><?php echo e(__('admin.name')); ?></th>
                <th ><?php echo e(__('admin.url')); ?></th>
                <th ><?php echo e(__('admin.logo')); ?></th>
                <th width="130"><?php echo e(__('admin.opt')); ?></th>
            </tr>
            </thead>

            <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><input type="checkbox" name="ids[]" value="<?php echo e($vo.link_id); ?>" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                <td><?php echo e($vo.link_id); ?></td>
                <td><input type="input" name="link_sort[]" value="<?php echo e($vo.link_sort); ?>" class="layui-input"></td>
                <td>
                    <select name="link_type[]">
                        <option value="0" <?php if(condition="$vo['link_type'] == 0"): ?>selected <?php endif; ?>><?php echo e(__('admin.admin/link/text_link')); ?></option>
                        <option value="1" <?php if(condition="$vo['link_type'] == 1"): ?>selected <?php endif; ?>><?php echo e(__('admin.admin/link/pic_link')); ?></option>
                    </select>
                </td>
                <td><input type="input" name="link_name[]" value="<?php echo e($vo.link_name); ?>" class="layui-input"></td>
                <td><input type="input" name="link_url[]" value="<?php echo e($vo.link_url); ?>" class="layui-input"></td>
                <td><input type="input" name="link_logo[]" value="<?php echo e($vo.link_logo); ?>" class="layui-input"></td>
                <td>
                    <a class="layui-badge-rim j-ajax" data-href="<?php echo e(url('index/check_back_link')); ?>?url=<?php echo e($vo['link_url']); ?>" refresh="no" href="javascript:;" title="<?php echo e(__('admin.detect')); ?>"><?php echo e(__('admin.detect')); ?></a>
                    <a class="layui-badge-rim j-iframe" data-href="<?php echo e(url('info?id='.$vo['link_id'])); ?>" href="javascript:;" title="<?php echo e(__('admin.edit')); ?>"><?php echo e(__('admin.edit')); ?></a>
                    <a class="layui-badge-rim j-tr-del" data-href="<?php echo e(url('del?ids='.$vo['link_id'])); ?>" href="javascript:;" title="<?php echo e(__('admin.del')); ?>"><?php echo e(__('admin.del')); ?></a>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <div id="pages" class="center"></div>

    </form>
</div>
<?php echo $__env->make('../../../application/admin/view/public/foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<script type="text/javascript">
    var curUrl="<?php echo e(url('link/index',$param)); ?>";
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
                    location.href = curUrl.replace('%7Bpage%7D',obj.curr).replace('%7Blimit%7D',obj.limit);
                }
            }
        });
    });

</script>
</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\link\index.blade.php ENDPATH**/ ?>