<?php $__env->startSection('title', '用户中心'); ?>

<?php $__env->startSection('user_content'); ?>
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div class="me-3">
                <img
                    src="<?php echo e(str_starts_with((string) $user->user_portrait, 'http') ? $user->user_portrait : ($user->user_portrait ? asset($user->user_portrait) : asset('static_new/images/touxiang.png'))); ?>"
                    alt="portrait"
                    class="rounded-circle border"
                    style="width: 84px; height: 84px; object-fit: cover;"
                >
            </div>
            <div>
                <h4 class="mb-1"><?php echo e($user->user_nick_name ?: $user->user_name); ?></h4>
                <div class="text-muted">账号：<?php echo e($user->user_name); ?></div>
                <div class="text-muted">会员组：<?php echo e($user->group->group_name ?? '游客'); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">账户信息</div>
            <div class="card-body">
                <p class="mb-2"><strong>积分：</strong><?php echo e($user->user_points); ?></p>
                <p class="mb-2"><strong>冻结积分：</strong><?php echo e($user->user_points_froze ?? 0); ?></p>
                <p class="mb-2"><strong>到期时间：</strong><?php echo e(!empty($user->user_end_time) ? date('Y-m-d H:i:s', $user->user_end_time) : '未设置'); ?></p>
                <p class="mb-0"><strong>累计登录：</strong><?php echo e($user->user_login_num); ?> 次</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">最近登录</div>
            <div class="card-body">
                <p class="mb-2"><strong>本次登录时间：</strong><?php echo e(!empty($user->user_login_time) ? date('Y-m-d H:i:s', $user->user_login_time) : '-'); ?></p>
                <p class="mb-2"><strong>本次登录 IP：</strong><?php echo e(!empty($user->user_login_ip) ? long2ip((int) $user->user_login_ip) : '-'); ?></p>
                <p class="mb-2"><strong>上次登录时间：</strong><?php echo e(!empty($user->user_last_login_time) ? date('Y-m-d H:i:s', $user->user_last_login_time) : '-'); ?></p>
                <p class="mb-0"><strong>上次登录 IP：</strong><?php echo e(!empty($user->user_last_login_ip) ? long2ip((int) $user->user_last_login_ip) : '-'); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">快捷入口</div>
    <div class="card-body d-flex flex-wrap gap-2">
        <a href="<?php echo e(route('user.info')); ?>" class="btn btn-outline-primary">修改资料</a>
        <a href="<?php echo e(route('user.portrait')); ?>" class="btn btn-outline-primary">上传头像</a>
        <a href="<?php echo e(route('user.buy')); ?>" class="btn btn-outline-primary">在线充值</a>
        <a href="<?php echo e(route('user.orders')); ?>" class="btn btn-outline-primary">订单记录</a>
        <a href="<?php echo e(route('user.plog')); ?>" class="btn btn-outline-primary">积分记录</a>
        <a href="<?php echo e(route('logout')); ?>" class="btn btn-outline-danger">退出登录</a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\user\index.blade.php ENDPATH**/ ?>