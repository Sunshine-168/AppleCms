@extends('admin.layouts.inner')
@section('title', admin_t('page.tags'))

@section('plain')
<div class="card card-panel tag-index list-desk">
    <div class="card-header">
        <span>{{ admin_t('ui.tags') }} <em id="tag-count"></em></span>
        <button type="button" class="btn btn-sm" id="video-tag-add-btn">{{ admin_t('ui.add_tag') }}</button>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="video-tag-search" onsubmit="return false;">
            <input type="hidden" name="unused">
            <input type="text" name="name" placeholder="{{ admin_t('ui.ph_tag') }}" autocomplete="off">
            <select name="status">
                <option value="">{{ admin_t('ui.status') }}</option>
                <option value="1">{{ admin_t('ui.enabled') }}</option>
                <option value="0">{{ admin_t('ui.disabled') }}</option>
            </select>
            <button type="button" class="btn btn-sm" id="video-tag-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="video-tag-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="tag-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}</button>
            <button type="button" class="chip" data-queue="status" data-value="1">{{ admin_t('ui.enabled') }}</button>
            <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.disabled') }}</button>
            <button type="button" class="chip" data-queue="unused" data-value="1">{{ admin_t('ui.unused') }}</button>
        </div>
        <p class="muted recycle-lead">{{ admin_t('ui.tags_lead') }}</p>
        <div class="batch-bar" id="tag-batch" hidden>
            <strong id="tag-batch-count">{{ admin_t('ui.selected_n', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="tag-batch-on">{{ admin_t('ui.enabled') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="tag-batch-off">{{ admin_t('ui.disabled') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="tag-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="tag-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="video-tag-table"></div>
    </div>
</div>
<template id="video-tag-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>{{ admin_t('ui.name') }}</label>
        <input class="entry-title" type="text" name="name" placeholder="{{ admin_t('ui.ph_tag') }}" required autofocus>
        <p class="muted field-hint">{{ admin_t('ui.tags_lead') }}</p>
        <label>{{ admin_t('ui.alias') }}</label>
        <input type="text" name="slug" placeholder="{{ admin_t('live.ph_slug') }}">
        <p class="muted field-hint">{{ admin_t('live.slug_hint') }}</p>
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
        <p class="muted field-hint">{{ admin_t('ui.tag_disabled_hint') }}</p>
    </form>
</template>
@endsection
@php
    $tagJsLang = [
        'tags' => admin_t('ui.tags'),
        'sort' => admin_t('ui.sort'),
        'status' => admin_t('ui.status'),
        'actions' => admin_t('ui.actions'),
        'edit' => admin_t('ui.edit'),
        'delete' => admin_t('ui.delete'),
        'front' => admin_t('ui.front'),
        'enabled' => admin_t('ui.enabled'),
        'disabled' => admin_t('ui.disabled'),
        'add_tag' => admin_t('ui.add_tag'),
        'edit_tag' => admin_t('ui.edit_tag'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'empty_tags' => admin_t('ui.empty_tags'),
        'empty_tags_hint' => admin_t('ui.empty_tags_hint'),
        'no_match_tags' => admin_t('ui.no_match_tags'),
        'tag_videos_n' => admin_t('ui.tag_videos_n', ['n' => '__N__']),
        'tag_no_videos' => admin_t('ui.tag_no_videos'),
        'selected_n' => admin_t('ui.selected_n', ['n' => '__N__']),
        'videos' => admin_t('nav.videos'),
        'need_name' => admin_t('manga.need_title'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'created' => admin_t('ui.created'),
        'deleted' => admin_t('ui.deleted'),
        'please_select' => admin_t('ui.please_select'),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'confirm_batch_del' => admin_t('manga.confirm_batch_del'),
        'confirm_del_named' => admin_t('live.confirm_del'),
    ];
@endphp
@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($tagJsLang);
    var QUEUE_KEYS = ['unused'];
    var form = document.getElementById('video-tag-search');
    var batchBar = document.getElementById('tag-batch');
    var batchCount = document.getElementById('tag-batch-count');
    var countEl = document.getElementById('tag-count');

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
        var unused = form.unused.value;
        U.qa('#tag-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && unused === '') on = true;
            else if (key === 'status' && unused === '' && status === val) on = true;
            else if (key === 'unused' && unused === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        if (key === 'status') form.status.value = value || '';
        else {
            form.status.value = '';
            if (key && form[key]) form[key].value = value || '1';
        }
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function nameHtml(d) {
        var meta = '#' + U.escape(d.id);
        if (d.slug) meta += ' · /' + U.escape(d.slug);
        var n = parseInt(d.video_count, 10) || 0;
        meta += n > 0 ? ' · ' + String(L.tag_videos_n || '').replace('__N__', String(n)) : ' · ' + L.tag_no_videos;
        return '<div><a class="vod-title js-edit" href="#">' + U.escape(d.name || '') + '</a>'
            + '<div class="muted">' + meta + '</div></div>';
    }

    var table = U.table({
        el: '#video-tag-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/tags/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_tags + '</p><p><button type="button" class="btn btn-muted btn-sm" id="tag-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_tags + '</p><p class="muted">' + L.empty_tags_hint + '</p><p><button type="button" class="btn btn-primary btn-sm" id="tag-empty-add">' + L.add_tag + '</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            var add = document.getElementById('tag-empty-add');
            var reset = document.getElementById('tag-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_n || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.tags, html: nameHtml},
            {key: 'sort', title: L.sort, width: 64},
            {title: L.status, width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, L.enabled) : U.status(false, L.disabled);
            }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var href = d.url ? String(d.url) : ('/tag/' + encodeURIComponent(d.slug || d.id));
                return '<a href="/admin/video?tag_id=' + encodeURIComponent(d.id) + '" class="btn-link">' + L.videos + '</a>'
                    + '<a href="' + U.escape(href) + '" target="_blank" rel="noopener" class="btn-link">' + L.front + '</a>'
                    + '<a href="#" class="btn-link js-edit">' + L.edit + '</a>'
                    + '<a href="#" class="btn-link js-del">' + L.delete + '</a>';
            }}
        ]
    });
    markChips();

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? L.edit_tag : L.add_tag,
            wide: true,
            content: document.getElementById('video-tag-dialog-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    slug: row.slug || '',
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? '1' : String(row.status)
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast(L.need_name, 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/tags/save', data).then(function (res) {
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
        if (!ids.length) { U.toast(L.please_select, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/tags/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }

    U.on('#video-tag-search-btn', 'click', runSearch);
    U.on('#video-tag-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#video-tag-add-btn', 'click', function () { openDialog('add'); });
    document.getElementById('tag-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#tag-batch-on', 'click', function () { batch('status', 1); });
    U.on('#tag-batch-off', 'click', function () { batch('status', 0); });
    U.on('#tag-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del); });
    U.on('#tag-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#video-tag-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank' || (a.getAttribute('href') || '').indexOf('/admin/video') === 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm(String(L.confirm_del_named || '').replace(':name', row.name || ''))) return;
            U.post('/admin/video/tags/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
