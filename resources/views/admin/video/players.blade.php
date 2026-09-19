@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'artplayer' => 0, 'dplayer' => 0, 'videojs' => 0, 'iframe' => 0, 'off' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $playerJsLang = [
        'status' => admin_t('ui.status'),
        'actions' => admin_t('ui.actions'),
        'edit' => admin_t('ui.edit'),
        'delete' => admin_t('ui.delete'),
        'enabled' => admin_t('ui.enabled'),
        'disabled' => admin_t('ui.disabled'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'created' => admin_t('ui.created'),
        'deleted' => admin_t('ui.deleted'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'selected_n' => admin_t('ui.selected_n', ['n' => '__N__']),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'unnamed' => admin_t('ui.unnamed'),
        'players' => admin_t('ui.players'),
        'sort' => admin_t('ui.sort'),
        'slug' => admin_t('ui.slug'),
        'add_player' => admin_t('ui.add_player'),
        'edit_player' => admin_t('ui.edit_player'),
        'ensure_builtin' => admin_t('ui.ensure_builtin'),
        'sources_n' => admin_t('ui.sources_n', ['n' => '__N__']),
        'has_parse' => admin_t('ui.has_parse'),
        'empty_players' => admin_t('ui.empty_players'),
        'empty_players_hint' => admin_t('ui.empty_players_hint'),
        'no_match_players' => admin_t('ui.no_match_players'),
        'please_fill_name' => admin_t('ui.please_fill_name'),
        'please_fill_code' => admin_t('ui.please_fill_code'),
        'please_select_players' => admin_t('ui.please_select_players'),
        'please_pick_engine' => admin_t('ui.please_pick_engine'),
        'confirm_batch_del_players' => admin_t('ui.confirm_batch_del_players'),
        'confirm_del_player' => admin_t('ui.confirm_del_player', ['name' => '__NAME__']),
        'copy_code' => admin_t('ui.copy_code'),
        'code_copied' => admin_t('ui.code_copied'),
        'finished' => admin_t('ui.finished'),
    ];
@endphp

@section('plain')
<div class="card card-panel player-index list-desk">
    <div class="card-header">
        <span>{{ admin_t('ui.players') }} <em id="player-count"></em></span>
        <div>
            <button type="button" class="btn btn-muted btn-sm" id="player-ensure-btn">{{ admin_t('ui.ensure_builtin') }}</button>
            <button type="button" class="btn btn-sm" id="player-add-btn">{{ admin_t('ui.add_player') }}</button>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="player-search" onsubmit="return false;">
            <input type="hidden" name="engine">
            <input type="text" name="name" placeholder="{{ admin_t('ui.ph_player') }}" autocomplete="off">
            <select name="status">
                <option value="">{{ admin_t('ui.status') }}</option>
                <option value="1">{{ admin_t('ui.enabled') }}</option>
                <option value="0">{{ admin_t('ui.disabled') }}</option>
            </select>
            <button type="button" class="btn btn-sm" id="player-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="player-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="player-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="engine" data-value="artplayer">ArtPlayer @if($q('artplayer') > 0)<em>{{ $q('artplayer') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="engine" data-value="dplayer">DPlayer @if($q('dplayer') > 0)<em>{{ $q('dplayer') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="engine" data-value="videojs">Video.js @if($q('videojs') > 0)<em>{{ $q('videojs') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="engine" data-value="iframe">{{ admin_t('ui.engine_parse') }} @if($q('iframe') > 0)<em>{{ $q('iframe') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.deactivated') }}@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">{{ admin_t('ui.players_lead') }}</p>
        <div class="player-engines">
            <article>
                <strong>ArtPlayer</strong>
                <p>{{ admin_t('ui.engine_artplayer_lead') }}</p>
            </article>
            <article>
                <strong>DPlayer</strong>
                <p>{{ admin_t('ui.engine_dplayer_lead') }}</p>
            </article>
            <article>
                <strong>Video.js</strong>
                <p>{{ admin_t('ui.engine_videojs_lead') }}</p>
            </article>
            <article>
                <strong>{{ admin_t('ui.engine_parse_title') }}</strong>
                <p>{{ admin_t('ui.engine_parse_lead') }}</p>
            </article>
        </div>
        <div class="batch-bar" id="player-batch" hidden>
            <strong id="player-batch-count">{{ admin_t('ui.selected_n', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="player-batch-on">{{ admin_t('ui.enabled') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="player-batch-off">{{ admin_t('ui.disabled') }}</button>
            <select id="player-batch-engine" class="batch-select" aria-label="{{ admin_t('ui.move') }}">
                <option value="">{{ admin_t('ui.move') }}</option>
                <option value="artplayer">ArtPlayer</option>
                <option value="dplayer">DPlayer</option>
                <option value="videojs">Video.js</option>
                <option value="iframe">{{ admin_t('ui.engine_parse') }}</option>
            </select>
            <button type="button" class="btn btn-muted btn-sm" id="player-batch-move">{{ admin_t('ui.move') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="player-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="player-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="player-table"></div>
    </div>
</div>
<template id="player-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>{{ admin_t('ui.name') }}</label>
        <input type="text" name="name" placeholder="{{ admin_t('ui.ph_player_name') }}">
        <p class="muted field-hint">{{ admin_t('ui.hint_player_name') }}</p>
        <label>{{ admin_t('ui.slug') }}</label>
        <input type="text" name="code" placeholder="{{ admin_t('ui.ph_player_code') }}">
        <p class="muted field-hint">{{ admin_t('ui.hint_player_code') }}</p>
        <label>{{ admin_t('ui.label_engine') }}</label>
        <select name="engine">
            <option value="artplayer">{{ admin_t('ui.engine_artplayer_opt') }}</option>
            <option value="dplayer">{{ admin_t('ui.engine_dplayer_opt') }}</option>
            <option value="videojs">{{ admin_t('ui.engine_videojs_opt') }}</option>
            <option value="iframe">{{ admin_t('ui.engine_parse_opt') }}</option>
        </select>
        <div id="player-parse-wrap">
            <label>{{ admin_t('ui.label_parse_url') }}</label>
            <textarea name="parse" class="player-parse" placeholder="{{ admin_t('ui.ph_player_parse') }}"></textarea>
            <p class="muted field-hint">{{ admin_t('ui.hint_player_parse') }}</p>
        </div>
        <div class="admin-dialog-grid">
            <div>
                <label>{{ admin_t('ui.sort') }}</label>
                <input type="number" name="sort" value="0">
            </div>
            <div>
                <label>{{ admin_t('ui.status') }}</label>
                <select name="status">
                    <option value="1">{{ admin_t('ui.enabled') }}</option>
                    <option value="0">{{ admin_t('ui.disabled') }}</option>
                </select>
            </div>
        </div>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($playerJsLang);
    var QUEUE_KEYS = ['engine'];
    var form = document.getElementById('player-search');
    var batchBar = document.getElementById('player-batch');
    var batchCount = document.getElementById('player-batch-count');
    var countEl = document.getElementById('player-count');

    function cleanWhere(data) {
        var out = {};
        Object.keys(data).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function queryWhere() {
        return Object.assign({limit: 30}, cleanWhere(U.formData(form)));
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return k !== 'limit' && where[k] !== ''; });
    }
    function markChips() {
        var status = form.status.value;
        var engine = form.engine.value;
        U.qa('#player-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && engine === '') on = true;
            else if (key === 'engine' && status === '' && engine === val) on = true;
            else if (key === 'status' && engine === '' && status === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        form.status.value = '';
        if (key === 'status') form.status.value = value || '';
        else if (key && form[key]) form[key].value = value || '';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function nameHtml(d) {
        var badge = String(d.status) === '1' ? '' : '<span class="badge badge-off">' + L.disabled + '</span>';
        var meta = [U.escape(d.engine_label || d.engine || ''), L.slug + ' ' + U.escape(d.code || '')];
        if (d.source_count) meta.push(String(L.sources_n || '').replace('__N__', String(d.source_count)));
        if (d.parse) meta.push(L.has_parse);
        return '<div><div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(d.name || L.unnamed) + '</a> ' + badge + '</div>'
            + '<div class="entry-row-meta">' + meta.join(' · ') + '</div></div>';
    }

    var table = U.table({
        el: '#player-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/players/list',
        where: queryWhere(),
        pager: false,
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_players + '</p><p><button type="button" class="btn btn-muted btn-sm" id="player-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_players + '</p><p class="muted">' + L.empty_players_hint + '</p><p><button type="button" class="btn btn-primary btn-sm" id="player-empty-ensure">' + L.ensure_builtin + '</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            var ensure = document.getElementById('player-empty-ensure');
            var reset = document.getElementById('player-empty-reset');
            if (ensure) ensure.addEventListener('click', ensurePlayers);
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_n || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.players, html: nameHtml},
            {key: 'sort', title: L.sort, width: 64},
            {title: L.status, width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, L.enabled) : U.status(false, L.disabled);
            }},
            {title: L.actions, cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-copy">' + L.copy_code + '</a><a href="#" class="btn-link js-edit">' + L.edit + '</a><a href="#" class="btn-link js-del">' + L.delete + '</a>';
            }}
        ]
    });
    markChips();

    function copyText(text) {
        if (!text) return Promise.resolve();
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text).catch(function () {});
        }
        var ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(ta);
        return Promise.resolve();
    }
    function bindEngine(formEl) {
        var engine = formEl.querySelector('[name=engine]');
        var wrap = formEl.querySelector('#player-parse-wrap');
        function sync() { wrap.hidden = engine.value !== 'iframe'; }
        engine.addEventListener('change', sync);
        sync();
    }
    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            wide: true,
            title: mode === 'edit' ? L.edit_player : L.add_player,
            content: document.getElementById('player-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var formEl = body.querySelector('form');
                U.fillForm(formEl, {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    code: row.code || (mode === 'add' ? 'artplayer' : ''),
                    engine: row.engine || 'artplayer',
                    parse: row.parse || '',
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? '1' : String(row.status)
                });
                bindEngine(formEl);
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast(L.please_fill_name, 'err'); return false; }
                if (!data.code) { U.toast(L.please_fill_code, 'err'); return false; }
                if (data.engine !== 'iframe') data.parse = data.parse || '';
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/players/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    U.toast(mode === 'edit' ? L.saved : L.created, 'ok');
                    table.refresh();
                });
            }
        });
    }
    function ensurePlayers() {
        U.post('/admin/video/players/ensure', {}).then(function (res) {
            U.toast((res && res.msg) || L.finished, res && res.code === 0 ? 'ok' : 'err');
            if (res && res.code === 0) table.refresh();
        });
    }
    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_players, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/players/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }

    U.on('#player-search-btn', 'click', runSearch);
    U.on('#player-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#player-add-btn', 'click', function () { openDialog('add'); });
    U.on('#player-ensure-btn', 'click', ensurePlayers);
    document.getElementById('player-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#player-batch-on', 'click', function () { batch('status', 1); });
    U.on('#player-batch-off', 'click', function () { batch('status', 0); });
    U.on('#player-batch-move', 'click', function () {
        var val = document.getElementById('player-batch-engine').value;
        if (!val) { U.toast(L.please_pick_engine, 'err'); return; }
        batch('engine', val);
    });
    U.on('#player-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_players); });
    U.on('#player-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#player-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-copy')) {
            copyText(row.code || '').then(function () { U.toast(L.code_copied, 'ok'); });
        }
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm(String(L.confirm_del_player || '').replace('__NAME__', row.name || ''))) return;
            U.post('/admin/video/players/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
