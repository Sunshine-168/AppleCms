<?php echo $__env->make('admin.public.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-container">
    <form class="layui-form layui-form-pane" method="post" action="<?php echo e(route('admin.system.configcomment')); ?>">
        <?php echo csrf_field(); ?>
        <div class="layui-tab">
            <ul class="layui-tab-title">
                <li class="layui-this"><?php echo e(__('admin/system/configcomment/title')); ?></li>
            </ul>
            <div class="layui-tab-content">
                <div class="layui-tab-item layui-show">
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/configcomment/gbook')); ?>：</label>
                        <div class="layui-input-inline">
                            <input type="radio" name="gbook[status]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'gbook.status', '0') !== '1'): echo 'checked'; endif; ?>>
                            <input type="radio" name="gbook[status]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'gbook.status', '0') === '1'): echo 'checked'; endif; ?>>
                        </div>
                        <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin/system/configcomment/gbook_tip')); ?></div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/configcomment/audit')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="gbook[audit]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'gbook.audit', '0') !== '1'): echo 'checked'; endif; ?>>
                            <input type="radio" name="gbook[audit]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'gbook.audit', '0') === '1'): echo 'checked'; endif; ?>>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/configcomment/login')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="gbook[login]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'gbook.login', '0') !== '1'): echo 'checked'; endif; ?>>
                            <input type="radio" name="gbook[login]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'gbook.login', '0') === '1'): echo 'checked'; endif; ?>>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/configcomment/verify')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="gbook[verify]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'gbook.verify', '0') !== '1'): echo 'checked'; endif; ?>>
                            <input type="radio" name="gbook[verify]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'gbook.verify', '0') === '1'): echo 'checked'; endif; ?>>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/configcomment/pagesize')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="text" name="gbook[pagesize]" placeholder="<?php echo e(__('admin/system/configcomment/pagesize_tip')); ?>" value="<?php echo e(data_get($config, 'gbook.pagesize', '')); ?>" class="layui-input w150">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/configcomment/timespan')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="text" name="gbook[timespan]" placeholder="<?php echo e(__('admin/system/configcomment/timespan_tip')); ?>" value="<?php echo e(data_get($config, 'gbook.timespan', '')); ?>" class="layui-input w150">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/configcomment/comment')); ?>：</label>
                        <div class="layui-input-inline">
                            <input type="radio" name="comment[status]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'comment.status', '0') !== '1'): echo 'checked'; endif; ?>>
                            <input type="radio" name="comment[status]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'comment.status', '0') === '1'): echo 'checked'; endif; ?>>
                        </div>
                        <div class="layui-form-mid layui-word-aux"><?php echo e(__('admin/system/configcomment/comment_tip')); ?></div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/configcomment/audit')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="comment[audit]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'comment.audit', '0') !== '1'): echo 'checked'; endif; ?>>
                            <input type="radio" name="comment[audit]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'comment.audit', '0') === '1'): echo 'checked'; endif; ?>>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/configcomment/login')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="comment[login]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'comment.login', '0') !== '1'): echo 'checked'; endif; ?>>
                            <input type="radio" name="comment[login]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'comment.login', '0') === '1'): echo 'checked'; endif; ?>>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/configcomment/verify')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="radio" name="comment[verify]" value="0" title="<?php echo e(__('admin.close')); ?>" <?php if((string) data_get($config, 'comment.verify', '0') !== '1'): echo 'checked'; endif; ?>>
                            <input type="radio" name="comment[verify]" value="1" title="<?php echo e(__('admin.open')); ?>" <?php if((string) data_get($config, 'comment.verify', '0') === '1'): echo 'checked'; endif; ?>>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/configcomment/pagesize')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="text" name="comment[pagesize]" placeholder="<?php echo e(__('admin/system/configcomment/pagesize_tip')); ?>" value="<?php echo e(data_get($config, 'comment.pagesize', '')); ?>" class="layui-input w150">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label"><?php echo e(__('admin/system/configcomment/timespan')); ?>：</label>
                        <div class="layui-input-block">
                            <input type="text" name="comment[timespan]" placeholder="<?php echo e(__('admin/system/configcomment/timespan_tip')); ?>" value="<?php echo e(data_get($config, 'comment.timespan', '')); ?>" class="layui-input w150">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-form-item center">
            <div class="layui-input-block">
                <button type="submit" class="layui-btn" lay-submit lay-filter="formSubmit"><?php echo e(__('admin.btn_save')); ?></button>
                <button class="layui-btn layui-btn-warm" type="reset"><?php echo e(__('admin.btn_reset')); ?></button>
            </div>
        </div>
    </form>
</div>
<?php echo $__env->make('admin.public.foot', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\system\configcomment.blade.php ENDPATH**/ ?>