@php
    $tab = (string) ($tab ?? 'collect');
@endphp
<nav class="hub-tabs" aria-label="定时任务种类">
    <a href="/admin/video/collect_tasks" @class(['is-on' => $tab === 'collect'])>采集片子</a>
    <a href="/admin/system/tools/schedule" @class(['is-on' => $tab === 'other'])>备份 / 推送 / 插件</a>
</nav>
