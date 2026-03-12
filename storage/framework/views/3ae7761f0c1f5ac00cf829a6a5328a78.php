<?php echo $__env->make('../../../application/admin/view/public/head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container p10">

    <form class="layui-form" method="post" action="">

        <div class="my-toolbar-box">

            <div class="center mb10">

                    <div class="layui-input-inline w150">
                        <select name="type">
                            <option value=""><?php echo e(__('admin.select_type')); ?></option>
                            <?php $__currentLoopData = $type_tree; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($vo.type_mid == 2): ?>
                            <option value="<?php echo e($vo.type_id); ?>" <?php if(condition="$param['type'] == $vo.type_id"): ?>selected <?php endif; ?>><?php echo e($vo.type_name); ?></option>
                            <?php $__currentLoopData = $vo.child; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($ch.type_id); ?>" <?php if(condition="$param['type'] == $ch.type_id"): ?>selected <?php endif; ?>>&nbsp;&nbsp;&nbsp;&nbsp;├&nbsp;<?php echo e($ch.type_name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
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
                        <select name="lock">
                            <option value=""><?php echo e(__('admin.select_lock')); ?></option>
                            <option value="0" <?php if(condition="$param['lock'] == '0'"): ?>selected <?php endif; ?>><?php echo e(__('admin.unlock')); ?></option>
                            <option value="1" <?php if(condition="$param['lock'] == '1'"): ?>selected <?php endif; ?>><?php echo e(__('admin.lock')); ?></option>
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

                    <div class="layui-input-inline">
                        <input type="text" autocomplete="off" placeholder="<?php echo e(__('admin.wd')); ?>" class="layui-input" name="wd" value="<?php echo e($param['wd']); ?>">
                    </div>

            </div>

        </div>

        <fieldset class="layui-elem-field">
            <legend><?php echo e(__('admin.del_multi')); ?></legend>
            <div class="layui-field-box">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label"><input type="checkbox" lay-ignore value="1" name="ck_del"><?php echo e(__('admin.del_data')); ?></label>
                        <div class="layui-input-inline" style="width: 100px;">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <button type="button" class="layui-btn btn_submit"><?php echo e(__('admin.del_multi')); ?></button>
                </div>
            </div>
        </fieldset>

        <fieldset class="layui-elem-field">
        <legend><?php echo e(__('admin.multi_set')); ?></legend>
        <div class="layui-field-box">

            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label"><input type="checkbox" lay-ignore value="1" name="ck_level" title="<?php echo e(__('admin.level')); ?>"><?php echo e(__('admin.level')); ?></label>
                    <div class="layui-input-inline" style="width: 100px;">
                        <select name="val_level">
                            <option value=""><?php echo e(__('admin.select_level')); ?></option>
                            <option value="9" ><?php echo e(__('admin.level')); ?>9-<?php echo e(__('admin.slide')); ?></option>
                            <option value="1" ><?php echo e(__('admin.level')); ?>1</option>
                            <option value="2" ><?php echo e(__('admin.level')); ?>2</option>
                            <option value="3" ><?php echo e(__('admin.level')); ?>3</option>
                            <option value="4" ><?php echo e(__('admin.level')); ?>4</option>
                            <option value="5" ><?php echo e(__('admin.level')); ?>5</option>
                            <option value="6" ><?php echo e(__('admin.level')); ?>6</option>
                            <option value="7" ><?php echo e(__('admin.level')); ?>7</option>
                            <option value="8" ><?php echo e(__('admin.level')); ?>8</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label"><input type="checkbox" lay-ignore value="1" name="ck_lock"><?php echo e(__('admin.lock')); ?></label>
                    <div class="layui-input-inline" style="width: 100px;">
                        <select name="val_lock">
                            <option value=""><?php echo e(__('admin.select_opt')); ?></option>
                            <option value="0" ><?php echo e(__('admin.unlock')); ?></option>
                            <option value="1" ><?php echo e(__('admin.lock')); ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label"><input type="checkbox" lay-ignore value="1" name="ck_status"><?php echo e(__('admin.status')); ?></label>
                    <div class="layui-input-inline" style="width: 100px;">
                        <select name="val_status">
                            <option value=""><?php echo e(__('admin.select_status')); ?></option>
                            <option value="0" ><?php echo e(__('admin.reviewed')); ?></option>
                            <option value="1" ><?php echo e(__('admin.reviewed')); ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label"><input type="checkbox" lay-ignore value="1" name="ck_hits"><?php echo e(__('admin.hits')); ?></label>
                    <div class="layui-input-inline" style="width: 100px;">
                        <input type="text" name="val_hits_min" required  placeholder="<?php echo e(__('admin.min_val')); ?>" autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-input-inline" style="width: 100px;">
                        <input type="text" name="val_hits_max" required  placeholder="<?php echo e(__('admin.max_val')); ?>" autocomplete="off" class="layui-input">
                    </div>
                </div>
            </div>

            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label"><?php echo e(__('admin.page_limit')); ?></label>
                    <div class="layui-input-inline" style="width: 100px;">
                        <input type="text" name="limit" required  placeholder="" autocomplete="off" value="100" class="layui-input">
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <button type="submit" class="layui-btn btn_submit"><?php echo e(__('admin.start_exec')); ?></button>
            </div>

        </div>
    </fieldset>
    </form>
</div>

<script type="text/javascript">
    layui.use(['form'], function () {

    });

    $('.btn_submit').click(function(){
        $('form').submit();
    })
</script>
</body>
</html><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\art\batch.blade.php ENDPATH**/ ?>