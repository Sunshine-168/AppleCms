<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">
    <form class="layui-form layui-form-pane" action="">
        <input type="hidden" name="id" value="<?php echo e($param.id); ?>">
        <fieldset class="layui-elem-field">
            <legend><?php echo e(__('admin.admin/cj/label_data_rel')); ?></legend>
        </fieldset>

                    <table class="layui-table" lay-size="sm" style="width:600px;">
                        <thead>
                        <tr>
                            <th width="100"><?php echo e(__('admin.admin/cj/data_column')); ?></th>
                            <th width="100"><?php echo e(__('admin.admin/cj/label_column')); ?></th>
                            <th width="100"><?php echo e(__('admin.admin/cj/processing_function')); ?></th>
                        </tr>
                        </thead>

                        <?php $__currentLoopData = $column_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><input type="hidden" name="model_field[]" value="<?php echo e($vo.Field); ?>"><?php echo e($vo.Field); ?></td>
                            <td><select name="node_field[]">
                                <option value=""><?php echo e(__('admin.select_please')); ?></option>
                                <?php $__currentLoopData = $node_field; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $vo2): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>" <?php if(condition="$program_config['map'][$vo.Field] == $key"): ?>selected@endif><?php echo e($vo2); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            </td>
                            <td><select name="funcs[]"><option value="" ><?php echo e(__('admin.select_please')); ?></option><option value="trim" <?php if(condition="$program_config['funcs'][$vo.Field] == 'trim'"): ?>selected@endif><?php echo e(__('admin.admin/cj/trim_space')); ?></option></select></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>

        <div class="layui-form-item center">
            <div class="layui-input-block">
                <button type="submit" class="layui-btn" lay-submit="" lay-filter="formSubmit" data-child="true"><?php echo e(__('admin.btn_save')); ?></button>
                <button class="layui-btn layui-btn-warm" type="reset"><?php echo e(__('admin.btn_reset')); ?></button>
            </div>
        </div>
    </form>
</div>

<?php echo $__env->make('../../../application/admin/view/public/foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script type="text/javascript">

</script>

</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\cj\program.blade.php ENDPATH**/ ?>