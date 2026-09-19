@extends('admin.layouts.inner')
@section('title', admin_t('page.config_ip'))

@php
    $s = $site ?? [];
    $currentIp = (string) ($current_ip ?? '');
    $forwardedIp = (string) ($forwarded_ip ?? '');
    $local = (bool) ($local ?? false);
    $enabled = (bool) ($enabled ?? false);
    $rules = $rules ?? [];
    $currentOk = (bool) ($current_ok ?? true);
    $recent = $recent ?? [];
    $raw = trim((string) ($s['admin_ip_allow'] ?? ''));
@endphp

@section('plain')
<div class="card card-panel ip-config-index">
    <div class="card-header">
        <span>后台 IP 白名单</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/system/monitor/login-logs">登录日志</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/safety">挂马扫描</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/settings?tab=more">站点设置</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">只限制后台登录。名单为空不限制；填了以后只有这些 IP 能打开 <code>/admin</code>。家宽 IP 变了会进不去。</p>

        <div class="ai-stock">
            @if($enabled)
                <span class="badge badge-warn">已限制 · {{ count($rules) }} 条</span>
                @if($currentOk)
                    <span class="badge badge-ok">当前 IP 在名单内</span>
                @else
                    <span class="badge badge-warn">当前 IP 不在名单</span>
                @endif
            @else
                <span class="badge">未限制</span>
                <span class="muted">任何人能打开登录页（仍要账号密码）</span>
            @endif
        </div>

        <div class="ip-now">
            <div>
                <div class="label">当前访问 IP</div>
                <div class="value" id="ip-current">{{ $currentIp !== '' ? $currentIp : '读不到' }}</div>
                @if($local)
                    <p class="muted">这是本机或反向代理看到的地址。若站点前面有 Nginx，请先在 Laravel 里配置信任代理，否则公网 IP 对不上。</p>
                @endif
                @if($forwardedIp !== '')
                    <p class="muted">请求头里还有 {{ $forwardedIp }}。未信任代理时不会按它放行，不要只把这个写进名单。</p>
                @endif
            </div>
            <button type="button" class="btn" id="ip-add-current" @disabled($currentIp === '')>加入当前 IP</button>
        </div>

        <form class="admin-form settings-page ip-config-form" id="site-form">
            <h3>限制方式</h3>
            <div class="ingest-modes" id="ip-modes">
                <button type="button" class="ingest-mode{{ $enabled ? '' : ' is-on' }}" data-mode="off">
                    <strong>不限制</strong>
                    <span>只靠账号密码。办公室 IP 不固定时选这个。</span>
                </button>
                <button type="button" class="ingest-mode{{ $enabled ? ' is-on' : '' }}" data-mode="on">
                    <strong>只允许名单</strong>
                    <span>其它 IP 打开后台会 403。保存时必须包含你现在的 IP。</span>
                </button>
            </div>

            <div id="ip-list-wrap" @if(! $enabled) hidden @endif>
                <h3>允许的 IP</h3>
                <p class="muted field-hint">每行一条。支持单个地址，也支持网段如 <code>192.168.1.0/24</code>。不要写 <code>*</code>，留空就是不限制。</p>
                <div class="field-inline">
                    <input id="ip-new" type="text" placeholder="再加一条，例如 203.0.113.8" autocomplete="off" spellcheck="false">
                    <button type="button" class="btn btn-muted" id="ip-add-one">添加</button>
                </div>
                <ul class="ip-chips" id="ip-chips"></ul>
                <label for="admin_ip_allow">整段编辑</label>
                <textarea id="admin_ip_allow" name="admin_ip_allow" rows="6" placeholder="每行一个 IP 或网段">{{ $raw }}</textarea>
                @if($recent !== [])
                    <p class="muted field-hint">最近登录过、还不在名单里：</p>
                    <div class="ip-recent" id="ip-recent">
                        @foreach($recent as $rip)
                            <button type="button" class="btn btn-muted btn-sm" data-ip="{{ $rip }}">{{ $rip }}</button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="interface-howto">
                <p>把自己锁在外面时：</p>
                <ul>
                    <li>在服务器执行 <code>php artisan video:admin-ip-clear</code></li>
                    <li>或把 <code>video_options</code> 里 <code>admin_ip_allow</code> 改成空</li>
                </ul>
                <p class="muted">清名单后立刻不限制。前台、采集、会员登录都不走这套名单。</p>
            </div>

            <div class="form-actions settings-save">
                <button type="button" class="btn" id="site-save">保存</button>
                <a class="btn btn-muted" href="/admin/system/monitor/login-logs">看登录日志</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = window.AdminUi;
    var modeOn = {{ $enabled ? 'true' : 'false' }};
    var current = @json($currentIp);
    var original = normalize(document.getElementById('admin_ip_allow') ? document.getElementById('admin_ip_allow').value : '');
    var originalOn = {{ $enabled ? 'true' : 'false' }};
    var ta = document.getElementById('admin_ip_allow');
    var wrap = document.getElementById('ip-list-wrap');
    var chips = document.getElementById('ip-chips');
    var modes = document.getElementById('ip-modes');

    function lines(raw) {
        raw = String(raw || '');
        var seen = {};
        var out = [];
        raw.split(/\r?\n/).forEach(function (line) {
            line = String(line || '').trim();
            if (!line || line.charAt(0) === '#') return;
            line = line.replace(/\s+#.*$/, '').trim();
            line.split(/[\s,;]+/).forEach(function (p) {
                p = String(p || '').trim();
                if (!p || p.charAt(0) === '#') return;
                var k = p.toLowerCase();
                if (seen[k]) return;
                seen[k] = 1;
                out.push(p);
            });
        });
        return out;
    }
    function normalize(raw) { return lines(raw).join('\n'); }
    function setMode(on) {
        modeOn = !!on;
        if (modes) {
            modes.querySelectorAll('.ingest-mode').forEach(function (btn) {
                btn.classList.toggle('is-on', (btn.getAttribute('data-mode') === 'on') === modeOn);
            });
        }
        if (wrap) wrap.hidden = !modeOn;
        if (modeOn && ta && lines(ta.value).length === 0 && current) {
            ta.value = current;
        }
        renderChips();
    }
    function addIp(ip) {
        ip = String(ip || '').trim();
        if (!ip) { U && U.toast('请填写 IP', 'err'); return; }
        if (!modeOn) setMode(true);
        var cur = lines(ta ? ta.value : '');
        var k = ip.toLowerCase();
        if (cur.some(function (x) { return x.toLowerCase() === k; })) {
            U && U.toast('名单里已有 ' + ip, 'ok');
            renderChips();
            return;
        }
        cur.push(ip);
        if (ta) ta.value = cur.join('\n');
        renderChips();
        U && U.toast('已加入 ' + ip, 'ok');
    }
    function removeIp(ip) {
        var k = String(ip || '').toLowerCase();
        if (ta) ta.value = lines(ta.value).filter(function (x) { return x.toLowerCase() !== k; }).join('\n');
        renderChips();
    }
    function renderChips() {
        if (!chips) return;
        chips.innerHTML = '';
        lines(ta ? ta.value : '').forEach(function (ip) {
            var li = document.createElement('li');
            var span = document.createElement('span');
            span.textContent = ip;
            if (current && ip.toLowerCase() === String(current).toLowerCase()) {
                var mark = document.createElement('em');
                mark.textContent = '当前';
                span.appendChild(mark);
            }
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn-link';
            btn.textContent = '去掉';
            btn.addEventListener('click', function () { removeIp(ip); });
            li.appendChild(span);
            li.appendChild(btn);
            chips.appendChild(li);
        });
    }
    if (modes) {
        modes.addEventListener('click', function (e) {
            var btn = e.target.closest('.ingest-mode');
            if (!btn) return;
            setMode(btn.getAttribute('data-mode') === 'on');
        });
    }
    var addCur = document.getElementById('ip-add-current');
    if (addCur) addCur.addEventListener('click', function () { addIp(current); });
    var addOne = document.getElementById('ip-add-one');
    var neu = document.getElementById('ip-new');
    function addTyped() { addIp(neu ? neu.value : ''); if (neu) neu.value = ''; }
    if (addOne) addOne.addEventListener('click', addTyped);
    if (neu) neu.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); addTyped(); }
    });
    var recent = document.getElementById('ip-recent');
    if (recent) recent.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-ip]');
        if (btn) addIp(btn.getAttribute('data-ip'));
    });
    if (ta) ta.addEventListener('input', renderChips);
    renderChips();

    var save = document.getElementById('site-save');
    var form = document.getElementById('site-form');
    function doSave() {
        var value = modeOn ? normalize(ta ? ta.value : '') : '';
        if (modeOn && value === '') {
            U && U.toast('开了限制但名单是空的。请先加入当前 IP。', 'err');
            return;
        }
        if (modeOn === originalOn && value === original) {
            U && U.toast('没有改动，不用保存', 'ok');
            return;
        }
        U.post('/admin/video/settings', { admin_ip_allow: value }).then(function (res) {
            U.toast((res && res.msg) || '完成', res && res.code === 0 ? 'ok' : 'err');
            if (res && res.code === 0) {
                original = value;
                originalOn = modeOn;
            }
        });
    }
    if (save) save.addEventListener('click', doSave);
    if (form) form.addEventListener('submit', function (e) { e.preventDefault(); doSave(); });
})();
</script>
@endpush
