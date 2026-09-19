@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'on' => 0, 'off' => 0, 'never' => 0, 'fail' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $sourceId = (int) ($sourceId ?? 0);
    $sourceName = trim((string) ($sourceName ?? ''));
    $sourceChip = $sourceName !== '' ? $sourceName : ($sourceId > 0 ? ('采集源 #'.$sourceId) : '');
@endphp

@section('plain')
<div class="card card-panel collect-task-index desk-board">
    <div class="card-header">
        <span>定时采集 <em id="ctask-count"></em></span>
        <div>
            <a class="btn btn-sm" href="/admin/video/collect_tasks/create">新增定时采集</a>
            <button type="button" class="btn btn-muted btn-sm" id="ctask-due-btn" hidden>跑到期任务</button>
        </div>
    </div>
    <div class="card-body">
        @include('admin.partials.schedule-kind-tabs', ['tab' => 'collect'])
        <form class="filter-bar" id="ctask-search" onsubmit="return false;">
            <input type="hidden" name="status">
            <input type="hidden" name="never">
            <input type="hidden" name="failed">
            <input type="hidden" name="collect_source_id" value="{{ $sourceId > 0 ? $sourceId : '' }}">
            <input type="search" name="q" placeholder="搜任务或采集源" autocomplete="off" aria-label="搜索定时采集">
            <button type="button" class="btn btn-sm" id="ctask-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="ctask-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="ctask-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="1">启用@if($q('on') > 0)<em>{{ $q('on') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">停用@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="never" data-value="1">从未跑@if($q('never') > 0)<em>{{ $q('never') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="failed" data-value="1">上次失败@if($q('fail') > 0)<em>{{ $q('fail') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="source" id="ctask-source-chip" @if($sourceId < 1) hidden @endif>{{ $sourceChip }}</button>
        </div>
        <p class="muted recycle-lead">到点会自动采资源站。没有采集源时先去「<a href="/admin/video/collects">采集源</a>」加接口。备份、推送、插件任务在「备份 / 推送 / 插件」。服务器要每分钟跑 <code>php artisan schedule:run</code>。有任务后可点「跑到期任务」立刻检查。删任务不会改片库。</p>
        <div class="batch-bar" id="ctask-batch" hidden>
            <strong id="ctask-batch-count">已选 0 条</strong>
            <button type="button" class="btn btn-sm" id="ctask-batch-on">启用</button>
            <button type="button" class="btn btn-muted btn-sm" id="ctask-batch-off">停用</button>
            <button type="button" class="btn btn-danger btn-sm" id="ctask-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="ctask-batch-clear">取消选择</button>
        </div>
        <div id="ctask-table" class="desk-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('ctask-search');
    var batchBar = document.getElementById('ctask-batch');
    var batchCount = document.getElementById('ctask-batch-count');
    var countEl = document.getElementById('ctask-count');
    var sourceChip = document.getElementById('ctask-source-chip');

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
    function setSource(id, name) {
        form.collect_source_id.value = id ? String(id) : '';
        if (!sourceChip) return;
        sourceChip.textContent = name || (id ? ('采集源 #' + id) : '');
        sourceChip.hidden = !form.collect_source_id.value;
    }
    function markChips() {
        var status = form.status.value;
        var never = form.never.value;
        var failed = form.failed.value;
        var source = form.collect_source_id.value;
        U.qa('#ctask-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === 'source') on = !!source;
            else if (key === '' && status === '' && never === '' && failed === '') on = true;
            else if (key === 'status' && never === '' && failed === '' && status === val) on = true;
            else if (key === 'never' && never === '1' && status === '' && failed === '') on = true;
            else if (key === 'failed' && failed === '1' && status === '' && never === '') on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        if (key === 'source') {
            setSource('', '');
        } else {
            form.status.value = '';
            form.never.value = '';
            form.failed.value = '';
            if (key === 'status') form.status.value = value || '';
            if (key === 'never') form.never.value = '1';
            if (key === 'failed') form.failed.value = '1';
        }
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function fmtTime(ts) {
        ts = parseInt(ts, 10) || 0;
        if (!ts) return '从未跑';
        var d = new Date(ts * 1000);
        var now = new Date();
        var pad = function (n) { return n < 10 ? '0' + n : '' + n; };
        var hm = pad(d.getHours()) + ':' + pad(d.getMinutes());
        if (d.toDateString() === now.toDateString()) return '今天 ' + hm;
        var y = new Date(now);
        y.setDate(now.getDate() - 1);
        if (d.toDateString() === y.toDateString()) return '昨天 ' + hm;
        if (d.getFullYear() === now.getFullYear()) return pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + hm;
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    }
    function titleHtml(d) {
        var name = d.name || '未命名任务';
        var badges = [];
        if (parseInt(d.status, 10) !== 1) badges.push('<span class="badge badge-off">停用</span>');
        else badges.push('<span class="badge badge-ok">启用</span>');
        if (parseInt(d.source_missing, 10) === 1) badges.push('<span class="badge badge-warn">采集源已删</span>');
        else if (parseInt(d.source_off, 10) === 1) badges.push('<span class="badge badge-off">源已停</span>');
        if (parseInt(d.never, 10) === 1) badges.push('<span class="badge badge-search">从未跑</span>');
        else if (parseInt(d.last_ok, 10) !== 1) badges.push('<span class="badge badge-warn">上次失败</span>');
        var meta = [];
        if (d.source_name) meta.push('<a href="#" class="js-source">' + U.escape(d.source_name) + '</a>');
        if (d.cron_label) meta.push(U.escape(d.cron_label));
        if (d.hours_label) meta.push(U.escape(d.hours_label));
        meta.push(U.escape(String(d.pages || 1)) + ' 页');
        if (d.next_run_text && parseInt(d.status, 10) === 1) meta.push('下次 ' + U.escape(d.next_run_text));
        if (d.last_msg) meta.push(U.escape(d.last_msg));
        return '<div class="entry-row-title-line"><a class="entry-row-title" href="/admin/video/collect_tasks/' + encodeURIComponent(d.id || '') + '/edit">' + U.escape(name) + '</a> ' + badges.join(' ') + '</div>'
            + '<div class="entry-row-meta">' + (meta.join(' · ') || '—') + '</div>';
    }

    var table = U.table({
        el: '#ctask-table',
        countEl: countEl,
        url: '/admin/video/collect_tasks/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的定时采集。</p><p><button type="button" class="btn btn-muted btn-sm" id="ctask-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有定时采集。</p><p class="muted">先有采集源，再设到点自动采当天更新。</p><p><a class="btn btn-primary btn-sm" href="/admin/video/collect_tasks/create">新增定时采集</a></p></div>';
        },
        onDraw: function (wrap, list) {
            var dueBtn = document.getElementById('ctask-due-btn');
            if (dueBtn) dueBtn.hidden = !list.length;
            U.qa('tbody tr[data-idx]', wrap).forEach(function (tr) {
                var d = list[parseInt(tr.getAttribute('data-idx'), 10)];
                if (!d) return;
                if (parseInt(d.status, 10) !== 1) tr.classList.add('is-off');
                if (parseInt(d.never, 10) !== 1 && parseInt(d.last_ok, 10) !== 1) tr.classList.add('is-fail');
            });
            var reset = document.getElementById('ctask-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                form.status.value = '';
                form.never.value = '';
                form.failed.value = '';
                setSource('', '');
                runSearch();
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 条';
        },
        cols: [
            {check: true, width: 36},
            {title: '任务', html: titleHtml},
            {title: '上次', width: 120, html: function (d) { return fmtTime(d.last_run_at); }},
            {title: '操作', cls: 'actions', html: function (d) {
                return '<a href="#" class="btn-link js-run">立刻采</a>'
                    + '<a class="btn-link" href="/admin/video/collect_tasks/' + encodeURIComponent(d.id || '') + '/edit">编辑</a>'
                    + '<a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });
    markChips();

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选任务', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/collect_tasks/batch', {ids: ids.join(','), action: action, value: value || ''}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }

    U.on('#ctask-search-btn', 'click', runSearch);
    U.on('#ctask-reset-btn', 'click', function () {
        setTimeout(function () {
            form.status.value = '';
            form.never.value = '';
            form.failed.value = '';
            setSource('', '');
            runSearch();
        }, 0);
    });
    U.on('#ctask-due-btn', 'click', function () {
        U.loading(true);
        U.post('/admin/video/collect-due', {}).then(function (res) {
            U.loading(false);
            table.refresh();
            U.toast((res && res.msg) || '已检查到期任务', res && res.code === 0 ? 'ok' : 'err');
        });
    });
    document.getElementById('ctask-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#ctask-batch-on', 'click', function () { batch('status', 1); });
    U.on('#ctask-batch-off', 'click', function () { batch('status', 0); });
    U.on('#ctask-batch-del', 'click', function () { batch('delete', '', '删除选中任务？片库不会变。'); });
    U.on('#ctask-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#ctask-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var href = a.getAttribute('href') || '';
        if (href.indexOf('/admin/video/collect_tasks/') === 0 || href.indexOf('/admin/video/collects') === 0) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        e.preventDefault();
        if (a.classList.contains('js-source')) {
            if (!row) return;
            setSource(row.collect_source_id || '', row.source_name || '');
            runSearch();
            return;
        }
        if (!row) return;
        if (a.classList.contains('js-run')) {
            U.loading(true);
            U.post('/admin/video/collect_tasks/run', {id: row.id}).then(function (res) {
                U.loading(false);
                table.refresh();
                U.toast((res && res.msg) || '采集完成', res && res.code === 0 ? 'ok' : 'err');
            });
            return;
        }
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除这条定时采集？片库不会变。')) return;
            U.post('/admin/video/collect_tasks/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
