@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('ui.synonyms'))

@php
    $queues = $queues ?? ['all' => 0, 'on' => 0, 'off' => 0, 'empty_to' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $synJsLang = [
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
        'selected_rows' => admin_t('ui.selected_rows', ['n' => '__N__']),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'add_synonym' => admin_t('ui.add_synonym'),
        'edit_synonym' => admin_t('ui.edit_synonym'),
        'col_rule' => admin_t('ui.col_rule'),
        'col_from_word' => admin_t('ui.col_from_word'),
        'col_to_word' => admin_t('ui.col_to_word'),
        'empty_to_word' => admin_t('ui.empty_to_word'),
        'meta_empty_to' => admin_t('ui.meta_empty_to'),
        'meta_synonym_on' => admin_t('ui.meta_synonym_on'),
        'meta_synonym_off' => admin_t('ui.meta_synonym_off'),
        'empty_synonyms' => admin_t('ui.empty_synonyms'),
        'empty_synonyms_hint' => admin_t('ui.empty_synonyms_hint'),
        'no_match_synonyms' => admin_t('ui.no_match_synonyms'),
        'go_searchwords' => admin_t('ui.go_searchwords'),
        'please_fill_from_word' => admin_t('ui.please_fill_from_word'),
        'please_fill_to_word' => admin_t('ui.please_fill_to_word'),
        'from_to_same' => admin_t('ui.from_to_same'),
        'please_select_synonyms' => admin_t('ui.please_select_synonyms'),
        'confirm_batch_del_synonyms' => admin_t('ui.confirm_batch_del_synonyms'),
        'confirm_del_synonym' => admin_t('ui.confirm_del_synonym', ['name' => '__NAME__']),
        'please_try_word' => admin_t('ui.please_try_word'),
    ];
@endphp

@section('plain')
<div class="card card-panel synonym-index" id="synonym-index">
    <div class="card-header">
        <span>{{ admin_t('ui.synonyms') }} <em id="syn-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="syn-add-btn">{{ admin_t('ui.add_synonym') }}</button>
            <a class="btn btn-muted btn-sm" href="/admin/video/searchwords">{{ admin_t('ui.searchwords') }}</a>
            <a class="btn btn-muted btn-sm" href="/search" target="_blank" rel="noopener">{{ admin_t('ui.front_search') }}</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.synonyms_lead') }}</p>
        <form class="filter-bar" id="syn-search" onsubmit="return false;">
            <input type="hidden" name="empty_to">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_synonym') }}" autocomplete="off" aria-label="{{ admin_t('ui.synonyms') }}">
            <select name="status">
                <option value="">{{ admin_t('ui.status') }}</option>
                <option value="1">{{ admin_t('ui.enabled') }}</option>
                <option value="0">{{ admin_t('ui.disabled') }}</option>
            </select>
            <button type="button" class="btn btn-sm" id="syn-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="syn-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="syn-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">{{ admin_t('ui.enabled') }}@if($q('on') > 0)<em>{{ $q('on') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.disabled') }}@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="empty_to" data-value="1">{{ admin_t('ui.chip_empty_to') }}@if($q('empty_to') > 0)<em>{{ $q('empty_to') }}</em>@endif</button>
        </div>
        <form class="filter-bar syn-try-bar" id="syn-try" onsubmit="return false;">
            <input type="search" name="kw" placeholder="{{ admin_t('ui.ph_try_synonym') }}" autocomplete="off" aria-label="{{ admin_t('ui.try_once') }}">
            <button type="button" class="btn btn-muted btn-sm" id="syn-try-btn">{{ admin_t('ui.try_once') }}</button>
            <span class="muted" id="syn-try-out"></span>
        </form>
        <p class="muted field-hint">{{ admin_t('ui.try_synonym_hint') }}</p>
        <div class="batch-bar" id="syn-batch" hidden>
            <strong id="syn-batch-count">{{ admin_t('ui.selected_rows', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="syn-batch-on">{{ admin_t('ui.enabled') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="syn-batch-off">{{ admin_t('ui.disabled') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="syn-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="syn-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="syn-table"></div>
    </div>
</div>
<template id="syn-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>{{ admin_t('ui.label_from_word') }}</label>
        <input type="text" name="from_word" maxlength="80" placeholder="{{ admin_t('ui.ph_from_word') }}" required>
        <label>{{ admin_t('ui.label_to_word') }}</label>
        <input type="text" name="to_word" maxlength="80" placeholder="{{ admin_t('ui.ph_to_word') }}" required>
        <p class="muted field-hint">{{ admin_t('ui.synonym_form_hint') }}</p>
        <label>{{ admin_t('ui.status') }}</label>
        <select name="status">
            <option value="1">{{ admin_t('ui.enabled') }}</option>
            <option value="0">{{ admin_t('ui.disabled') }}</option>
        </select>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($synJsLang);
    var QUEUE_KEYS = ['empty_to'];
    var form = document.getElementById('syn-search');
    var tryForm = document.getElementById('syn-try');
    var tryOut = document.getElementById('syn-try-out');
    var batchBar = document.getElementById('syn-batch');
    var batchCount = document.getElementById('syn-batch-count');
    var countEl = document.getElementById('syn-count');

    function cleanWhere(data) {
        var out = {};
        Object.keys(data).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function queryWhere() {
        return Object.assign({limit: 20}, cleanWhere(U.formData(form)));
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return k !== 'limit' && where[k] !== ''; });
    }
    function markChips() {
        var status = form.status.value;
        var emptyTo = form.empty_to.value;
        U.qa('#syn-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && emptyTo === '') on = true;
            else if (key === 'status' && emptyTo === '' && status === val) on = true;
            else if (key === 'empty_to' && status === '' && emptyTo === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        form.status.value = '';
        if (key === 'status') form.status.value = value || '';
        else if (key && form[key]) form[key].value = value || '1';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function wordHtml(d) {
        var badge = d.is_on ? '' : '<span class="badge badge-off">' + L.disabled + '</span>';
        var preview = d.preview || ((d.from_word || '') + ' → ' + (d.to_word || ''));
        var meta = d.empty_to ? L.meta_empty_to : (d.is_on ? L.meta_synonym_on : L.meta_synonym_off);
        return '<div><div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(preview) + '</a> ' + badge + '</div>'
            + '<div class="entry-row-meta">' + U.escape(meta) + '</div></div>';
    }

    var table = U.table({
        el: '#syn-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/synonyms/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_synonyms + '</p><p><button type="button" class="btn btn-muted btn-sm" id="syn-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_synonyms + '</p><p class="muted">' + L.empty_synonyms_hint + '</p><p><button type="button" class="btn btn-primary btn-sm" id="syn-empty-add">' + L.add_synonym + '</button> <a class="btn btn-muted btn-sm" href="/admin/video/searchwords">' + L.go_searchwords + '</a></p></div>';
        },
        onDraw: function (_wrap, list) {
            var add = document.getElementById('syn-empty-add');
            var reset = document.getElementById('syn-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_rows || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.col_rule, html: wordHtml},
            {title: L.col_from_word, width: 140, html: function (d) { return U.escape(d.from_word || ''); }},
            {title: L.col_to_word, width: 140, html: function (d) { return d.empty_to ? '<span class="muted">' + L.empty_to_word + '</span>' : U.escape(d.to_word || ''); }},
            {title: L.status, width: 72, html: function (d) {
                return d.is_on ? U.status(true, L.enabled) : U.status(false, L.disabled);
            }},
            {title: L.actions, cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">' + L.edit + '</a><a href="#" class="btn-link js-del">' + L.delete + '</a>';
            }}
        ]
    });
    markChips();

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? L.edit_synonym : L.add_synonym,
            content: document.getElementById('syn-dialog-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: mode === 'edit' ? (row.id || '') : '',
                    from_word: row.from_word || '',
                    to_word: row.to_word || '',
                    status: row.status == null ? '1' : String(row.status)
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.from_word) { U.toast(L.please_fill_from_word, 'err'); return false; }
                if (!data.to_word) { U.toast(L.please_fill_to_word, 'err'); return false; }
                if (data.from_word === data.to_word) { U.toast(L.from_to_same, 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/synonyms/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    U.toast(mode === 'edit' ? L.saved : L.created, 'ok');
                    table.refresh();
                });
            }
        });
    }

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_synonyms, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/synonyms/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }

    U.on('#syn-search-btn', 'click', runSearch);
    U.on('#syn-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#syn-add-btn', 'click', function () { openDialog('add'); });
    U.on('#syn-try-btn', 'click', function () {
        var kw = String((U.formData(tryForm).kw || '')).trim();
        if (!kw) { U.toast(L.please_try_word, 'err'); return; }
        tryOut.textContent = '…';
        U.post('/admin/video/synonyms/try', {kw: kw}).then(function (res) {
            tryOut.textContent = (res && res.msg) || L.fail;
            if (!res || res.code !== 0) U.toast((res && res.msg) || L.fail, 'err');
        });
    });
    document.getElementById('syn-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#syn-batch-on', 'click', function () { batch('status', 1); });
    U.on('#syn-batch-off', 'click', function () { batch('status', 0); });
    U.on('#syn-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_synonyms); });
    U.on('#syn-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#syn-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm(String(L.confirm_del_synonym || '').replace('__NAME__', row.from_word || ''))) return;
            U.post('/admin/video/synonyms/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
