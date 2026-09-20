@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('nav.publish_page'))

@php
    $desk = in_array((string) ($desk ?? ''), ['config', 'groups'], true) ? (string) $desk : 'config';
    $options = is_array($options ?? null) ? $options : [];
    $publishJsLang = [
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'no_match' => admin_t('ui.no_match'),
        'empty_groups' => admin_t('ui.empty_groups'),
        'add_group' => admin_t('ui.add_group'),
        'edit_group' => admin_t('ui.edit_group'),
        'please_fill_name' => admin_t('ui.please_fill_name'),
        'confirm_remove' => admin_t('ui.confirm_remove'),
        'deleted' => admin_t('ui.deleted'),
        'url_count' => admin_t('ui.url_count'),
    ];
@endphp

@section('plain')
<div class="card card-panel publish-board desk-board" id="publish-board">
    <div class="card-header">
        <span>{{ admin_t('nav.publish_page') }} <em id="publish-count"></em></span>
        <div>
            @if($desk === 'groups')
                <button type="button" class="btn btn-sm" id="publish-add-btn">{{ admin_t('ui.add') }}</button>
            @endif
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.publish_lead') }}</p>
        <div class="queue-chips">
            <a class="chip{{ $desk === 'config' ? ' active' : '' }}" href="/admin/video/publish_pages">{{ admin_t('ui.params_chip') }}</a>
            <a class="chip{{ $desk === 'groups' ? ' active' : '' }}" href="/admin/video/publish_pages?desk=groups">{{ admin_t('ui.line_groups') }}</a>
        </div>
        @if($desk === 'config')
            <form id="publish-config" onsubmit="return false;">
                <input type="hidden" name="desk" value="config">
                <label>{{ admin_t('ui.switch_label') }}</label>
                <select name="status">
                    <option value="0" @selected((int) ($options['status'] ?? 0) !== 1)>{{ admin_t('ui.gate_off') }}</option>
                    <option value="1" @selected((int) ($options['status'] ?? 0) === 1)>{{ admin_t('ui.gate_on') }}</option>
                </select>
                <label>{{ admin_t('ui.title_label') }}</label>
                <input type="text" name="title" value="{{ $options['title'] ?? admin_t('ui.publish_default_title') }}">
                <label>{{ admin_t('ui.subtitle') }}</label>
                <input type="text" name="subtitle" value="{{ $options['subtitle'] ?? '' }}">
                <label>{{ admin_t('ui.bookmark') }}</label>
                <input type="text" name="bookmark" value="{{ $options['bookmark'] ?? '' }}">
                <label>{{ admin_t('ui.footer_text') }}</label>
                <input type="text" name="footer" value="{{ $options['footer'] ?? '' }}">
                <label>{{ admin_t('ui.permanent_text') }}</label>
                <input type="text" name="permanent_text" value="{{ $options['permanent_text'] ?? '' }}">
                <label>{{ admin_t('ui.permanent_url') }}</label>
                <input type="text" name="permanent_url" value="{{ $options['permanent_url'] ?? '' }}" placeholder="https://">
                <p class="muted field-hint">{{ admin_t('ui.publish_cookie_hint') }}</p>
                <p><button type="button" class="btn btn-sm" id="publish-config-save">{{ admin_t('ui.save') }}</button></p>
            </form>
        @else
            <form class="filter-bar" id="publish-search" onsubmit="return false;">
                <input type="hidden" name="desk" value="groups">
                <input type="search" name="q" placeholder="{{ admin_t('ui.ph_search_groups') }}" autocomplete="off">
                <button type="button" class="btn btn-sm" id="publish-search-btn">{{ admin_t('ui.search') }}</button>
                <button type="reset" class="btn btn-muted btn-sm" id="publish-reset-btn">{{ admin_t('ui.reset') }}</button>
            </form>
            <div id="publish-table"></div>
        @endif
    </div>
</div>
<template id="publish-group-tpl">
    <form class="admin-form">
        <input type="hidden" name="id">
        <input type="hidden" name="desk" value="groups">
        <label>{{ admin_t('ui.name') }}</label>
        <input class="entry-title" type="text" name="title" required placeholder="{{ admin_t('ui.ph_line_one') }}" autofocus>
        <label>{{ admin_t('ui.remarks') }}</label>
        <input type="text" name="hint" placeholder="{{ admin_t('ui.visitor_hint') }}">
        <label>{{ admin_t('ui.label_url') }}</label>
        <textarea name="urls_text" rows="6" placeholder="{{ admin_t('ui.ph_urls_named') }}"></textarea>
        <p class="muted field-hint">{{ admin_t('ui.urls_hint') }}</p>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($publishJsLang, JSON_UNESCAPED_UNICODE);
    var desk = @json($desk, JSON_UNESCAPED_UNICODE);
    if (desk === 'config') {
        U.on('#publish-config-save', 'click', function () {
            var data = U.formData(document.getElementById('publish-config'));
            data.desk = 'config';
            U.post('/admin/video/publish_pages/save', data).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                U.toast(L.saved, 'ok');
            });
        });
        return;
    }
    var form = document.getElementById('publish-search');
    var countEl = document.getElementById('publish-count');
    function queryWhere() {
        var data = U.formData(form);
        var out = {desk: 'groups', limit: 20};
        if (data.q) out.q = data.q;
        return out;
    }
    var table = U.table({
        el: '#publish-table',
        url: '/admin/video/publish_pages/list',
        where: queryWhere(),
        emptyHtml: function (_p, where) {
            if (where && where.q) return '<div class="list-empty"><p>' + L.no_match + '</p></div>';
            return '<div class="list-empty"><p>' + L.empty_groups + '</p><p><button type="button" class="btn btn-primary btn-sm" id="publish-empty-add">' + L.add_group + '</button></p></div>';
        },
        onDraw: function (_w, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            var add = document.getElementById('publish-empty-add');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
        },
        cols: [
            {title: AdminUi.t('name'), html: function (d) { return '<a class="js-edit" href="#">' + U.escape(d.title || AdminUi.t('not_filled')) + '</a>'; }},
            {title: L.url_count, width: 80, html: function (d) { return U.escape(String(d.url_count == null ? 0 : d.url_count)); }},
            {title: AdminUi.t('actions'), cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">' + AdminUi.t('edit') + '</a><a href="#" class="btn-link js-del">' + AdminUi.t('delete') + '</a>';
            }}
        ]
    });
    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            wide: true,
            title: mode === 'edit' ? L.edit_group : L.add_group,
            content: document.getElementById('publish-group-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: mode === 'edit' ? (row.id || '') : '',
                    title: row.title || '',
                    hint: row.hint || '',
                    urls_text: row.urls_text || ''
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.title) { U.toast(L.please_fill_name, 'err'); return false; }
                data.desk = 'groups';
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/publish_pages/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    U.toast(L.saved, 'ok');
                    table.refresh();
                });
            }
        });
    }
    U.on('#publish-search-btn', 'click', function () { table.reload(queryWhere()); });
    U.on('#publish-reset-btn', 'click', function () { setTimeout(function () { table.reload(queryWhere()); }, 0); });
    U.on('#publish-add-btn', 'click', function () { openDialog('add'); });
    U.on('#publish-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm(L.confirm_remove)) return;
            U.post('/admin/video/publish_pages/delete', {id: row.id, desk: 'groups'}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
