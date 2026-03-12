<?php $__env->startSection('title', $video->vod_name . ' - ' . $currentEpisode['name']); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-9">
        <h3><?php echo e($video->vod_name); ?> - <?php echo e($currentEpisode['name']); ?></h3>
        
        <div class="ratio ratio-16x9 bg-dark">
            <iframe src="<?php echo e($currentEpisode['url']); ?>" title="<?php echo e($video->vod_name); ?>" allowfullscreen></iframe>
        </div>
        
        <div class="mt-3">
            <h4>Description</h4>
            <p><?php echo e(strip_tags($video->vod_content)); ?></p>
        </div>
    </div>
    <div class="col-md-3">
        <h4>Episodes</h4>
        <?php $__currentLoopData = $playList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pIndex => $player): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="mb-3">
            <h5><?php echo e($player['player_name']); ?></h5>
            <div class="list-group">
                <?php $__currentLoopData = $player['urls']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $eIndex => $episode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('vod.play', ['id' => $video->vod_id, 'sid' => $pIndex + 1, 'nid' => $eIndex + 1])); ?>" 
                   class="list-group-item list-group-item-action <?php echo e(($sid == $pIndex + 1 && $nid == $eIndex + 1) ? 'active' : ''); ?>">
                   <?php echo e($episode['name']); ?>

                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\vod\play.blade.php ENDPATH**/ ?>