@php
    $desk = in_array((string) ($desk ?? 'look'), ['look', 'files'], true) ? (string) $desk : 'look';
@endphp
<span class="btn-split" id="theme-desks" role="group" aria-label="{{ admin_t('ui.theme_bench') }}">
    <a class="btn btn-sm{{ $desk === 'look' ? ' is-on' : ' btn-muted' }}" href="/admin/video/templates">{{ admin_t('ui.look') }}</a>
    <a class="btn btn-sm{{ $desk === 'files' ? ' is-on' : ' btn-muted' }}" href="/admin/video/templates?desk=files">{{ admin_t('ui.theme_files') }}</a>
</span>
