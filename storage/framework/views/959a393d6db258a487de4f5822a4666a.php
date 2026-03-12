<script type="text/javascript" src="<?php echo e(asset('static')); ?>/editor/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
    var EDITOR = tinymce;
</script>
<script>
    var editor = "<?php echo e($editor); ?>";
    function editor_getEditor(obj)
    {
        tinyMCE.init({
            language: 'zh_CN',
            plugins: 'print preview paste importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern noneditable help charmap quickbars emoticons',
            menubar: 'file edit view insert format tools table help',
            toolbar: 'undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media template link anchor codesample | ltr rtl',
            toolbar_sticky: true,
            images_upload_url: "<?php echo e(route('admin.upload.upload')); ?>?from=tinymce&flag=<?php echo e($cl|strtolower); ?>_editor&input=file",
            selector: '#'+obj,
            init_instance_callback : function(editor) {
                res = editor;
            }
        });
        return tinyMCE.activeEditor;
    }
    function editor_setContent(obj,html)
    {
        return obj.setContent(html);
    }
    function editor_getContent(obj)
    {
        return obj.getContent();
    }
	$(document).on('click', '[lay-filter="formSubmit"]', function() {
		tinyMCE.triggerSave();
	});
</script>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\extend\editor\tinymce.blade.php ENDPATH**/ ?>