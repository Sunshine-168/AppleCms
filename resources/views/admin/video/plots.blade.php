@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('ui.plots'))

@php
    $queues = $queues ?? ['all' => 0, 'no_content' => 0, 'no_title' => 0, 'no_video' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $videoId = (int) ($videoId ?? 0);
    $videoTitle = (string) ($videoTitle ?? '');
    $plotJsLang = [
        'sort' => admin_t('ui.sort'),
        'actions' => admin_t('ui.actions'),
        'edit' => admin_t('ui.edit'),
        'delete' => admin_t('ui.delete'),
        'front' => admin_t('ui.front'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'created' => admin_t('ui.created'),
        'deleted' => admin_t('ui.deleted'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'selected_rows' => admin_t('ui.selected_rows', ['n' => '__N__']),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'add_plot' => admin_t('ui.add_plot'),
        'edit_plot' => admin_t('ui.edit_plot'),
        'col_plot' => admin_t('ui.col_plot'),
        'col_video' => admin_t('ui.col_video'),
        'col_episode' => admin_t('ui.col_episode'),
        'col_written' => admin_t('ui.col_written'),
        'empty_plots' => admin_t('ui.empty_plots'),
        'empty_plots_hint' => admin_t('ui.empty_plots_hint'),
        'no_match_plots' => admin_t('ui.no_match_plots'),
        'no_content_meta' => admin_t('ui.no_content_meta'),
        'no_video_linked' => admin_t('ui.no_video_linked'),
        'video_deleted' => admin_t('ui.video_deleted', ['id' => '__ID__']),
        'video_hash' => admin_t('ui.video_hash', ['id' => '__ID__']),
        'hint_plot_video' => admin_t('ui.hint_plot_video'),
        'plot_video_missing' => admin_t('ui.plot_video_missing', ['id' => '__ID__']),
        'attach_to_video' => admin_t('ui.attach_to_video', ['name' => '__NAME__']),
        'please_fill_video_id' => admin_t('ui.please_fill_video_id'),
        'please_fill_episode' => admin_t('ui.please_fill_episode'),
        'please_fill_plot' => admin_t('ui.please_fill_plot'),
        'please_select_plots' => admin_t('ui.please_select_plots'),
        'confirm_batch_del_plots' => admin_t('ui.confirm_batch_del_plots'),
        'confirm_del_plot' => admin_t('ui.confirm_del_plot', ['name' => '__NAME__']),
    ];
@endphp

@section('plain')
<div class="card card-panel plot-index" id="plot-index">
    <div class="card-header">
        <span>{{ admin_t('ui.plots') }} <em id="plot-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="plot-add-btn">{{ admin_t('ui.add_plot') }}</button>
            <a class="btn btn-muted btn-sm" href="/admin/video">{{ admin_t('ui.videos') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video?has_plot=1">{{ admin_t('ui.videos_with_plot') }}</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.plots_lead') }}</p>
        @if($videoId > 0)
            <p class="plot-focus">{{ $videoTitle !== '' ? admin_t('ui.plot_focus', ['name' => $videoTitle]) : admin_t('ui.plot_video_missing', ['id' => $videoId]) }}</p>
        @endif
        <form class="filter-bar" id="plot-search" onsubmit="return false;">
            <input type="hidden" name="empty_video">
            <input type="hidden" name="empty_content">
            <input type="hidden" name="empty_title">
            <input type="hidden" name="video_id" value="{{ $videoId > 0 ? $videoId : '' }}">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_plot') }}" autocomplete="off" aria-label="{{ admin_t('ui.plots') }}">
            <button type="button" class="btn btn-sm" id="plot-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="plot-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="plot-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="empty_content" data-value="1">{{ admin_t('ui.chip_no_content') }}@if($q('no_content') > 0)<em>{{ $q('no_content') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="empty_title" data-value="1">{{ admin_t('ui.chip_no_title') }}@if($q('no_title') > 0)<em>{{ $q('no_title') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="empty_video" data-value="1">{{ admin_t('ui.chip_no_video') }}@if($q('no_video') > 0)<em>{{ $q('no_video') }}</em>@endif</button>
        </div>
        <div class="batch-bar" id="plot-batch" hidden>
            <strong id="plot-batch-count">{{ admin_t('ui.selected_rows', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-danger btn-sm" id="plot-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="plot-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="plot-table"></div>
    </div>
</div>
<template id="plot-dialog-tpl">
    <form class="admin-form">
        <input type="hidden" name="id">
        <label>{{ admin_t('ui.label_video_id') }}</label>
        <input type="number" name="video_id" min="1" placeholder="{{ admin_t('ui.ph_video_id') }}" inputmode="numeric" required>
        <p class="muted field-hint" id="plot-video-hint">{{ admin_t('ui.hint_plot_video') }}</p>
        <label>{{ admin_t('ui.label_episode') }}</label>
        <input type="number" name="episode_num" min="1" placeholder="{{ admin_t('ui.ph_episode') }}" inputmode="numeric" required>
        <p class="muted field-hint">{{ admin_t('ui.hint_episode') }}</p>
        <label>{{ admin_t('ui.title_label') }}</label>
        <input type="text" name="title" placeholder="{{ admin_t('ui.ph_plot_title') }}">
        <label>{{ admin_t('ui.label_plot') }}</label>
        <textarea name="content" rows="8" placeholder="{{ admin_t('ui.ph_plot_content') }}" required></textarea>
        <label>{{ admin_t('ui.sort') }}</label>
        <input type="number" name="sort" value="0">
        <p class="muted field-hint">{{ admin_t('ui.hint_sort_asc') }}</p>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($plotJsLang, JSON_UNESCAPED_UNICODE);
    var QUEUE_KEYS = ['empty_video', 'empty_content', 'empty_title'];
    var form = document.getElementById('plot-search');
    var batchBar = document.getElementById('plot-batch');
    var batchCount = document.getElementById('plot-batch-count');
    var countEl = document.getElementById('plot-count');
    var prefillVideo = @json($videoId > 0 ? $videoId : 0);
    var prefillVideoTitle = @json($videoTitle, JSON_UNESCAPED_UNICODE);

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
        var active = '';
        QUEUE_KEYS.forEach(function (k) {
            if (form[k] && form[k].value === '1') active = k;
        });
        U.qa('#plot-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var on = false;
            if (key === '' && !active) on = true;
            else if (key && active === key) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        if (key && form[key]) form[key].value = value || '1';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function plotHtml(d) {
        var title = d.title_text || d.title || '';
        var preview = d.content_preview || '';
        var meta = '#' + U.escape(d.id);
        if (!d.has_content) meta += ' · ' + L.no_content_meta;
        else if (preview) meta += ' · ' + U.escape(preview);
        return '<div><a class="vod-title js-edit" href="#">' + U.escape(title) + '</a>'
            + '<div class="muted">' + meta + '</div></div>';
    }
    function videoHtml(d) {
        var vid = parseInt(d.video_id, 10) || 0;
        if (vid < 1) return '<span class="muted">' + L.no_video_linked + '</span>';
        if (d.video_missing) return '<span class="muted">' + String(L.video_deleted || '').replace('__ID__', vid) + '</span>';
        var title = d.video_title || String(L.video_hash || '').replace('__ID__', vid);
        return '<a href="/admin/video/' + vid + '/edit">' + U.escape(title) + '</a>';
    }

    var table = U.table({
        el: '#plot-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/plots/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_plots + '</p><p><button type="button" class="btn btn-muted btn-sm" id="plot-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_plots + '</p><p class="muted">' + L.empty_plots_hint + '</p><p><button type="button" class="btn btn-primary btn-sm" id="plot-empty-add">' + L.add_plot + '</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            var add = document.getElementById('plot-empty-add');
            var reset = document.getElementById('plot-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                if (prefillVideo) form.video_id.value = String(prefillVideo);
                runSearch();
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_rows || '').replace('__N__', ids.length);
        },
        cols: [
            {check: true, width: 36},
            {title: L.col_plot, html: plotHtml},
            {title: L.col_video, html: videoHtml},
            {title: L.col_episode, width: 88, html: function (d) { return U.escape(d.episode_label || ''); }},
            {key: 'sort', title: L.sort, width: 64},
            {title: L.col_written, width: 140, html: function (d) { return U.escape(d.created_at_text || ''); }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var href = d.url ? String(d.url) : ('/plot/' + encodeURIComponent(d.id));
                return '<a href="' + U.escape(href) + '" target="_blank" rel="noopener" class="btn-link">' + L.front + '</a>'
                    + '<a href="#" class="btn-link js-edit">' + L.edit + '</a>'
                    + '<a href="#" class="btn-link js-del">' + L.delete + '</a>';
            }}
        ]
    });
    markChips();

    function bindLookups(formEl, row) {
        var videoInput = formEl.querySelector('input[name=video_id]');
        var videoHint = formEl.querySelector('#plot-video-hint');
        function setHint(text) { if (videoHint) videoHint.textContent = text; }
        function showVideo(id, title, missing) {
            id = parseInt(id, 10) || 0;
            if (id < 1) { setHint(L.hint_plot_video); return; }
            if (missing) { setHint(String(L.plot_video_missing || '').replace('__ID__', id)); return; }
            if (title) { setHint(String(L.attach_to_video || '').replace('__NAME__', title)); return; }
            setHint(String(L.video_hash || '').replace('__ID__', id));
        }
        showVideo(videoInput.value, row.video_title || (String(videoInput.value) === String(prefillVideo) ? prefillVideoTitle : ''), row.video_missing);
        videoInput.addEventListener('blur', function () {
            var id = parseInt(videoInput.value, 10) || 0;
            if (id < 1) { showVideo(0, '', 0); return; }
            U.get('/admin/video/info', {id: id}).then(function (res) {
                var d = (res && res.data) || {};
                var title = d.title || '';
                showVideo(id, title, res && res.code === 0 && title ? 0 : 1);
            });
        });
    }

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            wide: true,
            title: mode === 'edit' ? L.edit_plot : L.add_plot,
            content: document.getElementById('plot-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var formEl = body.querySelector('form');
                var videoVal = mode === 'edit' ? (row.video_id || 0) : (row.video_id || prefillVideo || 0);
                U.fillForm(formEl, {
                    id: mode === 'edit' ? (row.id || '') : '',
                    video_id: videoVal || '',
                    episode_num: row.episode_num || '',
                    title: row.title || '',
                    content: row.content || '',
                    sort: row.sort == null ? 0 : row.sort
                });
                bindLookups(formEl, {
                    video_title: row.video_title || '',
                    video_missing: row.video_missing
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.video_id) { U.toast(L.please_fill_video_id, 'err'); return false; }
                if (!data.episode_num || parseInt(data.episode_num, 10) < 1) { U.toast(L.please_fill_episode, 'err'); return false; }
                if (mode !== 'edit' && !data.content) { U.toast(L.please_fill_plot, 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/plots/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    U.toast(mode === 'edit' ? L.saved : L.created, 'ok');
                    table.refresh();
                });
            }
        });
    }

    function selectedIds() { return table.selectedIds(); }
    function batch(action, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_plots, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/plots/batch', {ids: ids.join(','), action: action}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }

    U.on('#plot-search-btn', 'click', runSearch);
    U.on('#plot-reset-btn', 'click', function () {
        setTimeout(function () {
            if (prefillVideo) form.video_id.value = String(prefillVideo);
            runSearch();
        }, 0);
    });
    U.on('#plot-add-btn', 'click', function () { openDialog('add'); });
    document.getElementById('plot-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#plot-batch-del', 'click', function () { batch('delete', L.confirm_batch_del_plots); });
    U.on('#plot-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#plot-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank' || (a.getAttribute('href') || '').indexOf('/admin/video/') === 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm(String(L.confirm_del_plot || '').replace('__NAME__', row.title_text || row.episode_label || ''))) return;
            U.post('/admin/video/plots/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
