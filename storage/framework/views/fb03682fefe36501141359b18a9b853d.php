<script type="text/javascript" src="<?php echo e(asset('static')); ?>/editor/kindeditor/kindeditor-all-min.js"></script>
<script type="text/javascript">
    var EDITOR = KindEditor;
</script>
<script>
    var editor = "<?php echo e($editor); ?>";
    function editor_getEditor(obj)
    {
        return KindEditor.create('#'+obj, { uploadJson:"<?php echo e(route('admin.upload.upload')); ?>?from=kindeditor&flag=<?php echo e($cl|strtolower); ?>_editor&input=imgFile" , allowFileManager : false });
    }
    function editor_setContent(obj,html)
    {
        return obj.html(html);
    }
    function editor_getContent(obj)
    {
        return obj.html();
    }
</script>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\extend\editor\kindeditor.blade.php ENDPATH**/ ?>