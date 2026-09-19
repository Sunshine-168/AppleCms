@php
    $desk = in_array((string) ($desk ?? 'look'), ['look', 'files'], true) ? (string) $desk : 'look';
@endphp
<span class="btn-split" id="theme-desks" role="group" aria-label="模板工作台">
    <a class="btn btn-sm{{ $desk === 'look' ? ' is-on' : ' btn-muted' }}" href="/admin/video/templates">外观</a>
    <a class="btn btn-sm{{ $desk === 'files' ? ' is-on' : ' btn-muted' }}" href="/admin/video/templates?desk=files">文件</a>
</span>
