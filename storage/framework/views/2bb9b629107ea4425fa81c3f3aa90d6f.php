<?php $__env->startSection('title', '绑定账号'); ?>

<?php $__env->startSection('user_content'); ?>
<div class="card mb-3">
    <div class="card-header">绑定邮箱/手机</div>
    <div class="card-body">
        <p class="text-muted mb-0">当前邮箱：<?php echo e($user->user_email ?: '未绑定'); ?>，当前手机：<?php echo e($user->user_phone ?: '未绑定'); ?></p>
    </div>
</div>
<div class="card mb-3">
    <div class="card-body">
        <h5 class="card-title">第一步：发送验证码</h5>
        <form method="post" action="<?php echo e(route('user.bindmsg')); ?>">
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
        <h5 class="card-title">第二步：提交绑定</h5>
        <form method="post" action="<?php echo e(route('user.bind')); ?>">
            <?php echo csrf_field(); ?>
            <div class="mb-3">
                <select class="form-select" name="ac">
                    <option value="email" <?php if(($param['ac'] ?? 'email') === 'email'): echo 'selected'; endif; ?>>邮箱</option>
                    <option value="phone" <?php if(($param['ac'] ?? '') === 'phone'): echo 'selected'; endif; ?>>手机</option>
                </select>
            </div>
            <div class="mb-3"><input class="form-control" name="to" value="<?php echo e($param['to'] ?? ''); ?>" placeholder="邮箱或手机号"></div>
            <div class="mb-3"><input class="form-control" name="code" placeholder="验证码"></div>
            <button type="submit" class="btn btn-primary">绑定</button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\user\bind.blade.php ENDPATH**/ ?>