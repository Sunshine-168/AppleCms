@php
$editor = strtolower(config('maccms.app.editor', 'ueditor'));
$ue_old = public_path('static/ueditor/');
$ue_new = public_path('static/editor/' . $editor);
if ((!file_exists($ue_new) && file_exists($ue_old)) || $editor == '') {
    $editor = 'ueditor';
}
@endphp
@include('admin.extend.editor.' . $editor, ['flag' => $flag ?? ''])
