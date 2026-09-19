@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'today' => 0, 'missing' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $memberId = (int) ($memberId ?? 0);
    $videoId = (int) ($videoId ?? 0);
    $memberName = (string) ($memberName ?? '');
    $videoTitle = (string) ($videoTitle ?? '');
    $favJsLang = [
        'actions' => admin_t('ui.actions'),
        'fail' => admin_t('ui.fail'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'selected_rows' => admin_t('ui.selected_rows', ['n' => '__N__']),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'members' => admin_t('ui.members'),
        'videos' => admin_t('ui.videos'),
        'front' => admin_t('ui.front'),
        'col_video' => admin_t('ui.col_video'),
        'col_member' => admin_t('ui.col_member'),
        'favor_time' => admin_t('ui.favor_time'),
        'film_deleted' => admin_t('ui.film_deleted'),
        'film_deleted_id' => admin_t('ui.film_deleted_id', ['id' => '__ID__']),
        'member_deleted' => admin_t('ui.member_deleted'),
        'member_deleted_id' => admin_t('ui.member_deleted_id', ['id' => '__ID__']),
        'member_hash' => admin_t('ui.member_hash', ['id' => '__ID__']),
        'video_hash' => admin_t('ui.video_hash', ['id' => '__ID__']),
        'unfavor' => admin_t('ui.unfavor'),
        'empty_favorites' => admin_t('ui.empty_favorites'),
        'empty_favorites_hint' => admin_t('ui.empty_favorites_hint'),
        'no_match_favorites' => admin_t('ui.no_match_favorites'),
        'please_select_favorites' => admin_t('ui.please_select_favorites'),
        'confirm_batch_unfavor' => admin_t('ui.confirm_batch_unfavor', ['n' => '__N__']),
        'confirm_unfavor' => admin_t('ui.confirm_unfavor', ['name' => '__NAME__']),
        'unfavor_ok' => admin_t('ui.unfavor_ok'),
        'canceled' => admin_t('ui.canceled'),
        'favorites_lead' => admin_t('ui.favorites_lead'),
    ];
@endphp

@section('plain')
<div class="card card-panel fav-index">
    <div class="card-header">
        <span>{{ admin_t('ui.favorites') }} <em id="fav-count"></em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/members">{{ admin_t('ui.members') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video">{{ admin_t('ui.videos') }}</a>
            <a class="btn btn-muted btn-sm" href="/member/favorites" target="_blank" rel="noopener">{{ admin_t('ui.front_favorites') }}</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="fav-search" onsubmit="return false;">
            <input type="hidden" name="today">
            <input type="hidden" name="missing">
            <input type="hidden" name="member_id" value="{{ $memberId > 0 ? $memberId : '' }}">
            <input type="hidden" name="video_id" value="{{ $videoId > 0 ? $videoId : '' }}">
            <input type="search" name="q" placeholder="{{ admin_t('ui.ph_favorite') }}" autocomplete="off" aria-label="{{ admin_t('ui.aria_search_favorites') }}">
            <button type="button" class="btn btn-sm" id="fav-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="fav-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="fav-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="today" data-value="1">{{ admin_t('ui.today') }}@if($q('today') > 0)<em>{{ $q('today') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="missing" data-value="1">{{ admin_t('ui.film_deleted') }}@if($q('missing') > 0)<em>{{ $q('missing') }}</em>@endif</button>
        </div>
        @if($memberId > 0 || $videoId > 0)
            <p class="muted recycle-lead" id="fav-focus">
                @if($memberId > 0)
                    {{ admin_t('ui.fav_focus_member', ['name' => $memberName !== '' ? $memberName : ('#'.$memberId)]) }}
                @endif
                @if($videoId > 0)
                    {{ admin_t('ui.fav_focus_video', ['name' => $videoTitle !== '' ? $videoTitle : ('#'.$videoId)]) }}
                @endif
                <button type="button" class="btn-link" id="fav-clear-focus">{{ admin_t('ui.view_all') }}</button>
            </p>
        @else
            <p class="muted recycle-lead">{{ admin_t('ui.favorites_lead') }}</p>
        @endif
        <div class="batch-bar" id="fav-batch" hidden>
            <strong id="fav-batch-count">{{ admin_t('ui.selected_rows', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-danger btn-sm" id="fav-batch-del">{{ admin_t('ui.unfavor') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="fav-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="fav-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($favJsLang);
    var form = document.getElementById('fav-search');
    var batchBar = document.getElementById('fav-batch');
    var batchCount = document.getElementById('fav-batch-count');
    var countEl = document.getElementById('fav-count');
    var QUEUE_KEYS = ['today', 'missing'];

    function cleanWhere(data) {
        var out = {};
        Object.keys(data || {}).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function queryWhere() {
        return Object.assign({limit: 20}, cleanWhere(U.formData(form)));
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return k !== 'limit' && where[k] !== ''; });
    }
    function markChips() {
        var today = form.today.value;
        var missing = form.missing.value;
        U.qa('#fav-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && today === '' && missing === '') on = true;
            else if (key === 'today' && missing === '' && today === val) on = true;
            else if (key === 'missing' && today === '' && missing === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        if (key && form[key]) form[key].value = value || '1';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function filmHtml(d) {
        var title = d.video_title || '';
        if (parseInt(d.video_missing, 10) === 1 || !title) {
            return '<span class="muted">' + U.escape(d.video_id
                ? String(L.film_deleted_id || '').replace('__ID__', String(d.video_id))
                : L.film_deleted) + '</span>';
        }
        return '<a href="/vod/' + encodeURIComponent(d.video_id) + '" target="_blank" rel="noopener">' + U.escape(title) + '</a>';
    }
    function memberHtml(d) {
        var name = d.member_name || '';
        if (parseInt(d.member_missing, 10) === 1 || (!name && !d.member_id)) {
            return '<span class="muted">' + U.escape(d.member_id
                ? String(L.member_deleted_id || '').replace('__ID__', String(d.member_id))
                : L.member_deleted) + '</span>';
        }
        var label = name || String(L.member_hash || '').replace('__ID__', String(d.member_id));
        var html = '<a href="/admin/video/members?q=' + encodeURIComponent(d.member_id) + '">' + U.escape(label) + '</a>';
        if (d.member_email) html += '<div class="muted">' + U.escape(d.member_email) + '</div>';
        return html;
    }

    var table = U.table({
        el: '#fav-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/favorites/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_favorites + '</p><p><button type="button" class="btn btn-muted btn-sm" id="fav-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_favorites + '</p><p class="muted">' + U.escape(L.empty_favorites_hint) + '</p></div>';
        },
        onDraw: function (_wrap, list) {
            var reset = document.getElementById('fav-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
                if (form.member_id) form.member_id.value = '';
                if (form.video_id) form.video_id.value = '';
                runSearch();
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_rows || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.col_video, html: filmHtml},
            {title: L.col_member, html: memberHtml},
            {title: L.favor_time, width: 150, html: function (d) { return U.escape(d.created_at_text || ''); }},
            {title: L.actions, cls: 'actions', html: function (d) {
                var html = '';
                if (d.video_id && parseInt(d.video_missing, 10) !== 1) {
                    html += '<a class="btn-link" href="/vod/' + encodeURIComponent(d.video_id) + '" target="_blank" rel="noopener">' + L.front + '</a>';
                    html += '<a class="btn-link" href="/admin/video?q=' + encodeURIComponent(d.video_id) + '">' + L.videos + '</a>';
                }
                if (d.member_id && parseInt(d.member_missing, 10) !== 1) {
                    html += '<a class="btn-link" href="/admin/video/members?q=' + encodeURIComponent(d.member_id) + '">' + L.members + '</a>';
                }
                html += '<a href="#" class="btn-link js-del">' + L.unfavor + '</a>';
                return html;
            }}
        ]
    });
    markChips();

    function selectedIds() { return table.selectedIds(); }
    function batchDel() {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_favorites, 'err'); return; }
        if (!U.confirm(String(L.confirm_batch_unfavor || '').replace('__N__', String(ids.length)))) return;
        U.post('/admin/video/favorites/batch', {ids: ids.join(','), action: 'delete'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.canceled, 'ok');
        });
    }

    U.on('#fav-search-btn', 'click', runSearch);
    U.on('#fav-reset-btn', 'click', function () { setTimeout(function () {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        runSearch();
    }, 0); });
    U.on('#fav-clear-focus', 'click', function () {
        if (form.member_id) form.member_id.value = '';
        if (form.video_id) form.video_id.value = '';
        if (history.replaceState) history.replaceState({}, '', '/admin/video/favorites');
        var lead = document.getElementById('fav-focus');
        if (lead) lead.textContent = L.favorites_lead;
        runSearch();
    });
    document.getElementById('fav-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#fav-batch-del', 'click', batchDel);
    U.on('#fav-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#fav-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var href = a.getAttribute('href') || '';
        if (href && href !== '#' && href.indexOf('javascript:') !== 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-del')) {
            var label = row.video_title || String(L.video_hash || '').replace('__ID__', String(row.video_id || ''));
            if (!U.confirm(String(L.confirm_unfavor || '').replace('__NAME__', label))) return;
            U.post('/admin/video/favorites/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.unfavor_ok, 'ok');
            });
        }
    });
})();
</script>
@endpush
