@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('page.roles'))

@php
    $queues = $queues ?? ['all' => 0, 'on' => 0, 'off' => 0, 'no_video' => 0, 'no_actor' => 0, 'no_cover' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $videoId = (int) ($videoId ?? 0);
    $actorId = (int) ($actorId ?? 0);
    $videoTitle = (string) ($videoTitle ?? '');
    $actorName = (string) ($actorName ?? '');
    $roleJsLang = [
        'no_image' => admin_t('ui.no_image'),
        'no_video' => admin_t('ui.no_video_link'),
        'no_actor' => admin_t('ui.no_actor_link'),
        'no_match' => admin_t('ui.no_match_roles'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'empty' => admin_t('ui.empty_cast'),
        'empty_hint' => admin_t('ui.empty_cast_hint'),
        'add_role' => admin_t('ui.add_role'),
        'cast' => admin_t('ui.cast'),
        'videos' => admin_t('ui.videos'),
        'actors' => admin_t('ui.actors'),
        'selected_n' => admin_t('ui.selected_n', ['n' => '__N__']),
        'video_deleted_n' => admin_t('ui.video_deleted_n'),
        'video_n' => admin_t('ui.video_n'),
        'actor_deleted_n' => admin_t('ui.actor_deleted_n'),
        'actor_n' => admin_t('ui.actor_n'),
        'role_no_video_hint' => admin_t('ui.role_no_video_hint'),
        'video_missing_will_fail' => admin_t('ui.video_missing_will_fail'),
        'will_attach' => admin_t('ui.will_attach'),
        'actor_will' => admin_t('ui.actor_will'),
        'actor_missing_will_fail' => admin_t('ui.actor_missing_will_fail'),
        'role_actor_hint' => admin_t('ui.role_actor_hint'),
        'edit_role' => admin_t('ui.edit_role'),
        'need_role_name' => admin_t('ui.need_role_name'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'created' => admin_t('ui.created'),
        'please_select_roles' => admin_t('ui.please_select_roles'),
        'op_fail' => admin_t('ui.op_fail'),
        'op_ok' => admin_t('ui.op_ok'),
        'confirm_del_roles' => admin_t('ui.confirm_del_roles'),
        'confirm_del_role' => admin_t('ui.confirm_del_role'),
        'deleted' => admin_t('ui.deleted'),
    ];
@endphp

@section('plain')
<div class="card card-panel role-index" id="role-index">
    <div class="card-header">
        <span>{{ admin_t('ui.cast_lib') }} <em id="role-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="role-add-btn">{{ admin_t('ui.add_role') }}</button>
            <a class="btn btn-muted btn-sm" href="/admin/video">{{ admin_t('ui.videos') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/actors">{{ admin_t('ui.actors') }}</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.cast_lead') }}</p>
        @if($videoId > 0)
            <p class="role-focus">{{ $videoTitle !== '' ? admin_t('ui.watching_roles', ['name' => $videoTitle]) : admin_t('ui.video_missing_save', ['id' => $videoId]) }}</p>
        @endif
        @if($actorId > 0)
            <p class="role-focus">{{ $actorName !== '' ? admin_t('ui.watching_actor_roles', ['name' => $actorName]) : admin_t('ui.actor_missing_save', ['id' => $actorId]) }}</p>
        @endif
        <form class="filter-bar" id="role-search" onsubmit="return false;">
            <input type="hidden" name="empty_video">
            <input type="hidden" name="empty_actor">
            <input type="hidden" name="empty_pic">
            <input type="hidden" name="video_id" value="{{ $videoId > 0 ? $videoId : '' }}">
            <input type="hidden" name="actor_id" value="{{ $actorId > 0 ? $actorId : '' }}">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_search_cast') }}" autocomplete="off" aria-label="{{ admin_t('ui.ph_search_cast') }}">
            <select name="status">
                <option value="">{{ admin_t('ui.status') }}</option>
                <option value="1">{{ admin_t('ui.enabled') }}</option>
                <option value="0">{{ admin_t('ui.disabled') }}</option>
            </select>
            <button type="button" class="btn btn-sm" id="role-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="role-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="role-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">{{ admin_t('ui.enabled') }}@if($q('on') > 0)<em>{{ $q('on') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.disabled') }}@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="empty_video" data-value="1">{{ admin_t('ui.no_video_link') }}@if($q('no_video') > 0)<em>{{ $q('no_video') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="empty_actor" data-value="1">{{ admin_t('ui.no_actor_link') }}@if($q('no_actor') > 0)<em>{{ $q('no_actor') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="empty_pic" data-value="1">{{ admin_t('ui.no_cover') }}@if($q('no_cover') > 0)<em>{{ $q('no_cover') }}</em>@endif</button>
        </div>
        <div class="batch-bar" id="role-batch" hidden>
            <strong id="role-batch-count">{{ admin_t('ui.selected_n', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="role-batch-on">{{ admin_t('ui.enabled') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="role-batch-off">{{ admin_t('ui.disabled') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="role-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="role-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="role-table"></div>
    </div>
</div>
<template id="role-dialog-tpl">
    <form class="admin-form">
        <input type="hidden" name="id">
        <label>{{ admin_t('ui.role_name') }}</label>
        <input class="entry-title" type="text" name="name" placeholder="{{ admin_t('ui.ph_role_name') }}" required autofocus>
        <p class="muted field-hint">{{ admin_t('ui.role_name_hint') }}</p>
        <div class="admin-dialog-grid">
            <div>
                <label>{{ admin_t('ui.video_id') }}</label>
                <input type="number" name="video_id" min="0" placeholder="{{ admin_t('ui.ph_video_id') }}" inputmode="numeric">
                <p class="muted field-hint" id="role-video-hint">{{ admin_t('ui.role_no_video_hint') }}</p>
            </div>
            <div>
                <label>{{ admin_t('ui.actor_id') }}</label>
                <input type="number" name="actor_id" min="0" placeholder="{{ admin_t('ui.ph_actor_id') }}" inputmode="numeric">
                <p class="muted field-hint" id="role-actor-hint">{{ admin_t('ui.role_actor_hint') }}</p>
            </div>
        </div>
        <label>{{ admin_t('ui.cover') }}</label>
        <div class="field-inline">
            <input type="text" name="cover" placeholder="{{ admin_t('ui.ph_cover_opt') }}">
            <button type="button" class="btn btn-muted role-cover-upload-btn">{{ admin_t('ui.upload') }}</button>
        </div>
        <img class="img-preview role-cover-preview" alt="">
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
        <details class="form-more">
            <summary>{{ admin_t('ui.blurb_detail') }}</summary>
            <label>{{ admin_t('ui.slug') }}</label>
            <input type="text" name="slug" placeholder="{{ admin_t('ui.ph_slug_url') }}">
            <label>{{ admin_t('ui.intro') }}</label>
            <input type="text" name="blurb" placeholder="{{ admin_t('ui.ph_one_line') }}">
            <label>{{ admin_t('ui.role_detail') }}</label>
            <textarea name="content" rows="4" placeholder="{{ admin_t('ui.optional') }}"></textarea>
        </details>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($roleJsLang, JSON_UNESCAPED_UNICODE);
    var QUEUE_KEYS = ['empty_video', 'empty_actor', 'empty_pic'];
    var form = document.getElementById('role-search');
    var batchBar = document.getElementById('role-batch');
    var batchCount = document.getElementById('role-batch-count');
    var countEl = document.getElementById('role-count');
    var prefillVideo = @json($videoId > 0 ? $videoId : 0);
    var prefillActor = @json($actorId > 0 ? $actorId : 0);
    var prefillVideoTitle = @json($videoTitle, JSON_UNESCAPED_UNICODE);
    var prefillActorName = @json($actorName, JSON_UNESCAPED_UNICODE);

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
        U.qa('#role-queues .chip').forEach(function (chip) {
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
        var cover = String(d.cover || '').trim();
        var thumb = cover
            ? '<img class="vod-thumb role-thumb" src="' + U.escape(cover) + '" alt="">'
            : '<span class="vod-thumb role-thumb is-empty">' + L.no_image + '</span>';
        var meta = '#' + U.escape(d.id);
        if (d.blurb) meta += ' · ' + U.escape(d.blurb);
        return '<div class="vod-cell">' + thumb + '<div><a class="vod-title js-edit" href="#">' + U.escape(d.name || '') + '</a>'
            + '<div class="muted">' + meta + '</div></div></div>';
    }
    function videoHtml(d) {
        var vid = parseInt(d.video_id, 10) || 0;
        if (vid < 1) return '<span class="muted">' + L.no_video + '</span>';
        if (d.video_missing) return '<span class="muted">' + U.escape((L.video_deleted_n || '').replace(':id', vid)) + '</span>';
        var title = d.video_title || (L.video_n || '').replace(':id', vid);
        return '<a href="/admin/video/' + vid + '/edit">' + U.escape(title) + '</a>';
    }
    function actorHtml(d) {
        var aid = parseInt(d.actor_id, 10) || 0;
        if (aid < 1) return '<span class="muted">' + L.no_actor + '</span>';
        if (d.actor_missing) return '<span class="muted">' + U.escape((L.actor_deleted_n || '').replace(':id', aid)) + '</span>';
        var name = d.actor_name || (L.actor_n || '').replace(':id', aid);
        return '<a href="/admin/video/actors">' + U.escape(name) + '</a>';
    }

    var table = U.table({
        el: '#role-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/roles/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match + '</p><p><button type="button" class="btn btn-muted btn-sm" id="role-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty + '</p><p class="muted">' + L.empty_hint + '</p><p><button type="button" class="btn btn-primary btn-sm" id="role-empty-add">' + L.add_role + '</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            var add = document.getElementById('role-empty-add');
            var reset = document.getElementById('role-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                if (prefillVideo) form.video_id.value = String(prefillVideo);
                if (prefillActor) form.actor_id.value = String(prefillActor);
                runSearch();
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_n || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.cast, html: nameHtml},
            {title: L.videos, html: videoHtml},
            {title: L.actors, html: actorHtml},
            {key: 'sort', title: AdminUi.t('sort'), width: 64},
            {title: AdminUi.t('status'), width: 72, html: function (d) {
                return d.is_on ? U.status(true, AdminUi.t('enabled')) : U.status(false, AdminUi.t('disabled'));
            }},
            {title: AdminUi.t('actions'), cls: 'actions', html: function (d) {
                var href = d.url ? String(d.url) : ('/role/' + encodeURIComponent(d.id));
                return '<a href="' + U.escape(href) + '" target="_blank" rel="noopener" class="btn-link">' + AdminUi.t('front') + '</a>'
                    + '<a href="#" class="btn-link js-edit">' + AdminUi.t('edit') + '</a>'
                    + '<a href="#" class="btn-link js-del">' + AdminUi.t('delete') + '</a>';
            }}
        ]
    });
    markChips();

    function bindCover(formEl) {
        U.bindImageField(formEl, {
            input: 'input[name=cover]',
            btn: '.role-cover-upload-btn',
            preview: '.role-cover-preview'
        });
    }
    function bindLookups(formEl, row) {
        var videoInput = formEl.querySelector('input[name=video_id]');
        var actorInput = formEl.querySelector('input[name=actor_id]');
        var videoHint = formEl.querySelector('#role-video-hint');
        var actorHint = formEl.querySelector('#role-actor-hint');
        function setHint(el, text) { if (el) el.textContent = text; }
        function showVideo(id, title, missing) {
            id = parseInt(id, 10) || 0;
            if (id < 1) { setHint(videoHint, L.role_no_video_hint); return; }
            if (missing) { setHint(videoHint, (L.video_missing_will_fail || '').replace(':id', id)); return; }
            if (title) { setHint(videoHint, (L.will_attach || '').replace(':name', title)); return; }
            setHint(videoHint, (L.video_n || '').replace(':id', id));
        }
        function showActor(id, name, missing) {
            id = parseInt(id, 10) || 0;
            if (id < 1) { setHint(actorHint, L.role_actor_hint); return; }
            if (missing) { setHint(actorHint, (L.actor_missing_will_fail || '').replace(':id', id)); return; }
            if (name) { setHint(actorHint, (L.actor_will || '').replace(':name', name)); return; }
            setHint(actorHint, (L.actor_n || '').replace(':id', id));
        }
        showVideo(videoInput.value, row.video_title || (String(videoInput.value) === String(prefillVideo) ? prefillVideoTitle : ''), row.video_missing);
        showActor(actorInput.value, row.actor_name || (String(actorInput.value) === String(prefillActor) ? prefillActorName : ''), row.actor_missing);
        videoInput.addEventListener('blur', function () {
            var id = parseInt(videoInput.value, 10) || 0;
            if (id < 1) { showVideo(0, '', 0); return; }
            U.get('/admin/video/info', {id: id}).then(function (res) {
                var d = (res && res.data) || {};
                var title = d.title || '';
                showVideo(id, title, res && res.code === 0 && title ? 0 : 1);
            });
        });
        actorInput.addEventListener('blur', function () {
            var id = parseInt(actorInput.value, 10) || 0;
            if (id < 1) { showActor(0, '', 0); return; }
            U.get('/admin/video/actors/list', {id: id, limit: 1}).then(function (res) {
                var list = ((res && res.data) || {}).data || [];
                var hit = list[0];
                showActor(id, hit && parseInt(hit.id, 10) === id ? (hit.name || '') : '', hit && parseInt(hit.id, 10) === id ? 0 : 1);
            });
        });
    }

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            wide: true,
            title: mode === 'edit' ? L.edit_role : L.add_role,
            content: document.getElementById('role-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var formEl = body.querySelector('form');
                var videoVal = mode === 'edit' ? (row.video_id || 0) : (row.video_id || prefillVideo || 0);
                var actorVal = mode === 'edit' ? (row.actor_id || 0) : (row.actor_id || prefillActor || 0);
                U.fillForm(formEl, {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    video_id: videoVal || '',
                    actor_id: actorVal || '',
                    cover: row.cover || '',
                    slug: row.slug || '',
                    blurb: row.blurb || '',
                    content: row.content || '',
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? '1' : String(row.status)
                });
                bindCover(formEl);
                bindLookups(formEl, {
                    video_title: row.video_title || '',
                    video_missing: row.video_missing,
                    actor_name: row.actor_name || '',
                    actor_missing: row.actor_missing
                });
                if (row.slug || row.blurb || row.content) {
                    var more = body.querySelector('.form-more');
                    if (more) more.open = true;
                }
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast(L.need_role_name, 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/roles/save', data).then(function (res) {
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
        if (!ids.length) { U.toast(L.please_select_roles, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/roles/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }

    U.on('#role-search-btn', 'click', runSearch);
    U.on('#role-reset-btn', 'click', function () {
        setTimeout(function () {
            if (prefillVideo) form.video_id.value = String(prefillVideo);
            if (prefillActor) form.actor_id.value = String(prefillActor);
            runSearch();
        }, 0);
    });
    U.on('#role-add-btn', 'click', function () { openDialog('add'); });
    document.getElementById('role-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#role-batch-on', 'click', function () { batch('status', 1); });
    U.on('#role-batch-off', 'click', function () { batch('status', 0); });
    U.on('#role-batch-del', 'click', function () { batch('delete', '', L.confirm_del_roles); });
    U.on('#role-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#role-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank' || (a.getAttribute('href') || '').indexOf('/admin/video/') === 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm((L.confirm_del_role || '').replace(':name', row.name || ''))) return;
            U.post('/admin/video/roles/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
