<?php $__env->startSection('title', '验证码找回密码'); ?>

<?php $__env->startSection('content'); ?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <h3 class="mb-4 text-center">验证码找回密码</h3>
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">第一步：发送验证码</h5>
                <form method="post" action="<?php echo e(route('user.findpass_msg')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <select class="form-select" name="ac">
                            <option value="email" <?php if(($param['ac'] ?? 'email') === 'email'): echo 'selected'; endif; ?>>邮箱</option>
                            <option value="phone" <?php if(($param['ac'] ?? '') === 'phone'): echo 'selected'; endif; ?>>手机</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <input class="form-control" name="to" value="<?php echo e($param['to'] ?? ''); ?>" placeholder="邮箱或手机号">
                    </div>
                    <button type="submit" class="btn btn-outline-primary">发送验证码</button>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">第二步：重置密码</h5>
                <form method="post" action="<?php echo e(route('user.findpass_reset')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <select class="form-select" name="ac">
                            <option value="email" <?php if(($param['ac'] ?? 'email') === 'email'): echo 'selected'; endif; ?>>邮箱</option>
                            <option value="phone" <?php if(($param['ac'] ?? '') === 'phone'): echo 'selected'; endif; ?>>手机</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <input class="form-control" name="to" value="<?php echo e($param['to'] ?? ''); ?>" placeholder="邮箱或手机号">
                    </div>
                    <div class="mb-3">
                        <input class="form-control" name="code" placeholder="验证码">
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control" name="user_pwd" placeholder="新密码">
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control" name="user_pwd2" placeholder="确认新密码">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">重置密码</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\user\findpass_msg.blade.php ENDPATH**/ ?>