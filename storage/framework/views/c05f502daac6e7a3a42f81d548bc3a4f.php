<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <h3>Config: <?php echo e($name); ?></h3>

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
            <form action="<?php echo e(route('admin.addon.config', $name)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                
                <?php $__currentLoopData = $config; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="mb-3">
                    <label class="form-label"><?php echo e($item['title']); ?> (<?php echo e($item['name']); ?>)</label>
                    
                    <?php if($item['type'] == 'string' || $item['type'] == 'text'): ?>
                        <input type="text" class="form-control" name="row[<?php echo e($item['name']); ?>]" value="<?php echo e($item['value']); ?>">
                    <?php elseif($item['type'] == 'radio'): ?>
                        <div>
                            <?php $__currentLoopData = $item['content']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="row[<?php echo e($item['name']); ?>]" value="<?php echo e($k); ?>" <?php echo e($item['value'] == $k ? 'checked' : ''); ?>>
                                <label class="form-check-label"><?php echo e($v); ?></label>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php elseif($item['type'] == 'select'): ?>
                        <select class="form-select" name="row[<?php echo e($item['name']); ?>]">
                            <?php $__currentLoopData = $item['content']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($k); ?>" <?php echo e($item['value'] == $k ? 'selected' : ''); ?>><?php echo e($v); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    <?php endif; ?>
                    
                    <?php if(isset($item['tip'])): ?>
                    <div class="form-text"><?php echo e($item['tip']); ?></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                
                <button type="submit" class="btn btn-primary">Save Config</button>
                <a href="<?php echo e(route('admin.addon.index')); ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\addon\config.blade.php ENDPATH**/ ?>