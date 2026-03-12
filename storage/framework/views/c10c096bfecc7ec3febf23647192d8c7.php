<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <h3>Addon Management</h3>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Author</th>
                        <th>Version</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $localAddons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $addon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($addon['name']); ?></td>
                        <td><?php echo e($addon['title']); ?></td>
                        <td><?php echo e($addon['intro']); ?></td>
                        <td><?php echo e($addon['author']); ?></td>
                        <td><?php echo e($addon['version']); ?></td>
                        <td>
                            <?php if($addon['state'] == 1): ?>
                                <span class="badge bg-success">Enabled</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Disabled</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo e(route('admin.addon.config', $addon['name'])); ?>" class="btn btn-sm btn-info">Config</a>
                            <?php if($addon['state'] == 1): ?>
                                <button onclick="changeState('<?php echo e($addon['name']); ?>', 'disable')" class="btn btn-sm btn-warning">Disable</button>
                            <?php else: ?>
                                <button onclick="changeState('<?php echo e($addon['name']); ?>', 'enable')" class="btn btn-sm btn-success">Enable</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="text-center">No addons found. Place addons in the <code>addons/</code> directory.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function changeState(name, action) {
    if(confirm('Are you sure you want to ' + action + ' this addon?')) {
        fetch('<?php echo e(route("admin.addon.state")); ?>?name=' + name + '&action=' + action)
        .then(res => res.json())
        .then(data => {
            if(data.code == 1) location.reload();
            else alert(data.msg);
        });
    }
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\addon\index.blade.php ENDPATH**/ ?>