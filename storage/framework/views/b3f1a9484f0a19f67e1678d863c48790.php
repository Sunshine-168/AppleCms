<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <h3><?php echo e($info->actor_id ? 'Edit Actor' : 'Add Actor'); ?></h3>

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
            <form action="<?php echo e(route('admin.actor.info.save', $info->actor_id)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="mb-3">
                    <label for="actor_name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="actor_name" name="actor_name" value="<?php echo e(old('actor_name', $info->actor_name)); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="type_id" class="form-label">Category</label>
                    <select class="form-select" id="type_id" name="type_id">
                        <option value="">Select Category</option>
                        <?php $__currentLoopData = $type_tree; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($type->type_id); ?>" <?php echo e($info->type_id == $type->type_id ? 'selected' : ''); ?>>
                                <?php echo e($type->type_name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="actor_sex" class="form-label">Sex</label>
                    <select class="form-select" id="actor_sex" name="actor_sex">
                        <option value="男" <?php echo e($info->actor_sex == '男' ? 'selected' : ''); ?>>Male</option>
                        <option value="女" <?php echo e($info->actor_sex == '女' ? 'selected' : ''); ?>>Female</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="actor_pic" class="form-label">Picture URL</label>
                    <input type="text" class="form-control" id="actor_pic" name="actor_pic" value="<?php echo e(old('actor_pic', $info->actor_pic)); ?>">
                </div>
                
                <div class="mb-3">
                    <label for="actor_content" class="form-label">Content</label>
                    <textarea class="form-control" id="actor_content" name="actor_content" rows="3"><?php echo e(old('actor_content', $info->actor_content)); ?></textarea>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="actor_status" name="actor_status" value="1" <?php echo e($info->actor_status == 1 ? 'checked' : ''); ?>>
                    <label class="form-check-label" for="actor_status">Enabled</label>
                </div>
                
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="<?php echo e(route('admin.actor.index')); ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\actor\info.blade.php ENDPATH**/ ?>