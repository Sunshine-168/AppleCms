@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'open' => 0, 'done' => 0, 'today' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
@endphp

@section('plain')
<div class="card card-panel report-index">
    <div class="card-header">
        <span>报错@if($q('open') > 0) <em>· {{ $q('open') }} 未处理</em>@endif</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/playfails">播放失败</a>
            <a class="btn btn-muted btn-sm" href="/admin/video">影片列表</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="report-search" onsubmit="return false;">
            <input type="hidden" name="status">
            <input type="hidden" name="today">
            <input type="search" name="q" placeholder="搜内容、片名或影片 ID" autocomplete="off" aria-label="搜索报错">
            <button type="button" class="btn btn-sm" id="report-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="report-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="report-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">未处理@if($q('open') > 0)<em>{{ $q('open') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">已处理@if($q('done') > 0)<em>{{ $q('done') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="today" data-value="1">今天@if($q('today') > 0)<em>{{ $q('today') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">会员在影片详情页提交的无法播放、地址失效。处理完打标即可；删掉只去掉这条记录，<strong>不会改播放地址</strong>。播放器自动记下的失败在「播放失败」。</p>
        <div class="batch-bar" id="report-batch" hidden>
            <strong id="report-batch-count">已选 0 条</strong>
            <button type="button" class="btn btn-sm" id="report-batch-done">标为已处理</button>
            <button type="button" class="btn btn-muted btn-sm" id="report-batch-open">标为未处理</button>
            <button type="button" class="btn btn-danger btn-sm" id="report-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="report-batch-clear">取消选择</button>
        </div>
        <div id="report-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('report-search');
    var batchBar = document.getElementById('report-batch');
    var batchCount = document.getElementById('report-batch-count');
    var QUEUE_KEYS = ['today'];

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
        var today = form.today.value;
        U.qa('#report-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && today === '') on = true;
            else if (key === 'status' && today === '' && status === val) on = true;
            else if (key === 'today' && today === val) on = true;
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
    function contentHtml(d) {
        var who = U.escape(d.member_name || (parseInt(d.member_id, 10) > 0 ? ('会员 #' + d.member_id) : '游客'));
        var meta = who;
        if (d.created_at_text) meta += ' · ' + U.escape(d.created_at_text);
        if (d.ip) meta += ' · ' + U.escape(d.ip);
        return '<div class="comment-cell"><div class="comment-body">' + U.escape(d.content || '') + '</div>'
            + '<div class="muted">' + meta + ' · ' + filmHtml(d) + '</div></div>';
    }

    var table = U.table({
        el: '#report-table',
        url: '/admin/video/reports/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的报错</p><p><button type="button" class="btn btn-muted btn-sm" id="report-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有报错</p><p class="muted">访客在影片详情页提交「无法播放 / 地址失效」后会出现在这里。处理完打标，不会自动改线路。</p></div>';
        },
        onDraw: function (_wrap, rows) {
            var reset = document.getElementById('report-empty-reset');
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
            {title: '报错', html: contentHtml},
            {title: '状态', width: 88, html: function (d) {
                return parseInt(d.status, 10) === 1 ? U.status(true, '已处理') : U.status(false, '未处理');
            }},
            {title: '操作', cls: 'actions', html: function (d) {
                var html = '';
                if (parseInt(d.status, 10) === 1) html += '<a href="#" class="btn-link js-open">未处理</a>';
                else html += '<a href="#" class="btn-link js-done">已处理</a>';
                if (parseInt(d.video_id, 10) > 0) {
                    html += '<a class="btn-link" href="/admin/video/' + encodeURIComponent(d.video_id) + '/edit">改影片</a>';
                    html += '<a class="btn-link" href="/vod/' + encodeURIComponent(d.video_id) + '" target="_blank" rel="noopener">前台</a>';
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
        if (!ids.length) { U.toast('请先勾选报错', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/reports/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }
    function setStatus(row, status) {
        U.post('/admin/video/reports/save', {id: row.id, status: status}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
            table.refresh();
            U.toast(status === 1 ? '已处理' : '标回未处理', 'ok');
        });
    }

    U.on('#report-search-btn', 'click', runSearch);
    U.on('#report-reset-btn', 'click', function () {
        setTimeout(function () {
            QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
            form.status.value = '';
            runSearch();
        }, 0);
    });
    document.getElementById('report-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#report-batch-done', 'click', function () { batch('status', 1); });
    U.on('#report-batch-open', 'click', function () { batch('status', 0); });
    U.on('#report-batch-del', 'click', function () { batch('delete', '', '删除选中报错？只去记录，不会改播放地址。'); });
    U.on('#report-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#report-table', 'click', function (e) {
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
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除这条报错？只去记录，不会改播放地址。')) return;
            U.post('/admin/video/reports/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
