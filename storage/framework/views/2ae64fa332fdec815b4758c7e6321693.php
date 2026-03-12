<?php $__env->startSection('title', 'Search Topic: ' . $wd); ?>

<?php $__env->startSection('content'); ?>
<h2 class="mb-4">Search Results for: "<?php echo e($wd); ?>"</h2>

<form class="d-flex mb-4" action="<?php echo e(route('topic.search')); ?>" method="GET">
    <input class="form-control me-2" type="search" name="wd" value="<?php echo e($wd); ?>" placeholder="Search Topic" aria-label="Search">
    <button class="btn btn-outline-success" type="submit">Search</button>
</form>

<?php if($topics->isEmpty()): ?>
    <div class="alert alert-warning">No topics found.</div>
<?php else: ?>
    <div class="row">
        <?php $__currentLoopData = $topics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $topic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card h-100">
                <a href="<?php echo e(route('topic.detail', $topic->topic_id)); ?>">
                    <img src="<?php echo e($topic->topic_pic); ?>" class="card-img-top" alt="<?php echo e($topic->topic_name); ?>" style="height: 180px; object-fit: cover;">
                </a>
                <div class="card-body">
                    <h5 class="card-title">
                        <a href="<?php echo e(route('topic.detail', $topic->topic_id)); ?>" class="text-decoration-none text-dark"><?php echo e($topic->topic_name); ?></a>
                    </h5>
                    <p class="card-text text-muted"><?php echo e(Str::limit(strip_tags($topic->topic_blurb), 80)); ?></p>
                    <small class="text-muted"><?php echo e(date('Y-m-d', $topic->topic_time)); ?></small>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="d-flex justify-content-center mt-4">
        <?php echo e($topics->links()); ?>

    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\topic\search.blade.php ENDPATH**/ ?>