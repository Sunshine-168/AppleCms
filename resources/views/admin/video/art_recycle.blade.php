@extends('admin.layouts.inner')
@section('title', $title)

@php
    $ready = (bool) ($ready ?? false);
    $count = (int) ($count ?? 0);
    $recycleJsLang = [
        'title' => admin_t('ui.title_label'),
        'actions' => admin_t('ui.actions'),
        'deleted_at' => admin_t('ui.col_deleted_at'),
        'restore' => admin_t('ui.restore'),
        'purge' => admin_t('ui.purge'),
        'fail' => admin_t('ui.fail'),
        'op_fail' => admin_t('ui.op_fail'),
        'op_ok' => admin_t('ui.op_ok'),
        'emptied' => admin_t('ui.emptied'),
        'restored' => admin_t('ui.restored'),
        'purged' => admin_t('ui.purged'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'migrate_first' => admin_t('ui.migrate_first'),
        'no_match' => admin_t('ui.no_match_art_recycle'),
        'empty' => admin_t('ui.empty_art_recycle'),
        'empty_hint' => admin_t('ui.empty_art_recycle_hint'),
        'go_arts' => admin_t('ui.go_arts_short'),
        'please_select_rows' => admin_t('ui.please_select_rows'),
        'confirm_batch_restore' => admin_t('ui.confirm_batch_restore_arts'),
        'confirm_batch_purge' => admin_t('ui.confirm_batch_purge_arts', ['n' => '__N__']),
        'confirm_empty' => admin_t('ui.confirm_empty_art_recycle'),
        'confirm_purge' => admin_t('ui.confirm_purge_art', ['name' => '__NAME__']),
        'selected_n' => admin_t('ui.selected_n', ['n' => '__N__']),
        'no_title' => admin_t('ui.no_title'),
        'uncategorized' => admin_t('ui.uncategorized'),
        'today_at' => admin_t('ui.today_at', ['t' => '__T__']),
        'yesterday_at' => admin_t('ui.yesterday_at', ['t' => '__T__']),
    ];
@endphp

@section('plain')
<div class="card card-panel recycle-index">
    <div class="card-header">
        <span>{{ admin_t('ui.recycle') }} <em id="art-recycle-count"></em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/arts">{{ admin_t('ui.back_arts') }}</a>
            @if($ready && $count > 0)
                <button type="button" class="btn btn-danger btn-sm" id="art-recycle-empty">{{ admin_t('ui.empty_recycle') }}</button>
            @endif
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="art-recycle-search" onsubmit="return false;">
            <input type="hidden" name="trash" value="1">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_art_recycle') }}" autocomplete="off" aria-label="{{ admin_t('ui.aria_search_art_recycle') }}">
            <button type="button" class="btn btn-sm" id="art-recycle-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="art-recycle-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <p class="muted recycle-lead">{{ admin_t('ui.art_recycle_lead') }}</p>
        <div class="batch-bar" id="art-recycle-batch" hidden>
            <strong id="art-recycle-batch-count">{{ admin_t('ui.selected_n', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="art-recycle-restore">{{ admin_t('ui.batch_restore') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="art-recycle-purge">{{ admin_t('ui.batch_purge') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="art-recycle-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="art-recycle-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($recycleJsLang, JSON_UNESCAPED_UNICODE);
    var form = document.getElementById('art-recycle-search');
    var batchBar = document.getElementById('art-recycle-batch');
    var batchCount = document.getElementById('art-recycle-batch-count');
    var countEl = document.getElementById('art-recycle-count');
    var ready = @json($ready, JSON_UNESCAPED_UNICODE);

    function cleanWhere(data) {
        var out = {};
        Object.keys(data).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function queryWhere() {
        return Object.assign({limit: 15, trash: 1}, cleanWhere(U.formData(form)));
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return k !== 'limit' && k !== 'trash' && where[k] !== ''; });
    }
    function fmtTime(ts) {
        ts = parseInt(ts, 10) || 0;
        if (!ts) return '—';
        var d = new Date(ts * 1000);
        var now = new Date();
        var pad = function (n) { return n < 10 ? '0' + n : '' + n; };
        var hm = pad(d.getHours()) + ':' + pad(d.getMinutes());
        if (d.toDateString() === now.toDateString()) return String(L.today_at || '').replace('__T__', hm);
        var y = new Date(now);
        y.setDate(now.getDate() - 1);
        if (d.toDateString() === y.toDateString()) return String(L.yesterday_at || '').replace('__T__', hm);
        if (d.getFullYear() === now.getFullYear()) return pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + hm;
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    }
    function runSearch() { table.reload(queryWhere()); }

    var table = U.table({
        el: '#art-recycle-table',
        countEl: countEl,
        url: '/admin/video/arts/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (!ready) return '<div class="list-empty"><p>' + U.escape(L.migrate_first) + '</p></div>';
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + U.escape(L.no_match) + '</p><p><button type="button" class="btn btn-muted btn-sm" id="art-recycle-empty-reset">' + U.escape(L.clear_filter) + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + U.escape(L.empty) + '</p><p class="muted">' + U.escape(L.empty_hint) + '</p><p><a class="btn btn-muted btn-sm" href="/admin/video/arts">' + U.escape(L.go_arts) + '</a></p></div>';
        },
        onDraw: function (_wrap, list) {
            var reset = document.getElementById('art-recycle-empty-reset');
            if (reset) reset.addEventListener('click', function () { form.reset(); if (form.trash) form.trash.value = '1'; runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_n || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.title, html: function (d) {
                return '<div class="entry-row-title">' + U.escape(d.title || L.no_title) + '</div>'
                    + '<div class="entry-row-meta">' + U.escape(d.type_name || L.uncategorized) + ' · #' + U.escape(d.id) + '</div>';
            }},
            {title: L.deleted_at, width: 140, html: function (d) {
                return '<span class="muted">' + U.escape(fmtTime(d.deleted_at_unix || d.deleted_at)) + '</span>';
            }},
            {title: L.actions, cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-restore">' + U.escape(L.restore) + '</a><a href="#" class="btn-link js-purge">' + U.escape(L.purge) + '</a>';
            }}
        ]
    });

    function selectedIds() { return table.selectedIds(); }
    function batch(action, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_rows, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/arts/batch', {ids: ids.join(','), action: action}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }
    U.on('#art-recycle-search-btn', 'click', runSearch);
    U.on('#art-recycle-reset-btn', 'click', function () { setTimeout(function () { if (form.trash) form.trash.value = '1'; runSearch(); }, 0); });
    U.on('#art-recycle-restore', 'click', function () { batch('restore', L.confirm_batch_restore); });
    U.on('#art-recycle-purge', 'click', function () {
        var n = selectedIds().length;
        batch('purge', String(L.confirm_batch_purge || '').replace('__N__', String(n)));
    });
    U.on('#art-recycle-clear', 'click', function () { table.clearSelection(); });
    U.on('#art-recycle-empty', 'click', function () {
        if (!U.confirm(L.confirm_empty)) return;
        U.post('/admin/video/art-recycle/empty', {}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.emptied, 'ok');
        });
    });
    U.on('#art-recycle-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        if (a.classList.contains('js-restore')) {
            e.preventDefault();
            U.post('/admin/video/arts/batch', {ids: String(row.id), action: 'restore'}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.restored, 'ok');
            });
        }
        if (a.classList.contains('js-purge')) {
            e.preventDefault();
            if (!U.confirm(String(L.confirm_purge || '').replace('__NAME__', row.title || ''))) return;
            U.post('/admin/video/arts/batch', {ids: String(row.id), action: 'purge'}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.purged, 'ok');
            });
        }
    });
})();
</script>
@endpush
