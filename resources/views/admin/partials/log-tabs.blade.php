<nav class="log-tabs" aria-label="日志种类">
    <a href="/admin/system/monitor/login-logs" @class(['is-on' => $tab === 'login'])>登录</a>
    <a href="/admin/system/monitor/operate-logs" @class(['is-on' => $tab === 'operate'])>操作</a>
    <a href="/admin/system/monitor/system-logs" @class(['is-on' => $tab === 'error'])>报错</a>
</nav>
