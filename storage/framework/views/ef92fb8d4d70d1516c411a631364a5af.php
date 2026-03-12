<?php $__env->startSection('title', '用户登录'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $loginVerify = (string) config('maccms.user.login_verify', '0') === '1';
    $connect = (array) config('maccms.connect', []);
    $oauthItems = [
        'qq' => ['label' => 'QQ登录'],
        'weixin' => ['label' => '微信登录'],
    ];
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">用户登录</div>
            <div class="card-body">
                <?php if($errors->any()): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form action="<?php echo e(url('/user/login')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label for="user_name" class="form-label">用户名/邮箱</label>
                        <input type="text" class="form-control" id="user_name" name="user_name" value="<?php echo e(old('user_name')); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="user_pwd" class="form-label">密码</label>
                        <input type="password" class="form-control" id="user_pwd" name="user_pwd" required>
                    </div>
                    <?php if($loginVerify): ?>
                        <div class="mb-3">
                            <label for="verify" class="form-label">验证码</label>
                            <div class="d-flex gap-2">
                                <input type="text" class="form-control" id="verify" name="verify" required>
                                <img
                                    src="<?php echo e(route('verify.index')); ?>"
                                    alt="verify"
                                    style="width: 120px; height: 40px; cursor: pointer;"
                                    onclick="this.src='<?php echo e(route('verify.index')); ?>?t=' + Date.now()"
                                >
                            </div>
                        </div>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary w-100">登录</button>
                </form>
                <?php if(collect($oauthItems)->contains(fn ($item, $key) => (string) data_get($connect, $key . '.status', '0') === '1')): ?>
                    <div class="mt-4 pt-3 border-top">
                        <div class="text-muted small mb-2">第三方登录</div>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php $__currentLoopData = $oauthItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if((string) data_get($connect, $key . '.status', '0') === '1'): ?>
                                    <a href="<?php echo e(route('user.oauth', ['type' => $key])); ?>" class="btn btn-outline-secondary"><?php echo e($item['label']); ?></a>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between mt-3">
                    <a href="<?php echo e(route('user.reg')); ?>" class="text-decoration-none">注册账号</a>
                    <a href="<?php echo e(route('user.findpass')); ?>" class="text-decoration-none">安全问题找回</a>
                    <a href="<?php echo e(route('user.findpass_msg')); ?>" class="text-decoration-none">验证码找回</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\user\login.blade.php ENDPATH**/ ?>