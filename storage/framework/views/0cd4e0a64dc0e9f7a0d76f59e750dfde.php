<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">

    <div class="my-toolbar-box" >
        <div class="center mb10">
            <form class="layui-form " method="post" action="<?php echo e(url('data')); ?>">
                <div class="layui-input-inline w100">
                    <select name="status">
                        <option value=""><?php echo e(__('admin.select_status')); ?></option>
                        <option value="0" <?php if(condition="$param['status'] == '0'"): ?>selected <?php endif; ?>><?php echo e(__('admin.reviewed_not')); ?></option>
                        <option value="1" <?php if(condition="$param['status'] == '1'"): ?>selected <?php endif; ?>><?php echo e(__('admin.reviewed')); ?></option>
                    </select>
                </div>
                <div class="layui-input-inline w100">
                    <select name="type">
                        <option value=""><?php echo e(__('admin.select_reply_status')); ?></option>
                        <option value="1" <?php if(condition="$param['reply'] == '1'"): ?>selected <?php endif; ?>><?php echo e(__('admin.reply_not')); ?></option>
                        <option value="2" <?php if(condition="$param['reply'] == '2'"): ?>selected <?php endif; ?>><?php echo e(__('admin.reply_yes')); ?></option>
                    </select>
                </div>
                <div class="layui-input-inline w100">
                    <select name="type">
                        <option value=""><?php echo e(__('admin.select_genre')); ?></option>
                        <option value="1" <?php if(condition="$param['type'] == '1'"): ?>selected <?php endif; ?>><?php echo e(__('admin.gbook')); ?></option>
                        <option value="2" <?php if(condition="$param['type'] == '2'"): ?>selected <?php endif; ?>><?php echo e(__('admin.report')); ?></option>
                    </select>
                </div>
                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" placeholder="<?php echo e(__('admin.wd')); ?>" class="layui-input" name="wd" value="<?php echo e($param['wd']); ?>">
                </div>
                <button class="layui-btn mgl-20 j-search" ><?php echo e(__('admin.btn_search')); ?></button>
            </form>
        </div>
        <div class="layui-btn-group">
            <a data-href="<?php echo e(url('del')); ?>" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i><?php echo e(__('admin.del')); ?></a>
            <a data-href="<?php echo e(url('index/select')); ?>?tab=gbook&col=gbook_status&tpl=select_status&url=gbook/field" data-width="470" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i><?php echo e(__('admin.status')); ?></a>
            <a data-href="<?php echo e(url('del')); ?>?all=1" class="layui-btn layui-btn-primary j-ajax" confirm="<?php echo e(__('admin.clear_confirm')); ?>"><i class="layui-icon">&#xe640;</i><?php echo e(__('admin.clear')); ?></a>
        </div>
    </div>


        <form class="layui-form" method="post" id="pageListForm" >
            <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="60"><?php echo e(__('admin.id')); ?></th>
                <th width="60"><?php echo e(__('admin.status')); ?></th>
                <th width="60"><?php echo e(__('admin.genre')); ?></th>
                <th ><?php echo e(__('admin.gbook')); ?></th>
                <th ><?php echo e(__('admin.report')); ?></th>
                <th width="100"><?php echo e(__('admin.opt')); ?></th>
            </tr>
            </thead>

            <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><input type="checkbox" name="ids[]" value="<?php echo e($vo.gbook_id); ?>" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                <td><?php echo e($vo.gbook_id); ?></td>
                <td><?php if($vo.gbook_status == 0): ?><span class="layui-badge"><?php echo e(__('admin.reviewed_not')); ?></span>{else}<span class="layui-badge layui-bg-green"><?php echo e(__('admin.reviewed')); ?></span><?php endif; ?></td>
                <td><?php if($vo.gbook_rid == 0): ?><?php echo e(__('admin.gbook')); ?><?php else: ?><?php echo e(__('admin.report')); ?><?php endif; ?></td>
                <td>
                    <div class="c-999 f-12">
                        <u style="cursor:pointer" class="text-primary"><?php echo e($vo.gbook_name|htmlspecialchars); ?>：</u>
                        <time>【<?php echo e($vo.gbook_time|mac_day='color'); ?>】</time>
                        <span class="ml-20">ip：【<?php echo e($vo.gbook_ip|long2ip); ?>】</span>
                    </div>
                    <div class="f-12 c-999">
                        <span class="ml-20"><?php echo e(__('admin.status')); ?>：</span>
                        <?php echo e(__('admin.gbook')); ?>：<?php echo e($vo.gbook_content|htmlspecialchars); ?>

                    </div>
                </td>
                <td>
                    <div class="c-999 f-12">
                        <?php echo e(__('admin.reply_time')); ?>：<?php echo e($vo.gbook_reply_time|mac_day='color'); ?>

                    </div>
                    <div class="f-12 c-999">
                        <?php echo e(__('admin.reply')); ?>：<?php echo e($vo.gbook_reply|htmlspecialchars); ?>

                    </div>
                    <div> </div>
                </td>
                <td>
                    <a class="layui-badge-rim j-iframe" data-href="<?php echo e(url('info?id='.$vo['gbook_id'])); ?>" href="javascript:;" title="<?php echo e(__('admin.reply')); ?>"><?php echo e(__('admin.reply')); ?></a>
                    <a class="layui-badge-rim j-tr-del" data-href="<?php echo e(url('del?ids='.$vo['gbook_id'])); ?>" href="javascript:;" title="<?php echo e(__('admin.del')); ?>"><?php echo e(__('admin.del')); ?></a>
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
    var curUrl="<?php echo e(url('gbook/data',$param)); ?>";
    layui.use(['laypage', 'layer','form'], function() {
        var laypage = layui.laypage
                , layer = layui.layer,
                form = layui.form;

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
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\gbook\index.blade.php ENDPATH**/ ?>