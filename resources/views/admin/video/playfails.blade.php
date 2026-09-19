@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'open' => 0, 'done' => 0, 'offline' => 0, 'today' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
@endphp

@section('plain')
<div class="card card-panel playfail-index list-desk">
    <div class="card-header">
        <span>播放失败@if($q('open') > 0) <em>· {{ $q('open') }} 未处理</em>@endif</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/reports">报错</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/tools/players">批量播放器</a>
            <a class="btn btn-muted btn-sm" href="/admin/video">影片列表</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="fail-search" onsubmit="return false;">
            <input type="hidden" name="status">
            <input type="hidden" name="offline">
            <input type="hidden" name="today">
            <input type="search" name="q" placeholder="搜片名、线路、地址或影片 ID" autocomplete="off" aria-label="搜索播放失败">
            <button type="button" class="btn btn-sm" id="fail-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="fail-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="fail-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">未处理@if($q('open') > 0)<em>{{ $q('open') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">已处理@if($q('done') > 0)<em>{{ $q('done') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="offline" data-value="1">可下线@if($q('offline') > 0)<em>{{ $q('offline') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="today" data-value="1">今天@if($q('today') > 0)<em>{{ $q('today') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">播放页点「播放报错」记下的坏链。标已处理<strong>不会改播放地址</strong>。点「下线线路」才会关掉这条线。详情页文字报错在「报错」。</p>
        <div class="batch-bar" id="fail-batch" hidden>
            <strong id="fail-batch-count">已选 0 条</strong>
            <button type="button" class="btn btn-sm" id="fail-batch-done">标为已处理</button>
            <button type="button" class="btn btn-muted btn-sm" id="fail-batch-open">标为未处理</button>
            <button type="button" class="btn btn-muted btn-sm" id="fail-batch-off">下线线路</button>
            <button type="button" class="btn btn-danger btn-sm" id="fail-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="fail-batch-clear">取消选择</button>
        </div>
        <div id="fail-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('fail-search');
    var batchBar = document.getElementById('fail-batch');
    var batchCount = document.getElementById('fail-batch-count');
    var QUEUE_KEYS = ['offline', 'today'];

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
        var status = form.status.value;
        var offline = form.offline.value;
        var today = form.today.value;
        U.qa('#fail-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && offline === '' && today === '') on = true;
            else if (key === 'status' && offline === '' && today === '' && status === val) on = true;
            else if (key === 'offline' && status === '' && today === '' && offline === val) on = true;
            else if (key === 'today' && status === '' && offline === '' && today === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        form.status.value = '';
        if (key === 'status') form.status.value = value || '';
        else if (key && form[key]) form[key].value = value || '1';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function filmHtml(d) {
        if (d.video_title) {
            return '<a href="/admin/video/' + encodeURIComponent(d.video_id) + '/edit">' + U.escape(d.video_title) + '</a>';
        }
        return d.video_id ? ('影片 #' + U.escape(d.video_id)) : '影片已删';
    }
    function lineHtml(d) {
        var parts = [];
        if (d.source_name) parts.push(U.escape(d.source_name));
        else if (d.source_id) parts.push('线路 #' + U.escape(d.source_id));
        if (d.episode_label) parts.push(U.escape(d.episode_label));
        if (d.source_player) parts.push(U.escape(d.source_player));
        return parts.join(' · ') || '没有关联线路';
    }
    function contentHtml(d) {
        var meta = filmHtml(d) + ' · ' + lineHtml(d);
        if (d.created_at_text) meta += ' · ' + U.escape(d.created_at_text);
        if (d.ip) meta += ' · ' + U.escape(d.ip);
        var url = d.url ? '<div class="muted fail-url">' + U.escape(d.url) + '</div>' : '';
        var badges = [];
        if (parseInt(d.source_id, 10) > 0 && parseInt(d.source_status, 10) === 0) {
            badges.push('<span class="badge badge-off">线路已下线</span>');
        }
        return '<div class="comment-cell"><div class="comment-body">' + U.escape(d.content || '播放失败') + '</div>'
            + url
            + '<div class="muted">' + meta + '</div>'
            + (badges.length ? '<div class="vod-badges">' + badges.join('') + '</div>' : '')
            + '</div>';
    }

    var table = U.table({
        el: '#fail-table',
        queueKeys: QUEUE_KEYS,
        url: '/admin/video/playfails/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的播放失败</p><p><button type="button" class="btn btn-muted btn-sm" id="fail-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有播放失败</p><p class="muted">访客在播放页点「播放报错」后会出现在这里。确认线路坏了可以下线；删掉只去掉记录。</p><p><a class="btn btn-muted btn-sm" href="/admin/video/players">去播放器</a></p></div>';
        },
        onDraw: function () {
            var reset = document.getElementById('fail-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
                form.status.value = '';
                runSearch();
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 条';
        },
        cols: [
            {check: true, width: 36},
            {title: '失败', html: contentHtml},
            {title: '状态', width: 88, html: function (d) {
                return parseInt(d.status, 10) === 1 ? U.status(true, '已处理') : U.status(false, '未处理');
            }},
            {title: '操作', cls: 'actions', html: function (d) {
                var html = '';
                if (parseInt(d.status, 10) === 1) html += '<a href="#" class="btn-link js-open">未处理</a>';
                else html += '<a href="#" class="btn-link js-done">已处理</a>';
                if (parseInt(d.can_offline, 10) === 1) html += '<a href="#" class="btn-link js-off">下线线路</a>';
                if (parseInt(d.video_id, 10) > 0) {
                    html += '<a class="btn-link" href="/admin/video/' + encodeURIComponent(d.video_id) + '/edit">改影片</a>';
                    html += '<a class="btn-link" href="' + U.escape(d.play_url || ('/vod/' + d.video_id)) + '" target="_blank" rel="noopener">前台</a>';
                }
                html += '<a href="#" class="btn-link js-del">删除</a>';
                return html;
            }}
        ]
    });
    markChips();

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选播放失败', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/playfails/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }
    function setStatus(row, status) {
        U.post('/admin/video/playfails/save', {id: row.id, status: status}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
            table.refresh();
            U.toast(status === 1 ? '已处理' : '标回未处理', 'ok');
        });
    }
    function offline(row) {
        if (!U.confirm('下线这条失败关联的播放线路？前台将不再出这条线。')) return;
        U.post('/admin/video/playfails/offline', {id: row.id}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '已下线', 'ok');
        });
    }

    U.on('#fail-search-btn', 'click', runSearch);
    U.on('#fail-reset-btn', 'click', function () {
        setTimeout(function () {
            QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
            form.status.value = '';
            runSearch();
        }, 0);
    });
    document.getElementById('fail-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#fail-batch-done', 'click', function () { batch('status', 1); });
    U.on('#fail-batch-open', 'click', function () { batch('status', 0); });
    U.on('#fail-batch-off', 'click', function () { batch('offline', '', '下线选中记录关联的播放线路？前台将不再出这些线。'); });
    U.on('#fail-batch-del', 'click', function () { batch('delete', '', '删除选中记录？只去记录，不会改播放线路。'); });
    U.on('#fail-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#fail-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank') return;
        if (a.getAttribute('href') && a.getAttribute('href').indexOf('/admin/video/') === 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-done')) setStatus(row, 1);
        if (a.classList.contains('js-open')) setStatus(row, 0);
        if (a.classList.contains('js-off')) offline(row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除这条播放失败？只去记录，不会改播放线路。')) return;
            U.post('/admin/video/playfails/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
