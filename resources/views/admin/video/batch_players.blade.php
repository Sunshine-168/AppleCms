@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('page.tool_players'))

@php
    $players = is_array($players ?? null) ? $players : [];
    $usages = is_array($usages ?? null) ? $usages : [];
    $sourceN = (int) ($source_n ?? 0);
    $unknownN = (int) ($unknown_n ?? 0);
    $jsLang = [
        'empty_usages' => admin_t('ui.play_empty_usages'),
        'empty_code' => admin_t('ui.play_empty_code'),
        'in_table' => admin_t('ui.play_in_table'),
        'not_in_table' => admin_t('ui.play_not_in_table'),
        'on_air_n' => admin_t('ui.play_on_air_n'),
        'use_from' => admin_t('ui.play_use_from'),
        'sources_n' => admin_t('ui.sources_n', ['n' => '__N__']),
        'finished' => admin_t('ui.completed'),
        'hint_disable' => admin_t('ui.play_hint_disable'),
        'hint_created' => admin_t('ui.play_hint_created'),
        'hint_rename' => admin_t('ui.play_hint_rename'),
        'go_check' => admin_t('ui.play_go_check'),
        'need_from' => admin_t('ui.play_need_from'),
        'need_to' => admin_t('ui.play_need_to'),
        'confirm_disable' => admin_t('ui.play_confirm_disable'),
        'confirm_disable_n' => admin_t('ui.play_confirm_disable_n'),
        'confirm_rename' => admin_t('ui.play_confirm_rename'),
        'confirm_rename_n' => admin_t('ui.play_confirm_rename_n'),
        'op_fail' => admin_t('ui.op_fail'),
        'net_retry' => admin_t('ui.net_retry'),
    ];
@endphp

