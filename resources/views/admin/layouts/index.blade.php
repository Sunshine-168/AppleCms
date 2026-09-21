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
    $adminName = (string) (session('admin_username') ?: admin_t('top.admin'));
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
    $navUri = \App\Support\AdminNav::requestUri();
@endphp
<div class="shell">
    <div class="side-backdrop" id="sideBackdrop"></div>
    <aside class="side" id="adminSide">
        <div class="side-brand">
            <span class="logo">{{ $brandMark }}</span>
            <span>{{ $brand }}</span>
        </div>
        @include('admin.partials.mod-nav', ['class' => 'mod-nav-side'])
        <nav class="side-nav">
            @foreach(\App\Support\AdminNav::groups() as $group)
                @if(! empty($group['header']))
                    <div class="nav-header">{{ admin_t($group['header']) }}</div>
                @endif
                @foreach($group['items'] as $item)
                    @if(! empty($item['children']) && is_array($item['children']))
                        @php
                            $nestedOpen = \App\Support\AdminNav::itemIsActive($item, $navUri);
                        @endphp
                        <details class="nav-fold nav-fold-nested{{ $nestedOpen ? ' is-open is-active' : '' }}" @if($nestedOpen) open @endif>
                            <summary>
                                <i class="fas fa-{{ $item['icon'] ?? 'circle' }}" aria-hidden="true"></i>
                                <span>{{ admin_t($item['label']) }}</span>
                            </summary>
                            <div class="side-sub">
                                @foreach($item['children'] as $child)
                                    @include('admin.partials.side-link', [
                                        'url' => $child['url'] ?? '',
                                        'icon' => $child['icon'] ?? '',
                                        'label' => admin_t($child['label'] ?? ''),
                                        'active' => \App\Support\AdminNav::hrefIsActive((string) ($child['url'] ?? ''), $navUri),
                                        'force' => (bool) ($child['force'] ?? $item['force'] ?? false),
                                    ])
                                @endforeach
                            </div>
                        </details>
                    @else
                        @include('admin.partials.side-link', [
                            'url' => $item['url'],
                            'icon' => $item['icon'],
                            'label' => admin_t($item['label']),
                            'active' => \App\Support\AdminNav::itemIsActive($item, $navUri),
                            'force' => (bool) ($item['force'] ?? false),
                        ])
                    @endif
                @endforeach
                @if(! empty($group['fold']['items']))
                    @php $foldOpen = \App\Support\AdminNav::foldOpen($group, $activeUrl); @endphp
                    <details class="nav-fold{{ $foldOpen ? ' is-open' : '' }}" @if($foldOpen) open @endif>
                        <summary>{{ admin_t($group['fold']['label'] ?? 'nav.more') }}</summary>
                        <div class="side-sub">
                            @foreach($group['fold']['items'] as $item)
                                @if(! empty($item['children']) && is_array($item['children']))
                                    @php
                                        $nestedOpen = \App\Support\AdminNav::itemIsActive($item, $navUri);
                                    @endphp
                                    <details class="nav-fold nav-fold-nested{{ $nestedOpen ? ' is-open is-active' : '' }}" @if($nestedOpen) open @endif>
                                        <summary>
                                            <i class="fas fa-{{ $item['icon'] ?? 'circle' }}" aria-hidden="true"></i>
                                            <span>{{ admin_t($item['label']) }}</span>
                                        </summary>
                                        <div class="side-sub">
                                            @foreach($item['children'] as $child)
                                                @include('admin.partials.side-link', [
                                                    'url' => $child['url'] ?? '',
                                                    'icon' => $child['icon'] ?? '',
                                                    'label' => admin_t($child['label'] ?? ''),
                                                    'active' => \App\Support\AdminNav::hrefIsActive((string) ($child['url'] ?? ''), $navUri),
                                                    'force' => (bool) ($child['force'] ?? $item['force'] ?? false),
                                                ])
                                            @endforeach
                                        </div>
                                    </details>
                                @else
                                    @include('admin.partials.side-link', [
                                        'url' => $item['url'],
                                        'icon' => $item['icon'],
                                        'label' => admin_t($item['label']),
                                        'active' => \App\Support\AdminNav::itemIsActive($item, $navUri),
                                        'force' => (bool) ($item['force'] ?? false),
                                    ])
                                @endif
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
                @include('admin.partials.mod-nav', ['class' => 'mod-nav-top'])
                <h1 class="topbar-title">@yield('title', admin_t('brand'))</h1>
            </div>
            <div class="topbar-right">
                @include('admin.partials.lang-pick')
                <a class="topbar-icon topbar-front" href="{{ url('/') }}" target="_blank" rel="noopener" title="{{ admin_t('top.front') }}" aria-label="{{ admin_t('top.front') }}"><i class="fas fa-external-link-alt" aria-hidden="true"></i></a>
                <details class="account-menu">
                    <summary class="topbar-icon" title="{{ admin_t('top.opt') }}" aria-label="{{ admin_t('top.opt') }}">
                        <i class="fas fa-ellipsis-h" aria-hidden="true"></i>
                    </summary>
                    <div class="account-menu-panel">
                        <a href="/admin/set/user/password">{{ admin_t('top.password') }}</a>
                        <button type="button" id="quickCacheClear">{{ admin_t('top.cache_clear') }}</button>
                        <button type="button" id="lockScreen">{{ admin_t('top.lock') }}</button>
                        <form method="post" action="/admin/logout" id="logoutForm">
                            @csrf
                            <button type="submit">{{ admin_t('top.logout') }}</button>
                        </form>
                    </div>
                </details>
            </div>
        </header>
        <main class="content" id="admin-content">
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
<div id="lockOverlay" class="lock-overlay" hidden>
    <form id="unlockForm" class="lock-card" method="post" action="/admin/unlock" autocomplete="off" data-empty="{{ admin_t('top.unlock_empty') }}" data-fail="{{ admin_t('top.unlock_fail') }}" data-expired="{{ admin_t('top.unlock_expired') }}" data-network="{{ admin_t('auth.network') }}">
        @csrf
        <p>{{ admin_t('top.lock') }}</p>
        <input id="unlockPassword" type="password" name="password" autocomplete="off" maxlength="64" placeholder="{{ admin_t('top.unlock_ph') }}">
        <button type="submit" id="unlockBtn">{{ admin_t('top.unlock_btn') }}</button>
        <button type="button" class="lock-out" id="unlockLogout">{{ admin_t('top.unlock_out') }}</button>
        <p class="lock-hint">{{ admin_t('top.unlock_forgot') }}</p>
        <p class="lock-err" id="unlockErr" hidden></p>
    </form>
