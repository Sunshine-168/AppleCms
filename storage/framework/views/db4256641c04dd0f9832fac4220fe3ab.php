<?php $__env->startSection('title', '会员升级'); ?>

<?php $__env->startSection('user_content'); ?>
<div class="card">
    <div class="card-header">会员升级</div>
    <div class="card-body">
        <p>当前积分：<?php echo e($user->user_points); ?></p>
        <form method="post" action="<?php echo e(route('user.upgrade')); ?>">
            <?php echo csrf_field(); ?>
            <div class="mb-3">
                <label class="form-label">会员组</label>
                <select class="form-select" name="group_id">
                    <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($group->group_id); ?>"><?php echo e($group->group_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">时长</label>
                <select class="form-select" name="long">
                    <option value="day">天</option>
                    <option value="week">周</option>
                    <option value="month">月</option>
                    <option value="year">年</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">升级</button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\user\upgrade.blade.php ENDPATH**/ ?>