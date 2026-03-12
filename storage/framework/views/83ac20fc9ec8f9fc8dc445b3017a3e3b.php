<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">
    <form class="layui-form layui-form-pane" method="post" action="<?php echo e(route('admin.card.info', ['id' => $info->card_id ?: null])); ?>">
        <?php echo csrf_field(); ?>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.admin/card/make_num')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e(old('num', 10)); ?>" lay-verify="num" name="num">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.money')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e(old('money', '')); ?>" lay-verify="money" name="money">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.points')); ?>：</label>
            <div class="layui-input-block">
                <input type="text" class="layui-input" value="<?php echo e(old('point', '')); ?>" lay-verify="point" name="point">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.card_no')); ?><?php echo e(__('admin.rule')); ?>：</label>
            <div class="layui-input-block">
                <input type="radio" name="role_no" value="" title="<?php echo e(__('admin.mixing')); ?>" <?php if(old('role_no', '') === ''): echo 'checked'; endif; ?>>
                <input type="radio" name="role_no" value="letter" title="<?php echo e(__('admin.abc')); ?>" <?php if(old('role_no') === 'letter'): echo 'checked'; endif; ?>>
                <input type="radio" name="role_no" value="num" title="<?php echo e(__('admin.number')); ?>" <?php if(old('role_no') === 'num'): echo 'checked'; endif; ?>>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><?php echo e(__('admin.pass')); ?><?php echo e(__('admin.rule')); ?>：</label>
            <div class="layui-input-block">
                <input type="radio" name="role_pwd" value="" title="<?php echo e(__('admin.mixing')); ?>" <?php if(old('role_pwd', '') === ''): echo 'checked'; endif; ?>>
                <input type="radio" name="role_pwd" value="letter" title="<?php echo e(__('admin.abc')); ?>" <?php if(old('role_pwd') === 'letter'): echo 'checked'; endif; ?>>
                <input type="radio" name="role_pwd" value="num" title="<?php echo e(__('admin.number')); ?>" <?php if(old('role_pwd') === 'num'): echo 'checked'; endif; ?>>
            </div>
        </div>

        <div class="layui-form-item center">
            <div class="layui-input-block">
                <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit" data-child="true"><?php echo e(__('admin.btn_save')); ?></button>
                <button class="layui-btn layui-btn-warm" type="reset"><?php echo e(__('admin.btn_reset')); ?></button>
            </div>
        </div>
    </form>
</div>
<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script type="text/javascript">
    layui.use(['form', 'layer'], function () {
        var form = layui.form, layer = layui.layer, $ = layui.jquery;

        form.verify({
            num: function (value) {
                if (value === "") {
                    return "<?php echo e(__('admin.admin/card/please_input_make_num')); ?>";
                }
            },
            money: function (value) {
                if (value === "") {
                    return "<?php echo e(__('admin.admin/card/please_input_money')); ?>";
                }
            },
            point: function (value) {
                if (value === "") {
                    return "<?php echo e(__('admin.admin/card/please_input_points')); ?>";
                }
            }
        });
    });
</script><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\card\info.blade.php ENDPATH**/ ?>