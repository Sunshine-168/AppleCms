<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ conf('name') ?: '苹果v12' }} - 后台</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.staticfile.net/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ @filemtime(public_path('css/admin.css')) ?: '1' }}">
</head>
<body class="admin-iframe-shell">
@php
    $adminName = (string) (session('admin_username') ?: '管理员');
    $brand = (string) (conf('name') ?: '苹果v12');
    $brandMark = function_exists('mb_substr') ? mb_substr($brand, 0, 1) : substr($brand, 0, 1);
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
                <div class="nav-header">{{ $group['header'] }}</div>
                @foreach($group['items'] as $item)
                    @include('admin.partials.side-link', [
                        'url' => $item['url'],
                        'icon' => $item['icon'],
                        'label' => $item['label'],
                        'active' => ($item['url'] ?? '') === '/admin/welcome',
                        'force' => (bool) ($item['force'] ?? false),
                    ])
                @endforeach
                @if(! empty($group['fold']['items']))
                    <details class="nav-fold">
                        <summary>{{ $group['fold']['label'] ?? '更多' }}</summary>
                        <div class="side-sub">
                            @foreach($group['fold']['items'] as $item)
                                @include('admin.partials.side-link', [
                                    'url' => $item['url'],
                                    'icon' => $item['icon'],
                                    'label' => $item['label'],
                                    'active' => false,
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
                <em>后台</em>
            </span>
        </div>
    </aside>

    <div class="main-wrap">
        <header class="topbar">
            <div class="topbar-left">
                <button type="button" class="btn btn-muted btn-sm topbar-menu" id="toggleSide" title="菜单"><i class="fas fa-bars"></i></button>
                <h1 class="topbar-title" id="topbarTitle">仪表盘</h1>
            </div>
            <div class="topbar-right">
                <button type="button" class="btn btn-muted btn-sm" id="refreshFrame" title="刷新"><i class="fas fa-sync-alt"></i></button>
                <a class="btn btn-sm topbar-front" href="{{ url('/') }}" target="_blank" rel="noopener">前台</a>
                <details class="account-menu">
                    <summary class="user-chip">
                        <span class="avatar">{{ function_exists('mb_substr') ? mb_substr($adminName, 0, 1) : substr($adminName, 0, 1) }}</span>
                        <span class="user-chip-name">{{ $adminName }}</span>
                    </summary>
                    <div class="account-menu-panel">
                        <p class="muted">管理员</p>
                        <a class="account-menu-link" href="/admin/set/user/password" data-frame="1">修改密码</a>
                        <form method="post" action="/admin/logout" id="logoutForm">
                            @csrf
                            <button class="btn btn-muted btn-sm" type="submit">退出</button>
                        </form>
                    </div>
                </details>
            </div>
        </header>
        <main class="content work-area">
            <iframe id="workFrame" class="work-frame" src="/admin/welcome" title="后台内容"></iframe>
        </main>
    </div>
</div>
<script>
(function () {
    var side = document.getElementById('adminSide');
    var backdrop = document.getElementById('sideBackdrop');
    var frame = document.getElementById('workFrame');
    var titleEl = document.getElementById('topbarTitle');

    function setOpen(open) {
        side && side.classList.toggle('open', open);
        backdrop && backdrop.classList.toggle('show', open);
        document.body.classList.toggle('side-open', open);
    }

    function setActive(url) {
        var path = String(url || '').split('#')[0];
        var best = null;
        var bestLen = -1;
        document.querySelectorAll('.side-nav a[data-frame]').forEach(function (a) {
            var href = a.getAttribute('href') || '';
            a.classList.remove('active');
            var hit = href === path || (href.indexOf('?') === -1 && (path === href || path.indexOf(href + '/') === 0 || path.indexOf(href + '?') === 0));
            if (hit && href.length > bestLen) {
                best = a;
                bestLen = href.length;
            }
        });
        if (best) {
            best.classList.add('active');
            var fold = best.closest('details.nav-fold');
            if (fold) fold.open = true;
            var label = best.querySelector('span');
            if (label && titleEl) titleEl.textContent = label.textContent.trim();
        }
    }

    function openPage(url, name) {
        if (!url) return;
        frame.src = url;
        if (name && titleEl) titleEl.textContent = name;
        setActive(url);
        setOpen(false);
    }

    document.getElementById('toggleSide') && document.getElementById('toggleSide').addEventListener('click', function () {
        setOpen(!(side && side.classList.contains('open')));
    });
    backdrop && backdrop.addEventListener('click', function () { setOpen(false); });

    document.getElementById('refreshFrame') && document.getElementById('refreshFrame').addEventListener('click', function () {
        try { frame.contentWindow.location.reload(); } catch (e) { frame.src = frame.src; }
    });

    document.querySelectorAll('a[data-frame]').forEach(function (a) {
        a.addEventListener('click', function (ev) {
            ev.preventDefault();
            var name = (a.querySelector('span') && a.querySelector('span').textContent) || a.textContent;
            openPage(a.getAttribute('href'), name.trim());
        });
    });

    frame.addEventListener('load', function () {
        try {
            var loc = frame.contentWindow.location;
            setActive(loc.pathname + loc.search);
            var doc = frame.contentDocument;
            if (doc && doc.head && !doc.getElementById('cms-inner-skin')) {
                var link = doc.createElement('link');
                link.id = 'cms-inner-skin';
                link.rel = 'stylesheet';
                link.href = '{{ asset('css/admin-inner.css') }}?v={{ @filemtime(public_path('css/admin-inner.css')) ?: 1 }}';
                doc.head.appendChild(link);
            }
        } catch (e) {}
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
</body>
</html>
