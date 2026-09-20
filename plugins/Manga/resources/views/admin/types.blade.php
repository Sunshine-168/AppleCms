@extends('admin.layouts.inner')
@section('title', admin_t('ui.types'))

@php
    $typeJsLang = [
        'top' => admin_t('ui.top_level'),
        'move_parent' => admin_t('ui.move_parent'),
        'works_n' => admin_t('ui.works_n', ['n' => '__N__']),
        'children_n' => admin_t('ui.children_n', ['n' => '__N__']),
        'no_match' => admin_t('ui.no_match_noun', ['name' => admin_t('ui.types')]),
        'clear_filter' => admin_t('ui.clear_filter'),
        'empty' => admin_t('ui.empty_types'),
        'empty_hint' => admin_t('manga.empty_types_hint'),
        'add_type' => admin_t('ui.add_type'),
        'please_select' => admin_t('ui.please_select_noun', ['name' => admin_t('ui.types')]),
        'please_parent' => admin_t('ui.please_pick_parent'),
        'confirm_batch' => admin_t('ui.confirm_batch_del_types', ['name' => admin_t('ui.types'), 'content' => admin_t('ui.works')]),
        'confirm_del' => admin_t('ui.confirm_del_type', ['name' => '__NAME__', 'content' => admin_t('ui.works')]),
        'fail' => admin_t('ui.fail'),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'deleted' => admin_t('ui.deleted'),
    ];
@endphp

