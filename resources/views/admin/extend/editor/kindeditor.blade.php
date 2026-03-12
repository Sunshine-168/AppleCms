<script type="text/javascript" src="{{ asset('static') }}/editor/kindeditor/kindeditor-all-min.js"></script>
<script type="text/javascript">
    var EDITOR = KindEditor;
</script>
<script>
    var editor = "{{ $editor }}";
    function editor_getEditor(obj)
    {
        return KindEditor.create('#'+obj, { uploadJson:"{{ route('admin.upload.upload') }}?from=kindeditor&flag={{ $cl|strtolower }}_editor&input=imgFile" , allowFileManager : false });
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
