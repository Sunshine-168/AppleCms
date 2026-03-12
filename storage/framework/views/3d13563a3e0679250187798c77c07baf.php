<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <h3>File Explorer</h3>
    
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                Current Path: <strong><?php echo e($path); ?></strong>
                <?php if($path != 'upload'): ?>
                    <a href="<?php echo e(route('admin.annex.file', ['path' => $upPath])); ?>" class="btn btn-sm btn-secondary ms-2">Go Up</a>
                <?php endif; ?>
            </div>
            <div>
                <span class="badge bg-info">Dirs: <?php echo e($num_path); ?></span>
                <span class="badge bg-primary">Files: <?php echo e($num_file); ?></span>
                <span class="badge bg-success">Size: <?php echo e($sum_size); ?></span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Size</th>
                        <th>Modified Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                            <?php if($file['isfile']): ?>
                                <i class="bi bi-file-earmark"></i>
                                <a href="<?php echo e(asset($file['path'])); ?>" target="_blank"><?php echo e($file['name']); ?></a>
                            <?php else: ?>
                                <i class="bi bi-folder-fill text-warning"></i>
                                <a href="<?php echo e(route('admin.annex.file', ['path' => $file['path']])); ?>"><?php echo e($file['name']); ?></a>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e(isset($file['size']) ? $file['size'] : '-'); ?></td>
                        <td><?php echo e(date('Y-m-d H:i:s', $file['time'])); ?></td>
                        <td>
                            <!-- File actions placeholder -->
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\annex\file.blade.php ENDPATH**/ ?>