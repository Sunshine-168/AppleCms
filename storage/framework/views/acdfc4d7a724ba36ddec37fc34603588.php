<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">
    <form class="layui-form layui-form-pane" method="post" action="">
        <input id="group_id" name="group_id" type="hidden" value="<?php echo e($info.group_id); ?>">
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.name')); ?>：</label>
            <div class="layui-input-block  ">
                <input type="text" class="layui-input" value="<?php echo e($info.group_name); ?>" placeholder="" lay-verify="group_name" name="group_name">
            </div>
        </div>

        <?php if($info.group_id > 2): ?>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.admin/group/pack_day')); ?><?php echo e(__('admin.points')); ?>：</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input" value="<?php echo e($info.group_points_day); ?>" placeholder="" lay-verify="group_points_day" name="group_points_day">
            </div>
            <label class="layui-form-label"><?php echo e(__('admin.admin/group/pack_week')); ?><?php echo e(__('admin.points')); ?>：</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input" value="<?php echo e($info.group_points_week); ?>" placeholder="" lay-verify="group_points_week" name="group_points_week">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.admin/group/pack_month')); ?><?php echo e(__('admin.points')); ?>：</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input" value="<?php echo e($info.group_points_month); ?>" placeholder="" lay-verify="group_points_month" name="group_points_month">
            </div>
            <label class="layui-form-label"><?php echo e(__('admin.admin/group/pack_year')); ?><?php echo e(__('admin.points')); ?>：</label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input" value="<?php echo e($info.group_points_year); ?>" placeholder="" lay-verify="group_points_year" name="group_points_year">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.status')); ?>：</label>
            <div class="layui-input-block">
                    <input name="group_status" type="radio" value="0" title="<?php echo e(__('admin.disable')); ?>" <?php if(condition="$info['group_status'] != 1"): ?>checked <?php endif; ?>>
                    <input name="group_status" type="radio" value="1" title="<?php echo e(__('admin.enable')); ?>" <?php if(condition="$info['group_status'] == 1"): ?>checked <?php endif; ?>>
            </div>
        </div>
        <?php endif; ?>

        <div class="layui-form-item ">
            <label class="layui-form-label"><?php echo e(__('admin.admin/group/popedom')); ?>：</label>
            <div class="layui-input-block">
                <blockquote class="layui-elem-quote layui-quote-nm">
                    <?php echo e(__('admin.admin/group/popedom_tip')); ?>

                </blockquote>

                <div class="role-list-form ">
                <?php $__currentLoopData = $type_tree; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <dl class="role-list-form-top permission-list">
                        <dt>
                            <?php echo e(__('admin.type')); ?>：<input type="checkbox" value="<?php echo e($vo.type_id); ?>" name="group_type[]" data-id="<?php echo e($k1); ?>" lay-skin="primary" lay-filter="roleAuth1" title="<?php echo e($vo.type_name); ?>" <?php if(condition="strpos(','.$info['group_type'],','.$vo['type_id'].',')>0"): ?>checked <?php endif; ?>>
                            <?php echo e(__('admin.popedom')); ?>：<input type="checkbox" name="group_popedom[<?php echo e($vo.type_id); ?>][1]" value="1" lay-skin="primary" title="<?php echo e(__('admin.admin/group/popedom_list')); ?>" <?php if(condition="!empty($info['group_popedom'][$vo.type_id][1])"): ?>checked <?php endif; ?>>
                            <input type="checkbox" name="group_popedom[<?php echo e($vo.type_id); ?>][2]" value="2" lay-skin="primary" title="<?php echo e(__('admin.admin/group/popedom_detail')); ?>" <?php if(condition="!empty($info['group_popedom'][$vo.type_id][2])"): ?>checked <?php endif; ?>>
                            <?php if($vo.type_mid == 1): ?>
                            <input type="checkbox" name="group_popedom[<?php echo e($vo.type_id); ?>][3]" value="3" lay-skin="primary" title="<?php echo e(__('admin.admin/group/popedom_play')); ?>" <?php if(condition="!empty($info['group_popedom'][$vo.type_id][3])"): ?>checked <?php endif; ?>>
                            <input type="checkbox" name="group_popedom[<?php echo e($vo.type_id); ?>][4]" value="4" lay-skin="primary" title="<?php echo e(__('admin.admin/group/popedom_down')); ?>" <?php if(condition="!empty($info['group_popedom'][$vo.type_id][4])"): ?>checked <?php endif; ?>>
                            <input type="checkbox" name="group_popedom[<?php echo e($vo.type_id); ?>][5]" value="5" lay-skin="primary" title="<?php echo e(__('admin.admin/group/popedom_trysee')); ?>" <?php if(condition="!empty($info['group_popedom'][$vo.type_id][5])"): ?>checked <?php endif; ?>>
                            <?php endif; ?>
                        </dt>
                    </dl>
                    <?php $__currentLoopData = $$vo.child; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <dl class="role-list-form-top permission-list">
                        <dt>
                            <?php echo e(__('admin.type')); ?>：<input type="checkbox" value="<?php echo e($sub.type_id); ?>" name="group_type[]" data-id="<?php echo e($k1); ?>" lay-skin="primary" lay-filter="roleAuth1" title="---<?php echo e($sub.type_name); ?>" <?php if(condition="strpos(','.$info['group_type'],','.$sub  ['type_id'].',')>0"): ?>checked <?php endif; ?>>
                            <?php echo e(__('admin.popedom')); ?>：<input type="checkbox" name="group_popedom[<?php echo e($sub.type_id); ?>][1]" value="1" lay-skin="primary" title="<?php echo e(__('admin.admin/group/popedom_list')); ?>" <?php if(condition="!empty($info['group_popedom'][$sub.type_id][1])"): ?>checked <?php endif; ?>>
                            <input type="checkbox" name="group_popedom[<?php echo e($sub.type_id); ?>][2]" value="2" lay-skin="primary" title="<?php echo e(__('admin.admin/group/popedom_detail')); ?>" <?php if(condition="!empty($info['group_popedom'][$sub.type_id][2])"): ?>checked <?php endif; ?>>
                            <?php if($sub.type_mid == 1): ?>
                            <input type="checkbox" name="group_popedom[<?php echo e($sub.type_id); ?>][3]" value="3" lay-skin="primary" title="<?php echo e(__('admin.admin/group/popedom_play')); ?>" <?php if(condition="!empty($info['group_popedom'][$sub.type_id][3])"): ?>checked <?php endif; ?>>
                            <input type="checkbox" name="group_popedom[<?php echo e($sub.type_id); ?>][4]" value="4" lay-skin="primary" title="<?php echo e(__('admin.admin/group/popedom_down')); ?>" <?php if(condition="!empty($info['group_popedom'][$sub.type_id][4])"): ?>checked <?php endif; ?>>
                            <input type="checkbox" name="group_popedom[<?php echo e($sub.type_id); ?>][5]" value="5" lay-skin="primary" title="<?php echo e(__('admin.admin/group/popedom_trysee')); ?>" <?php if(condition="!empty($info['group_popedom'][$sub.type_id][5])"): ?>checked <?php endif; ?>>
                            <?php endif; ?>
                        </dt>
                    </dl>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        <div class="layui-form-item center">
            <div class="layui-input-block">
                <button type="button" class="layui-btn layui-btn-normal formCheckAll" lay-filter="formCheckAll" ><?php echo e(__('admin.check_all')); ?></button>
                <button type="button" class="layui-btn layui-btn-normal formCheckOther" lay-filter="formCheckOther"><?php echo e(__('admin.check_other')); ?></button>
                <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit" data-child="true"><?php echo e(__('admin.btn_save')); ?></button>
                <button class="layui-btn layui-btn-warm" type="reset"><?php echo e(__('admin.btn_reset')); ?></button>
            </div>
        </div>
    </form>

</div>
<?php echo $__env->make('../../../application/admin/view/public/foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script type="text/javascript">
    layui.use(['form', 'layer'], function () {
        // 操作对象
        var form = layui.form
                , layer = layui.layer
                , $ = layui.jquery;

        // 验证
        form.verify({
            group_name: function (value) {
                if (value == "") {
                    return "<?php echo e(__('admin.name_empty')); ?>";
                }
            }
        });

        $('.formCheckAll').click(function(){
            var child = $('.role-list-form').find('input');
            /* 自动选中子节点 */
            child.each(function(index, item) {
                item.checked = true;
            });
            form.render('checkbox');
        });
        $('.formCheckOther').click(function(){
            var child = $('.role-list-form').find('input');
            /* 自动选中子节点 */
            child.each(function(index, item) {
                item.checked = (item.checked  ? false : true);
            });
            form.render('checkbox');
        });
    });

</script>

</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\group\info.blade.php ENDPATH**/ ?>