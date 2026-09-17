@php
    $tab = (string) ($tab ?? 'backup');
@endphp
<nav class="db-tabs" aria-label="数据库工具">
    <a href="/admin/system/database/backup" @class(['is-on' => $tab === 'backup'])>备份</a>
    <a href="/admin/system/database/restore" @class(['is-on' => $tab === 'restore'])>恢复</a>
    <a href="/admin/system/database/sql" @class(['is-on' => $tab === 'sql'])>SQL</a>
    <a href="/admin/system/database/replace" @class(['is-on' => $tab === 'replace'])>替换</a>
    <a href="/admin/system/database/dict" @class(['is-on' => $tab === 'dict'])>字段</a>
</nav>
