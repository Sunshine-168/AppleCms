@php
    $step = (string) ($step ?? '');
@endphp
<nav class="hub-tabs access-chain" aria-label="菜单、角色、管理员">
    <a href="/admin/system/menus" @class(['is-on' => $step === 'menus'])>菜单</a>
    <a href="/admin/system/roles" @class(['is-on' => $step === 'roles'])>角色</a>
    <a href="/admin/user" @class(['is-on' => $step === 'admins'])>管理员</a>
</nav>
