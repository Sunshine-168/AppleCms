@extends('admin.layouts.inner')
@section('title', $title)

@php
    $topicJsLang = [
        'status' => admin_t('ui.status'),
        'actions' => admin_t('ui.actions'),
        'edit' => admin_t('ui.edit'),
        'delete' => admin_t('ui.delete'),
        'sort' => admin_t('ui.sort'),
        'on' => admin_t('ui.on'),
        'off' => admin_t('ui.off'),
        'front' => admin_t('ui.front'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'created' => admin_t('ui.created'),
        'deleted' => admin_t('ui.deleted'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'selected_n' => admin_t('ui.selected_n', ['n' => '__N__']),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'please_fill_name' => admin_t('ui.please_fill_name'),
        'search' => admin_t('ui.search'),
        'col_topic' => admin_t('ui.col_topic'),
        'add_topic' => admin_t('ui.add_topic'),
        'edit_topic' => admin_t('ui.edit_topic'),
        'empty_topics' => admin_t('ui.empty_topics'),
        'empty_topics_hint' => admin_t('ui.empty_topics_hint'),
        'no_match_topics' => admin_t('ui.no_match_topics'),
        'no_pic' => admin_t('ui.no_pic'),
        'topic_no_videos' => admin_t('ui.topic_no_videos'),
        'tag_videos_n' => admin_t('ui.tag_videos_n', ['n' => '__N__']),
        'topic_arts_n' => admin_t('ui.topic_arts_n', ['n' => '__N__']),
        'bind_videos' => admin_t('ui.bind_videos'),
        'bind_arts' => admin_t('ui.bind_arts'),
        'created_then_bind' => admin_t('ui.created_then_bind'),
        'please_select_topics' => admin_t('ui.please_select_topics'),
        'confirm_batch_del_topics' => admin_t('ui.confirm_batch_del_topics'),
        'confirm_del_topic' => admin_t('ui.confirm_del_topic', ['name' => '__NAME__']),
        'load_fail' => admin_t('ui.load_fail'),
        'remove' => admin_t('ui.remove'),
        'bind_videos_empty' => admin_t('ui.bind_videos_empty'),
        'bind_videos_hint' => admin_t('ui.bind_videos_hint'),
        'ph_bind_video' => admin_t('ui.ph_bind_video'),
        'bind_videos_title' => admin_t('ui.bind_videos_title', ['name' => '__NAME__']),
        'save_video_list' => admin_t('ui.save_video_list'),
        'already_in_list' => admin_t('ui.already_in_list'),
        'please_search_title' => admin_t('ui.please_search_title'),
        'no_match_videos' => admin_t('ui.no_match_videos'),
        'video_list_saved' => admin_t('ui.video_list_saved'),
        'bind_arts_empty' => admin_t('ui.bind_arts_empty'),
        'bind_arts_hint' => admin_t('ui.bind_arts_hint'),
        'ph_bind_art' => admin_t('ui.ph_bind_art'),
        'bind_arts_title' => admin_t('ui.bind_arts_title', ['name' => '__NAME__']),
        'save_art_list' => admin_t('ui.save_art_list'),
        'already_in_topic' => admin_t('ui.already_in_topic'),
        'please_search_art_title' => admin_t('ui.please_search_art_title'),
        'no_match_arts' => admin_t('ui.no_match_arts'),
        'art_list_saved' => admin_t('ui.art_list_saved'),
    ];
@endphp

@section('plain')
<div class="card card-panel topic-index">
    <div class="card-header">
        <span>{{ admin_t('ui.topics') }}</span>
        <button type="button" class="btn btn-sm" id="topic-add-btn">{{ admin_t('ui.add_topic') }}</button>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="topic-search" onsubmit="return false;">
            <input type="text" name="name" placeholder="{{ admin_t('ui.ph_topic') }}" autocomplete="off">
            <select name="status">
                <option value="">{{ admin_t('ui.status') }}</option>
                <option value="1">{{ admin_t('ui.on') }}</option>
                <option value="0">{{ admin_t('ui.off') }}</option>
            </select>
            <button type="button" class="btn btn-sm" id="topic-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="topic-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="topic-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}</button>
            <button type="button" class="chip" data-queue="status" data-value="1">{{ admin_t('ui.on') }}</button>
            <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.off') }}</button>
        </div>
        <p class="muted recycle-lead">{{ admin_t('ui.topics_lead') }}</p>
        <div class="batch-bar" id="topic-batch" hidden>
            <strong id="topic-batch-count">{{ admin_t('ui.selected_n', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="topic-batch-on">{{ admin_t('ui.on') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="topic-batch-off">{{ admin_t('ui.off') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="topic-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="topic-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="topic-table"></div>
    </div>
</div>
<template id="topic-dialog-tpl">
    <form class="admin-form">
        <input type="hidden" name="id">
        <label>{{ admin_t('ui.name') }}</label>
        <input class="entry-title" type="text" name="name" placeholder="{{ admin_t('ui.ph_topic') }}" required autofocus>
        <label>{{ admin_t('ui.alias') }}</label>
        <input type="text" name="slug" placeholder="{{ admin_t('live.ph_slug') }}">
        <p class="muted field-hint">{{ admin_t('live.slug_hint') }}</p>
        <label>{{ admin_t('ui.cover') }}</label>
        <div class="field-inline">
            <input type="text" name="cover" class="topic-cover-input" placeholder="{{ admin_t('live.ph_cover') }}">
            <button type="button" class="btn btn-muted topic-cover-upload-btn">{{ admin_t('ui.upload') }}</button>
        </div>
        <img class="img-preview topic-cover-preview" alt="">
        <p class="muted field-hint">{{ admin_t('live.cover_hint') }}</p>
        <label>{{ admin_t('ui.intro') }}</label>
        <input type="text" name="blurb" placeholder="{{ admin_t('ui.optional') }}">
        <label>{{ admin_t('ui.content') }}</label>
        <textarea name="content" rows="4" placeholder="{{ admin_t('ui.optional') }}"></textarea>
        <details class="form-more topic-more">
        <summary>{{ admin_t('ui.form_more_display') }}</summary>
        <label>{{ admin_t('ui.subtitle') }}</label>
        <input type="text" name="sub" placeholder="{{ admin_t('ui.ph_topic_sub') }}">
        <div class="admin-dialog-grid">
            <div>
                <label>{{ admin_t('ui.letter') }}</label>
                <input type="text" name="letter" maxlength="8" placeholder="{{ admin_t('ui.ph_letter_auto') }}">
            </div>
            <div>
                <label>{{ admin_t('ui.highlight_color') }}</label>
                <input type="text" name="color" maxlength="16" placeholder="{{ admin_t('ui.ph_color') }}">
            </div>
        </div>
        <div class="admin-dialog-grid">
            <div>
                <label>{{ admin_t('ui.recommend') }}</label>
                <input type="number" name="level" value="0" min="0">
            </div>
            <div>
                <label>{{ admin_t('ui.remarks') }}</label>
                <input type="text" name="remarks" placeholder="{{ admin_t('ui.ph_remarks_internal') }}">
            </div>
        </div>
        <p class="muted field-hint">{{ admin_t('ui.hint_topic_level') }}</p>
        <label>{{ admin_t('ui.thumb') }}</label>
        <div class="field-inline">
            <input type="text" name="cover_thumb" class="topic-thumb-input" placeholder="{{ admin_t('ui.ph_thumb') }}">
            <button type="button" class="btn btn-muted topic-thumb-upload-btn">{{ admin_t('ui.upload') }}</button>
        </div>
        <img class="img-preview topic-thumb-preview" alt="">
        <label>{{ admin_t('ui.slide_cover') }}</label>
        <div class="field-inline">
            <input type="text" name="cover_slide" class="topic-slide-input" placeholder="{{ admin_t('ui.ph_slide_cover') }}">
            <button type="button" class="btn btn-muted topic-slide-upload-btn">{{ admin_t('ui.upload') }}</button>
        </div>
        <img class="img-preview topic-slide-preview" alt="">
        <label>{{ admin_t('ui.tpl') }}</label>
        <input type="text" name="tpl" placeholder="{{ admin_t('ui.ph_tpl_default') }}">
        <p class="muted field-hint">{{ admin_t('ui.hint_topic_tpl') }}</p>
        <label>{{ admin_t('ui.ext_classes') }}</label>
        <input type="text" name="type" placeholder="{{ admin_t('ui.ph_topic_type') }}">
        <label>{{ admin_t('ui.tags') }}</label>
        <input type="text" name="tag" placeholder="{{ admin_t('ui.ph_tags_csv') }}">
        <label>{{ admin_t('ui.seo_title') }}</label>
        <input type="text" name="seo_title" placeholder="{{ admin_t('ui.ph_seo_title') }}">
        <label>{{ admin_t('ui.seo_key') }}</label>
        <input type="text" name="seo_key">
        <label>{{ admin_t('ui.seo_des') }}</label>
        <input type="text" name="seo_des">
        </details>
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
        <p class="muted field-hint">{{ admin_t('ui.hint_topic_status') }}</p>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($topicJsLang, JSON_UNESCAPED_UNICODE);
    var form = document.getElementById('topic-search');
    var batchBar = document.getElementById('topic-batch');
    var batchCount = document.getElementById('topic-batch-count');

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
        U.qa('#topic-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = (key === '' && status === '') || (key === 'status' && status === val);
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        form.status.value = key === 'status' ? (value || '') : '';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function nameHtml(d) {
        var cover = String(d.cover || '').trim();
        var thumb = cover
            ? '<img class="vod-thumb" src="' + U.escape(cover) + '" alt="">'
            : '<span class="vod-thumb is-empty">' + U.escape(L.no_pic) + '</span>';
        var meta = '#' + U.escape(d.id);
        if (d.slug) meta += ' · /' + U.escape(d.slug);
        var n = parseInt(d.video_count, 10) || 0;
        meta += n > 0
            ? ' · ' + String(L.tag_videos_n || '').replace('__N__', String(n))
            : ' · ' + L.topic_no_videos;
        var an = parseInt(d.art_count, 10) || 0;
        if (an > 0) meta += ' · ' + String(L.topic_arts_n || '').replace('__N__', String(an));
        if (d.blurb) meta += ' · ' + U.escape(d.blurb);
        return '<div class="vod-cell">' + thumb + '<div><a class="vod-title js-edit" href="#">' + U.escape(d.name || '') + '</a>'
            + '<div class="muted">' + meta + '</div></div></div>';
    }

    var table = U.table({
        el: '#topic-table',
        url: '/admin/video/topics/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_topics + '</p><p><button type="button" class="btn btn-muted btn-sm" id="topic-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_topics + '</p><p class="muted">' + L.empty_topics_hint + '</p><p><button type="button" class="btn btn-primary btn-sm" id="topic-empty-add">' + L.add_topic + '</button></p></div>';
        },
        onDraw: function () {
            var add = document.getElementById('topic-empty-add');
            var reset = document.getElementById('topic-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_n || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.col_topic, html: nameHtml},
            {key: 'sort', title: L.sort, width: 64},
            {title: L.status, width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, L.on) : U.status(false, L.off);
            }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var href = d.url ? String(d.url) : ('/topic/' + encodeURIComponent(d.slug || d.id));
                return '<a href="#" class="btn-link js-bind">' + L.bind_videos + '</a>'
                    + '<a href="#" class="btn-link js-bind-art">' + L.bind_arts + '</a>'
                    + '<a href="' + U.escape(href) + '" target="_blank" rel="noopener" class="btn-link">' + L.front + '</a>'
                    + '<a href="#" class="btn-link js-edit">' + L.edit + '</a>'
                    + '<a href="#" class="btn-link js-del">' + L.delete + '</a>';
            }}
        ]
    });
    markChips();

    function bindCover(formEl) {
        U.bindImageField(formEl, {
            input: 'input[name=cover]',
            btn: '.topic-cover-upload-btn',
            preview: '.topic-cover-preview'
        });
        U.bindImageField(formEl, {
            input: 'input[name=cover_thumb]',
            btn: '.topic-thumb-upload-btn',
            preview: '.topic-thumb-preview'
        });
        U.bindImageField(formEl, {
            input: 'input[name=cover_slide]',
            btn: '.topic-slide-upload-btn',
            preview: '.topic-slide-preview'
        });
    }

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? L.edit_topic : L.add_topic,
            content: document.getElementById('topic-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var formEl = body.querySelector('form');
                U.fillForm(formEl, {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    slug: row.slug || '',
                    cover: row.cover || '',
                    blurb: row.blurb || '',
                    content: row.content || '',
                    sub: row.sub || '',
                    letter: row.letter || '',
                    color: row.color || '',
                    level: row.level == null ? 0 : row.level,
                    remarks: row.remarks || '',
                    cover_thumb: row.cover_thumb || '',
                    cover_slide: row.cover_slide || '',
                    tpl: row.tpl || '',
                    type: row.type || '',
                    tag: row.tag || '',
                    seo_title: row.seo_title || '',
                    seo_key: row.seo_key || '',
                    seo_des: row.seo_des || '',
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? '1' : String(row.status)
                });
                bindCover(formEl);
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast(L.please_fill_name, 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/topics/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    U.toast(mode === 'edit' ? L.saved : L.created_then_bind, 'ok');
                    table.refresh();
                });
            }
        });
    }

    function openBind(row) {
        U.loading(true);
        U.get('/admin/video/topics/' + row.id + '/videos').then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.load_fail, 'err'); return; }
            var picked = (res.data && res.data.videos) ? res.data.videos.slice() : [];
            function renderList() {
                if (!picked.length) return '<p class="muted">' + L.bind_videos_empty + '</p>';
                var html = '<ul class="topic-bind-list">';
                picked.forEach(function (v, i) {
                    html += '<li data-id="' + U.escape(v.id) + '"><span>' + U.escape(v.title || ('#' + v.id)) + '</span>'
                        + '<button type="button" class="btn-link js-remove" data-i="' + i + '">' + L.remove + '</button></li>';
                });
                html += '</ul>';
                return html;
            }
            var html = '<p class="hint">' + L.bind_videos_hint + '</p>'
                + '<div class="field-inline"><input type="text" id="topic-bind-q" placeholder="' + U.escape(L.ph_bind_video) + '"><button type="button" class="btn btn-sm" id="topic-bind-search">' + L.search + '</button></div>'
                + '<div id="topic-bind-hits" class="topic-bind-hits"></div>'
                + '<div id="topic-bind-picked">' + renderList() + '</div>';
            U.dialog({
                title: String(L.bind_videos_title || '').replace('__NAME__', row.name || ''),
                wide: true,
                okText: L.save_video_list,
                content: html,
                onOpen: function (body) {
                    var hits = body.querySelector('#topic-bind-hits');
                    var pickedBox = body.querySelector('#topic-bind-picked');
                    function redraw() { pickedBox.innerHTML = renderList(); }
                    function addVideo(v) {
                        var id = parseInt(v.id, 10);
                        if (picked.some(function (x) { return parseInt(x.id, 10) === id; })) {
                            U.toast(L.already_in_list, 'err');
                            return;
                        }
                        picked.push({id: id, title: v.title || ('#' + id), cover: v.cover || ''});
                        redraw();
                    }
                    body.querySelector('#topic-bind-search').addEventListener('click', function () {
                        var q = (body.querySelector('#topic-bind-q').value || '').trim();
                        if (!q) { U.toast(L.please_search_title, 'err'); return; }
                        U.get('/admin/video/list', {title: q, limit: 8}).then(function (r) {
                            var list = (r && r.data && r.data.data) || [];
                            if (!list.length) { hits.innerHTML = '<p class="muted">' + L.no_match_videos + '</p>'; return; }
                            var out = '';
                            list.forEach(function (v) {
                                out += '<button type="button" class="chip js-add" data-id="' + U.escape(v.id) + '" data-title="' + U.escape(v.title || '') + '">' + U.escape(v.title || ('#' + v.id)) + '</button>';
                            });
                            hits.innerHTML = out;
                        });
                    });
                    body.querySelector('#topic-bind-q').addEventListener('keydown', function (ev) {
                        if (ev.key === 'Enter') { ev.preventDefault(); body.querySelector('#topic-bind-search').click(); }
                    });
                    hits.addEventListener('click', function (e) {
                        var btn = e.target.closest('.js-add');
                        if (!btn) return;
                        addVideo({id: btn.getAttribute('data-id'), title: btn.getAttribute('data-title')});
                    });
                    pickedBox.addEventListener('click', function (e) {
                        var btn = e.target.closest('.js-remove');
                        if (!btn) return;
                        picked.splice(parseInt(btn.getAttribute('data-i'), 10), 1);
                        redraw();
                    });
                },
                onSave: function () {
                    var ids = picked.map(function (v) { return v.id; }).join(',');
                    return U.post('/admin/video/topics/' + row.id + '/videos', {video_ids: ids}).then(function (r) {
                        if (!r || r.code !== 0) { U.toast((r && r.msg) || L.fail, 'err'); return false; }
                        U.toast((r && r.msg) || L.video_list_saved, 'ok');
                        table.refresh();
                    });
                }
            });
        });
    }

    function openBindArt(row) {
        U.loading(true);
        U.get('/admin/video/topics/' + row.id + '/arts').then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.load_fail, 'err'); return; }
            var picked = (res.data && res.data.arts) ? res.data.arts.slice() : [];
            function renderList() {
                if (!picked.length) return '<p class="muted">' + L.bind_arts_empty + '</p>';
                var html = '<ul class="topic-bind-list">';
                picked.forEach(function (v, i) {
                    html += '<li data-id="' + U.escape(v.id) + '"><span>' + U.escape(v.title || ('#' + v.id)) + '</span>'
                        + '<button type="button" class="btn-link js-remove" data-i="' + i + '">' + L.remove + '</button></li>';
                });
                html += '</ul>';
                return html;
            }
            var html = '<p class="hint">' + L.bind_arts_hint + '</p>'
                + '<div class="field-inline"><input type="text" id="topic-bind-art-q" placeholder="' + U.escape(L.ph_bind_art) + '"><button type="button" class="btn btn-sm" id="topic-bind-art-search">' + L.search + '</button></div>'
                + '<div id="topic-bind-art-hits" class="topic-bind-hits"></div>'
                + '<div id="topic-bind-art-picked">' + renderList() + '</div>';
            U.dialog({
                title: String(L.bind_arts_title || '').replace('__NAME__', row.name || ''),
                wide: true,
                okText: L.save_art_list,
                content: html,
                onOpen: function (body) {
                    var hits = body.querySelector('#topic-bind-art-hits');
                    var pickedBox = body.querySelector('#topic-bind-art-picked');
                    function redraw() { pickedBox.innerHTML = renderList(); }
                    function addArt(v) {
                        var id = parseInt(v.id, 10);
                        if (picked.some(function (x) { return parseInt(x.id, 10) === id; })) {
                            U.toast(L.already_in_topic, 'err');
                            return;
                        }
                        picked.push({id: id, title: v.title || ('#' + id), cover: v.cover || ''});
                        redraw();
                    }
                    body.querySelector('#topic-bind-art-search').addEventListener('click', function () {
                        var q = (body.querySelector('#topic-bind-art-q').value || '').trim();
                        if (!q) { U.toast(L.please_search_art_title, 'err'); return; }
                        U.get('/admin/video/arts/list', {title: q, q: q, limit: 8}).then(function (r) {
                            var list = (r && r.data && r.data.data) || [];
                            if (!list.length) { hits.innerHTML = '<p class="muted">' + L.no_match_arts + '</p>'; return; }
                            var out = '';
                            list.forEach(function (v) {
                                out += '<button type="button" class="chip js-add" data-id="' + U.escape(v.id) + '" data-title="' + U.escape(v.title || '') + '">' + U.escape(v.title || ('#' + v.id)) + '</button>';
                            });
                            hits.innerHTML = out;
                        });
                    });
                    body.querySelector('#topic-bind-art-q').addEventListener('keydown', function (ev) {
                        if (ev.key === 'Enter') { ev.preventDefault(); body.querySelector('#topic-bind-art-search').click(); }
                    });
                    hits.addEventListener('click', function (e) {
                        var btn = e.target.closest('.js-add');
                        if (!btn) return;
                        addArt({id: btn.getAttribute('data-id'), title: btn.getAttribute('data-title')});
                    });
                    pickedBox.addEventListener('click', function (e) {
                        var btn = e.target.closest('.js-remove');
                        if (!btn) return;
                        picked.splice(parseInt(btn.getAttribute('data-i'), 10), 1);
                        redraw();
                    });
                },
                onSave: function () {
                    var ids = picked.map(function (v) { return v.id; }).join(',');
                    return U.post('/admin/video/topics/' + row.id + '/arts', {art_ids: ids}).then(function (r) {
                        if (!r || r.code !== 0) { U.toast((r && r.msg) || L.fail, 'err'); return false; }
                        U.toast((r && r.msg) || L.art_list_saved, 'ok');
                        table.refresh();
                    });
                }
            });
        });
    }

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_topics, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/topics/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }

    U.on('#topic-search-btn', 'click', runSearch);
    U.on('#topic-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#topic-add-btn', 'click', function () { openDialog('add'); });
    document.getElementById('topic-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#topic-batch-on', 'click', function () { batch('status', 1); });
    U.on('#topic-batch-off', 'click', function () { batch('status', 0); });
    U.on('#topic-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_topics); });
    U.on('#topic-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#topic-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank') return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-bind')) openBind(row);
        if (a.classList.contains('js-bind-art')) openBindArt(row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm(String(L.confirm_del_topic || '').replace('__NAME__', row.name || ''))) return;
            U.post('/admin/video/topics/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
