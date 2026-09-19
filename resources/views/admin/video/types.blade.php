@extends('admin.layouts.inner')
@php
    $scope = in_array(($scope ?? 'vod'), ['art', 'website'], true) ? $scope : 'vod';
    $isArt = $scope === 'art';
    $isWebsite = $scope === 'website';
    $base = $isArt ? '/admin/video/art-types' : ($isWebsite ? '/admin/video/website-types' : '/admin/video/types');
    $contentUrl = $isArt ? '/admin/video/arts' : ($isWebsite ? '/admin/video/websites' : '/admin/video');
    $noun = $isArt ? admin_t('ui.column') : admin_t('ui.types');
    $unit = $isArt ? admin_t('ui.articles') : ($isWebsite ? admin_t('ui.sites') : admin_t('nav.videos'));
    $contentLabel = $isArt ? admin_t('ui.articles') : ($isWebsite ? admin_t('ui.sites') : admin_t('nav.videos'));
    $pageTitle = $isArt ? admin_t('ui.column') : ($isWebsite ? admin_t('ui.nav_types') : admin_t('page.types'));
    $createLabel = $isArt ? admin_t('ui.add_column') : ($isWebsite ? admin_t('ui.add_nav_type') : admin_t('ui.add_type'));
    $typeJsLang = [
        'noun' => $noun,
        'unit' => $unit,
        'content_label' => $contentLabel,
        'create_label' => $createLabel,
        'status' => admin_t('ui.status'),
        'actions' => admin_t('ui.actions'),
        'edit' => admin_t('ui.edit'),
        'delete' => admin_t('ui.delete'),
        'sort' => admin_t('ui.sort'),
        'enabled' => admin_t('ui.enabled'),
        'disabled' => admin_t('ui.disabled'),
        'front' => admin_t('ui.front'),
        'fail' => admin_t('ui.fail'),
        'deleted' => admin_t('ui.deleted'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'top_level' => admin_t('ui.top_level'),
        'move_parent' => admin_t('ui.move_parent'),
        'add_child' => admin_t('ui.add_child'),
        'child' => admin_t('ui.child'),
        'kind' => admin_t('ui.kind'),
        'write_art' => admin_t('ui.write_art'),
        'go_websites' => admin_t('ui.go_websites'),
        'selected_n' => admin_t('ui.selected_n', ['n' => '__N__']),
        'children_n' => admin_t('ui.children_n', ['n' => '__N__']),
        'empty_types' => admin_t('ui.empty_types'),
        'empty_types_hint' => admin_t('ui.empty_types_hint'),
        'empty_art_types' => admin_t('ui.empty_art_types'),
        'empty_art_types_hint' => admin_t('ui.empty_art_types_hint'),
        'empty_nav_types' => admin_t('ui.empty_nav_types'),
        'empty_nav_types_hint' => admin_t('ui.empty_nav_types_hint'),
        'no_match_noun' => admin_t('ui.no_match_noun', ['name' => $noun]),
        'please_select_noun' => admin_t('ui.please_select_noun', ['name' => $noun]),
        'please_pick_parent' => admin_t('ui.please_pick_parent'),
        'confirm_batch_del_types' => admin_t('ui.confirm_batch_del_types', ['name' => $noun, 'content' => $contentLabel]),
        'confirm_del_type' => admin_t('ui.confirm_del_type', ['name' => '__NAME__', 'content' => $contentLabel]),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
    ];
@endphp
@section('title', $pageTitle)

@section('plain')
<div class="card card-panel type-index list-desk">
    <div class="card-header">
        <span>{{ $isWebsite ? admin_t('ui.nav_types') : $noun }} <em id="type-count"></em></span>
        <div>
            <a class="btn btn-sm" href="{{ $base }}/create">{{ $createLabel }}</a>
            @if($isWebsite)
                <a class="btn btn-muted btn-sm" href="/admin/video/websites">{{ admin_t('ui.websites_nav') }}</a>
            @endif
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="video-type-search" onsubmit="return false;">
            <input type="text" name="name" placeholder="{{ admin_t('ui.ph_search_noun', ['name' => $noun]) }}" autocomplete="off">
            <button type="button" class="btn btn-sm" id="video-type-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="video-type-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <p class="muted recycle-lead">
            @if($isArt)
                {{ admin_t('ui.types_lead_art') }}
            @elseif($isWebsite)
                {{ admin_t('ui.types_lead_website') }}
            @else
                {{ admin_t('ui.types_lead_vod') }}
            @endif
        </p>
        <div class="batch-bar" id="type-batch" hidden>
            <strong id="type-batch-count">{{ admin_t('ui.selected_n', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="type-batch-on">{{ admin_t('ui.enabled') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="type-batch-off">{{ admin_t('ui.disabled') }}</button>
            <select id="type-batch-parent" class="batch-select"><option value="">{{ admin_t('ui.move_parent') }}</option></select>
            <button type="button" class="btn btn-muted btn-sm" id="type-batch-move">{{ admin_t('ui.move') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="type-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="type-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="video-type-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($typeJsLang);
    var form = document.getElementById('video-type-search');
    var batchBar = document.getElementById('type-batch');
    var batchCount = document.getElementById('type-batch-count');
    var countEl = document.getElementById('type-count');
    var isArt = @json($isArt);
    var isWebsite = @json($isWebsite);
    var base = @json($base);
    var contentUrl = @json($contentUrl);
    var countKey = isArt ? 'art_count' : (isWebsite ? 'website_count' : 'video_count');

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
        html += '<option value="0">' + U.escape(L.top_level) + '</option>';
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
        var n = parseInt(d[countKey], 10) || 0;
        var meta = '#' + U.escape(d.id);
        if (d.slug) meta += ' · /' + U.escape(d.slug);
        if (n > 0) {
            meta += ' · <a href="' + contentUrl + '?type_id=' + encodeURIComponent(d.id) + '">' + U.escape(String(n)) + ' ' + L.unit + '</a>';
        } else {
            meta += ' · 0 ' + L.unit;
        }
        if (isArt && d.kind_label && d.kind && d.kind !== 'list') {
            meta += ' · ' + U.escape(d.kind_label);
        }
        if (parseInt(d.child_count, 10) > 0) meta += ' · ' + String(L.children_n || '').replace('__N__', U.escape(String(d.child_count)));
        return '<div class="cat-cell" style="padding-left:' + (depth * 22) + 'px">' + branch
            + '<div><a class="vod-title" href="' + base + '/' + encodeURIComponent(d.id) + '/edit">' + U.escape(d.name || '') + '</a>'
            + '<div class="muted">' + meta + '</div></div></div>';
    }

    var table = U.table({
        el: '#video-type-table',
        countEl: countEl,
        url: base + '/list',
        pager: false,
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_noun + '</p><p><button type="button" class="btn btn-muted btn-sm" id="type-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            if (isArt) {
                return '<div class="list-empty"><p>' + L.empty_art_types + '</p><p class="muted">' + L.empty_art_types_hint + '</p><p><a class="btn btn-primary btn-sm" href="' + base + '/create">' + L.create_label + '</a></p></div>';
            }
            if (isWebsite) {
                return '<div class="list-empty"><p>' + L.empty_nav_types + '</p><p class="muted">' + L.empty_nav_types_hint + '</p><p><a class="btn btn-primary btn-sm" href="' + base + '/create">' + L.create_label + '</a> <a class="btn btn-muted btn-sm" href="/admin/video/websites">' + L.go_websites + '</a></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_types + '</p><p class="muted">' + L.empty_types_hint + '</p><p><a class="btn btn-primary btn-sm" href="' + base + '/create">' + L.create_label + '</a></p></div>';
        },
        onDraw: function (_wrap, list) {
            fillBatchParent();
            var reset = document.getElementById('type-empty-reset');
            if (reset) reset.addEventListener('click', function () { form.reset(); table.reload({}); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_n || '').replace('__N__', String(ids.length));
        },
        cols: (function () {
            var cols = [
                {check: true, width: 36},
                {title: L.noun, html: nameHtml}
            ];
            if (isArt) {
                cols.push({title: L.kind, width: 72, html: function (d) {
                    return d.kind_label ? '<span class="badge">' + U.escape(d.kind_label) + '</span>' : '—';
                }});
            }
            cols.push(
                {key: 'sort', title: L.sort, width: 64},
                {title: L.status, width: 72, html: function (d) {
                    return String(d.status) === '1' ? U.status(true, L.enabled) : U.status(false, L.disabled);
                }},
                {title: L.actions, cls: 'actions', html: function (d) {
                    var id = encodeURIComponent(d.id);
                    var html = '';
                    var kind = String(d.kind || 'list');
                    if (isArt && String(d.status) === '1') {
                        var front = (kind === 'link' && d.jump_url) ? String(d.jump_url) : ('/art/type/' + id);
                        html += '<a href="' + U.escape(front) + '" target="_blank" rel="noopener" class="btn-link">' + L.front + '</a>';
                    }
                    if (isWebsite && String(d.status) === '1') {
                        html += '<a href="/website?type_id=' + id + '" target="_blank" rel="noopener" class="btn-link">' + L.front + '</a>';
                    }
                    html += '<a class="btn-link js-child" href="' + base + '/create?parent_id=' + id + '">' + (isArt ? L.add_child : L.child) + '</a>';
                    html += '<a href="' + contentUrl + '?type_id=' + id + '" class="btn-link">' + L.content_label + '</a>';
                    if (isArt && kind !== 'hub' && kind !== 'link') {
                        html += '<a href="/admin/video/arts/create?type_id=' + id + '" class="btn-link">' + L.write_art + '</a>';
                    }
                    html += '<a href="' + base + '/' + id + '/edit" class="btn-link">' + L.edit + '</a>';
                    html += '<a href="#" class="btn-link js-del">' + L.delete + '</a>';
                    return html;
                }}
            );
            return cols;
        })()
    });

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_noun, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post(base + '/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }

    U.on('#video-type-search-btn', 'click', function () {
        var where = {};
        var name = (form.name.value || '').trim();
        if (name) where.name = name;
        table.reload(where);
    });
    U.on('#video-type-reset-btn', 'click', function () {
        setTimeout(function () { table.reload({}); }, 0);
    });
    U.on('#type-batch-on', 'click', function () { batch('status', 1); });
    U.on('#type-batch-off', 'click', function () { batch('status', 0); });
    U.on('#type-batch-move', 'click', function () {
        var val = document.getElementById('type-batch-parent').value;
        if (val === '') { U.toast(L.please_pick_parent, 'err'); return; }
        batch('parent', val);
    });
    U.on('#type-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_types); });
    U.on('#type-batch-clear', 'click', function () { table.clearSelection(); });

    U.on('#video-type-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a || !a.classList.contains('js-del')) return;
        e.preventDefault();
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        if (!U.confirm(String(L.confirm_del_type || '').replace('__NAME__', row.name || ''))) return;
        U.post(base + '/delete', {id: row.id}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            table.refresh();
            U.toast(L.deleted, 'ok');
        });
    });
})();
</script>
@endpush
