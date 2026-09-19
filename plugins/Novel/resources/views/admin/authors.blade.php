@extends('admin.layouts.inner')
@section('title', $title)

@php
    $ready = (bool) ($ready ?? false);
    $authorJsLang = [
        'authors' => admin_t('ui.authors'),
        'works' => admin_t('ui.works'),
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
        'works_n' => admin_t('ui.works_n', ['n' => '__N__']),
        'empty_authors' => admin_t('ui.empty_authors'),
        'empty_authors_hint' => admin_t('ui.empty_authors_hint'),
        'no_match_authors' => admin_t('ui.no_match_authors'),
        'run_migrate_first' => admin_t('ui.run_migrate_first'),
        'please_fill_author_name' => admin_t('ui.please_fill_author_name'),
        'please_select_authors' => admin_t('ui.please_select_authors'),
        'confirm_batch_del_authors' => admin_t('ui.confirm_batch_del_authors'),
        'confirm_del_author' => admin_t('ui.confirm_del_author', ['name' => '__NAME__']),
        'confirm_del_author_used' => admin_t('ui.confirm_del_author_used', ['name' => '__NAME__', 'n' => '__N__']),
        'add_fail' => admin_t('manga.add_fail'),
    ];
@endphp

@section('plain')
<div class="card card-panel tag-index">
    <div class="card-header">
        <span>{{ admin_t('ui.authors') }} <em id="novel-author-count"></em></span>
        <a class="btn btn-muted btn-sm" href="/admin/video/novel-authors/create">{{ admin_t('ui.full_form') }}</a>
    </div>
    <div class="card-body">
        <div class="tag-compose">
            <form class="tag-compose-form" id="novel-author-compose" onsubmit="return false;">
                <label class="tag-compose-label" for="novel-author-quick">{{ admin_t('ui.add_author') }}</label>
                <div class="tag-compose-row">
                    <input id="novel-author-quick" type="text" name="name" value="" placeholder="{{ admin_t('ui.ph_author_name') }}" aria-label="{{ admin_t('ui.add_author') }}" @if($ready) autofocus @endif>
                    <button class="btn" type="submit" id="novel-author-add">{{ admin_t('ui.add') }}</button>
                </div>
                <p class="muted field-hint">{{ admin_t('ui.tag_compose_lead') }}<a href="/admin/video/novel-authors/create">{{ admin_t('ui.open_full_form') }}</a>{{ admin_t('novel.author_compose_tail') }}</p>
            </form>
        </div>
        <form class="filter-bar" id="novel-author-search" onsubmit="return false;">
            <input type="hidden" name="unused" value="">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_author') }}" autocomplete="off" aria-label="{{ admin_t('ui.authors') }}">
            <button type="button" class="btn btn-sm" id="novel-author-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="novel-author-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="novel-author-queues">
            <button type="button" class="chip" data-unused="">{{ admin_t('ui.all') }}</button>
            <button type="button" class="chip" data-unused="1">{{ admin_t('ui.unused') }}</button>
        </div>
        <p class="muted recycle-lead">{{ admin_t('novel.authors_lead') }}</p>
        <div class="batch-bar" id="novel-author-batch" hidden>
            <strong id="novel-author-batch-count">{{ admin_t('ui.selected_n', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-danger btn-sm" id="novel-author-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="novel-author-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="novel-author-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($authorJsLang);
    var form = document.getElementById('novel-author-search');
    var compose = document.getElementById('novel-author-compose');
    var batchBar = document.getElementById('novel-author-batch');
    var batchCount = document.getElementById('novel-author-batch-count');
    var countEl = document.getElementById('novel-author-count');
    var ready = @json($ready);
    var base = '/admin/video/novel-authors';

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
        U.qa('#novel-author-queues .chip').forEach(function (chip) {
            chip.classList.toggle('active', (chip.getAttribute('data-unused') || '') === unused);
        });
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }

    var table = U.table({
        el: '#novel-author-table',
        url: base + '/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (!ready) {
                return '<div class="list-empty"><p>' + L.run_migrate_first + '</p></div>';
            }
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_authors + '</p><p><button type="button" class="btn btn-muted btn-sm" id="novel-author-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_authors + '</p><p class="muted">' + L.empty_authors_hint + '</p></div>';
        },
        onDraw: function (_wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            var reset = document.getElementById('novel-author-empty-reset');
            if (reset) reset.addEventListener('click', function () { form.reset(); if (form.unused) form.unused.value = ''; runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_n || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.authors, html: function (d) {
                var slug = String(d.slug || '').trim();
                return '<a class="entry-row-title" href="' + base + '/' + encodeURIComponent(d.id) + '/edit">' + U.escape(d.name || L.unnamed) + '</a>'
                    + '<div class="entry-row-meta">/novel?author=' + U.escape(slug || d.name || d.id) + ' · #' + U.escape(d.id) + '</div>';
            }},
            {title: L.works, width: 120, html: function (d) {
                var n = parseInt(d.novel_count, 10) || 0;
                if (n > 0) return '<a href="/admin/video/mangas?author_id=' + encodeURIComponent(d.id) + '">' + String(L.works_n || '').replace('__N__', String(n)) + '</a>';
                return '<span class="muted">' + L.unused + '</span>';
            }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var href = d.url ? String(d.url) : ('/novel?author=' + encodeURIComponent(d.slug || d.name || d.id));
                return '<a href="' + U.escape(href) + '" target="_blank" rel="noopener" class="btn-link">' + L.front + '</a>'
                    + '<a href="' + base + '/' + encodeURIComponent(d.id) + '/edit" class="btn-link">' + L.edit + '</a>'
                    + '<a href="#" class="btn-link js-del">' + L.delete + '</a>';
            }}
        ]
    });
    markChips();

    U.on('#novel-author-search-btn', 'click', runSearch);
    U.on('#novel-author-reset-btn', 'click', function () { setTimeout(function () { if (form.unused) form.unused.value = ''; runSearch(); }, 0); });
    document.getElementById('novel-author-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-unused]');
        if (!chip || !form.unused) return;
        form.unused.value = chip.getAttribute('data-unused') || '';
        runSearch();
    });
    compose.addEventListener('submit', function (e) {
        e.preventDefault();
        var name = String((compose.name && compose.name.value) || '').trim();
        if (!name) { U.toast(L.please_fill_author_name, 'err'); compose.name.focus(); return; }
        U.loading(true);
        U.post(base + '/save', {name: name, status: 1}).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.add_fail, 'err'); return; }
            compose.name.value = '';
            compose.name.focus();
            table.refresh();
            U.toast(L.added, 'ok');
        }).catch(function () { U.loading(false); U.toast(L.add_fail, 'err'); });
    });
    U.on('#novel-author-batch-del', 'click', function () {
        var ids = table.selectedIds();
        if (!ids.length) { U.toast(L.please_select_authors, 'err'); return; }
        if (!U.confirm(L.confirm_batch_del_authors)) return;
        U.post(base + '/batch', {ids: ids.join(','), action: 'delete'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.deleted, 'ok');
        });
    });
    U.on('#novel-author-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#novel-author-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a || a.target === '_blank') return;
        if (a.getAttribute('href') && a.getAttribute('href').indexOf(base + '/') === 0 && !a.classList.contains('js-del')) return;
        if (!a.classList.contains('js-del')) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        var n = parseInt(row.novel_count, 10) || 0;
        var msg = n > 0
            ? String(L.confirm_del_author_used || '').replace('__NAME__', row.name || '').replace('__N__', String(n))
            : String(L.confirm_del_author || '').replace('__NAME__', row.name || '');
        if (!U.confirm(msg)) return;
        U.post(base + '/delete', {id: row.id}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.deleted, 'ok');
        });
    });
})();
</script>
@endpush
