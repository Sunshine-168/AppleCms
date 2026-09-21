@extends('admin.layouts.inner')
@section('title', admin_t('page.videos'))

@php
    $queues = $queues ?? ['all' => 0, 'pending' => 0, 'empty_url' => 0, 'empty_pic' => 0, 'repeat' => 0, 'recycle' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $videoJsLang = [
        'types' => admin_t('ui.types'),
        'move_to_type' => admin_t('ui.move_to_type'),
        'disabled_suffix' => admin_t('ui.group_disabled_suffix'),
        'on' => admin_t('ui.on'),
        'off' => admin_t('ui.off'),
        'draft' => admin_t('ui.draft'),
        'rejected' => admin_t('ui.rejected'),
        'scheduled' => admin_t('ui.scheduled'),
        'no_pic' => admin_t('ui.no_pic'),
        'no_cover' => admin_t('ui.no_cover'),
        'no_url' => admin_t('ui.no_url'),
        'badge_rec' => admin_t('ui.badge_rec'),
        'badge_hot' => admin_t('ui.badge_hot'),
        'badge_lock' => admin_t('ui.badge_lock'),
        'uncategorized' => admin_t('ui.uncategorized'),
        'hits_n' => admin_t('ui.hits_n', ['n' => '__N__']),
        'videos' => admin_t('ui.videos'),
        'status' => admin_t('ui.status'),
        'col_points' => admin_t('ui.col_points'),
        'col_updated' => admin_t('ui.col_updated'),
        'actions' => admin_t('ui.actions'),
        'edit' => admin_t('ui.edit'),
        'lines' => admin_t('ui.lines'),
        'episodes' => admin_t('ui.episodes'),
        'delete' => admin_t('ui.delete'),
        'deleted' => admin_t('ui.deleted'),
        'fail' => admin_t('ui.fail'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'empty_videos' => admin_t('ui.empty_videos'),
        'empty_videos_hint' => admin_t('ui.empty_videos_hint'),
        'no_match_videos' => admin_t('ui.no_match_videos'),
        'no_match_videos_hint' => admin_t('ui.no_match_videos_hint'),
        'go_collect' => admin_t('ui.go_collect'),
        'add_video' => admin_t('ui.add_video'),
        'selected_videos' => admin_t('ui.selected_videos', ['n' => '__N__']),
        'please_select_videos' => admin_t('ui.please_select_videos'),
        'please_pick_type' => admin_t('ui.please_pick_type'),
        'please_select_two_videos' => admin_t('ui.please_select_two_videos'),
        'change_points' => admin_t('ui.change_points'),
        'ph_keep_video_id' => admin_t('ui.ph_keep_video_id'),
        'ph_replace_url' => admin_t('ui.ph_replace_url'),
        'confirm_merge_videos' => admin_t('ui.confirm_merge_videos', ['id' => '__ID__']),
        'confirm_batch_del_videos' => admin_t('ui.confirm_batch_del_videos'),
        'confirm_del_video' => admin_t('ui.confirm_del_video'),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
    ];
@endphp

@section('plain')
<div class="card card-panel video-index list-desk">
    <div class="card-header">
        <span>{{ admin_t('ui.video_list') }} <em id="video-count"></em></span>
        <div>
            <a class="btn btn-sm" href="/admin/video/create">{{ admin_t('ui.add_video') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/tools/recycle">{{ admin_t('ui.recycle') }}@if($q('recycle') > 0) ({{ $q('recycle') }})@endif</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="video-search" onsubmit="return false;">
            <input type="hidden" name="empty_url">
            <input type="hidden" name="empty_pic">
            <input type="hidden" name="empty_content">
            <input type="hidden" name="empty_seo">
            <input type="hidden" name="no_actor">
            <input type="hidden" name="missing_ep">
            <input type="hidden" name="repeat">
            <input type="hidden" name="need_points">
            <input type="hidden" name="has_plot">
            <input type="hidden" name="actor_id">
            <input type="hidden" name="tag_id">
            <input type="text" name="title" placeholder="{{ admin_t('ui.ph_title') }}" autocomplete="off">
            <select name="type_id" id="video-search-type"><option value="">{{ admin_t('ui.types') }}</option></select>
            <select name="status">
                <option value="">{{ admin_t('ui.status') }}</option>
                <option value="1">{{ admin_t('ui.on') }}</option>
                <option value="0">{{ admin_t('ui.off') }}</option>
                <option value="2">{{ admin_t('ui.draft') }}</option>
                <option value="3">{{ admin_t('ui.rejected') }}</option>
                <option value="4">{{ admin_t('ui.scheduled') }}</option>
            </select>
            <button type="button" class="btn btn-sm" id="video-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="video-reset-btn">{{ admin_t('ui.reset') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="video-more-toggle">{{ admin_t('ui.more_filters') }}</button>
            <div class="filter-more" id="video-filter-more">
                <select name="is_recommend">
                    <option value="">{{ admin_t('ui.recommend') }}</option>
                    <option value="1">{{ admin_t('ui.yes') }}</option>
                    <option value="0">{{ admin_t('ui.no') }}</option>
                </select>
                <select name="is_hot">
                    <option value="">{{ admin_t('ui.hot') }}</option>
                    <option value="1">{{ admin_t('ui.yes') }}</option>
                    <option value="0">{{ admin_t('ui.no') }}</option>
                </select>
                <select name="lock">
                    <option value="">{{ admin_t('ui.lock') }}</option>
                    <option value="1">{{ admin_t('ui.locked') }}</option>
                    <option value="0">{{ admin_t('ui.unlocked') }}</option>
                </select>
                <input type="text" name="year" placeholder="{{ admin_t('ui.ph_year') }}">
                <input type="text" name="area" placeholder="{{ admin_t('ui.area') }}">
                <input type="text" name="weekday" placeholder="{{ admin_t('ui.ph_weekday') }}">
                <input type="number" name="points_min" placeholder="{{ admin_t('ui.ph_points_min') }}">
            </div>
        </form>

        <div class="queue-chips" id="video-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.off') }}@if($q('pending') > 0)<em>{{ $q('pending') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="empty_url" data-value="1">{{ admin_t('ui.no_url') }}@if($q('empty_url') > 0)<em>{{ $q('empty_url') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="empty_pic" data-value="1">{{ admin_t('ui.no_cover') }}@if($q('empty_pic') > 0)<em>{{ $q('empty_pic') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="repeat" data-value="1">{{ admin_t('ui.duplicate') }}@if($q('repeat') > 0)<em>{{ $q('repeat') }}</em>@endif</button>
        </div>
        <details class="queue-more">
            <summary>{{ admin_t('ui.fill_tools') }}</summary>
            <div class="queue-chips">
                <button type="button" class="chip" data-queue="empty_content" data-value="1">{{ admin_t('ui.no_intro') }}@if($q('empty_content') > 0)<em>{{ $q('empty_content') }}</em>@endif</button>
                <button type="button" class="chip" data-queue="empty_seo" data-value="1">{{ admin_t('ui.no_seo') }}@if($q('empty_seo') > 0)<em>{{ $q('empty_seo') }}</em>@endif</button>
                <button type="button" class="chip" data-queue="no_actor" data-value="1">{{ admin_t('ui.no_actor') }}@if($q('no_actor') > 0)<em>{{ $q('no_actor') }}</em>@endif</button>
                <button type="button" class="chip" data-queue="missing_ep" data-value="1">{{ admin_t('ui.missing_ep') }}@if($q('missing_ep') > 0)<em>{{ $q('missing_ep') }}</em>@endif</button>
                <button type="button" class="chip" data-queue="status" data-value="2">{{ admin_t('ui.draft') }}</button>
                <button type="button" class="chip" data-queue="status" data-value="3">{{ admin_t('ui.rejected') }}</button>
                <button type="button" class="chip" data-queue="status" data-value="4">{{ admin_t('ui.scheduled') }}</button>
                <button type="button" class="chip" data-queue="need_points" data-value="1">{{ admin_t('ui.need_points') }}</button>
                <button type="button" class="chip" data-queue="has_plot" data-value="1">{{ admin_t('ui.has_plot') }}</button>
                <a class="chip" href="/admin/video/tools/images">{{ admin_t('page.images') }}</a>
                <a class="chip" href="/admin/video/tools/quality">{{ admin_t('page.quality') }}</a>
            </div>
        </details>

        <div class="batch-bar" id="video-batch" hidden>
            <strong id="video-batch-count">{{ admin_t('ui.selected_videos', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="video-batch-on">{{ admin_t('ui.on') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="video-batch-off">{{ admin_t('ui.off') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="video-batch-rec">{{ admin_t('ui.recommend') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="video-batch-lock">{{ admin_t('ui.lock') }}</button>
            <select id="video-batch-type" class="batch-select"><option value="">{{ admin_t('ui.move_to_type') }}</option></select>
            <button type="button" class="btn btn-muted btn-sm" id="video-batch-type-go">{{ admin_t('ui.change_type') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="video-batch-points">{{ admin_t('ui.change_points') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="video-batch-merge">{{ admin_t('ui.merge_dup') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="video-batch-replace-url">{{ admin_t('ui.replace_play_url') }}</button>
            @includeIf('ai_content::batch_button')
            <button type="button" class="btn btn-danger btn-sm" id="video-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="video-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>

        <div id="video-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($videoJsLang, JSON_UNESCAPED_UNICODE);
    var QUEUE_KEYS = ['empty_url', 'empty_pic', 'empty_content', 'empty_seo', 'no_actor', 'missing_ep', 'repeat', 'need_points', 'has_plot'];
    var form = document.getElementById('video-search');
    var moreBox = document.getElementById('video-filter-more');
    var batchBar = document.getElementById('video-batch');
    var batchCount = document.getElementById('video-batch-count');

    function fillSelect(sel, options, selected, emptyLabel, disabledSuffix) {
        if (!sel) return;
        var html = '<option value="">' + U.escape(emptyLabel) + '</option>';
        (options || []).forEach(function (o) {
            var name = o.name || '';
            if (disabledSuffix && String(o.status) === '0') name += disabledSuffix;
            html += '<option value="' + U.escape(o.id) + '">' + U.escape(name) + '</option>';
        });
        sel.innerHTML = html;
        sel.value = selected == null || selected === '' ? '' : String(selected);
    }
    var typeOptions = null;
    function loadTypes(cb) {
        if (typeOptions) { cb(typeOptions); return; }
        U.get('/admin/video/types/options').then(function (res) {
            typeOptions = (res && res.code === 0) ? (res.data || []) : [];
            cb(typeOptions);
        });
    }
    loadTypes(function (opts) {
        fillSelect(document.getElementById('video-search-type'), opts, '', L.types, L.disabled_suffix);
        fillSelect(document.getElementById('video-batch-type'), opts, '', L.move_to_type, L.disabled_suffix);
        var qsType = new URLSearchParams(location.search).get('type_id');
        if (qsType) document.getElementById('video-search-type').value = qsType;
    });

    var qs = new URLSearchParams(location.search);
    ['title','type_id','status','is_recommend','is_hot','lock','year','area','weekday','points_min','actor_id','tag_id'].concat(QUEUE_KEYS).forEach(function (k) {
        var v = qs.get(k);
        if (v && form[k]) form[k].value = v;
    });
    if (['is_recommend','is_hot','lock','year','area','weekday','points_min'].some(function (k) { return qs.get(k); })) {
        moreBox.classList.add('is-open');
        document.getElementById('video-more-toggle').classList.add('is-on');
    }
    if (['empty_content','empty_seo','no_actor','missing_ep','need_points','has_plot'].some(function (k) { return qs.get(k); })
        || ['2','3','4'].indexOf(qs.get('status') || '') >= 0) {
        document.querySelector('.queue-more').open = true;
    }

    function cleanWhere(data) {
        var out = {};
        Object.keys(data).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function syncUrl(where) {
        var qs = new URLSearchParams();
        Object.keys(where).forEach(function (k) { qs.set(k, where[k]); });
        var s = qs.toString();
        history.replaceState(null, '', s ? (location.pathname + '?' + s) : location.pathname);
    }
    function markChips() {
        var status = form.status.value;
        var active = '';
        QUEUE_KEYS.forEach(function (k) {
            if (form[k] && form[k].value === '1') active = k;
        });
        U.qa('#video-queues .chip, .queue-more .chip[data-queue]').forEach(function (chip) {
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
        if (key === 'status') {
            form.status.value = value || '';
        } else {
            if (key === '') form.status.value = '';
            if (key && form[key]) form[key].value = value || '1';
        }
        runSearch();
    }
    function runSearch() {
        var where = cleanWhere(U.formData(form));
        table.reload(where);
        syncUrl(where);
        markChips();
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return where[k] !== ''; });
    }

    function statusHtml(d) {
        var map = {
            1: ['status-ok', L.on],
            2: ['status-warn', L.draft],
            3: ['status-off', L.rejected],
            4: ['status-info', L.scheduled],
            0: ['status-off', L.off]
        };
        var s = map[String(d.status)] || map[0];
        return '<span class="status ' + s[0] + '">' + s[1] + '</span>';
    }
    function titleHtml(d) {
        var cover = String(d.cover || '').trim();
        var thumb = cover
            ? '<img class="vod-thumb" src="' + U.escape(cover) + '" alt="">'
            : '<span class="vod-thumb is-empty">' + L.no_pic + '</span>';
        var badges = [];
        if (!cover) badges.push('<span class="badge badge-warn">' + L.no_cover + '</span>');
        if (!d.has_play) badges.push('<span class="badge badge-tool">' + L.no_url + '</span>');
        if (String(d.is_recommend) === '1') badges.push('<span class="badge badge-ok">' + L.badge_rec + '</span>');
        if (String(d.is_hot) === '1') badges.push('<span class="badge badge-search">' + L.badge_hot + '</span>');
        if (String(d.lock) === '1') badges.push('<span class="badge badge-off">' + L.badge_lock + '</span>');
        var meta = U.escape(d.type_name || L.uncategorized);
        if (d.year) meta += ' · ' + U.escape(d.year);
        if (d.hits) meta += ' · ' + String(L.hits_n || '').replace('__N__', U.escape(d.hits));
        return '<div class="vod-cell">' + thumb + '<div><a class="vod-title" href="/admin/video/' + encodeURIComponent(d.id) + '/edit">' + U.escape(d.title || '') + '</a>'
            + '<div class="muted">' + meta + '</div>'
            + (badges.length ? '<div class="vod-badges">' + badges.join('') + '</div>' : '')
            + '</div></div>';
    }

    var table = window.videoIndexTable = U.table({
        el: '#video-table',
        countEl: document.getElementById('video-count'),
        queueKeys: QUEUE_KEYS,
        url: '/admin/video/list',
        where: cleanWhere(U.formData(form)),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + L.no_match_videos + '</p><p class="muted">' + L.no_match_videos_hint + '</p><p><button type="button" class="btn btn-muted btn-sm" id="video-empty-reset">' + L.clear_filter + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + L.empty_videos + '</p><p class="muted">' + L.empty_videos_hint + '</p><p><a class="btn btn-primary btn-sm" href="/admin/video/collects">' + L.go_collect + '</a> <a class="btn btn-muted btn-sm" href="/admin/video/create">' + L.add_video + '</a></p></div>';
        },
        onDraw: function () {
            var reset = document.getElementById('video-empty-reset');
            if (reset) reset.addEventListener('click', function () { form.reset(); QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; }); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_videos || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.videos, html: titleHtml},
            {title: L.status, width: 80, html: statusHtml},
            {key: 'points', title: L.col_points, width: 70},
            {key: 'updated_at_text', title: L.col_updated, width: 160},
            {title: L.actions, cls: 'actions', html: function (d) {
                var id = encodeURIComponent(d.id);
                return '<a href="/admin/video/' + id + '/edit" class="btn-link">' + L.edit + '</a>'
                    + '<a href="/admin/video/sources?video_id=' + id + '" class="btn-link">' + L.lines + '</a>'
                    + '<a href="/admin/video/sources?video_id=' + id + '&open_episode=1" class="btn-link">' + L.episodes + '</a>'
                    + '<a href="#" class="btn-link js-del">' + L.delete + '</a>';
            }}
        ]
    });
    markChips();

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_videos, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast(L.op_ok, 'ok');
        });
    }

    U.on('#video-search-btn', 'click', runSearch);
    U.on('#video-reset-btn', 'click', function () {
        setTimeout(function () {
            QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
            runSearch();
        }, 0);
    });
    U.on('#video-more-toggle', 'click', function () {
        moreBox.classList.toggle('is-open');
        this.classList.toggle('is-on', moreBox.classList.contains('is-open'));
    });
    document.getElementById('video-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip || chip.tagName === 'A') return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    document.querySelector('.queue-more').addEventListener('click', function (e) {
        var chip = e.target.closest('button[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });

    U.on('#video-batch-on', 'click', function () { batch('status', 1); });
    U.on('#video-batch-off', 'click', function () { batch('status', 0); });
    U.on('#video-batch-rec', 'click', function () { batch('recommend', 1); });
    U.on('#video-batch-lock', 'click', function () { batch('lock', 1); });
    U.on('#video-batch-type-go', 'click', function () {
        var val = document.getElementById('video-batch-type').value;
        if (!val) { U.toast(L.please_pick_type, 'err'); return; }
        batch('type', val);
    });
    U.on('#video-batch-points', 'click', function () {
        var val = U.prompt(L.change_points, '0');
        if (val == null) return;
        batch('points', val);
    });
    U.on('#video-batch-merge', 'click', function () {
        var ids = selectedIds();
        if (ids.length < 2) { U.toast(L.please_select_two_videos, 'err'); return; }
        var keep = U.prompt(L.ph_keep_video_id, String(Math.min.apply(null, ids.map(Number))));
        if (keep == null) return;
        batch('merge', keep, String(L.confirm_merge_videos || '').replace('__ID__', keep));
    });
    U.on('#video-batch-replace-url', 'click', function () {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_videos, 'err'); return; }
        var val = U.prompt(L.ph_replace_url);
        if (val == null) return;
        U.post('/admin/video/batch-replace-url', {value: val, ids: ids}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast(L.op_ok, 'ok');
        });
    });
    U.on('#video-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_videos); });
    U.on('#video-batch-clear', 'click', function () { table.clearSelection(); });

    U.on('#video-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a || !a.classList.contains('js-del')) return;
        e.preventDefault();
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        if (!U.confirm(L.confirm_del_video)) return;
        U.post('/admin/video/delete', {id: row.id}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
            table.refresh();
            U.toast(L.deleted, 'ok');
        });
    });
})();
</script>
@endpush
