<?php $__env->startSection('title', '在线充值'); ?>

<?php $__env->startSection('user_content'); ?>
<div class="card mb-4">
    <div class="card-header">创建充值订单</div>
    <div class="card-body">
        <form method="post" action="<?php echo e(route('user.buy')); ?>">
            <?php echo csrf_field(); ?>
            <div class="mb-3">
                <label class="form-label">充值金额</label>
                <input type="number" step="0.01" min="0" class="form-control" name="price">
            </div>
            <button type="submit" class="btn btn-primary">创建订单</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">充值卡兑换</div>
    <div class="card-body">
        <form method="post" action="<?php echo e(route('user.buy')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="flag" value="card">
            <div class="mb-3">
                <label class="form-label">卡号</label>
                <input type="text" class="form-control" name="card_no">
            </div>
            <div class="mb-3">
                <label class="form-label">卡密</label>
                <input type="text" class="form-control" name="card_pwd">
            </div>
            <button type="submit" class="btn btn-outline-primary">兑换</button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\user\buy.blade.php ENDPATH**/ ?>