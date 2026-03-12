<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(config('maccms.site.site_name', 'SiteMap')); ?> - SiteMap</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #333; }
        h1, h2 { margin-bottom: 12px; }
        .section { margin-bottom: 24px; }
        .links { display: flex; flex-wrap: wrap; gap: 10px 18px; }
        .links a { color: #1e9fff; text-decoration: none; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1><?php echo e(config('maccms.site.site_name', 'SiteMap')); ?></h1>

    <div class="section">
        <h2>视频分类</h2>
        <div class="links">
            <?php $__currentLoopData = $vodTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('vod.type', ['id' => $type->type_id])); ?>"><?php echo e($type->type_name); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <div class="section">
        <h2>文章分类</h2>
        <div class="links">
            <?php $__currentLoopData = $artTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('art.type', ['id' => $type->type_id])); ?>"><?php echo e($type->type_name); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <div class="section">
        <h2>专题</h2>
        <div class="links">
            <?php $__currentLoopData = $topics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $topic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('topic.detail', ['id' => $topic->topic_id])); ?>"><?php echo e($topic->topic_name); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <div class="section">
        <h2>最新视频</h2>
        <div class="links">
            <?php $__currentLoopData = $videos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $video): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('vod.detail', ['id' => $video->vod_id])); ?>"><?php echo e($video->vod_name); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <div class="section">
        <h2>最新文章</h2>
        <div class="links">
            <?php $__currentLoopData = $articles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('art.detail', ['id' => $article->art_id])); ?>"><?php echo e($article->art_name); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</body>
</html>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\map\index.blade.php ENDPATH**/ ?>