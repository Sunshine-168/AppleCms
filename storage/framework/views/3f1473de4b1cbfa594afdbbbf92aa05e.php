<div class="p-3">
    <?php
        $loginVerify = (string) config('maccms.user.login_verify', '0') === '1';
        $connect = (array) config('maccms.connect', []);
        $oauthItems = [
            'qq' => ['label' => 'QQ登录'],
            'weixin' => ['label' => '微信登录'],
        ];
    ?>
    <form class="mac_login_form">
        <?php echo csrf_field(); ?>
        <div class="mb-3">
            <label class="form-label">用户名</label>
            <input type="text" class="form-control" name="user_name">
        </div>
        <div class="mb-3">
            <label class="form-label">密码</label>
            <input type="password" class="form-control" name="user_pwd">
        </div>
        <?php if($loginVerify): ?>
            <div class="mb-3">
                <label class="form-label">验证码</label>
                <div class="d-flex gap-2">
                    <input type="text" class="form-control" name="verify">
                    <img
                        src="<?php echo e(route('verify.index')); ?>"
                        alt="verify"
                        style="width: 120px; height: 40px; cursor: pointer;"
                        onclick="this.src='<?php echo e(route('verify.index')); ?>?t=' + Date.now()"
                    >
                </div>
            </div>
        <?php endif; ?>
        <button type="button" class="btn btn-primary login_form_submit">登录</button>
    </form>
    <?php if(collect($oauthItems)->contains(fn ($item, $key) => (string) data_get($connect, $key . '.status', '0') === '1')): ?>
        <div class="mt-3 pt-3 border-top">
            <div class="text-muted small mb-2">第三方登录</div>
            <div class="d-flex gap-2 flex-wrap">
                <?php $__currentLoopData = $oauthItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if((string) data_get($connect, $key . '.status', '0') === '1'): ?>
                        <a href="<?php echo e(route('user.oauth', ['type' => $key])); ?>" class="btn btn-outline-secondary btn-sm"><?php echo e($item['label']); ?></a>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\user\ajax_login.blade.php ENDPATH**/ ?>