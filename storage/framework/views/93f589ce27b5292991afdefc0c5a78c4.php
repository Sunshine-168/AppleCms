<?php $__env->startSection('title', '找回密码'); ?>

<?php $__env->startSection('content'); ?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">安全问题找回密码</div>
            <div class="card-body">
                <form method="post" action="<?php echo e(route('user.findpass')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3"><input class="form-control" name="user_name" value="<?php echo e($param['user_name'] ?? ''); ?>" placeholder="用户名"></div>
                    <div class="mb-3"><input class="form-control" name="user_question" value="<?php echo e($param['user_question'] ?? ''); ?>" placeholder="安全问题"></div>
                    <div class="mb-3"><input class="form-control" name="user_answer" value="<?php echo e($param['user_answer'] ?? ''); ?>" placeholder="安全答案"></div>
                    <div class="mb-3"><input type="password" class="form-control" name="user_pwd" placeholder="新密码"></div>
                    <div class="mb-3"><input type="password" class="form-control" name="user_pwd2" placeholder="确认新密码"></div>
                    <div class="mb-3"><input class="form-control" name="verify" placeholder="验证码"></div>
                    <button type="submit" class="btn btn-primary w-100">提交</button>
                </form>
            </div>
            <div class="card-footer text-center">
                <a href="<?php echo e(route('user.findpass_msg')); ?>" class="text-decoration-none">改用验证码找回</a>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\user\findpass.blade.php ENDPATH**/ ?>