@section('plain')
<div class="card card-panel play-index">
    <div class="card-header">
        <span>{{ admin_t('ui.play_title') }}</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/players">{{ admin_t('ui.players') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/config/player">{{ admin_t('ui.config_player') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video">{{ admin_t('ui.video_list') }}</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.play_lead') }}</p>

        @if($unknownN > 0)
            <p class="muted play-note">{{ admin_t('ui.play_unknown_n', ['n' => $unknownN]) }}</p>
        @endif

        <form class="play-form" id="play-form" onsubmit="return false;">
            <div class="play-fields">
                <div>
                    <label for="play-from">{{ admin_t('ui.play_from') }}</label>
                    <input id="play-from" type="text" name="from" placeholder="{{ admin_t('ui.ph_play_from') }}" autocomplete="off" spellcheck="false">
                </div>
                <div>
                    <label for="play-to">{{ admin_t('ui.play_to') }}</label>
                    <input id="play-to" type="text" name="to" list="play-to-list" placeholder="{{ admin_t('ui.ph_play_to') }}" autocomplete="off" spellcheck="false">
                    <datalist id="play-to-list">
                        @foreach($players as $p)
                            @php $code = trim((string) ($p['code'] ?? '')); @endphp
                            @if($code !== '')
                                <option value="{{ $code }}">{{ trim((string) ($p['name'] ?? $code)) }}</option>
                            @endif
                        @endforeach
                    </datalist>
                </div>
            </div>
            <p class="muted field-hint">{{ admin_t('ui.play_form_hint') }}</p>
            <div class="hub-actions play-ops">
                <button type="button" class="btn" id="play-rename-btn">{{ admin_t('ui.play_rename') }}</button>
                <button type="button" class="btn btn-danger" id="play-off-btn">{{ admin_t('ui.play_off') }}</button>
            </div>
        </form>

        <div class="hub-result" id="play-result">
            <div class="hub-empty" id="play-empty">
                <p>{{ admin_t('ui.play_empty') }}</p>
                <p class="muted">{{ admin_t('ui.play_empty_hint') }}</p>
            </div>
            <div class="hub-fail" id="play-fail" hidden>
                <p class="hub-fail-title">{{ admin_t('ui.play_fail_title') }}</p>
                <p class="hub-fail-msg" id="play-fail-msg"></p>
                <p class="muted">{{ admin_t('ui.play_fail_hint') }}</p>
            </div>
            <div class="hub-ok" id="play-ok" hidden>
                <p class="hub-ok-stats" id="play-ok-stats"></p>
                <p class="muted" id="play-ok-hint"></p>
                <div class="hub-actions" id="play-ok-actions"></div>
            </div>
        </div>

        <div class="play-block">
            <div class="hub-saved-head">
                <strong>{{ admin_t('ui.play_usage_title') }}</strong>
                @if($sourceN > 0)
                    <span class="muted">{{ admin_t('ui.sources_n', ['n' => $sourceN]) }}</span>
                @endif
            </div>
            <div id="play-usages">
                @if($usages === [])
                    <p class="muted hub-saved-empty">{{ admin_t('ui.play_empty_usages') }}</p>
                @else
                    <div class="img-list">
                        @foreach($usages as $u)
                            @php
                                $code = trim((string) ($u['code'] ?? ''));
                                $count = (int) ($u['count'] ?? 0);
                                $onN = (int) ($u['on_n'] ?? $count);
                                $known = (bool) ($u['known'] ?? false);
                                $name = trim((string) ($u['name'] ?? ''));
                                $engine = trim((string) ($u['engine'] ?? ''));
                            @endphp
                            <div class="img-row play-use" data-code="{{ $code }}" data-count="{{ $count }}">
                                <div>
                                    <strong>{{ $code !== '' ? $code : admin_t('ui.play_empty_code') }}</strong>
                                    @if($known)
                                        <span class="badge badge-ok">{{ $name !== '' ? $name : admin_t('ui.play_in_table') }}</span>
                                    @elseif($code !== '')
                                        <span class="badge">{{ admin_t('ui.play_not_in_table') }}</span>
                                    @endif
                                    @if($engine !== '')
                                        <span class="muted">{{ $engine }}</span>
                                    @endif
                                    <div class="muted">{{ admin_t('ui.sources_n', ['n' => $count]) }}@if($onN < $count) · {{ admin_t('ui.play_on_air_n', ['n' => $onN]) }}@endif</div>
                                </div>
                                <div class="img-row-ops">
                                    @if($code !== '')
                                        <button type="button" class="btn-link play-fill-from" data-code="{{ $code }}">{{ admin_t('ui.play_use_from') }}</button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="play-block">
            <div class="hub-saved-head">
                <strong>{{ admin_t('ui.play_catalog_title') }}</strong>
                @if(count($players) > 0)
                    <span class="muted">{{ admin_t('ui.play_catalog_hint') }}</span>
                @endif
            </div>
            <div id="play-catalog">
                @if($players === [])
                    <p class="muted hub-saved-empty">{{ admin_t('ui.play_no_players_before') }}<a href="/admin/video/players">{{ admin_t('ui.players') }}</a>{{ admin_t('ui.play_no_players_after') }}</p>
                @else
                    <div class="hub-tags play-tags">
                        @foreach($players as $p)
                            @php
                                $code = trim((string) ($p['code'] ?? ''));
                                $name = trim((string) ($p['name'] ?? ''));
                                $engine = trim((string) ($p['engine'] ?? ''));
                                $on = (int) ($p['status'] ?? 1) === 1;
                            @endphp
                            @if($code !== '')
                                <button type="button" class="hub-tag play-fill-to{{ $on ? '' : ' is-off' }}" data-code="{{ $code }}" title="{{ $engine }}">
                                    {{ $name !== '' && $name !== $code ? $name.' · '.$code : $code }}
                                </button>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($jsLang, JSON_UNESCAPED_UNICODE);
    var fromEl = document.getElementById('play-from');
    var toEl = document.getElementById('play-to');
    var emptyEl = document.getElementById('play-empty');
    var failEl = document.getElementById('play-fail');
    var failMsg = document.getElementById('play-fail-msg');
    var okEl = document.getElementById('play-ok');
    var usagesWrap = document.getElementById('play-usages');

    function show(which) {
        emptyEl.hidden = which !== 'empty';
        failEl.hidden = which !== 'fail';
        okEl.hidden = which !== 'ok';
    }
    function usageCount(code) {
        var n = 0;
        document.querySelectorAll('.play-use').forEach(function (row) {
            if ((row.getAttribute('data-code') || '') === code) {
                n = parseInt(row.getAttribute('data-count'), 10) || 0;
            }
        });
        return n;
    }
    function renderUsages(usages) {
        if (!usagesWrap) return;
        if (!usages || !usages.length) {
            usagesWrap.innerHTML = '<p class="muted hub-saved-empty">' + U.escape(L.empty_usages) + '</p>';
            return;
        }
        var html = '<div class="img-list">';
        usages.forEach(function (u) {
            var code = String(u.code || '');
            var count = parseInt(u.count, 10) || 0;
            var onN = parseInt(u.on_n, 10);
            if (isNaN(onN)) onN = count;
            var title = code !== '' ? U.escape(code) : U.escape(L.empty_code);
            var badge = u.known
                ? '<span class="badge badge-ok">' + U.escape(u.name || L.in_table) + '</span>'
                : (code !== '' ? '<span class="badge">' + U.escape(L.not_in_table) + '</span>' : '');
            var engine = u.engine ? '<span class="muted">' + U.escape(u.engine) + '</span>' : '';
            var extra = onN < count ? ' · ' + String(L.on_air_n || '').replace(':n', String(onN)) : '';
            var op = code !== ''
                ? '<button type="button" class="btn-link play-fill-from" data-code="' + U.escape(code) + '">' + U.escape(L.use_from) + '</button>'
                : '';
            html += '<div class="img-row play-use" data-code="' + U.escape(code) + '" data-count="' + count + '">';
            html += '<div><strong>' + title + '</strong> ' + badge + ' ' + engine;
            html += '<div class="muted">' + String(L.sources_n || '').replace('__N__', String(count)) + extra + '</div></div>';
            html += '<div class="img-row-ops">' + op + '</div></div>';
        });
        html += '</div>';
        usagesWrap.innerHTML = html;
        bindFill();
    }
    function renderOk(data, msg) {
        document.getElementById('play-ok-stats').textContent = msg || L.finished;
        var hint = '';
        if (data && data.mode === 'disable') {
            hint = L.hint_disable;
        } else if (data && data.created) {
            hint = L.hint_created;
        } else {
            hint = L.hint_rename;
        }
        document.getElementById('play-ok-hint').textContent = hint;
        var actions = document.getElementById('play-ok-actions');
        actions.innerHTML = '';
        var go = document.createElement('a');
        go.className = 'btn';
        go.href = '/admin/video/players';
        go.textContent = L.go_check;
        actions.appendChild(go);
        renderUsages((data && data.usages) || []);
        show('ok');
    }
    function run(mode) {
        var from = String(fromEl.value || '').trim();
        var to = String(toEl.value || '').trim();
        if (!from) {
            U.toast(L.need_from, 'err');
            fromEl.focus();
            return;
        }
        if (mode === 'rename' && !to) {
            U.toast(L.need_to, 'err');
            toEl.focus();
            return;
        }
        var n = usageCount(from);
        var confirmMsg;
        if (mode === 'disable') {
            confirmMsg = n
                ? String(L.confirm_disable_n || '').replace(':from', from).replace(':n', String(n))
                : String(L.confirm_disable || '').replace(':from', from);
        } else {
            confirmMsg = n
                ? String(L.confirm_rename_n || '').replace(':from', from).replace(':to', to).replace(':n', String(n))
                : String(L.confirm_rename || '').replace(':from', from).replace(':to', to);
        }
        if (!U.confirm(confirmMsg)) return;
        U.loading(true);
        U.post('/admin/video/tools/players/run', {action: 'replace', from: from, to: to, mode: mode}).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) {
                failMsg.textContent = (res && res.msg) || L.op_fail;
                if (res && res.data && res.data.usages) renderUsages(res.data.usages);
                show('fail');
                U.toast((res && res.msg) || L.op_fail, 'err');
                return;
            }
            renderOk(res.data || {}, res.msg || '');
            U.toast(res.msg || L.finished, 'ok');
        }).catch(function () {
            U.loading(false);
            failMsg.textContent = L.net_retry;
            show('fail');
            U.toast(L.op_fail, 'err');
        });
    }
    function bindFill() {
        document.querySelectorAll('.play-fill-from').forEach(function (btn) {
            btn.addEventListener('click', function () {
                fromEl.value = btn.getAttribute('data-code') || '';
                fromEl.focus();
            });
        });
    }
    document.querySelectorAll('.play-fill-to').forEach(function (btn) {
        btn.addEventListener('click', function () {
            toEl.value = btn.getAttribute('data-code') || '';
            toEl.focus();
        });
    });
    bindFill();
    document.getElementById('play-rename-btn').addEventListener('click', function () { run('rename'); });
    document.getElementById('play-off-btn').addEventListener('click', function () { run('disable'); });
})();
</script>
@endpush
