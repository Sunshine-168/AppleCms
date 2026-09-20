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
    $jsLang = [
        'please_fill_ip' => admin_t('ui.please_fill_ip'),
        'ip_already' => admin_t('ui.ip_already'),
        'ip_added' => admin_t('ui.ip_added'),
        'current' => admin_t('ui.ip_current_mark'),
        'remove' => admin_t('ui.ip_remove'),
        'empty_list' => admin_t('ui.ip_empty_on'),
        'no_change_save' => admin_t('ui.no_change_save'),
        'finished' => admin_t('ui.finished'),
    ];
@endphp

@section('plain')
<div class="card card-panel ip-config-index">
    <div class="card-header">
        <span>{{ admin_t('ui.config_ip') }}</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/system/monitor/login-logs">{{ admin_t('ui.login_logs') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/safety">{{ admin_t('ui.safety') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/settings?tab=more">{{ admin_t('ui.site_settings') }}</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.ip_lead_before') }}<code>/admin</code>{{ admin_t('ui.ip_lead_after') }}</p>

        <div class="ai-stock">
            @if($enabled)
                <span class="badge badge-warn">{{ admin_t('ui.ip_limited_n', ['n' => count($rules)]) }}</span>
                @if($currentOk)
                    <span class="badge badge-ok">{{ admin_t('ui.ip_current_ok') }}</span>
                @else
                    <span class="badge badge-warn">{{ admin_t('ui.ip_current_miss') }}</span>
                @endif
            @else
                <span class="badge">{{ admin_t('ui.ip_unlimited') }}</span>
                <span class="muted">{{ admin_t('ui.ip_open_login') }}</span>
            @endif
        </div>

        <div class="ip-now">
            <div>
                <div class="label">{{ admin_t('ui.ip_now_label') }}</div>
                <div class="value" id="ip-current">{{ $currentIp !== '' ? $currentIp : admin_t('ui.unavailable') }}</div>
                @if($local)
                    <p class="muted">{{ admin_t('ui.ip_local_hint') }}</p>
                @endif
                @if($forwardedIp !== '')
                    <p class="muted">{{ admin_t('ui.ip_forwarded_hint', ['ip' => $forwardedIp]) }}</p>
                @endif
            </div>
            <button type="button" class="btn" id="ip-add-current" @disabled($currentIp === '')>{{ admin_t('ui.ip_add_current') }}</button>
        </div>

        <form class="admin-form settings-page ip-config-form" id="site-form">
            <h3>{{ admin_t('ui.ip_mode_title') }}</h3>
            <div class="ingest-modes" id="ip-modes">
                <button type="button" class="ingest-mode{{ $enabled ? '' : ' is-on' }}" data-mode="off">
                    <strong>{{ admin_t('ui.ip_mode_off') }}</strong>
                    <span>{{ admin_t('ui.ip_mode_off_hint') }}</span>
                </button>
                <button type="button" class="ingest-mode{{ $enabled ? ' is-on' : '' }}" data-mode="on">
                    <strong>{{ admin_t('ui.ip_mode_on') }}</strong>
                    <span>{{ admin_t('ui.ip_mode_on_hint') }}</span>
                </button>
            </div>

            <div id="ip-list-wrap" @if(! $enabled) hidden @endif>
                <h3>{{ admin_t('ui.ip_allow_list') }}</h3>
                <p class="muted field-hint">{{ admin_t('ui.ip_allow_hint_before') }}<code>192.168.1.0/24</code>{{ admin_t('ui.ip_allow_hint_mid') }}<code>*</code>{{ admin_t('ui.ip_allow_hint_after') }}</p>
                <div class="field-inline">
                    <input id="ip-new" type="text" placeholder="{{ admin_t('ui.ph_ip_add') }}" autocomplete="off" spellcheck="false">
                    <button type="button" class="btn btn-muted" id="ip-add-one">{{ admin_t('ui.add') }}</button>
                </div>
                <ul class="ip-chips" id="ip-chips"></ul>
                <label for="admin_ip_allow">{{ admin_t('ui.ip_raw_edit') }}</label>
                <textarea id="admin_ip_allow" name="admin_ip_allow" rows="6" placeholder="{{ admin_t('ui.ph_ip_raw') }}">{{ $raw }}</textarea>
                @if($recent !== [])
                    <p class="muted field-hint">{{ admin_t('ui.ip_recent_hint') }}</p>
                    <div class="ip-recent" id="ip-recent">
                        @foreach($recent as $rip)
                            <button type="button" class="btn btn-muted btn-sm" data-ip="{{ $rip }}">{{ $rip }}</button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="interface-howto">
                <p>{{ admin_t('ui.ip_lockout_title') }}</p>
                <ul>
                    <li>{{ admin_t('ui.ip_lockout_cli') }} <code>php artisan video:admin-ip-clear</code></li>
                    <li>{{ admin_t('ui.ip_lockout_db_before') }} <code>video_options</code> {{ admin_t('ui.ip_lockout_db_mid') }} <code>admin_ip_allow</code> {{ admin_t('ui.ip_lockout_db_after') }}</li>
                </ul>
                <p class="muted">{{ admin_t('ui.ip_lockout_note') }}</p>
            </div>

            <div class="form-actions settings-save">
                <button type="button" class="btn" id="site-save">{{ admin_t('ui.save') }}</button>
                <a class="btn btn-muted" href="/admin/system/monitor/login-logs">{{ admin_t('ui.view_login_logs') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = window.AdminUi;
    var L = @json($jsLang, JSON_UNESCAPED_UNICODE);
    var modeOn = {{ $enabled ? 'true' : 'false' }};
    var current = @json($currentIp, JSON_UNESCAPED_UNICODE);
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
        if (!ip) { U && U.toast(L.please_fill_ip, 'err'); return; }
        if (!modeOn) setMode(true);
        var cur = lines(ta ? ta.value : '');
        var k = ip.toLowerCase();
        if (cur.some(function (x) { return x.toLowerCase() === k; })) {
            U && U.toast(String(L.ip_already || '').replace(':ip', ip), 'ok');
            renderChips();
            return;
        }
        cur.push(ip);
        if (ta) ta.value = cur.join('\n');
        renderChips();
        U && U.toast(String(L.ip_added || '').replace(':ip', ip), 'ok');
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
                mark.textContent = L.current;
                span.appendChild(mark);
            }
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn-link';
            btn.textContent = L.remove;
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
            U && U.toast(L.empty_list, 'err');
            return;
        }
        if (modeOn === originalOn && value === original) {
            U && U.toast(L.no_change_save, 'ok');
            return;
        }
        U.post('/admin/video/settings', { admin_ip_allow: value }).then(function (res) {
            U.toast((res && res.msg) || L.finished, res && res.code === 0 ? 'ok' : 'err');
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
