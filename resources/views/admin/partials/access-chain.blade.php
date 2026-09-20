@php
    $step = (string) ($step ?? '');
@endphp
<nav class="hub-tabs access-chain" aria-label="{{ admin_t('access.chain') }}">
    <a href="/admin/system/menus" @class(['is-on' => $step === 'menus'])>{{ admin_t('access.step_menus') }}</a>
    <a href="/admin/system/roles" @class(['is-on' => $step === 'roles'])>{{ admin_t('access.step_roles') }}</a>
    <a href="/admin/user" @class(['is-on' => $step === 'admins'])>{{ admin_t('access.step_admins') }}</a>
</nav>
