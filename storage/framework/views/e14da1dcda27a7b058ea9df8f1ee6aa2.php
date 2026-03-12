<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <?php $__currentLoopData = $videos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $video): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <url>
        <loc><?php echo e(route('vod.detail', ['id' => $video->vod_id])); ?></loc>
        <lastmod><?php echo e(date('c', (int) ($video->vod_time ?? time()))); ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</urlset>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\rss\sitemap.blade.php ENDPATH**/ ?>