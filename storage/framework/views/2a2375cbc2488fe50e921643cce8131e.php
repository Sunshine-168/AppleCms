<?php $__env->startSection('title', '解除绑定'); ?>

<?php $__env->startSection('user_content'); ?>
<div class="card">
    <div class="card-header">解除绑定</div>
    <div class="card-body">
        <p class="text-muted">当前邮箱：<?php echo e($user->user_email ?: '未绑定'); ?>，当前手机：<?php echo e($user->user_phone ?: '未绑定'); ?></p>
        <form method="post" action="<?php echo e(route('user.unbind')); ?>">
            <?php echo csrf_field(); ?>
            <div class="mb-3">
                <select class="form-select" name="ac">
                    <option value="email">邮箱</option>
                    <option value="phone">手机</option>
                </select>
            </div>
            <button type="submit" class="btn btn-danger">解除绑定</button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\user\unbind.blade.php ENDPATH**/ ?>