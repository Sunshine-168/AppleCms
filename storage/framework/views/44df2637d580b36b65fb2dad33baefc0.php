<?php $__env->startSection('title', 'My Logs'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="list-group">
                <a href="<?php echo e(route('user.index')); ?>" class="list-group-item list-group-item-action">Profile</a>
                <a href="<?php echo e(route('user.ulog', ['type' => 1])); ?>" class="list-group-item list-group-item-action <?php echo e(request('type') == 1 ? 'active' : ''); ?>">Browse History</a>
                <a href="<?php echo e(route('user.ulog', ['type' => 2])); ?>" class="list-group-item list-group-item-action <?php echo e(request('type') == 2 ? 'active' : ''); ?>">Favorites</a>
                <a href="<?php echo e(route('user.ulog', ['type' => 4])); ?>" class="list-group-item list-group-item-action <?php echo e(request('type') == 4 ? 'active' : ''); ?>">Play History</a>
                <a href="<?php echo e(route('user.ulog', ['type' => 5])); ?>" class="list-group-item list-group-item-action <?php echo e(request('type') == 5 ? 'active' : ''); ?>">Download History</a>
                <a href="/user/logout" class="list-group-item list-group-item-action text-danger">Logout</a>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Logs</span>
                    <button class="btn btn-sm btn-danger" onclick="clearLogs()">Clear All</button>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $item = $log->data;
                            ?>
                            <?php if($item): ?>
                                <a href="<?php echo e($log->ulog_mid == 1 ? route('vod.detail', $item->vod_id) : route('art.detail', $item->art_id)); ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1"><?php echo e($log->ulog_mid == 1 ? $item->vod_name : $item->art_name); ?></h5>
                                        <small><?php echo e(date('Y-m-d H:i', $log->ulog_time)); ?></small>
                                    </div>
                                    <small class="text-muted"><?php echo e($log->ulog_mid == 1 ? 'Video' : 'Article'); ?></small>
                                </a>
                            <?php else: ?>
                                <div class="list-group-item">Content deleted</div>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="list-group-item">No logs found.</div>
                        <?php endif; ?>
                    </div>
                    <div class="mt-3">
                        <?php echo e($logs->links()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="clear-form" action="<?php echo e(route('user.ulog.delete')); ?>" method="POST" style="display: none;">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="type" value="<?php echo e(request('type')); ?>">
    <input type="hidden" name="all" value="1">
</form>

<script>
    function clearLogs() {
        if(confirm('Are you sure you want to clear all logs of this type?')) {
            document.getElementById('clear-form').submit();
        }
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\user\ulog.blade.php ENDPATH**/ ?>