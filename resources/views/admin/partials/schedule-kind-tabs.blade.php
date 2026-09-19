@php
    $tab = (string) ($tab ?? 'collect');
@endphp
<nav class="hub-tabs" aria-label="{{ admin_t('ui.schedule_kind_aria') }}">
    <a href="/admin/video/collect_tasks" @class(['is-on' => $tab === 'collect'])>{{ admin_t('ui.schedule_tab_collect') }}</a>
    <a href="/admin/system/tools/schedule" @class(['is-on' => $tab === 'other'])>{{ admin_t('ui.schedule_tab_other') }}</a>
</nav>
