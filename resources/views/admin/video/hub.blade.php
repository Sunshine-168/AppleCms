@extends('admin.layouts.inner')
@section('title', admin_t('page.tool_hub'))

@php
    $unions = is_array($unions ?? null) ? $unions : [];
    $unionCount = (int) ($union_count ?? count($unions));
    $collectCount = (int) ($collect_count ?? 0);
    $pendingCount = (int) ($pending_count ?? 0);
    $prefill = (string) ($prefill ?? '');
    $jsLang = [
        'ok_title' => admin_t('ui.hub_ok_title'),
        'unknown_host' => admin_t('ui.hub_unknown_host'),
        'types_n' => admin_t('ui.hub_types_n'),
        'about_n' => admin_t('ui.hub_about_n'),
        'over_100k' => admin_t('ui.hub_over_100k'),
        'samples' => admin_t('ui.hub_samples'),
        'list_sep' => admin_t('ui.list_sep'),
        'no_samples' => admin_t('ui.hub_no_samples'),
        'go_bind' => admin_t('ui.hub_go_bind'),
        'already_collect' => admin_t('ui.hub_already_collect'),
        'favors' => admin_t('ui.favors'),
        'adopt_collect' => admin_t('ui.adopt_collect'),
        'adopt_need_bind' => admin_t('ui.hub_adopt_need_bind'),
        'need_url' => admin_t('ui.hub_need_url'),
        'probe_fail' => admin_t('ui.hub_probe_fail'),
        'net_retry' => admin_t('ui.net_retry'),
        'need_probe' => admin_t('ui.hub_need_probe'),
        'resource_site' => admin_t('ui.col_union'),
        'already_saved' => admin_t('ui.hub_already_saved'),
        'favor_fail' => admin_t('ui.hub_favor_fail'),
        'favor_ok' => admin_t('ui.hub_favor_ok'),
        'adopt_fail' => admin_t('ui.adopt_fail'),
        'adopt_ok' => admin_t('ui.adopt_ok'),
        'favor_incomplete' => admin_t('ui.hub_favor_incomplete'),
        'confirm_adopt' => admin_t('ui.hub_confirm_adopt'),
    ];
@endphp

