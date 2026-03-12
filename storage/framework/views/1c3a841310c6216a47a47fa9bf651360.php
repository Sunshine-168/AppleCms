<script type="text/javascript" src="<?php echo e(asset('static')); ?>/editor/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
    var EDITOR = CKEDITOR;
</script>
<script>
    var editor = "<?php echo e($editor); ?>";
    function editor_getEditor(obj)
    {
        return CKEDITOR.replace(obj,{filebrowserImageUploadUrl:"<?php echo e(route('admin.upload.upload')); ?>?from=ckeditor&flag=<?php echo e($cl|strtolower); ?>_editor&input=upload"});
    }
    function editor_setContent(obj,html)
    {
        return obj.setData(html);
    }
    function editor_getContent(obj)
    {
        return obj.getData();
    }
</script>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\extend\editor\ckeditor.blade.php ENDPATH**/ ?>