<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <h3><?php echo e($info->admin_id ? 'Edit Admin' : 'Add Admin'); ?></h3>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form action="<?php echo e(route('admin.admin.info', $info->admin_id)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="mb-3">
                    <label for="admin_name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="admin_name" name="admin_name" value="<?php echo e(old('admin_name', $info->admin_name)); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="admin_pwd" class="form-label">Password</label>
                    <input type="password" class="form-control" id="admin_pwd" name="admin_pwd" placeholder="<?php echo e($info->admin_id ? 'Leave blank to keep unchanged' : 'Required'); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="admin_status" id="status1" value="1" <?php echo e($info->admin_status == 1 ? 'checked' : ''); ?>>
                        <label class="form-check-label" for="status1">Enabled</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="admin_status" id="status0" value="0" <?php echo e($info->admin_status == 0 ? 'checked' : ''); ?>>
                        <label class="form-check-label" for="status0">Disabled</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Permissions</label>
                    <?php $__currentLoopData = $menus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupName => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="card mb-2">
                            <div class="card-header"><?php echo e($groupName); ?></div>
                            <div class="card-body">
                                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="admin_auth[]" value="<?php echo e($key); ?>" 
                                            <?php echo e(strpos($info->admin_auth, ",$key,") !== false ? 'checked' : ''); ?>>
                                        <label class="form-check-label"><?php echo e($label); ?></label>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <button type="submit" class="btn btn-primary">Save</button>
                <a href="<?php echo e(route('admin.admin.index')); ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\admin\info.blade.php ENDPATH**/ ?>