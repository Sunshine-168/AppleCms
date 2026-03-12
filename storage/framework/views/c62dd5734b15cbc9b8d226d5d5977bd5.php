<?php $__env->startSection('title', '头像上传'); ?>

<?php $__env->startSection('user_content'); ?>
<div class="card">
    <div class="card-header">头像上传</div>
    <div class="card-body">
        <?php if($user->user_portrait): ?>
            <div class="mb-3">
                <img src="<?php echo e(asset($user->user_portrait)); ?>" alt="portrait" style="max-width: 120px;">
            </div>
        <?php endif; ?>
        <form method="post" action="<?php echo e(route('user.portrait')); ?>" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <div class="mb-3">
                <input type="file" class="form-control" name="file">
            </div>
            <button type="submit" class="btn btn-primary">上传头像</button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\user\portrait.blade.php ENDPATH**/ ?>