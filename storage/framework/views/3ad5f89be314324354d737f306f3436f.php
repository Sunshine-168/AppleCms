<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h3>Actor Management</h3>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?php echo e(route('admin.actor.info')); ?>" class="btn btn-primary">Add Actor</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" class="row g-3 mb-4">
                <div class="col-auto">
                    <select name="type" class="form-select">
                        <option value="">All Categories</option>
                        <?php $__currentLoopData = $type_tree; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($type->type_id); ?>" <?php echo e(request('type') == $type->type_id ? 'selected' : ''); ?>>
                                <?php echo e($type->type_name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-auto">
                    <input type="text" name="wd" class="form-control" placeholder="Search name..." value="<?php echo e(request('wd')); ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary">Search</button>
                </div>
            </form>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Sex</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $actor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($actor->actor_id); ?></td>
                        <td><?php echo e($actor->actor_name); ?></td>
                        <td><?php echo e($actor->actor_sex); ?></td>
                        <td><?php echo e($actor->type ? $actor->type->type_name : '-'); ?></td>
                        <td>
                            <span class="badge <?php echo e($actor->actor_status == 1 ? 'bg-success' : 'bg-secondary'); ?>">
                                <?php echo e($actor->status_text); ?>

                            </span>
                        </td>
                        <td><?php echo e(date('Y-m-d H:i', $actor->actor_time)); ?></td>
                        <td>
                            <a href="<?php echo e(route('admin.actor.info', $actor->actor_id)); ?>" class="btn btn-sm btn-info">Edit</a>
                            <button onclick="delActor(<?php echo e($actor->actor_id); ?>)" class="btn btn-sm btn-danger">Delete</button>
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
function delActor(id) {
    if(confirm('Are you sure?')) {
        // Implement AJAX delete
        fetch('<?php echo e(route("admin.actor.del")); ?>?ids=' + id)
        .then(res => res.json())
        .then(data => {
            if(data.code == 1) location.reload();
            else alert(data.msg);
        });
    }
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\actor\index.blade.php ENDPATH**/ ?>