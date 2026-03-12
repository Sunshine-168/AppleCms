<script type="text/javascript" src="<?php echo e(asset('static')); ?>/ueditor/ueditor.config.js"></script>
<script type="text/javascript" src="<?php echo e(asset('static')); ?>/ueditor/ueditor.all.min.js"></script>
<script type="text/javascript">
    window.UEDITOR_CONFIG.serverUrl = "<?php echo e(route('admin.upload.upload')); ?>?from=ueditor&flag=<?php echo e($cl|strtolower); ?>_editor&input=upfile";
    var EDITOR = UE;
</script>
<script>
    var editor = "<?php echo e($editor); ?>";
    function editor_getEditor(obj)
    {
        return EDITOR.getEditor(obj);
    }
    function editor_setContent(obj,html)
    {
        return obj.setContent(html);
    }
    function editor_getContent(obj)
    {
        return obj.getContent();
    }
</script>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\extend\editor\ueditor.blade.php ENDPATH**/ ?>