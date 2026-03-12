<body style="margin:0px;padding:0px;text-align:left">
<form name="form" enctype="multipart/form-data" action="<?php echo e(route('admin.upload.upload')); ?>" method="post">
    <input type="hidden" name="path" value="<?php echo e($path); ?>">
    <input type="hidden" name="id" value="<?php echo e($id); ?>">
    <input type="file" id="file1" name="file1" style="width: 200px; height: 25px;">
    <input type="submit" name="submit" class="input" value="<?php echo e(lang('upload')); ?>">
</form>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\upload\index.blade.php ENDPATH**/ ?>