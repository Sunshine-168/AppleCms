<?php $__env->startSection('title', '资料修改'); ?>

<?php $__env->startSection('user_content'); ?>
<div class="card">
    <div class="card-header">资料修改</div>
    <div class="card-body">
        <form method="post" action="<?php echo e(route('user.info')); ?>">
            <?php echo csrf_field(); ?>
            <div class="mb-3">
                <label class="form-label">昵称</label>
                <input type="text" class="form-control" name="user_nick_name" value="<?php echo e($user->user_nick_name); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">QQ</label>
                <input type="text" class="form-control" name="user_qq" value="<?php echo e($user->user_qq); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">安全问题</label>
                <input type="text" class="form-control" name="user_question" value="<?php echo e($user->user_question); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">安全答案</label>
                <input type="text" class="form-control" name="user_answer" value="<?php echo e($user->user_answer); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">原密码</label>
                <input type="password" class="form-control" name="user_pwd">
            </div>
            <div class="mb-3">
                <label class="form-label">新密码</label>
                <input type="password" class="form-control" name="user_pwd1">
            </div>
            <div class="mb-3">
                <label class="form-label">确认新密码</label>
                <input type="password" class="form-control" name="user_pwd2">
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\user\info.blade.php ENDPATH**/ ?>