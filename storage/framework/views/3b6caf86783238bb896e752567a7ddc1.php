<div class="p-3">
    <div class="text-center mb-3">
        <img
            src="<?php echo e(str_starts_with((string) $user->user_portrait, 'http') ? $user->user_portrait : ($user->user_portrait ? asset($user->user_portrait) : asset('static_new/images/touxiang.png'))); ?>"
            alt="portrait"
            class="rounded-circle border"
            style="width: 72px; height: 72px; object-fit: cover;"
        >
    </div>
    <div class="text-center mb-3">
        <div class="fw-bold"><?php echo e($user->user_nick_name ?: $user->user_name); ?></div>
        <div class="text-muted small">会员组：<?php echo e($user->group->group_name ?? '游客'); ?></div>
        <div class="text-muted small">积分：<?php echo e($user->user_points); ?></div>
    </div>
    <div class="d-grid gap-2">
        <a href="<?php echo e(route('user.index')); ?>" target="_blank" class="btn btn-primary">进入用户中心</a>
        <a href="<?php echo e(route('user.info')); ?>" target="_blank" class="btn btn-outline-secondary">修改资料</a>
        <button type="button" class="btn btn-outline-danger" onclick="MAC.User.Logout()">退出登录</button>
    </div>
</div>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\user\ajax_info.blade.php ENDPATH**/ ?>