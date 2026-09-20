@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'pending' => 0, 'failed' => 0, 'done' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $sourceId = (int) ($sourceId ?? 0);
    $sourceName = trim((string) ($sourceName ?? ''));
    $sourceChip = $sourceName !== '' ? $sourceName : ($sourceId > 0 ? admin_t('ui.collect_source_n', ['id' => $sourceId]) : '');
    $toTemp = (bool) ($toTemp ?? false);
    $ctempJsLang = [
        'delete' => admin_t('ui.delete'),
        'fail' => admin_t('ui.fail'),
        'deleted' => admin_t('ui.deleted'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'clear_selection' => admin_t('ui.clear_selection'),
        'selected_videos' => admin_t('ui.selected_videos', ['n' => '__N__']),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'no_cover' => admin_t('ui.no_cover'),
        'collect_source_n' => admin_t('ui.collect_source_n', ['id' => '__ID__']),
        'source_id_n' => admin_t('ui.source_id_n', ['id' => '__ID__']),
        'untitled_title' => admin_t('ui.untitled_title'),
        'chip_pending_promote' => admin_t('ui.chip_pending_promote'),
        'col_vod' => admin_t('ui.col_vod'),
        'col_time' => admin_t('ui.col_time'),
        'actions' => admin_t('ui.actions'),
        'promote' => admin_t('ui.promote'),
        'today_at' => admin_t('ui.today_at', ['time' => '__TIME__']),
        'yesterday_at' => admin_t('ui.yesterday_at', ['time' => '__TIME__']),
        'empty_collect_temps' => admin_t('ui.empty_collect_temps'),
        'empty_collect_temps_hint' => admin_t('ui.empty_collect_temps_hint'),
        'empty_collect_temps_direct' => admin_t('ui.empty_collect_temps_direct'),
        'empty_collect_temps_direct_hint' => admin_t('ui.empty_collect_temps_direct_hint'),
        'no_match_collect_temps' => admin_t('ui.no_match_collect_temps'),
        'go_collects' => admin_t('ui.go_collects'),
        'go_content_access' => admin_t('ui.go_content_access'),
        'please_select_temps' => admin_t('ui.please_select_temps'),
        'promote_fail' => admin_t('ui.promote_fail'),
        'promoted' => admin_t('ui.promoted'),
        'promoted_library' => admin_t('ui.promoted_library'),
        'confirm_batch_promote' => admin_t('ui.confirm_batch_promote'),
        'confirm_batch_del_temps' => admin_t('ui.confirm_batch_del_temps'),
        'confirm_del_collect_temp' => admin_t('ui.confirm_del_collect_temp'),
    ];
@endphp

@section('plain')
<div class="card card-panel collect-temp-index desk-board">
    <div class="card-header">
        <span>{{ admin_t('ui.collect_temps') }} <em id="ctemp-count"></em></span>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="ctemp-search" onsubmit="return false;">
            <input type="hidden" name="status">
            <input type="hidden" name="failed">
            <input type="hidden" name="collect_source_id" value="{{ $sourceId > 0 ? $sourceId : '' }}">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_collect_temp') }}" autocomplete="off" aria-label="{{ admin_t('ui.collect_temps') }}">
            <button type="button" class="btn btn-sm" id="ctemp-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="ctemp-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="ctemp-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.chip_pending_promote') }}@if($q('pending') > 0)<em>{{ $q('pending') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="failed" data-value="1">{{ admin_t('ui.chip_promote_fail') }}@if($q('failed') > 0)<em>{{ $q('failed') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">{{ admin_t('ui.chip_promoted') }}@if($q('done') > 0)<em>{{ $q('done') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="source" id="ctemp-source-chip" @if($sourceId < 1) hidden @endif>{{ $sourceChip }}</button>
        </div>
        @if($toTemp)
            <p class="muted recycle-lead">{{ admin_t('ui.collect_temps_lead_on') }}</p>
        @else
            <p class="muted recycle-lead">
                {{ admin_t('ui.collect_temps_lead_off_before') }}<a href="/admin/video/config/collect">{{ admin_t('ui.content_access') }}</a>{{ admin_t('ui.collect_temps_lead_off_after') }}
            </p>
        @endif
        <div class="batch-bar" id="ctemp-batch" hidden>
            <strong id="ctemp-batch-count">{{ admin_t('ui.selected_videos', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="ctemp-batch-promote">{{ admin_t('ui.batch_promote') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="ctemp-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="ctemp-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="ctemp-table" class="desk-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($ctempJsLang, JSON_UNESCAPED_UNICODE);
    var form = document.getElementById('ctemp-search');
    var batchBar = document.getElementById('ctemp-batch');
    var batchCount = document.getElementById('ctemp-batch-count');
    var countEl = document.getElementById('ctemp-count');
    var sourceChip = document.getElementById('ctemp-source-chip');
    var toTemp = @json($toTemp, JSON_UNESCAPED_UNICODE);

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
    function setSource(id, name) {
        form.collect_source_id.value = id ? String(id) : '';
        if (!sourceChip) return;
        sourceChip.textContent = name || (id ? String(L.collect_source_n || '').replace('__ID__', id) : '');
        sourceChip.hidden = !form.collect_source_id.value;
    }
    function markChips() {
        var status = form.status.value;
        var failed = form.failed.value;
        var source = form.collect_source_id.value;
        U.qa('#ctemp-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === 'source') on = !!source;
            else if (key === '' && status === '' && failed === '') on = true;
            else if (key === 'failed' && failed === '1') on = true;
            else if (key === 'status' && failed === '' && status === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        if (key === 'source') {
            setSource('', '');
        } else {
            form.status.value = '';
            form.failed.value = '';
            if (key === 'status') form.status.value = value || '';
            if (key === 'failed') form.failed.value = '1';
        }
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
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
    function badgeClass(d) {
        if (parseInt(d.status, 10) === 1) return 'badge-ok';
        if (parseInt(d.failed, 10) === 1) return 'badge-warn';
        return 'badge-off';
    }
    function titleHtml(d) {
        var cover = (d.cover || '').trim();
        var thumb = cover
            ? '<img class="vod-thumb" src="' + U.escape(cover) + '" alt="">'
            : '<span class="vod-thumb is-empty">' + U.escape(L.no_cover) + '</span>';
        var badge = '<span class="badge ' + badgeClass(d) + '">' + U.escape(d.status_label || L.chip_pending_promote) + '</span>';
        var meta = [];
        if (d.source_name) meta.push('<a class="btn-link js-source" href="#">' + U.escape(d.source_name) + '</a>');
        if (d.type_name) meta.push(U.escape(d.type_name));
        if (d.collect_id) meta.push(U.escape(String(L.source_id_n || '').replace('__ID__', String(d.collect_id))));
        if (d.msg && parseInt(d.status, 10) !== 1) meta.push(U.escape(d.msg));
        return '<div class="vod-cell">' + thumb + '<div><div class="entry-row-title-line"><span class="entry-row-title">' + U.escape(d.title || L.untitled_title) + '</span> ' + badge + '</div>'
            + '<div class="entry-row-meta">' + (meta.join(' · ') || '—') + '</div></div></div>';
    }
    function opsHtml(d) {
        var html = '';
        if (parseInt(d.status, 10) !== 1) html += '<a href="#" class="btn-link js-promote">' + L.promote + '</a>';
        html += '<a href="#" class="btn-link js-del">' + L.delete + '</a>';
        return html;
    }

    var table = U.table({
        el: '#ctemp-table',
        countEl: countEl,
        url: '/admin/video/collect_temps/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_collect_temps + '</p><p><button type="button" class="btn btn-muted btn-sm" id="ctemp-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            if (!toTemp) {
                return '<div class="list-empty"><p>' + L.empty_collect_temps_direct + '</p><p class="muted">' + L.empty_collect_temps_direct_hint + '</p><p><a class="btn btn-primary btn-sm" href="/admin/video/config/collect">' + L.go_content_access + '</a> <a class="btn btn-muted btn-sm" href="/admin/video/collects">' + L.go_collects + '</a></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_collect_temps + '</p><p class="muted">' + L.empty_collect_temps_hint + '</p><p><a class="btn btn-primary btn-sm" href="/admin/video/collects">' + L.go_collects + '</a></p></div>';
        },
        onDraw: function (wrap, list) {
            U.qa('tbody tr[data-idx]', wrap).forEach(function (tr) {
                var d = list[parseInt(tr.getAttribute('data-idx'), 10)];
                if (!d) return;
                if (parseInt(d.failed, 10) === 1) tr.classList.add('is-fail');
                if (parseInt(d.status, 10) === 1) tr.classList.add('is-done');
            });
            var reset = document.getElementById('ctemp-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                form.status.value = '';
                form.failed.value = '';
                setSource('', '');
                runSearch();
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_videos || '').replace('__N__', ids.length);
        },
        cols: [
            {check: true, width: 36},
            {title: L.col_vod, html: titleHtml},
            {title: L.col_time, width: 120, html: function (d) { return fmtTime(d.created_at); }},
            {title: L.actions, cls: 'actions', html: opsHtml}
        ]
    });
    markChips();

    function selectedIds() { return table.selectedIds(); }
    function promoteIds(ids, doneMsg) {
        if (!ids.length) { U.toast(L.please_select_temps, 'err'); return; }
        U.loading(true);
        U.post('/admin/video/collect_temps/promote', {ids: ids.join(',')}).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.promote_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || doneMsg || L.promoted, 'ok');
        }).catch(function () {
            U.loading(false);
            U.toast(L.promote_fail, 'err');
        });
    }
    function batchPromote() {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_temps, 'err'); return; }
        if (!U.confirm(L.confirm_batch_promote)) return;
        promoteIds(ids, L.promoted);
    }
    function batchDel() {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_temps, 'err'); return; }
        if (!U.confirm(L.confirm_batch_del_temps)) return;
        U.post('/admin/video/collect_temps/batch', {ids: ids.join(','), action: 'delete'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }

    U.on('#ctemp-search-btn', 'click', runSearch);
    U.on('#ctemp-reset-btn', 'click', function () {
        setTimeout(function () {
            form.status.value = '';
            form.failed.value = '';
            setSource('', '');
            runSearch();
        }, 0);
    });
    document.getElementById('ctemp-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#ctemp-batch-promote', 'click', batchPromote);
    U.on('#ctemp-batch-del', 'click', batchDel);
    U.on('#ctemp-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#ctemp-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.getAttribute('href') && a.getAttribute('href').indexOf('/admin/video/') === 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        e.preventDefault();
        if (a.classList.contains('js-source')) {
            if (!row) return;
            setSource(row.collect_source_id || '', row.source_name || '');
            runSearch();
            return;
        }
        if (!row) return;
        if (a.classList.contains('js-promote')) {
            promoteIds([row.id], L.promoted_library);
            return;
        }
        if (a.classList.contains('js-del')) {
            if (!U.confirm(L.confirm_del_collect_temp)) return;
            U.post('/admin/video/collect_temps/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
