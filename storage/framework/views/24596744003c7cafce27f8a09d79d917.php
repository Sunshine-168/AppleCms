<?php $__env->startSection('title', '用户注册'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $verifyMode = (($userConfig['reg_phone_sms'] ?? '0') === '1') ? 'phone' : (((($userConfig['reg_email_sms'] ?? '0') === '1')) ? 'email' : '');
    $verifyLabel = $verifyMode === 'phone' ? '手机号' : '邮箱';
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">用户注册</div>
            <div class="card-body">
                <?php if($verifyMode !== ''): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">发送<?php echo e($verifyLabel); ?>验证码</h5>
                            <form method="post" action="<?php echo e(route('user.reg_msg')); ?>">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="ac" value="<?php echo e($verifyMode); ?>">
                                <div class="mb-3">
                                    <input type="text" class="form-control" name="to" value="<?php echo e($param['to'] ?? ''); ?>" placeholder="请输入<?php echo e($verifyLabel); ?>">
                                </div>
                                <button type="submit" class="btn btn-outline-primary">发送验证码</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
                <form method="post" action="<?php echo e(route('user.reg')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label class="form-label">用户名</label>
                        <input type="text" class="form-control" name="user_name" value="<?php echo e($param['user_name'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">密码</label>
                        <input type="password" class="form-control" name="user_pwd">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">确认密码</label>
                        <input type="password" class="form-control" name="user_pwd2">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">邀请码</label>
                        <input type="text" class="form-control" name="uid" value="<?php echo e($param['uid'] ?? ''); ?>">
                    </div>
                    <?php if($verifyMode !== ''): ?>
                        <input type="hidden" name="ac" value="<?php echo e($verifyMode); ?>">
                        <div class="mb-3">
                            <label class="form-label"><?php echo e($verifyLabel); ?></label>
                            <input type="text" class="form-control" name="to" value="<?php echo e($param['to'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">验证码</label>
                            <input type="text" class="form-control" name="code">
                        </div>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary w-100">注册</button>
                </form>
            </div>
            <div class="card-footer text-center">
                <a href="<?php echo e(route('login')); ?>" class="text-decoration-none">已有账号？立即登录</a>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\user\reg.blade.php ENDPATH**/ ?>