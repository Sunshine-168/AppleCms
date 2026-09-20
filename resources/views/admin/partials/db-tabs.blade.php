@php
    $tab = (string) ($tab ?? 'backup');
@endphp
<nav class="db-tabs" aria-label="{{ admin_t('ui.db_tabs_aria') }}">
    <a href="/admin/system/database/backup" @class(['is-on' => $tab === 'backup'])>{{ admin_t('ui.tab_backup') }}</a>
    <a href="/admin/system/database/restore" @class(['is-on' => $tab === 'restore'])>{{ admin_t('ui.restore') }}</a>
    <a href="/admin/system/database/sql" @class(['is-on' => $tab === 'sql'])>SQL</a>
    <a href="/admin/system/database/replace" @class(['is-on' => $tab === 'replace'])>{{ admin_t('ui.tab_replace') }}</a>
    <a href="/admin/system/database/dict" @class(['is-on' => $tab === 'dict'])>{{ admin_t('page.db_dict') }}</a>
</nav>
