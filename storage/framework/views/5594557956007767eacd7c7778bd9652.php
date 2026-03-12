<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', config('maccms.site.site_name')); ?></title>
    <meta name="keywords" content="<?php echo $__env->yieldContent('keywords', config('maccms.site.site_keywords')); ?>">
    <meta name="description" content="<?php echo $__env->yieldContent('description', config('maccms.site.site_description')); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .vod-item { margin-bottom: 20px; }
        .vod-item img { width: 100%; height: 200px; object-fit: cover; border-radius: 5px; }
        .vod-title { margin-top: 10px; font-weight: bold; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="/"><?php echo e(config('maccms.site.site_name')); ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo e(route('vod.index')); ?>">All Videos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo e(route('art.index')); ?>">Articles</a>
                    </li>
                    <?php if(isset($types)): ?>
                        <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo e(route('vod.type', $type->type_id)); ?>"><?php echo e($type->type_name); ?></a>
                        </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                    <?php if(Auth::check()): ?>
                        <li class="nav-item"><a class="nav-link" href="/user/index">Profile</a></li>
                        <li class="nav-item"><a class="nav-link" href="/user/logout">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="/user/login">Login</a></li>
                    <?php endif; ?>
                </ul>
                <form class="d-flex" action="<?php echo e(route('vod.search')); ?>" method="GET">
                    <input class="form-control me-2" type="search" name="wd" placeholder="Search" aria-label="Search">
                    <button class="btn btn-outline-success" type="submit">Search</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php echo $__env->yieldContent('content'); ?>
    </div>

    <footer class="bg-light text-center text-lg-start mt-5">
        <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.2);">
            © <?php echo e(date('Y')); ?> <?php echo e(config('maccms.site.site_name')); ?>

        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\layouts\front.blade.php ENDPATH**/ ?>