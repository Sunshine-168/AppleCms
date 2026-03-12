<?php $__env->startSection('title', 'Search Actor: ' . $wd); ?>

<?php $__env->startSection('content'); ?>
<h2 class="mb-4">Search Results for: "<?php echo e($wd); ?>"</h2>

<form class="d-flex mb-4" action="<?php echo e(route('actor.search')); ?>" method="GET">
    <input class="form-control me-2" type="search" name="wd" value="<?php echo e($wd); ?>" placeholder="Search Actor" aria-label="Search">
    <button class="btn btn-outline-success" type="submit">Search</button>
</form>

<?php if($actors->isEmpty()): ?>
    <div class="alert alert-warning">No actors found.</div>
<?php else: ?>
    <div class="row">
        <?php $__currentLoopData = $actors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $actor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-md-2 col-sm-4 mb-4">
            <div class="card h-100">
                <a href="<?php echo e(route('actor.detail', $actor->actor_id)); ?>">
                    <img src="<?php echo e($actor->actor_pic); ?>" class="card-img-top" alt="<?php echo e($actor->actor_name); ?>" style="height: 200px; object-fit: cover;">
                </a>
                <div class="card-body text-center">
                    <h5 class="card-title">
                        <a href="<?php echo e(route('actor.detail', $actor->actor_id)); ?>" class="text-decoration-none text-dark"><?php echo e($actor->actor_name); ?></a>
                    </h5>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="d-flex justify-content-center mt-4">
        <?php echo e($actors->links()); ?>

    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\actor\search.blade.php ENDPATH**/ ?>