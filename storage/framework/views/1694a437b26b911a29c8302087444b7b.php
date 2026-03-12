<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <h3>Article Management</h3>

    <div class="row mb-3">
        <div class="col-md-6">
            <a href="<?php echo e(route('admin.art.info')); ?>" class="btn btn-primary">Add Article</a>
        </div>
        <div class="col-md-6 text-end">
            <form action="" method="GET" class="d-inline-flex">
                <select name="type" class="form-select me-2" style="width: 150px;">
                    <option value="">All Types</option>
                    <?php $__currentLoopData = $type_tree; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($type->type_id); ?>" <?php echo e(request('type') == $type->type_id ? 'selected' : ''); ?>>
                            <?php echo e($type->type_name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <input type="text" name="wd" class="form-control me-2" placeholder="Search name..." value="<?php echo e(request('wd')); ?>">
                <button type="submit" class="btn btn-secondary">Search</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $art): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($art->art_id); ?></td>
                        <td><?php echo e($art->type ? $art->type->type_name : 'Unknown'); ?></td>
                        <td>
                            <?php if($art->art_pic): ?>
                                <i class="bi bi-image text-success" title="Has Image"></i>
                            <?php endif; ?>
                            <a href="<?php echo e(route('admin.art.info', $art->art_id)); ?>"><?php echo e($art->art_name); ?></a>
                        </td>
                        <td>
                            <?php if($art->art_status == 1): ?>
                                <span class="badge bg-success">Enabled</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Disabled</span>
                            <?php endif; ?>
                            <?php if($art->art_lock == 1): ?>
                                <span class="badge bg-warning text-dark">Locked</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e(date('Y-m-d H:i:s', $art->art_time)); ?></td>
                        <td>
                            <a href="<?php echo e(route('admin.art.info', $art->art_id)); ?>" class="btn btn-sm btn-info">Edit</a>
                            <button onclick="delArt(<?php echo e($art->art_id); ?>)" class="btn btn-sm btn-danger">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
            
            <?php echo e($list->links()); ?>

        </div>
    </div>
</div>

<script>
function delArt(id) {
    if(confirm('Are you sure you want to delete this article?')) {
        fetch('<?php echo e(route("admin.art.del")); ?>?ids=' + id)
        .then(res => res.json())
        .then(data => {
            if(data.code == 1) location.reload();
            else alert(data.msg);
        });
    }
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\art\index.blade.php ENDPATH**/ ?>