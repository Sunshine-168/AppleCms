@extends('admin.layouts.inner')
@section('title', $title ?? '弹幕')

@php
    $desk = in_array((string) ($desk ?? ''), ['messages', 'settings'], true) ? (string) $desk : 'messages';
    $queues = $queues ?? ['all' => 0, 'on' => 0, 'off' => 0, 'report' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $options = is_array($options ?? null) ? $options : ['danmaku_enabled' => 1, 'danmaku_login' => 0];
@endphp

@section('plain')
<style>.dm-swatch{display:inline-block;width:10px;height:10px;border-radius:2px;margin-right:6px;vertical-align:middle;border:1px solid rgba(0,0,0,.15)}</style>
<div class="card card-panel danmaku-board desk-board" id="danmaku-board">
    <div class="card-header">
        <span>弹幕</span>
        @if($desk === 'messages')
            <button type="button" class="btn btn-danger btn-sm" id="dm-clear-all">清空全部</button>
        @endif
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">播放器滚动弹幕，不是本片讨论。后台只审显示或删除，不能手添。没有 DPlayer 第三方接口。违禁词走站点设置。</p>
        <div class="queue-chips">
            <a class="chip{{ $desk === 'messages' ? ' active' : '' }}" href="/admin/video/danmaku">弹幕</a>
            <a class="chip{{ $desk === 'settings' ? ' active' : '' }}" href="/admin/video/danmaku?desk=settings">设置</a>
        </div>
        @if($desk === 'settings')
            <form id="dm-settings" onsubmit="return false;">
                <input type="hidden" name="desk" value="settings">
                <label>弹幕</label>
                <select name="danmaku_enabled">
                    <option value="1" @selected((int) ($options['danmaku_enabled'] ?? 1) === 1)>开启</option>
                    <option value="0" @selected((int) ($options['danmaku_enabled'] ?? 1) !== 1)>关闭</option>
                </select>
                <p class="muted field-hint">关闭后播放器不再加载弹幕层。已经发出的弹幕还在这张表里，可继续审。</p>
                <label>发送需登录</label>
                <select name="danmaku_login">
                    <option value="0" @selected((int) ($options['danmaku_login'] ?? 0) !== 1)>否</option>
                    <option value="1" @selected((int) ($options['danmaku_login'] ?? 0) === 1)>是</option>
                </select>
                <p class="muted field-hint">默认允许游客。打开后未登录会提示去登录，不会假装发出去。</p>
                <p><button type="button" class="btn btn-sm" id="dm-settings-save">保存</button></p>
            </form>
        @else
            <form class="filter-bar" id="dm-search" onsubmit="return false;">
                <input type="hidden" name="desk" value="messages">
                <input type="hidden" name="report">
                <input type="search" name="q" placeholder="搜弹幕内容" autocomplete="off">
                <input type="number" name="video_id" placeholder="影片编号" min="1">
                <input type="number" name="member_id" placeholder="会员编号" min="1">
                <select name="status">
                    <option value="">状态</option>
                    <option value="1">显示</option>
                    <option value="0">隐藏</option>
                </select>
                <select name="mode">
                    <option value="">样式</option>
                    <option value="0">滚动</option>
                    <option value="1">顶部</option>
                    <option value="2">底部</option>
                </select>
                <button type="button" class="btn btn-sm" id="dm-search-btn">查询</button>
                <button type="reset" class="btn btn-muted btn-sm" id="dm-reset-btn">重置</button>
            </form>
            <div class="queue-chips" id="dm-queues">
                <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
                <button type="button" class="chip" data-queue="status" data-value="1">显示@if($q('on') > 0)<em>{{ $q('on') }}</em>@endif</button>
                <button type="button" class="chip" data-queue="status" data-value="0">隐藏@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
                <button type="button" class="chip" data-queue="report" data-value="1">被举报@if($q('report') > 0)<em>{{ $q('report') }}</em>@endif</button>
            </div>
            <div class="batch-bar" id="dm-batch" hidden>
                <strong id="dm-batch-count">已选 0 条</strong>
                <button type="button" class="btn btn-sm" id="dm-batch-on">显示</button>
                <button type="button" class="btn btn-muted btn-sm" id="dm-batch-off">隐藏</button>
                <button type="button" class="btn btn-danger btn-sm" id="dm-batch-del">删除</button>
                <button type="button" class="btn btn-muted btn-sm" id="dm-batch-clear">取消选择</button>
            </div>
            <div id="dm-table" class="desk-table"></div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var desk = @json($desk);
    if (desk === 'settings') {
        U.on('#dm-settings-save', 'click', function () {
            var data = U.formData(document.getElementById('dm-settings'));
            U.post('/admin/video/danmaku/save', data).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                U.toast((res && res.msg) || '已保存', 'ok');
            });
        });
        return;
    }
    var QUEUE_KEYS = ['report'];
    var form = document.getElementById('dm-search');
    var batchBar = document.getElementById('dm-batch');
    var batchCount = document.getElementById('dm-batch-count');
    function cleanWhere(data) {
        var out = {};
        Object.keys(data).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return k !== 'desk' && where[k] !== ''; });
    }
    function markChips() {
        var status = form.status.value;
        var report = form.report.value;
        U.qa('#dm-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && report === '') on = true;
            else if (key === 'status' && report === '' && status === val) on = true;
            else if (key === 'report' && report === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        if (key === 'status') form.status.value = value || '';
        else {
            form.status.value = '';
            if (key && form[key]) form[key].value = value || '1';
        }
        runSearch();
    }
    function runSearch() {
        table.reload(cleanWhere(U.formData(form)));
        markChips();
    }
    function contentHtml(d) {
        var who = U.escape(d.name || (d.member_id ? ('会员 #' + d.member_id) : '游客'));
        var meta = who;
        if (d.time_text) meta += ' · ' + U.escape(d.time_text);
        if (d.mode_label) meta += ' · ' + U.escape(d.mode_label);
        if (d.created_at_text) meta += ' · ' + U.escape(d.created_at_text);
        if (d.ip) meta += ' · ' + U.escape(d.ip);
        var film = d.video_title
            ? '<a href="/vod/' + encodeURIComponent(d.video_id) + '" target="_blank" rel="noopener">' + U.escape(d.video_title) + '</a>'
            : (d.video_id ? '影片 #' + U.escape(d.video_id) : '影片已删');
        if (d.episode_id) film += ' · 集 #' + U.escape(d.episode_id);
        var badges = [];
        if (parseInt(d.report, 10) > 0) badges.push('<span class="badge badge-off">举报 ' + U.escape(d.report) + '</span>');
        var swatch = d.color ? '<span class="dm-swatch" style="background:' + U.escape(d.color) + '"></span>' : '';
        return '<div class="comment-cell"><div class="comment-body">' + swatch + U.escape(d.text || '') + '</div>'
            + '<div class="muted">' + meta + ' · ' + film + '</div>'
            + (badges.length ? '<div class="vod-badges">' + badges.join('') + '</div>' : '')
            + '</div>';
    }
    var table = U.table({
        el: '#dm-table',
        url: '/admin/video/danmaku/list',
        where: cleanWhere(U.formData(form)),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的弹幕</p><p><button type="button" class="btn btn-muted btn-sm" id="dm-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>还没有弹幕</p><p class="muted">用户在播放器发出的弹幕会出现在这里。后台不能手添。</p></div>';
        },
        onDraw: function () {
            var reset = document.getElementById('dm-empty-reset');
            if (reset) reset.addEventListener('click', function () {
                form.reset();
                QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
                runSearch();
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 条';
        },
        cols: [
            {check: true, width: 36},
            {title: '弹幕', html: contentHtml},
            {title: '状态', width: 80, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '显示') : U.status(false, '隐藏');
            }},
            {title: '操作', cls: 'actions', html: function (d) {
                var html = '';
                if (String(d.status) !== '1') html += '<a href="#" class="btn-link js-pass">显示</a>';
                else html += '<a href="#" class="btn-link js-hide">隐藏</a>';
                html += '<a href="#" class="btn-link js-del">删除</a>';
                return html;
            }}
        ]
    });
    markChips();
    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选弹幕', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/danmaku/batch', {ids: ids.join(','), action: action, value: value, desk: 'messages'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }
    function setStatus(row, status) {
        U.post('/admin/video/danmaku/save', {id: row.id, status: status, desk: 'messages'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
            table.refresh();
            U.toast(status === 1 ? '已显示' : '已隐藏', 'ok');
        });
    }
    U.on('#dm-search-btn', 'click', runSearch);
    U.on('#dm-reset-btn', 'click', function () {
        setTimeout(function () {
            QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
            runSearch();
        }, 0);
    });
    document.getElementById('dm-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#dm-batch-on', 'click', function () { batch('status', 1); });
    U.on('#dm-batch-off', 'click', function () { batch('status', 0); });
    U.on('#dm-batch-del', 'click', function () { batch('delete', '', '确认删除选中弹幕？'); });
    U.on('#dm-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#dm-clear-all', 'click', function () {
        if (!U.confirm('清空全部弹幕？播放器立刻看不到，不能恢复。')) return;
        U.post('/admin/video/danmaku/batch', {action: 'clear', desk: 'messages'}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '已清空', 'ok');
        });
    });
    U.on('#dm-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.target === '_blank') return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-pass')) setStatus(row, 1);
        if (a.classList.contains('js-hide')) setStatus(row, 0);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('删除这条弹幕？')) return;
            U.post('/admin/video/danmaku/delete', {id: row.id, desk: 'messages'}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
