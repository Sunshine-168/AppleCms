<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title><?php echo e(__('install.title')); ?></title>
    <link rel="stylesheet" href="<?php echo e(asset('static/layui/css/layui.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('static/css/admin_style.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('static/css/install.css')); ?>">
    <script type="text/javascript" src="<?php echo e(asset('static/layui/layui.js')); ?>"></script>
    <script>
        var ROOT_PATH = "<?php echo e(url('/')); ?>", ADMIN_PATH = "<?php echo e(request()->getPathInfo()); ?>";
    </script>
</head>
<body>
<div class="header">
    <h1><?php echo e(__('install.header')); ?> <?php echo e(__('install.header1')); ?></h1>
</div>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\install\head.blade.php ENDPATH**/ ?>