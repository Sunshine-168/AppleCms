@php
    $desk = in_array((string) ($desk ?? 'look'), ['look', 'files'], true) ? (string) $desk : 'look';
@endphp
<div class="queue-chips" id="theme-desks">
    <a class="chip{{ $desk === 'look' ? ' active' : '' }}" href="/admin/video/templates">外观</a>
    <a class="chip{{ $desk === 'files' ? ' active' : '' }}" href="/admin/video/templates?desk=files">文件</a>
</div>
