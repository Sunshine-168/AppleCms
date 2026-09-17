<!DOCTYPE html>
<html lang="{{ \App\Support\AdminUi::htmlLang() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ conf('name') ?: '苹果v12' }} - @yield('title', admin_t('brand'))</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.staticfile.net/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ @filemtime(public_path('css/admin.css')) ?: '1' }}">
    @stack('styles')
</head>
<body>
@php
    $adminName = (string) (session('admin_username') ?: '管理员');
    $brand = (string) (conf('name') ?: '苹果v12');
    $brandMark = function_exists('mb_substr') ? mb_substr($brand, 0, 1) : substr($brand, 0, 1);
    if (! isset($menus) || ! is_array($menus)) {
        try {
            $menus = app(\App\Services\Admin\System\SysPermService::class)->getAdminMenus((int) session('admin_uid', 0));
        } catch (\Throwable) {
            $menus = [];
        }
    }
    $menuUrl = static function (array $menu): string {
        if (! empty($menu['route']) && \Illuminate\Support\Facades\Route::has($menu['route'])) {
            return (string) route($menu['route'], [], false);
        }

        return (string) ($menu['url'] ?? '');
    };
    $flatten = static function (array $items) use (&$flatten, $menuUrl): array {
        $out = [];
        foreach ($items as $item) {
            if (! empty($item['sub']) && is_array($item['sub'])) {
                $out = array_merge($out, $flatten($item['sub']));
                continue;
            }
            $url = $menuUrl($item);
            if ($url !== '') {
                $out[$url] = (string) ($item['name'] ?? $url);
            }
        }

        return $out;
    };
    $nav = (object) [
        'allowed' => $flatten($menus ?? []),
        'shown' => [],
    ];
    $activeUrl = \App\Support\AdminNav::activeUrl();
@endphp
<div class="shell">
    <div class="side-backdrop" id="sideBackdrop"></div>
    <aside class="side" id="adminSide">
        <div class="side-brand">
            <span class="logo">{{ $brandMark }}</span>
            <span>{{ $brand }}</span>
        </div>
        <nav class="side-nav">
            @foreach(\App\Support\AdminNav::groups() as $group)
                <div class="nav-header">{{ admin_t($group['header']) }}</div>
                @foreach($group['items'] as $item)
                    @include('admin.partials.side-link', [
                        'url' => $item['url'],
                        'icon' => $item['icon'],
                        'label' => admin_t($item['label']),
                        'active' => ($item['url'] ?? '') === $activeUrl,
                        'force' => (bool) ($item['force'] ?? false),
                    ])
                @endforeach
                @if(! empty($group['fold']['items']))
                    @php $foldOpen = \App\Support\AdminNav::foldOpen($group, $activeUrl); @endphp
                    <details class="nav-fold{{ $foldOpen ? ' is-open' : '' }}" @if($foldOpen) open @endif>
                        <summary>{{ admin_t($group['fold']['label'] ?? 'nav.more') }}</summary>
                        <div class="side-sub">
                            @foreach($group['fold']['items'] as $item)
                                @include('admin.partials.side-link', [
                                    'url' => $item['url'],
                                    'icon' => $item['icon'],
                                    'label' => admin_t($item['label']),
                                    'active' => ($item['url'] ?? '') === $activeUrl,
                                    'force' => (bool) ($item['force'] ?? false),
                                ])
                            @endforeach
                        </div>
                    </details>
                @endif
            @endforeach
        </nav>
        <div class="side-foot">
            <span class="side-foot-avatar">{{ function_exists('mb_substr') ? mb_substr($adminName, 0, 1) : substr($adminName, 0, 1) }}</span>
            <span class="side-foot-meta">
                <strong>{{ $adminName }}</strong>
                <em>{{ admin_t('nav.admin') }}</em>
            </span>
        </div>
    </aside>

    <div class="main-wrap">
        <header class="topbar">
            <div class="topbar-left">
                <button type="button" class="btn btn-muted btn-sm topbar-menu" id="toggleSide" title="{{ admin_t('top.menu') }}"><i class="fas fa-bars"></i></button>
                <h1 class="topbar-title">@yield('title', admin_t('brand'))</h1>
            </div>
            <div class="topbar-right">
                <form method="post" action="{{ route('admin.ui-locale') }}" class="ui-switch">
                    @csrf
                    @foreach(\App\Support\AdminUi::options() as $code => $uiLabel)
                        <button type="submit" name="ui_locale" value="{{ $code }}" class="{{ \App\Support\AdminUi::current() === $code ? 'is-on' : '' }}">{{ $uiLabel }}</button>
                    @endforeach
                </form>
                <a class="btn btn-muted btn-sm topbar-front" href="{{ url('/') }}" target="_blank" rel="noopener">{{ admin_t('top.front') }}</a>
                <details class="account-menu">
                    <summary class="user-chip">
                        <span class="avatar">{{ function_exists('mb_substr') ? mb_substr($adminName, 0, 1) : substr($adminName, 0, 1) }}</span>
                        <span class="user-chip-name">{{ $adminName }}</span>
                    </summary>
                    <div class="account-menu-panel">
                        <p class="muted">{{ admin_t('top.admin') }}</p>
                        <a class="account-menu-link" href="/admin/set/user/password">{{ admin_t('top.password') }}</a>
                        <form method="post" action="/admin/logout" id="logoutForm">
                            @csrf
                            <button class="btn btn-muted btn-sm" type="submit">{{ admin_t('top.logout') }}</button>
                        </form>
                    </div>
                </details>
            </div>
        </header>
        <main class="content">
            @if(session('status'))
                <div class="flash">{{ session('status') }}</div>
            @endif
            @if(session('error'))
                <div class="flash error">{{ session('error') }}</div>
            @endif
            @hasSection('plain')
                @yield('plain')
            @else
                <div class="card card-panel">
                    <div class="card-header">
                        <span>@yield('title')</span>
                        <div>@yield('header_actions')</div>
                    </div>
                    <div class="card-body">
                        @yield('content')
                    </div>
                </div>
                @yield('after')
            @endif
        </main>
    </div>
</div>
<script>
(function () {
    var side = document.getElementById('adminSide');
    var backdrop = document.getElementById('sideBackdrop');

    function setOpen(open) {
        side && side.classList.toggle('open', open);
        backdrop && backdrop.classList.toggle('show', open);
        document.body.classList.toggle('side-open', open);
    }

    document.getElementById('toggleSide') && document.getElementById('toggleSide').addEventListener('click', function () {
        setOpen(!(side && side.classList.contains('open')));
    });
    backdrop && backdrop.addEventListener('click', function () { setOpen(false); });
    side && side.querySelectorAll('a').forEach(function (a) {
        a.addEventListener('click', function () { setOpen(false); });
    });
    document.getElementById('logoutForm') && document.getElementById('logoutForm').addEventListener('submit', function (ev) {
        ev.preventDefault();
        fetch('/admin/logout', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        }).finally(function () {
            location.href = '/admin/login';
        });
    });
})();
</script>
<script src="{{ asset('js/admin-ui.js') }}?v={{ @filemtime(public_path('js/admin-ui.js')) ?: '1' }}"></script>
<script>
(function () {
    if (!window.AdminUi) return;
    @if(session('error'))
    AdminUi.toast(@json(session('error')), 'err');
    @endif
    @if(session('status'))
    AdminUi.toast(@json(session('status')), 'ok');
    @endif
})();
</script>
@stack('scripts')
</body>
</html>
