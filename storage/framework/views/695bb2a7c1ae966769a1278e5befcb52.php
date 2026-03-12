<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <h3>Annex Management</h3>

    <div class="row mb-3">
        <div class="col-md-6">
            <a href="<?php echo e(route('admin.annex.file')); ?>" class="btn btn-primary">File Explorer</a>
        </div>
        <div class="col-md-6 text-end">
            <form action="" method="GET" class="d-inline-flex">
                <input type="text" name="wd" class="form-control me-2" placeholder="Search file..." value="<?php echo e(request('wd')); ?>">
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
                        <th>File Name</th>
                        <th>Type</th>
                        <th>Size</th>
                        <th>Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($item->annex_id); ?></td>
                        <td>
                            <a href="<?php echo e(asset($item->annex_file)); ?>" target="_blank"><?php echo e($item->annex_file); ?></a>
                        </td>
                        <td><?php echo e($item->annex_type); ?></td>
                        <td><?php echo e($item->annex_size); ?></td>
                        <td><?php echo e(date('Y-m-d H:i:s', $item->annex_time)); ?></td>
                        <td>
                            <button onclick="delAnnex(<?php echo e($item->annex_id); ?>)" class="btn btn-sm btn-danger">Delete</button>
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
function delAnnex(id) {
    if(confirm('Are you sure you want to delete this record?')) {
        fetch('<?php echo e(route("admin.annex.del")); ?>?ids=' + id)
        .then(res => res.json())
        .then(data => {
            if(data.code == 1) location.reload();
            else alert(data.msg);
        });
    }
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\annex\index.blade.php ENDPATH**/ ?>