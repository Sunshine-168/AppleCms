@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'on' => 0, 'off' => 0, 'skip' => 0, 'review' => 0, 'replace' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $auditJsLang = [
        'unnamed' => admin_t('ui.unnamed_rule'),
        'off' => admin_t('ui.disabled'),
        'on' => admin_t('ui.enabled'),
        'skip' => admin_t('ui.audit_skip'),
        'unlist' => admin_t('ui.audit_unlist'),
        'replace' => admin_t('ui.audit_replace'),
        'regex' => admin_t('ui.regex'),
        'watch' => admin_t('ui.watch_scope', ['scope' => '__SCOPE__']),
        'no_words' => admin_t('ui.no_words_yet'),
        'words_n' => admin_t('ui.words_n', ['n' => '__N__']),
        'empty_match' => admin_t('ui.empty_audit_match'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'empty' => admin_t('ui.empty_audit'),
        'empty_hint' => admin_t('ui.empty_audit_hint'),
        'add' => admin_t('ui.add_rule'),
        'go_collects' => admin_t('ui.go_collects'),
        'selected_n' => admin_t('ui.selected_n', ['n' => '__N__']),
        'rule' => admin_t('ui.rule'),
        'please_select' => admin_t('ui.please_select_rules'),
        'confirm_batch' => admin_t('ui.confirm_batch_del_rules'),
        'confirm_del' => admin_t('ui.confirm_del_rule'),
        'fail' => admin_t('ui.fail'),
        'deleted' => admin_t('ui.deleted'),
        'edit' => admin_t('ui.edit'),
        'op_ok' => admin_t('ui.op_ok'),
    ];
@endphp

@section('plain')
<div class="card card-panel audit-index">
    <div class="card-header">
        <span>{{ admin_t('ui.audit_rules') }} <em id="audit-count"></em></span>
        <div>
            <a class="btn btn-sm" href="/admin/video/audits/create">{{ admin_t('ui.add_rule') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/collects">{{ admin_t('ui.collects') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video?status=0">{{ admin_t('ui.offline_videos') }}</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="audit-search" onsubmit="return false;">
            <input type="hidden" name="status">
            <input type="hidden" name="action">
            <input type="hidden" name="scope">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_search_audit') }}" autocomplete="off" aria-label="{{ admin_t('ui.ph_search_audit') }}">
            <button type="button" class="btn btn-sm" id="audit-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="audit-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="audit-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">{{ admin_t('ui.enabled') }}@if($q('on') > 0)<em>{{ $q('on') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.disabled') }}@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="action" data-value="skip">{{ admin_t('ui.audit_skip') }}@if($q('skip') > 0)<em>{{ $q('skip') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="action" data-value="review">{{ admin_t('ui.audit_review') }}@if($q('review') > 0)<em>{{ $q('review') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="action" data-value="replace">{{ admin_t('ui.audit_replace') }}@if($q('replace') > 0)<em>{{ $q('replace') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">{{ admin_t('ui.audit_lead') }}</p>
        <div class="batch-bar" id="audit-batch" hidden>
            <strong id="audit-batch-count">{{ admin_t('ui.selected_n', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="audit-batch-on">{{ admin_t('ui.enabled') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="audit-batch-off">{{ admin_t('ui.disabled') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="audit-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="audit-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="audit-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($auditJsLang, JSON_UNESCAPED_UNICODE);
    var form = document.getElementById('audit-search');
    var batchBar = document.getElementById('audit-batch');
    var batchCount = document.getElementById('audit-batch-count');
    var countEl = document.getElementById('audit-count');

    function cleanWhere(data) {
        var out = {};
        Object.keys(data || {}).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
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
        var action = form.action.value;
        U.qa('#audit-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && action === '') on = true;
            else if (key === 'status' && action === '' && status === val) on = true;
            else if (key === 'action' && status === '' && action === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        form.status.value = '';
        form.action.value = '';
        form.scope.value = '';
        if (key === 'status') form.status.value = value || '';
        if (key === 'action') form.action.value = value || '';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function titleHtml(d) {
        var name = d.name || L.unnamed;
        var badges = [];
        if (parseInt(d.status, 10) !== 1) badges.push('<span class="badge badge-off">' + U.escape(L.off) + '</span>');
        else badges.push('<span class="badge badge-ok">' + U.escape(L.on) + '</span>');
        if (d.action === 'skip') badges.push('<span class="badge badge-warn">' + U.escape(d.action_label || L.skip) + '</span>');
        else if (d.action === 'review') badges.push('<span class="badge badge-search">' + U.escape(d.action_label || L.unlist) + '</span>');
        else badges.push('<span class="badge badge-ok">' + U.escape(d.action_label || L.replace) + '</span>');
        if (parseInt(d.is_regex, 10) === 1) badges.push('<span class="badge badge-off">' + U.escape(L.regex) + '</span>');
        var meta = [];
        if (d.scope_label) meta.push(L.watch.replace('__SCOPE__', U.escape(d.scope_label)));
        if (d.words_preview) meta.push(U.escape(d.words_preview));
        else meta.push(L.no_words);
        if (parseInt(d.word_n, 10) > 0) meta.push(L.words_n.replace('__N__', String(d.word_n)));
        return '<div class="entry-row-title-line"><a class="entry-row-title" href="/admin/video/audits/' + encodeURIComponent(d.id || '') + '/edit">' + U.escape(name) + '</a> ' + badges.join(' ') + '</div>'
            + '<div class="entry-row-meta">' + (meta.join(' · ') || '—') + '</div>';
    }

    var table = U.table({
        el: '#audit-table',
        countEl: countEl,
        url: '/admin/video/audits/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + U.escape(L.empty_match) + '</p><p><button type="button" class="btn btn-muted btn-sm" id="audit-empty-reset">' + U.escape(L.clear_filter) + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + U.escape(L.empty) + '</p><p class="muted">' + U.escape(L.empty_hint) + '</p><p><a class="btn btn-primary btn-sm" href="/admin/video/audits/create">' + U.escape(L.add) + '</a> <a class="btn btn-muted btn-sm" href="/admin/video/collects">' + U.escape(L.go_collects) + '</a></p></div>';
        },
        onDraw: function (wrap, list) {
            U.qa('tbody tr[data-idx]', wrap).forEach(function (tr) {
                var d = list[parseInt(tr.getAttribute('data-idx'), 10)];
                if (d && parseInt(d.status, 10) !== 1) tr.classList.add('is-off');
            });
            var reset = document.getElementById('audit-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                form.status.value = '';
                form.action.value = '';
                form.scope.value = '';
                runSearch();
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_n || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.rule, html: titleHtml},
            {title: AdminUi.t('actions'), cls: 'actions', html: function (d) {
                return '<a class="btn-link" href="/admin/video/audits/' + encodeURIComponent(d.id || '') + '/edit">' + U.escape(L.edit) + '</a>'
                    + '<a href="#" class="btn-link js-del">' + AdminUi.t('delete') + '</a>';
            }}
        ]
    });
    markChips();

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/audits/batch', {ids: ids.join(','), action: action, value: value || ''}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }

    U.on('#audit-search-btn', 'click', runSearch);
    U.on('#audit-reset-btn', 'click', function () {
        setTimeout(function () {
            form.status.value = '';
            form.action.value = '';
            form.scope.value = '';
            runSearch();
        }, 0);
    });
    document.getElementById('audit-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#audit-batch-on', 'click', function () { batch('status', 1); });
    U.on('#audit-batch-off', 'click', function () { batch('status', 0); });
    U.on('#audit-batch-del', 'click', function () { batch('delete', '', L.confirm_batch); });
    U.on('#audit-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#audit-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var href = a.getAttribute('href') || '';
        if (href.indexOf('/admin/video/audits/') === 0 || href.indexOf('/admin/video/collects') === 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        e.preventDefault();
        if (!row) return;
        if (a.classList.contains('js-del')) {
            if (!U.confirm(L.confirm_del)) return;
            U.post('/admin/video/audits/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
