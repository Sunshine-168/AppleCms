<?php $__env->startSection('title', '角色列表'); ?>

<?php $__env->startSection('content'); ?>
<h2 class="mb-4">角色列表</h2>

<form class="d-flex mb-4" action="<?php echo e(route('role.search')); ?>" method="GET">
    <input class="form-control me-2" type="search" name="wd" placeholder="搜索角色或演员">
    <button class="btn btn-outline-success" type="submit">搜索</button>
</form>

<div class="row">
    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="card h-100">
            <a href="<?php echo e(route('role.detail', $role->role_id)); ?>">
                <img src="<?php echo e($role->role_pic ?: asset('static/images/nopic.gif')); ?>" class="card-img-top" alt="<?php echo e($role->role_name); ?>" style="height: 220px; object-fit: cover;">
            </a>
            <div class="card-body">
                <h5 class="card-title">
                    <a href="<?php echo e(route('role.detail', $role->role_id)); ?>" class="text-decoration-none text-dark"><?php echo e($role->role_name); ?></a>
                </h5>
                <p class="card-text text-muted mb-1">演员：<?php echo e($role->role_actor ?: '-'); ?></p>
                <small class="text-muted"><?php echo e(date('Y-m-d', $role->role_time)); ?></small>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="d-flex justify-content-center mt-4">
    <?php echo e($roles->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\role\index.blade.php ENDPATH**/ ?>