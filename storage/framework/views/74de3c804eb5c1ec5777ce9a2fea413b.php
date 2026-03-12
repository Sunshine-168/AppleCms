<?php $__env->startSection('title', 'Guestbook'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-8 offset-md-2">
        <h2 class="mb-4">Guestbook</h2>

        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <form action="<?php echo e(route('gbook.save')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <textarea class="form-control" name="gbook_content" rows="3" placeholder="Leave a message..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>

        <div class="list-group">
            <?php $__currentLoopData = $gbooks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gbook): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="list-group-item">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1"><?php echo e($gbook->gbook_name); ?></h5>
                    <small><?php echo e(date('Y-m-d H:i', $gbook->gbook_time)); ?></small>
                </div>
                <p class="mb-1"><?php echo e($gbook->gbook_content); ?></p>
                <?php if($gbook->gbook_reply): ?>
                    <div class="alert alert-secondary mt-2">
                        <strong>Reply:</strong> <?php echo e($gbook->gbook_reply); ?>

                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="d-flex justify-content-center mt-4">
            <?php echo e($gbooks->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\gbook\index.blade.php ENDPATH**/ ?>