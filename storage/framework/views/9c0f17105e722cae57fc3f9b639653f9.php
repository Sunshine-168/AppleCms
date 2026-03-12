<?php
$editor = strtolower(config('maccms.app.editor', 'ueditor'));
$ue_old = public_path('static/ueditor/');
$ue_new = public_path('static/editor/' . $editor);
if ((!file_exists($ue_new) && file_exists($ue_old)) || $editor == '') {
    $editor = 'ueditor';
}
?>
<?php echo $__env->make('admin.extend.editor.' . $editor, ['flag' => $flag ?? ''], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\public\editor.blade.php ENDPATH**/ ?>