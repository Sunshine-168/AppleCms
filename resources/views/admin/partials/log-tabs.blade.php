<nav class="log-tabs" aria-label="{{ admin_t('ui.log_tabs_aria') }}">
    <a href="/admin/system/monitor/login-logs" @class(['is-on' => $tab === 'login'])>{{ admin_t('ui.tab_login') }}</a>
    <a href="/admin/system/monitor/operate-logs" @class(['is-on' => $tab === 'operate'])>{{ admin_t('ui.tab_operate') }}</a>
    <a href="/admin/system/monitor/system-logs" @class(['is-on' => $tab === 'error'])>{{ admin_t('ui.tab_error') }}</a>
</nav>
