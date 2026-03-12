<link rel="stylesheet" href="{{ asset('static') }}/editor/umeditor/themes/default/css/umeditor.css" type="text/css" rel="stylesheet">
<script type="text/javascript" src="{{ asset('static') }}/editor/umeditor/umeditor.config.js"></script>
<script type="text/javascript" src="{{ asset('static') }}/editor/umeditor/umeditor.min.js"></script>
<script type="text/javascript">
    window.UMEDITOR_CONFIG.imageUrl = "{{ route('admin.upload.upload') }}?from=umeditor&flag={{ $cl|strtolower }}_editor&input=upfile";
    var EDITOR = UM;
</script>
<script>
    var editor = "{{ $editor }}";
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
