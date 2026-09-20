@extends('admin.layouts.inner')
@section('title', $title)

@php
    $ready = (bool) ($ready ?? false);
    $artTagJsLang = [
        'tags' => admin_t('ui.tags'),
        'articles' => admin_t('ui.articles'),
        'actions' => admin_t('ui.actions'),
        'edit' => admin_t('ui.edit'),
        'delete' => admin_t('ui.delete'),
        'front' => admin_t('ui.front'),
        'fail' => admin_t('ui.fail'),
        'deleted' => admin_t('ui.deleted'),
        'added' => admin_t('ui.added'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'unused' => admin_t('ui.unused'),
        'unnamed' => admin_t('ui.unnamed'),
        'selected_n' => admin_t('ui.selected_n', ['n' => '__N__']),
        'topic_arts_n' => admin_t('ui.topic_arts_n', ['n' => '__N__']),
        'empty_art_tags' => admin_t('ui.empty_art_tags'),
        'empty_art_tags_hint' => admin_t('ui.empty_art_tags_hint'),
        'no_match_tags' => admin_t('ui.no_match_tags'),
        'go_arts' => admin_t('ui.go_arts'),
        'run_migrate_first' => admin_t('ui.run_migrate_first'),
        'please_fill_tag_name' => admin_t('ui.please_fill_tag_name'),
        'please_select_tags' => admin_t('ui.please_select_tags'),
        'confirm_batch_del_art_tags' => admin_t('ui.confirm_batch_del_art_tags'),
        'confirm_del_art_tag' => admin_t('ui.confirm_del_art_tag', ['name' => '__NAME__']),
        'confirm_del_art_tag_used' => admin_t('ui.confirm_del_art_tag_used', ['name' => '__NAME__', 'n' => '__N__']),
        'add_fail' => admin_t('manga.add_fail'),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
    ];
@endphp

@section('plain')
<div class="card card-panel tag-index list-desk">
    <div class="card-header">
        <span>{{ admin_t('ui.tags') }} <em id="art-tag-count"></em></span>
        <a class="btn btn-muted btn-sm" href="/admin/video/art-tags/create">{{ admin_t('ui.full_form') }}</a>
    </div>
    <div class="card-body">
        <div class="tag-compose">
            <form class="tag-compose-form" id="art-tag-compose" onsubmit="return false;">
                <label class="tag-compose-label" for="art-tag-quick">{{ admin_t('ui.add_tag') }}</label>
                <div class="tag-compose-row">
                    <input id="art-tag-quick" type="text" name="name" value="" placeholder="{{ admin_t('ui.ph_art_tag_name') }}" aria-label="{{ admin_t('ui.add_tag') }}">
                    <button class="btn" type="submit" id="art-tag-add">{{ admin_t('ui.add') }}</button>
                </div>
                <p class="muted field-hint">{{ admin_t('ui.art_tag_compose_lead') }}<a href="/admin/video/art-tags/create">{{ admin_t('ui.open_full_form') }}</a>{{ admin_t('ui.art_tag_compose_tail') }}</p>
            </form>
        </div>
        <form class="filter-bar" id="art-tag-search" onsubmit="return false;">
            <input type="hidden" name="unused" value="">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_art_tag') }}" autocomplete="off" aria-label="{{ admin_t('ui.tags') }}">
            <button type="button" class="btn btn-sm" id="art-tag-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="art-tag-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="art-tag-queues">
            <button type="button" class="chip" data-unused="">{{ admin_t('ui.all') }}</button>
            <button type="button" class="chip" data-unused="1">{{ admin_t('ui.unused') }}</button>
        </div>
        <p class="muted recycle-lead">{{ admin_t('ui.art_tags_lead') }}</p>
        <div class="batch-bar" id="art-tag-batch" hidden>
            <strong id="art-tag-batch-count">{{ admin_t('ui.selected_n', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-danger btn-sm" id="art-tag-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="art-tag-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="art-tag-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($artTagJsLang, JSON_UNESCAPED_UNICODE);
    var form = document.getElementById('art-tag-search');
    var compose = document.getElementById('art-tag-compose');
    var batchBar = document.getElementById('art-tag-batch');
    var batchCount = document.getElementById('art-tag-batch-count');
    var countEl = document.getElementById('art-tag-count');
    var ready = @json($ready, JSON_UNESCAPED_UNICODE);

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
        var unused = form.unused ? form.unused.value : '';
        U.qa('#art-tag-queues .chip').forEach(function (chip) {
            chip.classList.toggle('active', (chip.getAttribute('data-unused') || '') === unused);
        });
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }

    var table = U.table({
        el: '#art-tag-table',
        countEl: countEl,
        url: '/admin/video/art-tags/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (!ready) {
                return '<div class="list-empty"><p>' + L.run_migrate_first + '</p></div>';
            }
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_tags + '</p><p><button type="button" class="btn btn-muted btn-sm" id="art-tag-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_art_tags + '</p><p class="muted">' + L.empty_art_tags_hint + '</p><p><a class="btn btn-muted btn-sm" href="/admin/video/arts">' + L.go_arts + '</a></p></div>';
        },
        onDraw: function (_wrap, list) {
            var reset = document.getElementById('art-tag-empty-reset');
            if (reset) reset.addEventListener('click', function () { form.reset(); if (form.unused) form.unused.value = ''; runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_n || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.tags, html: function (d) {
                var slug = String(d.slug || '').trim();
                return '<a class="entry-row-title" href="/admin/video/art-tags/' + encodeURIComponent(d.id) + '/edit">' + U.escape(d.name || L.unnamed) + '</a>'
                    + '<div class="entry-row-meta">/art/tag/' + U.escape(slug || d.id) + ' · #' + U.escape(d.id) + '</div>';
            }},
            {title: L.articles, width: 120, html: function (d) {
                var n = parseInt(d.art_count, 10) || 0;
                if (n > 0) return '<a href="/admin/video/arts?tag_id=' + encodeURIComponent(d.id) + '">' + String(L.topic_arts_n || '').replace('__N__', String(n)) + '</a>';
                return '<span class="muted">' + L.unused + '</span>';
            }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var href = d.url ? String(d.url) : ('/art/tag/' + encodeURIComponent(d.slug || d.id));
                return '<a href="' + U.escape(href) + '" target="_blank" rel="noopener" class="btn-link">' + L.front + '</a>'
                    + '<a href="/admin/video/art-tags/' + encodeURIComponent(d.id) + '/edit" class="btn-link">' + L.edit + '</a>'
                    + '<a href="#" class="btn-link js-del">' + L.delete + '</a>';
            }}
        ]
    });
    markChips();

    U.on('#art-tag-search-btn', 'click', runSearch);
    U.on('#art-tag-reset-btn', 'click', function () { setTimeout(function () { if (form.unused) form.unused.value = ''; runSearch(); }, 0); });
    document.getElementById('art-tag-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-unused]');
        if (!chip || !form.unused) return;
        form.unused.value = chip.getAttribute('data-unused') || '';
        runSearch();
    });
    compose.addEventListener('submit', function (e) {
        e.preventDefault();
        var name = String((compose.name && compose.name.value) || '').trim();
        if (!name) { U.toast(L.please_fill_tag_name, 'err'); compose.name.focus(); return; }
        U.loading(true);
        U.post('/admin/video/art-tags/save', {name: name, status: 1}).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.add_fail, 'err'); return; }
            compose.name.value = '';
            compose.name.focus();
            table.refresh();
            U.toast(L.added, 'ok');
        }).catch(function () { U.loading(false); U.toast(L.add_fail, 'err'); });
    });
    U.on('#art-tag-batch-del', 'click', function () {
        var ids = table.selectedIds();
        if (!ids.length) { U.toast(L.please_select_tags, 'err'); return; }
        if (!U.confirm(L.confirm_batch_del_art_tags)) return;
        U.post('/admin/video/art-tags/batch', {ids: ids.join(','), action: 'delete'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.deleted, 'ok');
        });
    });
    U.on('#art-tag-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#art-tag-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a || a.target === '_blank') return;
        if (a.getAttribute('href') && a.getAttribute('href').indexOf('/admin/video/art-tags/') === 0 && !a.classList.contains('js-del')) return;
        if (!a.classList.contains('js-del')) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        var n = parseInt(row.art_count, 10) || 0;
        var msg = n > 0
            ? String(L.confirm_del_art_tag_used || '').replace('__NAME__', row.name || '').replace('__N__', String(n))
            : String(L.confirm_del_art_tag || '').replace('__NAME__', row.name || '');
        if (!U.confirm(msg)) return;
        U.post('/admin/video/art-tags/delete', {id: row.id}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.deleted, 'ok');
        });
    });
})();
</script>
@endpush
