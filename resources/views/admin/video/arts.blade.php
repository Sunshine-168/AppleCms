@extends('admin.layouts.inner')
@section('title', $title)

@php
    $types = $types ?? [];
    $tags = $tags ?? [];
    $queues = $queues ?? ['all' => 0, 'published' => 0, 'draft' => 0, 'pending' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $filterType = (string) request()->query('type_id', '');
    $filterTagId = (string) request()->query('tag_id', '');
    $filterTag = $filterTag ?? null;
    $looseCount = (int) ($looseCount ?? 0);
    $recycleCount = (int) ($recycleCount ?? 0);
    $writeHref = ($filterType !== '' && $filterType !== '0')
        ? '/admin/video/arts/create?type_id='.(int) $filterType
        : '/admin/video/arts/create';
    $artJsLang = [
        'status' => admin_t('ui.status'),
        'actions' => admin_t('ui.actions'),
        'edit' => admin_t('ui.edit'),
        'delete' => admin_t('ui.delete'),
        'fail' => admin_t('ui.fail'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'front' => admin_t('ui.front'),
        'draft' => admin_t('ui.draft'),
        'scheduled' => admin_t('ui.scheduled'),
        'loose_column' => admin_t('ui.loose_column'),
        'write_art' => admin_t('ui.write_art'),
        'title_label' => admin_t('ui.title_label'),
        'time' => admin_t('ui.time'),
        'unnamed' => admin_t('ui.unnamed'),
        'today_at' => admin_t('ui.today_at', ['time' => '__TIME__']),
        'yesterday_at' => admin_t('ui.yesterday_at', ['time' => '__TIME__']),
        'hits_n' => admin_t('ui.hits_n', ['n' => '__N__']),
        'write_to_column' => admin_t('ui.write_to_column'),
        'empty_arts' => admin_t('ui.empty_arts'),
        'empty_arts_hint' => admin_t('ui.empty_arts_hint'),
        'go_create_columns' => admin_t('ui.go_create_columns'),
        'no_match_content' => admin_t('ui.no_match_content'),
        'selected_arts' => admin_t('ui.selected_arts', ['n' => '__N__']),
        'please_select_arts' => admin_t('ui.please_select_arts'),
        'please_pick_column' => admin_t('ui.please_pick_column'),
        'confirm_batch_publish_arts' => admin_t('ui.confirm_batch_publish_arts'),
        'confirm_batch_draft_arts' => admin_t('ui.confirm_batch_draft_arts'),
        'confirm_batch_rec_arts' => admin_t('ui.confirm_batch_rec_arts'),
        'confirm_batch_unrec_arts' => admin_t('ui.confirm_batch_unrec_arts'),
        'confirm_batch_move_arts' => admin_t('ui.confirm_batch_move_arts'),
        'confirm_batch_copy_arts' => admin_t('ui.confirm_batch_copy_arts'),
        'confirm_batch_del_arts' => admin_t('ui.confirm_batch_del_arts'),
        'confirm_publish_art' => admin_t('ui.confirm_publish_art', ['name' => '__NAME__']),
        'confirm_copy_art' => admin_t('ui.confirm_copy_art', ['name' => '__NAME__']),
        'confirm_del_art' => admin_t('ui.confirm_del_art', ['name' => '__NAME__']),
        'published_ok' => admin_t('ui.published_ok'),
        'copied_as_draft' => admin_t('ui.copied_as_draft'),
        'moved_to_recycle' => admin_t('ui.moved_to_recycle'),
        'publish' => admin_t('ui.publish'),
        'copy_one' => admin_t('ui.copy_one'),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
    ];
@endphp

@section('plain')
<div class="art-workbench">
<aside class="art-cat-rail" id="art-cat-rail">
    <div class="art-cat-rail-head">
        <span>{{ admin_t('ui.column') }}</span>
        <a href="/admin/video/art-types">{{ admin_t('ui.manage') }}</a>
    </div>
    <a class="art-cat-item" data-type="" href="/admin/video/arts">{{ admin_t('ui.all') }}<em>{{ $q('all') }}</em></a>
    <a class="art-cat-item" data-type="0" href="/admin/video/arts?type_id=0">{{ admin_t('ui.loose_column') }}<em>{{ $looseCount }}</em></a>
    @forelse($types as $type)
        <a class="art-cat-item" data-type="{{ $type['id'] }}" href="/admin/video/arts?type_id={{ $type['id'] }}" style="padding-left: {{ 10 + (int) ($type['depth'] ?? 0) * 14 }}px">
            <span>{{ $type['title'] ?? $type['name'] }}@if(!empty($type['kind_label']) && ($type['kind'] ?? 'list') !== 'list') <small class="muted">{{ $type['kind_label'] }}</small>@endif</span>
            <em>{{ (int) ($type['art_count'] ?? 0) }}</em>
        </a>
    @empty
        <p class="muted art-cat-empty">{{ admin_t('ui.empty_columns') }} <a href="/admin/video/art-types/create">{{ admin_t('ui.add_column') }}</a></p>
    @endforelse
</aside>
<div class="art-workbench-main">
<div class="card card-panel art-index">
    <div class="card-header">
        <span>{{ admin_t('ui.articles') }} <em id="art-count"></em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/art-recycle">{{ admin_t('ui.recycle') }}@if($recycleCount > 0) {{ $recycleCount }}@endif</a>
            <a class="btn btn-sm" id="art-write" href="{{ $writeHref }}">{{ admin_t('ui.write_art') }}</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="art-search" onsubmit="return false;">
            <input type="hidden" name="queue" value="">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_art') }}" autocomplete="off" aria-label="{{ admin_t('ui.articles') }}">
            <select name="type_id" aria-label="{{ admin_t('ui.column') }}">
                <option value="">{{ admin_t('ui.all_columns') }}</option>
                <option value="0" @selected($filterType === '0')>{{ admin_t('ui.loose_column') }}</option>
                @foreach($types as $type)
                    <option value="{{ $type['id'] }}" @selected($filterType === (string) $type['id'])>{{ $type['name'] }}</option>
                @endforeach
            </select>
            <select name="status">
                <option value="">{{ admin_t('ui.status') }}</option>
                <option value="1">{{ admin_t('ui.published') }}</option>
                <option value="0">{{ admin_t('ui.draft') }}</option>
            </select>
            <select name="flag" aria-label="{{ admin_t('ui.flag_attrs') }}">
                <option value="">{{ admin_t('ui.flag_attrs') }}</option>
                <option value="top">{{ admin_t('ui.flag_top') }}</option>
                <option value="recommend">{{ admin_t('ui.recommend') }}</option>
                <option value="hot">{{ admin_t('ui.flag_hot') }}</option>
            </select>
            @if($tags !== [])
                <select name="tag_id" aria-label="{{ admin_t('ui.tags') }}">
                    <option value="">{{ admin_t('ui.all_tags') }}</option>
                    @foreach($tags as $tag)
                        <option value="{{ $tag['id'] }}" @selected($filterTagId === (string) $tag['id'])>{{ $tag['name'] }}</option>
                    @endforeach
                </select>
            @endif
            <button type="button" class="btn btn-sm" id="art-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="art-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="art-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="published">{{ admin_t('ui.published') }}@if($q('published') > 0)<em>{{ $q('published') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="draft">{{ admin_t('ui.draft') }}@if($q('draft') > 0)<em>{{ $q('draft') }}</em>@endif</button>
            @if($q('pending') > 0)
                <button type="button" class="chip" data-queue="pending">{{ admin_t('ui.scheduled') }}<em>{{ $q('pending') }}</em></button>
            @endif
            @if($filterTag)
                <a class="chip active" href="/admin/video/arts">{{ admin_t('ui.tag_chip', ['name' => $filterTag['name']]) }}</a>
            @endif
        </div>
        <p class="muted recycle-lead">{{ admin_t('ui.arts_lead') }}</p>
        <div class="batch-bar" id="art-batch" hidden>
            <strong id="art-batch-count">{{ admin_t('ui.selected_arts', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="art-batch-on">{{ admin_t('ui.publish_front') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="art-batch-off">{{ admin_t('ui.to_draft') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="art-batch-rec">{{ admin_t('ui.set_recommend') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="art-batch-unrec">{{ admin_t('ui.unset_recommend') }}</button>
            <select id="art-batch-type" class="batch-select" aria-label="{{ admin_t('ui.pick_column') }}">
                <option value="">{{ admin_t('ui.pick_column') }}</option>
                <option value="0">{{ admin_t('ui.loose_column') }}</option>
                @foreach($types as $type)
                    <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-muted btn-sm" id="art-batch-move">{{ admin_t('ui.move_there') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="art-batch-copy">{{ admin_t('ui.copy_one') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="art-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="art-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="art-table"></div>
    </div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($artJsLang);
    var form = document.getElementById('art-search');
    var batchBar = document.getElementById('art-batch');
    var batchCount = document.getElementById('art-batch-count');
    var countEl = document.getElementById('art-count');
    var hasTypes = @json(count($types) > 0);

    function cleanWhere(data) {
        var out = {};
        Object.keys(data).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function queryWhere() {
        return Object.assign({limit: 15}, cleanWhere(U.formData(form)));
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return k !== 'limit' && where[k] !== ''; });
    }
    function markChips() {
        var queue = form.queue ? form.queue.value : '';
        U.qa('#art-queues .chip').forEach(function (chip) {
            var val = chip.getAttribute('data-queue') || '';
            chip.classList.toggle('active', val === queue);
        });
    }
    function applyQueue(value) {
        if (form.queue) form.queue.value = value || '';
        form.status.value = '';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
        syncRail();
    }
    function syncRail() {
        var val = form.type_id ? String(form.type_id.value || '') : '';
        U.qa('#art-cat-rail [data-type]').forEach(function (el) {
            el.classList.toggle('is-on', (el.getAttribute('data-type') || '') === val);
        });
        var write = document.getElementById('art-write');
        if (write) {
            write.href = (val && val !== '0')
                ? '/admin/video/arts/create?type_id=' + encodeURIComponent(val)
                : '/admin/video/arts/create';
        }
        var url = '/admin/video/arts';
        var qs = [];
        if (val !== '') qs.push('type_id=' + encodeURIComponent(val));
        var tag = form.tag_id ? String(form.tag_id.value || '') : '';
        if (tag !== '') qs.push('tag_id=' + encodeURIComponent(tag));
        if (qs.length) url += '?' + qs.join('&');
        if (history.replaceState) history.replaceState(null, '', url);
    }
    function fmtTime(ts) {
        ts = parseInt(ts, 10) || 0;
        if (!ts) return '—';
        var d = new Date(ts * 1000);
        var now = new Date();
        var pad = function (n) { return n < 10 ? '0' + n : '' + n; };
        var hm = pad(d.getHours()) + ':' + pad(d.getMinutes());
        if (d.toDateString() === now.toDateString()) return String(L.today_at || '').replace('__TIME__', hm);
        var y = new Date(now);
        y.setDate(now.getDate() - 1);
        if (d.toDateString() === y.toDateString()) return String(L.yesterday_at || '').replace('__TIME__', hm);
        if (d.getFullYear() === now.getFullYear()) return pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + hm;
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    }
    function flagBadges(d) {
        var label = String(d.flags_label || '').trim();
        if (!label) return '';
        return label.split('/').map(function (s) {
            s = String(s || '').trim();
            return s ? ' <span class="badge badge-ok">' + U.escape(s) + '</span>' : '';
        }).join('');
    }
    function titleHtml(d) {
        var cover = String(d.cover || '').trim();
        var thumb = cover
            ? '<img class="vod-thumb" src="' + U.escape(cover) + '" alt="">'
            : '';
        var listed = d.listed === true || d.listed === 1 || d.listed === '1';
        var badge = '';
        if (String(d.status) !== '1') {
            badge = '<span class="badge badge-off">' + L.draft + '</span>';
        } else if (!listed) {
            badge = '<span class="badge badge-warn">' + L.scheduled + '</span>';
        }
        var meta = (d.type_name ? U.escape(d.type_name) : L.loose_column) + ' · #' + U.escape(d.id);
        var hits = parseInt(d.hits, 10) || 0;
        if (hits > 0) meta += ' · ' + String(L.hits_n || '').replace('__N__', U.escape(String(hits)));
        if (d.tag_label) meta += ' · ' + U.escape(d.tag_label);
        return '<div class="vod-cell">' + thumb + '<div class="entry-row-title-line"><a class="entry-row-title" href="/admin/video/arts/' + encodeURIComponent(d.id) + '/edit">' + U.escape(d.title || L.unnamed) + '</a> ' + badge + flagBadges(d) + '</div>'
            + '<div class="entry-row-meta">' + meta + '</div></div>';
    }

    var table = U.table({
        el: '#art-table',
        countEl: countEl,
        url: '/admin/video/arts/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                var tid = String(where.type_id || '');
                var write = (tid && tid !== '0')
                    ? '<a class="btn btn-primary btn-sm" href="/admin/video/arts/create?type_id=' + encodeURIComponent(tid) + '">' + L.write_to_column + '</a>'
                    : '<a class="btn btn-primary btn-sm" href="/admin/video/arts/create">' + L.write_art + '</a>';
                return '<div class="list-empty"><p>' + L.no_match_content + '</p><p><button type="button" class="btn btn-muted btn-sm" id="art-empty-reset">' + L.clear_filter + '</button> ' + write + '</p></div>';
            }
            if (!hasTypes) {
                return '<div class="list-empty"><p>' + L.empty_arts + '</p><p class="muted">' + L.empty_arts_hint + '</p><p><a class="btn btn-muted btn-sm" href="/admin/video/art-types">' + L.go_create_columns + '</a> <a class="btn btn-primary btn-sm" href="/admin/video/arts/create">' + L.write_art + '</a></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_arts + '</p><p><a class="btn btn-primary btn-sm" href="/admin/video/arts/create">' + L.write_art + '</a></p></div>';
        },
        onDraw: function (_wrap, list) {
            var reset = document.getElementById('art-empty-reset');
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_arts || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.title_label, html: titleHtml},
            {title: L.time, width: 120, html: function (d) {
                return '<span class="muted">' + U.escape(fmtTime(d.updated_at_unix || d.updated_at || d.created_at)) + '</span>';
            }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var href = d.url ? String(d.url) : ('/art/' + encodeURIComponent(d.id));
                var listed = d.listed === true || d.listed === 1 || d.listed === '1';
                var html = '';
                if (listed) {
                    html += '<a href="' + U.escape(href) + '" target="_blank" rel="noopener" class="btn-link">' + L.front + '</a>';
                } else if (String(d.status) !== '1') {
                    html += '<a href="#" class="btn-link js-pub">' + L.publish + '</a>';
                }
                html += '<a href="/admin/video/arts/' + encodeURIComponent(d.id) + '/edit" class="btn-link">' + L.edit + '</a>';
                html += '<a href="#" class="btn-link js-copy">' + L.copy_one + '</a>';
                html += '<a href="#" class="btn-link js-del">' + L.delete + '</a>';
                return html;
            }}
        ]
    });
    markChips();
    syncRail();

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_arts, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/arts/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }

    U.on('#art-search-btn', 'click', runSearch);
    U.on('#art-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    if (form.status) {
        form.status.addEventListener('change', function () {
            if (form.queue) form.queue.value = '';
        });
    }
    if (form.type_id) {
        form.type_id.addEventListener('change', runSearch);
    }
    if (form.tag_id) {
        form.tag_id.addEventListener('change', runSearch);
    }
    var rail = document.getElementById('art-cat-rail');
    if (rail) {
        rail.addEventListener('click', function (e) {
            var a = e.target.closest('[data-type]');
            if (!a || !form.type_id) return;
            e.preventDefault();
            form.type_id.value = a.getAttribute('data-type') || '';
            runSearch();
        });
    }
    document.getElementById('art-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '');
    });
    U.on('#art-batch-on', 'click', function () { batch('status', 1, L.confirm_batch_publish_arts); });
    U.on('#art-batch-off', 'click', function () { batch('status', 0, L.confirm_batch_draft_arts); });
    U.on('#art-batch-rec', 'click', function () { batch('flag_recommend', '', L.confirm_batch_rec_arts); });
    U.on('#art-batch-unrec', 'click', function () { batch('unflag_recommend', '', L.confirm_batch_unrec_arts); });
    U.on('#art-batch-move', 'click', function () {
        var val = document.getElementById('art-batch-type').value;
        if (!val) { U.toast(L.please_pick_column, 'err'); return; }
        batch('type', val, L.confirm_batch_move_arts);
    });
    U.on('#art-batch-copy', 'click', function () { batch('copy', '', L.confirm_batch_copy_arts); });
    U.on('#art-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_arts); });
    U.on('#art-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#art-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank') return;
        if (a.getAttribute('href') && a.getAttribute('href').indexOf('/admin/video/arts/') === 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-pub')) {
            if (!U.confirm(String(L.confirm_publish_art || '').replace('__NAME__', row.title || ''))) return;
            U.post('/admin/video/arts/save', {id: row.id, status: 1}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.published_ok, 'ok');
            });
        }
        if (a.classList.contains('js-copy')) {
            if (!U.confirm(String(L.confirm_copy_art || '').replace('__NAME__', row.title || ''))) return;
            U.post('/admin/video/arts/batch', {ids: String(row.id), action: 'copy'}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast((res && res.msg) || L.copied_as_draft, 'ok');
            });
        }
        if (a.classList.contains('js-del')) {
            if (!U.confirm(String(L.confirm_del_art || '').replace('__NAME__', row.title || ''))) return;
            U.post('/admin/video/arts/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast((res && res.msg) || L.moved_to_recycle, 'ok');
            });
        }
    });
})();
</script>
@endpush
