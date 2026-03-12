<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(__('admin/index/login/title')); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f5f5f5;
            <?php if(!empty($background)): ?>
            background-image: url('<?php echo e($background); ?>');
            background-size: cover;
            background-position: center;
            <?php endif; ?>
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="login-card">
    <h3 class="text-center mb-4"><?php echo e(__('admin/index/login/tip_sys')); ?></h3>
    
    <?php if($errors->any()): ?>
        <?php
            $translatedErrors = collect($errors->all())->map(function ($error) {
                if ($error === 'Invalid credentials') {
                    return __('admin/index/login/error_invalid');
                }
                if ($error === 'Account disabled') {
                    return __('admin/index/login/error_disabled');
                }
                return $error;
            });
        ?>
        <div class="alert alert-danger">
            <div class="fw-semibold mb-1"><?php echo e(__('admin/index/login/error_title')); ?></div>
            <div class="small">
                <?php $__currentLoopData = $translatedErrors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('admin.login')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <div class="mb-3">
            <label for="admin_name" class="form-label"><?php echo e(__('admin/index/login/filed_no')); ?></label>
            <input type="text" class="form-control" id="admin_name" name="admin_name" value="<?php echo e(old('admin_name')); ?>" autocomplete="username" autofocus required>
        </div>
        <div class="mb-3">
            <label for="admin_pwd" class="form-label"><?php echo e(__('admin/index/login/filed_pass')); ?></label>
            <input type="password" class="form-control" id="admin_pwd" name="admin_pwd" autocomplete="current-password" required>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary"><?php echo e(__('admin/index/login/btn_submit')); ?></button>
        </div>
    </form>
    
    <div class="text-center mt-3 text-muted">
        <small><?php echo e(__('admin/index/login/tip_welcome')); ?></small>
    </div>
</div>

</body>
</html>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\login.blade.php ENDPATH**/ ?>