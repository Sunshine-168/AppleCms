<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">

    <div class="my-toolbar-box">
        <?php if($param.select != 1): ?>
        <div class="center mb10">
            <form class="layui-form " method="post" action="<?php echo e(url('data')); ?>">
                <input type="hidden" value="<?php echo e($param.select); ?>" name="select">
                <input type="hidden" value="<?php echo e($param.input); ?>" name="input">
                <div class="layui-input-inline w150">
                    <select name="status">
                        <option value=""><?php echo e(__('admin.select_status')); ?></option>
                        <option value="0" <?php if(condition="$param['status'] == '0'"): ?>selected <?php endif; ?>><?php echo e(__('admin.reviewed_not')); ?></option>
                        <option value="1" <?php if(condition="$param['status'] == '1'"): ?>selected <?php endif; ?>><?php echo e(__('admin.reviewed')); ?></option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="level">
                        <option value=""><?php echo e(__('admin.select_level')); ?></option>
                        <option value="9" <?php if(condition="$param['level'] == '9'"): ?>selected <?php endif; ?>><?php echo e(__('admin.level')); ?>9-<?php echo e(__('admin.slide')); ?></option>
                        <option value="1" <?php if(condition="$param['level'] == '1'"): ?>selected <?php endif; ?>><?php echo e(__('admin.level')); ?>1</option>
                        <option value="2" <?php if(condition="$param['level'] == '2'"): ?>selected <?php endif; ?>><?php echo e(__('admin.level')); ?>2</option>
                        <option value="3" <?php if(condition="$param['level'] == '3'"): ?>selected <?php endif; ?>><?php echo e(__('admin.level')); ?>3</option>
                        <option value="4" <?php if(condition="$param['level'] == '4'"): ?>selected <?php endif; ?>><?php echo e(__('admin.level')); ?>4</option>
                        <option value="5" <?php if(condition="$param['level'] == '5'"): ?>selected <?php endif; ?>><?php echo e(__('admin.level')); ?>5</option>
                        <option value="6" <?php if(condition="$param['level'] == '6'"): ?>selected <?php endif; ?>><?php echo e(__('admin.level')); ?>6</option>
                        <option value="7" <?php if(condition="$param['level'] == '7'"): ?>selected <?php endif; ?>><?php echo e(__('admin.level')); ?>7</option>
                        <option value="8" <?php if(condition="$param['level'] == '8'"): ?>selected <?php endif; ?>><?php echo e(__('admin.level')); ?>8</option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="pic">
                        <option value=""><?php echo e(__('admin.select_pic')); ?></option>
                        <option value="1" <?php if(condition="$param['pic'] == '1'"): ?>selected@endif><?php echo e(__('admin.pic_empty')); ?></option>
                        <option value="2" <?php if(condition="$param['pic'] == '2'"): ?>selected@endif><?php echo e(__('admin.pic_remote')); ?></option>
                        <option value="3" <?php if(condition="$param['pic'] == '3'"): ?>selected@endif><?php echo e(__('admin.pic_sync_err')); ?></option>
                    </select>
                </div>
                <div class="layui-input-inline w150">
                    <select name="order">
                        <option value=""><?php echo e(__('admin.select_sort')); ?></option>
                        <option value="role_time" <?php if(condition="$param['order'] == 'role_time'"): ?>selected@endif><?php echo e(__('admin.update_time')); ?></option>
                        <option value="role_id" <?php if(condition="$param['order'] == 'role_id'"): ?>selected@endif><?php echo e(__('admin.id')); ?></option>
                        <option value="role_hits" <?php if(condition="$param['order'] == 'role_hits'"): ?>selected@endif><?php echo e(__('admin.hits')); ?></option>
                        <option value="role_hits_month" <?php if(condition="$param['order'] == 'role_hits_month'"): ?>selected@endif><?php echo e(__('admin.hits_month')); ?></option>
                        <option value="role_hits_week" <?php if(condition="$param['order'] == 'role_hits_week'"): ?>selected@endif><?php echo e(__('admin.hits_week')); ?></option>
                        <option value="role_hits_day" <?php if(condition="$param['order'] == 'role_hits_day'"): ?>selected<?php endif; ?>><?php echo e(__('admin.hits_day')); ?></option>
                    </select>
                </div>

                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" placeholder="<?php echo e(__('admin.wd')); ?>" class="layui-input" name="wd" value="<?php echo e($param['wd']); ?>">
                </div>
                <button class="layui-btn mgl-20 j-search" ><?php echo e(__('admin.btn_search')); ?></button>
            </form>
        </div>
        @endif

        <div class="layui-btn-group">
            <?php if(condition="$param.select == 1 && $param.rid != ''"): ?>
            <a data-href="<?php echo e(url('info')); ?>?tab=<?php echo e($param.tab); ?>&rid=<?php echo e($param.rid); ?>" data-full="1" class="layui-btn layui-btn-primary j-iframe"><i class="layui-icon">&#xe654;</i><?php echo e(__('admin.add')); ?></a>
            <?php endif; ?>
            <a data-href="<?php echo e(url('del')); ?>" class="layui-btn layui-btn-primary j-page-btns confirm"><i class="layui-icon">&#xe640;</i><?php echo e(__('admin.del')); ?></a>
            <a data-href="<?php echo e(url('index/select')); ?>?tab=role&col=role_level&tpl=select_level&url=role/field" data-width="270" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i><?php echo e(__('admin.level')); ?></a>
            <a data-href="<?php echo e(url('index/select')); ?>?tab=role&col=role_hits&tpl=select_hits&url=role/field" data-width="470" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i><?php echo e(__('admin.hits')); ?></a>
            <a data-href="<?php echo e(url('index/select')); ?>?tab=role&col=role_status&tpl=select_status&url=role/field" data-width="470" data-height="100" data-checkbox="1" class="layui-btn layui-btn-primary j-select"><i class="layui-icon">&#xe620;</i><?php echo e(__('admin.status')); ?></a>
            <a class="layui-btn layui-btn-primary j-iframe" data-href="<?php echo e(url('images/opt?tab=role')); ?>" href="javascript:;" title="<?php echo e(__('admin.pic_sync')); ?>"><i class="layui-icon">&#xe620;</i><?php echo e(__('admin.pic_sync')); ?></a>
        </div>

    </div>


    <form class="layui-form " method="post" id="pageListForm">
        <table class="layui-table" lay-size="sm">
            <thead>
            <tr>
                <th width="25"><input type="checkbox" lay-skin="primary" lay-filter="allChoose"></th>
                <th width="50"><?php echo e(__('admin.id')); ?></th>
                <th ><?php echo e(__('admin.vod_name')); ?></th>
                <th width="150"><?php echo e(__('admin.role_name')); ?></th>
                <th width="150"><?php echo e(__('admin.actor_name')); ?></th>
                <th width="40"><?php echo e(__('admin.sort')); ?></th>
                <th width="40"><?php echo e(__('admin.hits')); ?></th>
                <th width="40"><?php echo e(__('admin.level')); ?></th>
                <th width="120"><?php echo e(__('admin.update_time')); ?></th>
                <th width="80"><?php echo e(__('admin.opt')); ?></th>
            </tr>
            </thead>

            <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><input type="checkbox" name="ids[]" value="<?php echo e($vo.role_id); ?>" class="layui-checkbox checkbox-ids" lay-skin="primary"></td>
                <td><?php echo e($vo.role_id); ?></td>
                <td><a target="_blank" class="layui-badge-rim " href="<?php echo e(mac_url_vod_detail($vo.data)); ?>">[<?php echo e($vo.data.vod_name|htmlspecialchars); ?>]</a></td>
                <td> <a target="_blank" class="layui-badge-rim " href="<?php echo e(mac_url_role_detail($vo)); ?>"><?php echo e($vo.role_name|htmlspecialchars); ?></a> <?php if($vo.role_status == 0): ?> <span class="layui-badge"><?php echo e(__('admin.reviewed_not')); ?></span><?php endif; ?> <?php if($vo.role_lock == 1): ?> <span class="layui-badge"><?php echo e(__('admin.lock')); ?></span><?php endif; ?></td>
                <td><?php echo e($vo.role_actor|htmlspecialchars); ?></td>
                <td><?php echo e($vo.role_sort); ?></td>
                <td><?php echo e($vo.role_hits); ?></td>
                <td><a data-href="<?php echo e(url('index/select')); ?>?tab=role&col=role_level&tpl=select_level&url=role/field&ids=<?php echo e($vo.role_id); ?>" data-width="270" data-height="100" class=" j-select"><span class="layui-badge layui-bg-orange"><?php echo e($vo.role_level); ?></span></a></td>
                <td><?php echo e($vo.role_time|mac_day='color'); ?></td>
                <td>
                    <a class="layui-badge-rim j-iframe" data-full="1" data-href="<?php echo e(url('info?id='.$vo['role_id'])); ?>" href="javascript:;" title="<?php echo e(__('admin.edit')); ?>"><?php echo e(__('admin.edit')); ?></a>
                    <a class="layui-badge-rim j-tr-del" data-href="<?php echo e(url('del?ids='.$vo['role_id'])); ?>" href="javascript:;" title="<?php echo e(__('admin.del')); ?>"><?php echo e(__('admin.del')); ?></a>
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
    var curUrl="<?php echo e(url('role/data',$param)); ?>";
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
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\role\index.blade.php ENDPATH**/ ?>