@section('plain')
<div class="card card-panel type-index">
    <div class="card-header">
        <span>{{ admin_t('ui.types') }} <em id="type-count"></em></span>
        <a class="btn btn-muted btn-sm" href="/admin/video/manga-types/create">{{ admin_t('ui.full_form') }}</a>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="manga-type-search" onsubmit="return false;">
            <input type="text" name="name" placeholder="{{ admin_t('ui.ph_search_noun', ['name' => admin_t('ui.types')]) }}" autocomplete="off">
            <button type="button" class="btn btn-sm" id="manga-type-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="manga-type-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <p class="muted recycle-lead">{{ admin_t('manga.types_lead') }}</p>
        <div class="batch-bar" id="type-batch" hidden>
            <strong id="type-batch-count">{{ admin_t('ui.selected_n', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="type-batch-on">{{ admin_t('ui.enabled') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="type-batch-off">{{ admin_t('ui.disabled') }}</button>
            <select id="type-batch-parent" class="batch-select"><option value="">{{ admin_t('ui.move_parent') }}</option></select>
            <button type="button" class="btn btn-muted btn-sm" id="type-batch-move">{{ admin_t('ui.move') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="type-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="type-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="manga-type-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($typeJsLang, JSON_UNESCAPED_UNICODE);
    var form = document.getElementById('manga-type-search');
    var batchBar = document.getElementById('type-batch');
    var batchCount = document.getElementById('type-batch-count');
    var countEl = document.getElementById('type-count');
    var base = '/admin/video/manga-types';
    var api = '/admin/video/manga_types';

    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return where[k] !== ''; });
    }
    function fillParentSelect(sel, excludeId, selected, placeholder) {
        if (!sel) return;
        excludeId = parseInt(excludeId, 10) || 0;
        var skip = {};
        if (excludeId) skip[excludeId] = true;
        var rows = table.rows() || [];
        rows.forEach(function (r) {
            var id = parseInt(r.id, 10) || 0;
            var pid = parseInt(r.parent_id, 10) || 0;
            if (skip[pid]) skip[id] = true;
        });
        var html = placeholder ? '<option value="">' + U.escape(placeholder) + '</option>' : '';
        html += '<option value="0">' + U.escape(L.top) + '</option>';
        rows.forEach(function (r) {
            var id = parseInt(r.id, 10) || 0;
            if (skip[id]) return;
            var pad = '';
            var d = parseInt(r.depth, 10) || 0;
            while (d-- > 0) pad += '└ ';
            html += '<option value="' + U.escape(r.id) + '">' + pad + U.escape(r.name || '') + '</option>';
        });
        sel.innerHTML = html;
        if (placeholder && (selected === '' || selected == null)) {
            sel.value = '';
            return;
        }
        sel.value = selected == null || selected === '' ? '0' : String(selected);
    }
    function fillBatchParent() {
        fillParentSelect(document.getElementById('type-batch-parent'), 0, '', L.move_parent);
    }
    function nameHtml(d) {
        var depth = parseInt(d.depth, 10) || 0;
        var branch = depth > 0 ? '<span class="cat-branch">└</span>' : '';
        var n = parseInt(d.manga_count, 10) || 0;
        var meta = '#' + U.escape(d.id);
        if (d.slug) meta += ' · /' + U.escape(d.slug);
        if (n > 0) {
            meta += ' · <a href="/admin/video/mangas?type_id=' + encodeURIComponent(d.id) + '">' + String(L.works_n || '').replace('__N__', String(n)) + '</a>';
        } else {
            meta += ' · ' + String(L.works_n || '').replace('__N__', '0');
        }
        if (parseInt(d.child_count, 10) > 0) meta += ' · ' + String(L.children_n || '').replace('__N__', String(d.child_count));
        return '<div class="cat-cell" style="padding-left:' + (depth * 22) + 'px">' + branch
            + '<div><a class="vod-title" href="' + base + '/' + encodeURIComponent(d.id) + '/edit">' + U.escape(d.name || '') + '</a>'
            + '<div class="muted">' + meta + '</div></div></div>';
    }

    var table = U.table({
        el: '#manga-type-table',
        url: api + '/list',
        pager: false,
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match + '</p><p><button type="button" class="btn btn-muted btn-sm" id="type-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty + '</p><p class="muted">' + L.empty_hint + '</p><p><a class="btn btn-primary btn-sm" href="' + base + '/create">' + L.add_type + '</a></p></div>';
        },
        onDraw: function (_wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            fillBatchParent();
            var reset = document.getElementById('type-empty-reset');
            if (reset) reset.addEventListener('click', function () { form.reset(); table.reload({}); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(AdminUi.t('selected_n') || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: AdminUi.t('types'), html: nameHtml},
            {key: 'sort', title: AdminUi.t('sort'), width: 64},
            {title: AdminUi.t('status'), width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, AdminUi.t('enabled')) : U.status(false, AdminUi.t('disabled'));
            }},
            {title: AdminUi.t('actions'), cls: 'actions', html: function (d) {
                var id = encodeURIComponent(d.id);
                var html = '';
                if (String(d.status) === '1') {
                    html += '<a href="/manga?type=' + id + '" target="_blank" rel="noopener">' + AdminUi.t('front') + '</a>';
                }
                html += '<a href="' + base + '/create?parent_id=' + id + '">' + AdminUi.t('child') + '</a>';
                html += '<a href="/admin/video/mangas?type_id=' + id + '">' + AdminUi.t('works') + '</a>';
                html += '<a href="' + base + '/' + id + '/edit">' + AdminUi.t('edit') + '</a>';
                html += '<a href="#" class="js-del">' + AdminUi.t('delete') + '</a>';
                return html;
            }}
        ]
    });

    function batch(action, value, confirmText) {
        var ids = table.selectedIds();
        if (!ids.length) { U.toast(L.please_select, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post(api + '/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }

    U.on('#manga-type-search-btn', 'click', function () {
        var where = {};
        var name = (form.name.value || '').trim();
        if (name) where.name = name;
        table.reload(where);
    });
    U.on('#manga-type-reset-btn', 'click', function () {
        setTimeout(function () { table.reload({}); }, 0);
    });
    U.on('#type-batch-on', 'click', function () { batch('status', 1); });
    U.on('#type-batch-off', 'click', function () { batch('status', 0); });
    U.on('#type-batch-move', 'click', function () {
        var val = document.getElementById('type-batch-parent').value;
        if (val === '') { U.toast(L.please_parent, 'err'); return; }
        batch('parent', val);
    });
    U.on('#type-batch-del', 'click', function () { batch('delete', '', L.confirm_batch); });
    U.on('#type-batch-clear', 'click', function () { table.clearSelection(); });

    U.on('#manga-type-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a || !a.classList.contains('js-del')) return;
        e.preventDefault();
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        if (!U.confirm(String(L.confirm_del || '').replace('__NAME__', row.name || ''))) return;
        U.post(api + '/delete', {id: row.id}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.deleted, 'ok');
        });
    });
})();
</script>
@endpush
