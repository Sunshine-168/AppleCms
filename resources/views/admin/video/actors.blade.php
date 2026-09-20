@extends('admin.layouts.inner')
@section('title', admin_t('page.actors'))

@section('plain')
<div class="card card-panel actor-index list-desk">
    <div class="card-header">
        <span>{{ admin_t('ui.actors') }} <em id="actor-count"></em></span>
        <button type="button" class="btn btn-sm" id="actor-add-btn">{{ admin_t('ui.add_actor') }}</button>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="actor-search" onsubmit="return false;">
            <input type="hidden" name="empty_pic">
            <input type="hidden" name="repeat">
            <input type="text" name="name" placeholder="{{ admin_t('ui.ph_actor') }}" autocomplete="off">
            <select name="status">
                <option value="">{{ admin_t('ui.status') }}</option>
                <option value="1">{{ admin_t('ui.on') }}</option>
                <option value="0">{{ admin_t('ui.off') }}</option>
            </select>
            <button type="button" class="btn btn-sm" id="actor-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="actor-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="actor-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}</button>
            <button type="button" class="chip" data-queue="status" data-value="1">{{ admin_t('ui.on') }}</button>
            <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.off') }}</button>
            <button type="button" class="chip" data-queue="empty_pic" data-value="1">{{ admin_t('ui.no_avatar') }}</button>
            <button type="button" class="chip" data-queue="repeat" data-value="1">{{ admin_t('ui.duplicate') }}</button>
        </div>
        <p class="muted recycle-lead">{{ admin_t('ui.actors_lead') }}</p>
        <div class="batch-bar" id="actor-batch" hidden>
            <strong id="actor-batch-count">{{ admin_t('ui.selected_n', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="actor-batch-on">{{ admin_t('ui.on') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="actor-batch-off">{{ admin_t('ui.off') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="actor-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="actor-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="actor-table"></div>
    </div>
</div>
<template id="actor-dialog-tpl">
    <form class="admin-form">
        <input type="hidden" name="id">
        <label>{{ admin_t('ui.name') }}</label>
        <input class="entry-title" type="text" name="name" placeholder="{{ admin_t('ui.ph_actor') }}" required autofocus>
        <p class="muted field-hint">{{ admin_t('ui.actor_dialog_hint') }}</p>
        <label>{{ admin_t('ui.alias') }}</label>
        <input type="text" name="slug" placeholder="{{ admin_t('live.ph_slug') }}">
        <p class="muted field-hint">{{ admin_t('live.slug_hint') }}</p>
        <label>{{ admin_t('ui.avatar') }}</label>
        <div class="field-inline">
            <input type="text" name="avatar" placeholder="{{ admin_t('ui.ph_cover_upload') }}">
            <button type="button" class="btn btn-muted actor-avatar-upload-btn">{{ admin_t('ui.upload') }}</button>
        </div>
        <img class="img-preview actor-avatar-preview" alt="">
        <p class="muted field-hint">{{ admin_t('ui.actor_avatar_hint') }}</p>
        <div class="admin-dialog-grid">
            <div>
                <label>{{ admin_t('ui.sort') }}</label>
                <input type="number" name="sort" value="0">
            </div>
            <div>
                <label>{{ admin_t('ui.status') }}</label>
                <select name="status">
                    <option value="1">{{ admin_t('ui.on') }}</option>
                    <option value="0">{{ admin_t('ui.off') }}</option>
                </select>
            </div>
        </div>
        <p class="muted field-hint">{{ admin_t('ui.actor_off_hint') }}</p>
        <details class="form-more">
            <summary>{{ admin_t('ui.profile') }}</summary>
            <label>{{ admin_t('ui.gender') }}</label>
            <select name="sex">
                <option value="">{{ admin_t('ui.gender_unknown') }}</option>
                <option value="男">{{ admin_t('ui.gender_m') }}</option>
                <option value="女">{{ admin_t('ui.gender_f') }}</option>
            </select>
            <label>{{ admin_t('ui.area') }}</label>
            <input type="text" name="area" placeholder="{{ admin_t('ui.optional') }}">
            <label>{{ admin_t('ui.birthday') }}</label>
            <input type="text" name="birthday" placeholder="{{ admin_t('ui.optional') }}">
            <label>{{ admin_t('ui.intro') }}</label>
            <textarea name="content" rows="4" placeholder="{{ admin_t('ui.optional') }}"></textarea>
        </details>
    </form>
</template>
@endsection
@php
    $actorJsLang = [
        'actors' => admin_t('ui.actors'),
        'add_actor' => admin_t('ui.add_actor'),
        'edit_actor' => admin_t('ui.edit_actor'),
        'sort' => admin_t('ui.sort'),
        'status' => admin_t('ui.status'),
        'actions' => admin_t('ui.actions'),
        'edit' => admin_t('ui.edit'),
        'delete' => admin_t('ui.delete'),
        'front' => admin_t('ui.front'),
        'on' => admin_t('ui.on'),
        'off' => admin_t('ui.off'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'no_match' => admin_t('ui.no_match'),
        'selected_n' => admin_t('ui.selected_n', ['n' => '__N__']),
        'please_select' => admin_t('ui.please_select'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'created' => admin_t('ui.created'),
        'deleted' => admin_t('ui.deleted'),
        'need_name' => admin_t('manga.need_title'),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'confirm_batch_del' => admin_t('manga.confirm_batch_del'),
        'confirm_del_named' => admin_t('live.confirm_del'),
        'empty_actors' => admin_t('ui.none'),
        'empty_actors_title' => admin_t('ui.empty_actors'),
        'empty_actors_hint' => admin_t('ui.empty_actors_hint'),
        'no_match_actors' => admin_t('ui.no_match_actors'),
        'videos' => admin_t('nav.videos'),
        'no_image' => admin_t('ui.no_image'),
        'videos_n' => admin_t('ui.tag_videos_n', ['n' => '__N__']),
        'no_videos_yet' => admin_t('ui.tag_no_videos'),
    ];
@endphp
@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($actorJsLang, JSON_UNESCAPED_UNICODE);
    var QUEUE_KEYS = ['empty_pic', 'repeat'];
    var form = document.getElementById('actor-search');
    var batchBar = document.getElementById('actor-batch');
    var batchCount = document.getElementById('actor-batch-count');
    var countEl = document.getElementById('actor-count');

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
        var active = '';
        QUEUE_KEYS.forEach(function (k) {
            if (form[k] && form[k].value === '1') active = k;
        });
        U.qa('#actor-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && !active && status === '') on = true;
            else if (key === 'status' && !active && status === val) on = true;
            else if (key && key !== 'status' && active === key) on = true;
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
        var cover = String(d.avatar || '').trim();
        var thumb = cover
            ? '<img class="vod-thumb actor-thumb" src="' + U.escape(cover) + '" alt="">'
            : '<span class="vod-thumb actor-thumb is-empty">' + U.escape(L.no_image) + '</span>';
        var meta = '#' + U.escape(d.id);
        var n = parseInt(d.video_count, 10) || 0;
        meta += n > 0 ? ' · ' + String(L.videos_n || '').replace('__N__', String(n)) : ' · ' + U.escape(L.no_videos_yet);
        if (d.sex) meta += ' · ' + U.escape(d.sex);
        if (d.area) meta += ' · ' + U.escape(d.area);
        return '<div class="vod-cell">' + thumb + '<div><a class="vod-title js-edit" href="#">' + U.escape(d.name || '') + '</a>'
            + '<div class="muted">' + meta + '</div></div></div>';
    }

    var table = U.table({
        el: '#actor-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/actors/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_actors + '</p><p><button type="button" class="btn btn-muted btn-sm" id="actor-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_actors_title + '</p><p class="muted">' + L.empty_actors_hint + '</p><p><button type="button" class="btn btn-primary btn-sm" id="actor-empty-add">' + L.add_actor + '</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            var add = document.getElementById('actor-empty-add');
            var reset = document.getElementById('actor-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_n || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.actors, html: nameHtml},
            {key: 'sort', title: L.sort, width: 64},
            {title: L.status, width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, L.on) : U.status(false, L.off);
            }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var href = d.url ? String(d.url) : ('/actor/' + encodeURIComponent(d.id));
                return '<a href="/admin/video?actor_id=' + encodeURIComponent(d.id) + '" class="btn-link">' + L.videos + '</a>'
                    + '<a href="' + U.escape(href) + '" target="_blank" rel="noopener" class="btn-link">' + L.front + '</a>'
                    + '<a href="#" class="btn-link js-edit">' + L.edit + '</a>'
                    + '<a href="#" class="btn-link js-del">' + L.delete + '</a>';
            }}
        ]
    });
    markChips();

    function bindAvatar(formEl) {
        U.bindImageField(formEl, {
            input: 'input[name=avatar]',
            btn: '.actor-avatar-upload-btn',
            preview: '.actor-avatar-preview'
        });
    }

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? L.edit_actor : L.add_actor,
            wide: true,
            content: document.getElementById('actor-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var formEl = body.querySelector('form');
                U.fillForm(formEl, {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    slug: row.slug || '',
                    avatar: row.avatar || '',
                    sex: row.sex || '',
                    area: row.area || '',
                    birthday: row.birthday || '',
                    content: row.content || '',
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? '1' : String(row.status)
                });
                bindAvatar(formEl);
                if (row.content || row.sex || row.area || row.birthday) {
                    var more = body.querySelector('.form-more');
                    if (more) more.open = true;
                }
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast(L.need_name, 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/actors/save', data).then(function (res) {
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
        U.post('/admin/video/actors/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }

    U.on('#actor-search-btn', 'click', runSearch);
    U.on('#actor-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#actor-add-btn', 'click', function () { openDialog('add'); });
    document.getElementById('actor-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#actor-batch-on', 'click', function () { batch('status', 1); });
    U.on('#actor-batch-off', 'click', function () { batch('status', 0); });
    U.on('#actor-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del); });
    U.on('#actor-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#actor-table', 'click', function (e) {
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
            U.post('/admin/video/actors/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
