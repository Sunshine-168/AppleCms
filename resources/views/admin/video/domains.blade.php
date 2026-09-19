@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('page.domains'))

@php
    $queues = $queues ?? ['all' => 0, 'on' => 0, 'off' => 0, 'current' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $themes = is_array($themes ?? null) ? $themes : [];
    $currentHost = trim((string) ($currentHost ?? ''));
    $domainJsLang = [
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
        'tpl' => admin_t('ui.tpl'),
        'open_link' => admin_t('ui.open_link'),
        'current_host' => admin_t('ui.current_host'),
        'col_domain' => admin_t('ui.col_domain'),
        'site_name' => admin_t('ui.site_name'),
        'follow_site' => admin_t('ui.follow_site'),
        'theme_missing_suffix' => admin_t('ui.theme_missing_suffix'),
        'not_filled' => admin_t('ui.not_filled'),
        'add_domain' => admin_t('ui.add_domain'),
        'edit_domain' => admin_t('ui.edit_domain'),
        'empty_domains' => admin_t('ui.empty_domains'),
        'empty_domains_hint' => admin_t('ui.empty_domains_hint'),
        'no_match_domains' => admin_t('ui.no_match_domains'),
        'please_fill_host' => admin_t('ui.please_fill_host'),
        'please_select_domains' => admin_t('ui.please_select_domains'),
        'confirm_batch_del_domains' => admin_t('ui.confirm_batch_del_domains'),
        'confirm_del_domain' => admin_t('ui.confirm_del_domain', ['name' => '__NAME__']),
    ];
@endphp

@section('plain')
<div class="card card-panel domain-index" id="domain-index">
    <div class="card-header">
        <span>{{ admin_t('ui.domains') }} <em id="domain-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="domain-add-btn">{{ admin_t('ui.add_domain') }}</button>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.domains_lead') }}@if($currentHost !== '') {{ admin_t('ui.current_host') }}: <code>{{ $currentHost }}</code>.@endif</p>
        <form class="filter-bar" id="domain-search" onsubmit="return false;">
            <input type="hidden" name="current">
            <input type="hidden" name="status">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_domain') }}" autocomplete="off" aria-label="{{ admin_t('ui.domains') }}">
            <button type="button" class="btn btn-sm" id="domain-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="domain-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="domain-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">{{ admin_t('ui.enabled') }}@if($q('on') > 0)<em>{{ $q('on') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.disabled') }}@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="current" data-value="1">{{ admin_t('ui.current_host') }}@if($q('current') > 0)<em>{{ $q('current') }}</em>@endif</button>
        </div>
        <div class="batch-bar" id="domain-batch" hidden>
            <strong id="domain-batch-count">{{ admin_t('ui.selected_n', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="domain-batch-on">{{ admin_t('ui.enabled') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="domain-batch-off">{{ admin_t('ui.disabled') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="domain-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="domain-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="domain-table"></div>
    </div>
</div>
<template id="domain-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>{{ admin_t('ui.label_host') }}</label>
        <input class="entry-title" type="text" name="host" placeholder="{{ admin_t('ui.ph_host') }}" required autofocus>
        <p class="muted field-hint">{{ admin_t('ui.hint_host') }}</p>
        <label>{{ admin_t('ui.tpl') }}</label>
        <select name="theme">
            <option value="">{{ admin_t('ui.follow_site') }}</option>
            @foreach($themes as $theme)
                <option value="{{ $theme['name'] }}">{{ $theme['title'] }}</option>
            @endforeach
        </select>
        <p class="muted field-hint">{{ admin_t('ui.hint_domain_theme') }}</p>
        <h3>{{ admin_t('ui.site_copy') }}</h3>
        <label>{{ admin_t('ui.site_name') }}</label>
        <input type="text" name="site_name" placeholder="{{ admin_t('ui.ph_follow_site') }}">
        <label>{{ admin_t('ui.keyword') }}</label>
        <input type="text" name="site_keyword" placeholder="{{ admin_t('ui.ph_optional_empty') }}">
        <label>{{ admin_t('ui.description') }}</label>
        <textarea name="site_description" rows="3" placeholder="{{ admin_t('ui.ph_optional_empty') }}"></textarea>
        <label>{{ admin_t('ui.remarks') }}</label>
        <input type="text" name="remark" placeholder="{{ admin_t('ui.ph_remark_admin') }}">
        <label>{{ admin_t('ui.status') }}</label>
        <select name="status">
            <option value="1">{{ admin_t('ui.enabled') }}</option>
            <option value="0">{{ admin_t('ui.disabled') }}</option>
        </select>
        <p class="muted field-hint">{{ admin_t('ui.hint_domain_status') }}</p>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($domainJsLang);
    var QUEUE_KEYS = ['current'];
    var form = document.getElementById('domain-search');
    var batchBar = document.getElementById('domain-batch');
    var batchCount = document.getElementById('domain-batch-count');
    var countEl = document.getElementById('domain-count');
    var currentHost = @json($currentHost);

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
        var current = form.current.value;
        U.qa('#domain-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && current === '') on = true;
            else if (key === 'status' && current === '' && status === val) on = true;
            else if (key === 'current' && status === '' && current === val) on = true;
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
    function hostIsIp(host) {
        host = String(host || '');
        if (/^\d{1,3}(?:\.\d{1,3}){3}$/.test(host)) return true;
        if (host.indexOf(':') !== -1) return true;
        return false;
    }
    function hostHtml(d) {
        var host = String(d.host || '');
        var badge = '';
        if (d.is_current) badge += '<span class="badge">' + U.escape(L.current_host) + '</span>';
        if (String(d.status) !== '1') badge += '<span class="badge badge-off">' + U.escape(L.disabled) + '</span>';
        return '<div><div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(host || L.not_filled) + '</a> ' + badge + '</div>'
            + (d.remark ? '<div class="entry-row-meta">' + U.escape(d.remark) + '</div>' : '') + '</div>';
    }
    function themeHtml(d) {
        var label = d.theme_label || L.follow_site;
        if (d.theme_missing) return '<span class="muted">' + U.escape(label) + U.escape(L.theme_missing_suffix) + '</span>';
        return U.escape(label);
    }

    var table = U.table({
        el: '#domain-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/domains/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_domains + '</p><p><button type="button" class="btn btn-muted btn-sm" id="domain-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_domains + '</p><p class="muted">' + L.empty_domains_hint + '</p><p><button type="button" class="btn btn-primary btn-sm" id="domain-empty-add">' + L.add_domain + '</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            var add = document.getElementById('domain-empty-add');
            var reset = document.getElementById('domain-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_n || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.col_domain, html: hostHtml},
            {title: L.site_name, html: function (d) { return U.escape(d.site_name_label || L.follow_site); }},
            {title: L.tpl, html: themeHtml},
            {title: L.status, width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, L.enabled) : U.status(false, L.disabled);
            }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var html = '';
                var host = String(d.host || '');
                if (host && !hostIsIp(host)) {
                    html += '<a href="http://' + U.escape(host) + '" target="_blank" rel="noopener" class="btn-link">' + L.open_link + '</a>';
                }
                html += '<a href="#" class="btn-link js-edit">' + L.edit + '</a>';
                html += '<a href="#" class="btn-link js-del">' + L.delete + '</a>';
                return html;
            }}
        ]
    });
    markChips();

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            wide: true,
            title: mode === 'edit' ? L.edit_domain : L.add_domain,
            content: document.getElementById('domain-dialog-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: mode === 'edit' ? (row.id || '') : '',
                    host: mode === 'edit' ? (row.host || '') : (currentHost || ''),
                    theme: row.theme || '',
                    site_name: row.site_name || '',
                    site_keyword: row.site_keyword || '',
                    site_description: row.site_description || '',
                    remark: row.remark || '',
                    status: row.status == null ? '1' : String(row.status)
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.host) { U.toast(L.please_fill_host, 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/domains/save', data).then(function (res) {
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
        if (!ids.length) { U.toast(L.please_select_domains, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/domains/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }

    U.on('#domain-search-btn', 'click', runSearch);
    U.on('#domain-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#domain-add-btn', 'click', function () { openDialog('add'); });
    document.getElementById('domain-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#domain-batch-on', 'click', function () { batch('status', 1); });
    U.on('#domain-batch-off', 'click', function () { batch('status', 0); });
    U.on('#domain-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_domains); });
    U.on('#domain-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#domain-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank') return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm(String(L.confirm_del_domain || '').replace('__NAME__', row.host || ''))) return;
            U.post('/admin/video/domains/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
