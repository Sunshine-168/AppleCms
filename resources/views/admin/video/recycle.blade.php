@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('ui.recycle'))

@php
    $types = $types ?? [];
    $count = (int) ($count ?? 0);
    $recycleJsLang = [
        'fail' => admin_t('ui.fail'),
        'actions' => admin_t('ui.actions'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'no_pic' => admin_t('ui.no_pic'),
        'uncategorized' => admin_t('ui.uncategorized'),
        'untitled_vod' => admin_t('ui.untitled_vod'),
        'col_video' => admin_t('ui.col_video'),
        'col_deleted_at' => admin_t('ui.col_deleted_at'),
        'restore_vod' => admin_t('ui.restore_vod'),
        'purge' => admin_t('ui.purge'),
        'empty_recycle_bin' => admin_t('ui.empty_recycle_bin'),
        'empty_recycle_hint' => admin_t('ui.empty_recycle_hint'),
        'no_match_recycle' => admin_t('ui.no_match_recycle'),
        'go_videos' => admin_t('ui.go_videos'),
        'please_select_videos' => admin_t('ui.please_select_videos'),
        'confirm_batch_restore' => admin_t('ui.confirm_batch_restore', ['n' => '__N__']),
        'confirm_batch_purge' => admin_t('ui.confirm_batch_purge', ['n' => '__N__']),
        'confirm_empty_recycle' => admin_t('ui.confirm_empty_recycle'),
        'confirm_restore_vod' => admin_t('ui.confirm_restore_vod', ['name' => '__NAME__']),
        'confirm_purge_vod' => admin_t('ui.confirm_purge_vod', ['name' => '__NAME__']),
        'restored' => admin_t('ui.restored'),
        'purged' => admin_t('ui.purged'),
        'emptied' => admin_t('ui.emptied'),
        'today_at' => admin_t('ui.today_at', ['time' => '__TIME__']),
        'yesterday_at' => admin_t('ui.yesterday_at', ['time' => '__TIME__']),
        'selected_videos' => admin_t('ui.selected_videos', ['n' => '__N__']),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
    ];
@endphp

@section('plain')
<div class="card card-panel recycle-index">
    <div class="card-header">
        <span>{{ admin_t('ui.recycle') }} <em id="recycle-count">@if($count > 0)· {{ $count }}@endif</em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video">{{ admin_t('ui.back_videos') }}</a>
            <button type="button" class="btn btn-danger btn-sm" id="recycle-empty-btn" @if($count < 1) hidden @endif>{{ admin_t('ui.empty_recycle') }}</button>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="recycle-search" onsubmit="return false;">
            <input type="hidden" name="trash" value="1">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_recycle') }}" autocomplete="off" aria-label="{{ admin_t('ui.recycle') }}">
            <select name="type_id" aria-label="{{ admin_t('ui.types') }}">
                <option value="">{{ admin_t('ui.all_categories') }}</option>
                @foreach($types as $type)
                    <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-sm" id="recycle-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="recycle-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <p class="muted recycle-lead">{{ admin_t('ui.recycle_lead') }}</p>
        <div class="batch-bar" id="recycle-batch" hidden>
            <strong id="recycle-batch-count">{{ admin_t('ui.selected_videos', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="recycle-batch-restore">{{ admin_t('ui.batch_restore') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="recycle-batch-purge">{{ admin_t('ui.batch_purge') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="recycle-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="recycle-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($recycleJsLang);
    var form = document.getElementById('recycle-search');
    var batchBar = document.getElementById('recycle-batch');
    var batchCount = document.getElementById('recycle-batch-count');
    var countEl = document.getElementById('recycle-count');
    var emptyBtn = document.getElementById('recycle-empty-btn');

    function cleanWhere(data) {
        var out = {trash: 1, limit: 20};
        Object.keys(data || {}).forEach(function (k) {
            if (k === 'trash') return;
            if (data[k] !== '') out[k] = data[k];
        });
        return out;
    }
    function queryWhere() {
        return cleanWhere(U.formData(form));
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) {
            return k !== 'limit' && k !== 'trash' && where[k] !== '';
        });
    }
    function runSearch() {
        table.reload(queryWhere());
    }
    function fmtTime(ts) {
        ts = parseInt(ts, 10) || 0;
        if (!ts) return '—';
        var d = new Date(ts * 1000);
        var now = new Date();
        var pad = function (n) { return n < 10 ? '0' + n : '' + n; };
        var hm = pad(d.getHours()) + ':' + pad(d.getMinutes());
        if (d.toDateString() === now.toDateString()) return String(L.today_at || '').replace('__TIME__', hm);
        var y = new Date(now);
        y.setDate(now.getDate() - 1);
        if (d.toDateString() === y.toDateString()) return String(L.yesterday_at || '').replace('__TIME__', hm);
        if (d.getFullYear() === now.getFullYear()) return pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + hm;
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    }
    function titleHtml(d) {
        var cover = String(d.cover || '').trim();
        var thumb = cover
            ? '<img class="vod-thumb" src="' + U.escape(cover) + '" alt="">'
            : '<span class="vod-thumb is-empty">' + L.no_pic + '</span>';
        var meta = (d.type_name ? U.escape(d.type_name) : L.uncategorized) + ' · #' + U.escape(d.id);
        return '<div class="vod-cell">' + thumb + '<div><div class="entry-row-title">' + U.escape(d.title || L.untitled_vod) + '</div>'
            + '<div class="entry-row-meta">' + meta + '</div></div></div>';
    }

    var table = U.table({
        el: '#recycle-table',
        countEl: countEl,
        url: '/admin/video/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_recycle + '</p><p><button type="button" class="btn btn-muted btn-sm" id="recycle-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_recycle_bin + '</p><p class="muted">' + L.empty_recycle_hint + '</p><p><a class="btn btn-muted btn-sm" href="/admin/video">' + L.go_videos + '</a></p></div>';
        },
        onDraw: function (_wrap, list) {
            emptyBtn.hidden = list.length === 0 && !isFiltered(queryWhere());
            var reset = document.getElementById('recycle-empty-reset');
            if (reset) reset.addEventListener('click', function () { form.reset(); if (form.trash) form.trash.value = '1'; runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_videos || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.col_video, html: titleHtml},
            {title: L.col_deleted_at, width: 140, html: function (d) { return fmtTime(d.deleted_at); }},
            {title: L.actions, cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-restore">' + L.restore_vod + '</a><a href="#" class="btn-link js-purge">' + L.purge + '</a>';
            }}
        ]
    });

    function selectedIds() { return table.selectedIds(); }
    function post(action, extra) {
        return U.post('/admin/video/tools/recycle/run', Object.assign({action: action}, extra || {}));
    }
    function after(res, okMsg) {
        if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
        table.refresh();
        U.toast((res && res.msg) || okMsg || L.op_ok, 'ok');
    }

    U.on('#recycle-search-btn', 'click', runSearch);
    U.on('#recycle-reset-btn', 'click', function () { setTimeout(function () { if (form.trash) form.trash.value = '1'; runSearch(); }, 0); });
    U.on('#recycle-batch-restore', 'click', function () {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_videos, 'err'); return; }
        if (!U.confirm(String(L.confirm_batch_restore || '').replace('__N__', String(ids.length)))) return;
        post('restore', {ids: ids.join(',')}).then(function (res) { after(res, L.restored); });
    });
    U.on('#recycle-batch-purge', 'click', function () {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_videos, 'err'); return; }
        if (!U.confirm(String(L.confirm_batch_purge || '').replace('__N__', String(ids.length)))) return;
        post('purge', {ids: ids.join(',')}).then(function (res) { after(res, L.purged); });
    });
    U.on('#recycle-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#recycle-empty-btn', 'click', function () {
        if (!U.confirm(L.confirm_empty_recycle)) return;
        post('empty').then(function (res) { after(res, L.emptied); });
    });
    U.on('#recycle-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-restore')) {
            if (!U.confirm(String(L.confirm_restore_vod || '').replace('__NAME__', row.title || ''))) return;
            post('restore', {ids: String(row.id)}).then(function (res) { after(res, L.restored); });
        }
        if (a.classList.contains('js-purge')) {
            var name = row.title || L.untitled_vod;
            if (!U.confirm(String(L.confirm_purge_vod || '').replace('__NAME__', name))) return;
            post('purge', {ids: String(row.id)}).then(function (res) { after(res, L.purged); });
        }
    });
})();
</script>
@endpush
