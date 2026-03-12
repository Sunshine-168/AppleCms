<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta http-equiv="refresh" content="<?php echo e($wait); ?>;url=<?php echo e($url); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(__('jump')); ?></title>
    <style>
        body{margin:0;background:#f8f9fa;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Helvetica,Arial,sans-serif;color:#333}
        .wrap{max-width:640px;margin:80px auto;padding:32px;background:#fff;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.08)}
        .msg{font-size:18px;margin-bottom:16px}
        .meta{color:#666}
        a{color:#0d6efd;text-decoration:none}
        a:hover{text-decoration:underline}
    </style>
</head>
<body>
<div class="wrap">
    <div class="msg"><?php echo e($msg); ?></div>
    <div class="meta">
        <?php echo e(__('pause')); ?> <?php echo e($wait); ?> <?php echo e(__('continue_in_second') ?? '秒后继续'); ?>

        <a href="<?php echo e($url); ?>"><?php echo e(__('browser_jump') ?? '立即跳转'); ?></a>
    </div>
</div>
</body>
</html>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\public\jump.blade.php ENDPATH**/ ?>