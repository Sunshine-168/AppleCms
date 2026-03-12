<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
<channel>
    <title><![CDATA[<?php echo e(config('maccms.site.site_name', 'MacCMS')); ?>]]></title>
    <link><?php echo e(url('/')); ?></link>
    <description><![CDATA[<?php echo e(config('maccms.site.site_description', config('maccms.site.site_name', 'MacCMS'))); ?>]]></description>
    <language>zh-cn</language>
    <?php $__currentLoopData = $videos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $video): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <item>
        <title><![CDATA[<?php echo e($video->vod_name); ?>]]></title>
        <link><?php echo e(route('vod.detail', ['id' => $video->vod_id])); ?></link>
        <guid><?php echo e(route('vod.detail', ['id' => $video->vod_id])); ?></guid>
        <description><![CDATA[<?php echo e(strip_tags((string) ($video->vod_blurb ?? $video->vod_content ?? ''))); ?>]]></description>
    </item>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php $__currentLoopData = $articles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <item>
        <title><![CDATA[<?php echo e($article->art_name); ?>]]></title>
        <link><?php echo e(route('art.detail', ['id' => $article->art_id])); ?></link>
        <guid><?php echo e(route('art.detail', ['id' => $article->art_id])); ?></guid>
        <description><![CDATA[<?php echo e(strip_tags((string) ($article->art_blurb ?? $article->art_content ?? ''))); ?>]]></description>
    </item>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</channel>
</rss>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\rss\index.blade.php ENDPATH**/ ?>