<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($title ?? '后台管理'); ?> - <?php echo e(__('admin.admin/public/head/title')); ?></title>
    <link rel="stylesheet" href="<?php echo e(asset('static/layui/css/layui.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('static/css/admin_style.css')); ?>?v=<?php echo e(config('maccms.version.code', '10')); ?>">
    <script type="text/javascript" src="<?php echo e(asset('static/js/jquery.js')); ?>"></script>
    <script type="text/javascript" src="<?php echo e(asset('static/layui/layui.js')); ?>"></script>
    <script>
        var ROOT_PATH="<?php echo e(url('/')); ?>", ADMIN_PATH="<?php echo e(url('admin')); ?>", MAC_VERSION="v10";
    </script>
</head>
<body>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views/admin/public/head.blade.php ENDPATH**/ ?>