</div>
<script>
(function () {
    var backdrop = document.getElementById('sideBackdrop');

    function sideEl() { return document.getElementById('adminSide'); }
    function setOpen(open) {
        var side = sideEl();
        side && side.classList.toggle('open', open);
        backdrop && backdrop.classList.toggle('show', open);
        document.body.classList.toggle('side-open', open);
    }

    document.addEventListener('click', function (e) {
        var t = e.target && e.target.closest ? e.target : (e.target && e.target.parentElement);
        if (!t || !t.closest) return;
        if (t.closest('#toggleSide')) {
            var side = sideEl();
            setOpen(!(side && side.classList.contains('open')));
            return;
        }
        if (t.closest('#adminSide a')) setOpen(false);
    });
    backdrop && backdrop.addEventListener('click', function () { setOpen(false); });
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
@php
    $adminUiLang = [
        'close' => admin_t('ui.close'),
        'cancel' => admin_t('ui.cancel'),
        'save' => admin_t('ui.save'),
        'confirm' => admin_t('ui.confirm'),
        'delete' => admin_t('ui.delete'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'ok' => admin_t('ui.ok'),
        'add' => admin_t('ui.add'),
        'edit' => admin_t('ui.edit'),
        'search' => admin_t('ui.search'),
        'reset' => admin_t('ui.reset'),
        'all' => admin_t('ui.all'),
        'actions' => admin_t('ui.actions'),
        'added' => admin_t('ui.added'),
        'unnamed' => admin_t('ui.unnamed'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'status' => admin_t('ui.status'),
        'sort' => admin_t('ui.sort'),
        'name' => admin_t('ui.name'),
        'types' => admin_t('ui.types'),
        'tags' => admin_t('ui.tags'),
        'authors' => admin_t('ui.authors'),
        'works' => admin_t('ui.works'),
        'front' => admin_t('ui.front'),
        'unused' => admin_t('ui.unused'),
        'pages' => admin_t('ui.pages_col'),
        'type' => admin_t('ui.col_type'),
        'time' => admin_t('ui.col_time'),
        'hits' => admin_t('ui.hits'),
        'updated' => admin_t('ui.col_updated'),
        'enabled' => admin_t('ui.enabled'),
        'disabled' => admin_t('ui.disabled'),
        'available' => admin_t('ui.status_available'),
        'unavailable' => admin_t('ui.status_unavailable'),
        'not_filled' => admin_t('ui.not_filled'),
        'save_fail' => admin_t('ui.save_fail'),
        'selected_n' => admin_t('ui.selected_n', ['n' => '__N__']),
        'title' => admin_t('ui.title_label'),
        'address' => admin_t('ui.label_address'),
        'identifier' => admin_t('ui.identifier'),
        'child' => admin_t('ui.add_child'),
        'member' => admin_t('ui.member'),
        'points' => admin_t('ui.points'),
        'date' => admin_t('ui.date'),
        'clicks' => admin_t('ui.clicks'),
        'impressions' => admin_t('ui.impressions'),
        'period' => admin_t('ui.period'),
        'ads' => admin_t('ui.ads'),
        'coupon' => admin_t('ui.coupon'),
        'order' => admin_t('ui.order'),
        'duration' => admin_t('ui.duration'),
        'spider' => admin_t('ui.spider'),
        'show' => admin_t('ui.show'),
        'hide' => admin_t('ui.hide'),
        'move' => admin_t('ui.move'),
        'preview' => admin_t('ui.preview'),
        'size' => admin_t('ui.size'),
        'videos' => admin_t('ui.videos'),
        'actors' => admin_t('ui.actors'),
        'cast' => admin_t('ui.cast'),
        'received' => admin_t('ui.received'),
        'used' => admin_t('ui.used'),
        'expire' => admin_t('ui.expire'),
        'stock' => admin_t('ui.stock'),
        'sales' => admin_t('ui.sales'),
        'goods' => admin_t('ui.goods'),
        'driver' => admin_t('ui.driver'),
        'no_image' => admin_t('ui.no_image'),
        'visitor' => admin_t('ui.visitor'),
        'who' => admin_t('ui.who'),
        'opened' => admin_t('ui.opened'),
        'sites' => admin_t('ui.sites'),
        'upload_time' => admin_t('ui.upload_time'),
        'missing' => admin_t('ui.col_missing'),
        'line' => admin_t('ui.line'),
        'refresh' => admin_t('ui.refresh'),
        'open_link' => admin_t('ui.open_link'),
        'copy_url' => admin_t('ui.copy_url'),
        'already_off' => admin_t('ui.already_off'),
        'already_on' => admin_t('ui.already_on'),
        'copied' => admin_t('ui.copied'),
        'copy_fail' => admin_t('ui.copy_fail'),
        'product_code' => admin_t('ui.product_code'),
        'mch_id' => admin_t('ui.mch_id'),
        'label_slot' => admin_t('ui.label_slot'),
        'face_value' => admin_t('ui.face_value'),
        'scene' => admin_t('ui.scene'),
        'issued' => admin_t('ui.issued'),
        'showing' => admin_t('ui.showing'),
        'reported' => admin_t('ui.reported'),
        'n_items' => admin_t('ui.pager_total'),
        'prev_page' => admin_t('ui.prev_page'),
        'next_page' => admin_t('ui.next_page'),
        'pager' => admin_t('ui.pager'),
    ];
@endphp
<script>
(function () {
    if (window.AdminUi && AdminUi.setLang) {
        AdminUi.setLang(@json($adminUiLang, JSON_UNESCAPED_UNICODE));
    }
    if (!window.AdminUi) return;
    @if(session('error'))
    AdminUi.toast(@json(session('error')), 'err');
    @endif
    @if(session('status'))
    AdminUi.toast(@json(session('status')), 'ok');
    @endif
})();
(function () {
    var overlay = document.getElementById('lockOverlay');
    var form = document.getElementById('unlockForm');
    var err = document.getElementById('unlockErr');
    var inp = document.getElementById('unlockPassword');
    var btn = document.getElementById('unlockBtn');
    function csrfToken() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }
    function closeMenus() {
        document.querySelectorAll('details.account-menu, details.lang-pick').forEach(function (d) {
            d.open = false;
        });
    }
    function showErr(text) {
        if (!err) return;
        err.hidden = !text;
        err.textContent = text || '';
    }
    function showLock() {
        if (!overlay) return;
        overlay.hidden = false;
        overlay.removeAttribute('hidden');
        document.body.classList.add('is-locked');
        closeMenus();
        if (inp) {
            inp.value = '';
            setTimeout(function () { inp.focus(); }, 0);
        }
        showErr('');
    }
    function hideLock() {
        if (!overlay) return;
        overlay.hidden = true;
        overlay.setAttribute('hidden', '');
        document.body.classList.remove('is-locked');
        if (inp) inp.value = '';
        showErr('');
        try {
            sessionStorage.removeItem('admin_lock_v2');
            sessionStorage.removeItem('admin_lockscreen');
        } catch (e) {}
    }
    try {
        if (sessionStorage.getItem('admin_lock_v2') === '1') showLock();
    } catch (e) {}
    var lockBtn = document.getElementById('lockScreen');
    lockBtn && lockBtn.addEventListener('click', function () {
        try { sessionStorage.setItem('admin_lock_v2', '1'); } catch (e) {}
        showLock();
    });
    var unlockOut = document.getElementById('unlockLogout');
    unlockOut && unlockOut.addEventListener('click', function () {
        try {
            sessionStorage.removeItem('admin_lock_v2');
            sessionStorage.removeItem('admin_lockscreen');
        } catch (e) {}
        var logoutForm = document.getElementById('logoutForm');
        if (logoutForm && typeof logoutForm.requestSubmit === 'function') logoutForm.requestSubmit();
        else if (logoutForm) logoutForm.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
        else location.href = '/admin/login';
    });
    form && form.addEventListener('submit', function (ev) {
        ev.preventDefault();
        ev.stopPropagation();
        var pwd = ((inp && inp.value) || '').trim();
        if (!pwd) {
            showErr(form.getAttribute('data-empty') || '');
            inp && inp.focus();
            return;
        }
        if (btn) btn.disabled = true;
        showErr('');
        var body = new URLSearchParams();
        body.set('_token', csrfToken());
        body.set('password', pwd);
        fetch('/admin/unlock', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: body.toString()
        }).then(function (r) {
            return r.json().then(function (json) {
                return { r: r, json: json };
            }).catch(function () {
                return { r: r, json: null };
            });
        }).then(function (pack) {
            var json = pack.json;
            if (json && Number(json.code) === 0) {
                hideLock();
                return;
            }
            var msg = '';
            if (pack.r && pack.r.status === 419) msg = form.getAttribute('data-expired') || '';
            else if (json && json.msg) msg = String(json.msg);
            else if (json && Number(json.code) === 1001) msg = String(json.msg || '');
            showErr(msg || form.getAttribute('data-fail') || '');
            inp && inp.focus();
        }).catch(function () {
            showErr(form.getAttribute('data-network') || form.getAttribute('data-fail') || '');
        }).finally(function () {
            if (btn) btn.disabled = false;
        });
    });
    var cacheBtn = document.getElementById('quickCacheClear');
    cacheBtn && cacheBtn.addEventListener('click', function () {
        if (!window.AdminUi) return;
        AdminUi.post('/admin/system/tools/cache/clear', { kind: 'all' }).then(function (res) {
            AdminUi.toast(res.msg || '', Number(res.code) === 0 ? 'ok' : 'err');
        });
    });
})();
</script>
<div id="admin-page-scripts">
@stack('scripts')
</div>
</body>
</html>