@section('plain')
<div class="card card-panel hub-index">
    <div class="card-header">
        <span>{{ admin_t('ui.hub_title') }}</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/unions">{{ admin_t('ui.unions') }}@if($unionCount > 0) · {{ $unionCount }}@endif</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/collects">{{ admin_t('ui.collects') }}@if($collectCount > 0) · {{ $collectCount }}@endif</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.hub_lead') }}</p>

        <form class="hub-probe" id="hub-form" onsubmit="return false;">
            <label for="hub-url">{{ admin_t('ui.label_api_url') }}</label>
            <div class="field-inline hub-probe-row">
                <input id="hub-url" type="text" name="api_url" value="{{ $prefill }}" placeholder="{{ admin_t('ui.ph_hub_url') }}" autocomplete="off" spellcheck="false">
                <button type="submit" class="btn" id="hub-probe-btn">{{ admin_t('ui.hub_probe') }}</button>
            </div>
            <p class="muted field-hint">{{ admin_t('ui.hub_url_hint_before') }}<code>{{ admin_t('ui.ph_hub_api_ex') }}</code>{{ admin_t('ui.hub_url_hint_after') }}</p>
        </form>

        <div class="hub-result" id="hub-result">
            <div class="hub-empty" id="hub-empty">
                <p>{{ admin_t('ui.hub_empty') }}</p>
                <p class="muted">{{ admin_t('ui.hub_empty_hint') }}</p>
            </div>
            <div class="hub-fail" id="hub-fail" hidden>
                <p class="hub-fail-title">{{ admin_t('ui.hub_fail_title') }}</p>
                <p class="hub-fail-msg" id="hub-fail-msg"></p>
                <p class="muted">{{ admin_t('ui.hub_fail_hint') }}</p>
            </div>
            <div class="hub-ok" id="hub-ok" hidden>
                <div class="hub-ok-head">
                    <strong id="hub-ok-title">{{ admin_t('ui.hub_ok_title') }}</strong>
                    <span class="badge badge-ok" id="hub-ok-host"></span>
                    <span class="muted" id="hub-ok-format"></span>
                </div>
                <p class="hub-ok-stats" id="hub-ok-stats"></p>
                <div class="hub-tags" id="hub-ok-types"></div>
                <p class="muted hub-samples" id="hub-ok-samples"></p>
                <div class="hub-actions" id="hub-ok-actions"></div>
            </div>
        </div>

        <div class="hub-saved">
            <div class="hub-saved-head">
                <strong>{{ admin_t('ui.hub_saved_title') }}</strong>
                @if($pendingCount > 0)
                    <span class="muted">{{ admin_t('ui.hub_pending_n', ['n' => $pendingCount]) }}</span>
                @endif
            </div>
            @if($unions === [])
                <p class="muted hub-saved-empty">{{ admin_t('ui.hub_saved_empty') }}</p>
            @else
                <div class="hub-saved-list">
                    @foreach($unions as $u)
                        @php
                            $name = trim((string) ($u['name'] ?? ''));
                            $url = trim((string) ($u['api_url'] ?? ''));
                            $host = trim((string) ($u['host'] ?? ''));
                            $adopted = (int) ($u['adopted'] ?? 0) === 1;
                        @endphp
                        <div class="hub-saved-row">
                            <div>
                                <strong>{{ $name !== '' ? $name : ($host !== '' ? $host : admin_t('ui.unnamed')) }}</strong>
                                @if($adopted)
                                    <span class="badge badge-ok">{{ admin_t('ui.adopted') }}</span>
                                @else
                                    <span class="badge">{{ admin_t('ui.not_adopted') }}</span>
                                @endif
                                <div class="muted">{{ $host !== '' ? $host : $url }}</div>
                            </div>
                            <div class="hub-saved-ops">
                                <button type="button" class="btn-link hub-fill" data-url="{{ $url }}">{{ admin_t('ui.hub_probe') }}</button>
                                @if($adopted)
                                    <a class="btn-link" href="/admin/video/collects">{{ admin_t('ui.go_collects') }}</a>
                                @else
                                    <button type="button" class="btn-link hub-adopt" data-id="{{ (int) ($u['id'] ?? 0) }}" data-url="{{ $url }}">{{ admin_t('ui.hub_adopt') }}</button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($jsLang, JSON_UNESCAPED_UNICODE);
    var form = document.getElementById('hub-form');
    var input = document.getElementById('hub-url');
    var probeBtn = document.getElementById('hub-probe-btn');
    var emptyEl = document.getElementById('hub-empty');
    var failEl = document.getElementById('hub-fail');
    var failMsg = document.getElementById('hub-fail-msg');
    var okEl = document.getElementById('hub-ok');
    var last = null;

    function show(which) {
        emptyEl.hidden = which !== 'empty';
        failEl.hidden = which !== 'fail';
        okEl.hidden = which !== 'ok';
    }
    function fmtCount(n) {
        n = parseInt(n, 10) || 0;
        if (n <= 0) return '0';
        if (n > 99999) return L.over_100k;
        return String(n);
    }
    function renderOk(data) {
        last = data || {};
        document.getElementById('hub-ok-title').textContent = L.ok_title;
        document.getElementById('hub-ok-host').textContent = last.host || L.unknown_host;
        var fmt = last.format === 'xml' ? 'XML' : (last.format === 'json' ? 'JSON' : '');
        document.getElementById('hub-ok-format').textContent = fmt ? ('· ' + fmt) : '';
        var stats = [];
        stats.push(String(L.types_n || '').replace(':n', fmtCount(last.type_count)));
        if ((parseInt(last.record_count, 10) || 0) > 0) stats.push(String(L.about_n || '').replace(':n', fmtCount(last.record_count)));
        document.getElementById('hub-ok-stats').textContent = stats.join(' · ');
        var typesWrap = document.getElementById('hub-ok-types');
        typesWrap.innerHTML = '';
        (last.type_names || []).forEach(function (name) {
            var tag = document.createElement('span');
            tag.className = 'hub-tag';
            tag.textContent = name;
            typesWrap.appendChild(tag);
        });
        var samples = last.sample_titles || [];
        document.getElementById('hub-ok-samples').textContent = samples.length
            ? String(L.samples || '').replace(':list', samples.join(L.list_sep || ','))
            : L.no_samples;
        var actions = document.getElementById('hub-ok-actions');
        actions.innerHTML = '';
        if (last.collect_id) {
            var go = document.createElement('a');
            go.className = 'btn';
            go.href = '/admin/video/collects';
            go.textContent = L.go_bind;
            actions.appendChild(go);
            var note = document.createElement('span');
            note.className = 'muted';
            note.textContent = L.already_collect;
            actions.appendChild(note);
        } else {
            if (!last.union_id) {
                var save = document.createElement('button');
                save.type = 'button';
                save.className = 'btn btn-muted';
                save.textContent = L.favors;
                save.addEventListener('click', saveCurrent);
                actions.appendChild(save);
            }
            var adopt = document.createElement('button');
            adopt.type = 'button';
            adopt.className = 'btn';
            adopt.textContent = L.adopt_collect;
            adopt.addEventListener('click', adoptCurrent);
            actions.appendChild(adopt);
            var hint = document.createElement('span');
            hint.className = 'muted';
            hint.textContent = L.adopt_need_bind;
            actions.appendChild(hint);
        }
        show('ok');
    }
    function probe(url) {
        url = String(url || input.value || '').trim();
        if (!url) {
            U.toast(L.need_url, 'err');
            input.focus();
            return;
        }
        input.value = url;
        probeBtn.disabled = true;
        U.loading(true);
        U.post('/admin/video/tools/hub/run', {action: 'probe', api_url: url}).then(function (res) {
            U.loading(false);
            probeBtn.disabled = false;
            if (!res || res.code !== 0) {
                failMsg.textContent = (res && res.msg) || L.probe_fail;
                last = null;
                show('fail');
                U.toast((res && res.msg) || L.probe_fail, 'err');
                return;
            }
            if (res.data && res.data.api_url) input.value = res.data.api_url;
            renderOk(res.data || {});
            U.toast((res && res.msg) || L.ok_title, 'ok');
        }).catch(function () {
            U.loading(false);
            probeBtn.disabled = false;
            failMsg.textContent = L.net_retry;
            show('fail');
            U.toast(L.probe_fail, 'err');
        });
    }
    function saveUnion(thenAdopt) {
        if (!last || !last.api_url) {
            U.toast(L.need_probe, 'err');
            return;
        }
        var payload = {
            name: last.host || L.resource_site,
            api_url: last.api_url,
            status: 1
        };
        if (last.union_id) {
            if (thenAdopt) return adoptUnion(last.union_id);
            U.toast(L.already_saved, 'ok');
            return;
        }
        U.loading(true);
        U.post('/admin/video/unions/save', payload).then(function (res) {
            if (!res || res.code !== 0) {
                U.loading(false);
                U.toast((res && res.msg) || L.favor_fail, 'err');
                return;
            }
            last.union_id = (res.data && res.data.id) || last.union_id;
            if (thenAdopt && last.union_id) return adoptUnion(last.union_id);
            U.loading(false);
            renderOk(last);
            U.toast(L.favor_ok, 'ok');
        }).catch(function () {
            U.loading(false);
            U.toast(L.favor_fail, 'err');
        });
    }
    function saveCurrent() { saveUnion(false); }
    function adoptCurrent() { saveUnion(true); }
    function adoptUnion(id) {
        U.loading(true);
        return U.post('/admin/video/unions/adopt', {id: id}).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) {
                U.toast((res && res.msg) || L.adopt_fail, 'err');
                return;
            }
            U.toast((res && res.msg) || L.adopt_ok, 'ok');
            location.href = '/admin/video/collects';
        }).catch(function () {
            U.loading(false);
            U.toast(L.adopt_fail, 'err');
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        probe(input.value);
    });
    document.querySelectorAll('.hub-fill').forEach(function (btn) {
        btn.addEventListener('click', function () {
            probe(btn.getAttribute('data-url') || '');
        });
    });
    document.querySelectorAll('.hub-adopt').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = parseInt(btn.getAttribute('data-id'), 10) || 0;
            if (!id) {
                U.toast(L.favor_incomplete, 'err');
                return;
            }
            if (!U.confirm(L.confirm_adopt)) return;
            adoptUnion(id);
        });
    });
    if (String(input.value || '').trim()) probe(input.value);
})();
</script>
@endpush
