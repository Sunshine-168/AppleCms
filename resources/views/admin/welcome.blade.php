<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title>{{ conf('name') }} - 仪表盘</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.staticfile.net/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ @filemtime(public_path('css/admin.css')) ?: '1' }}">
</head>
<body class="iframe-body">
<div class="dash-iframe">
    <div class="dash-hero solo">
        <div class="stat-grid dash">
            <a class="stat-card" href="/admin/video">
                <div>
                    <div class="label">视频总数</div>
                    <div class="value" id="stat-vod-total">--</div>
                    <div class="hint">累计</div>
                </div>
                <div class="icon blue"><i class="fas fa-video"></i></div>
            </a>
            <a class="stat-card" href="/admin/video">
                <div>
                    <div class="label">今日新增</div>
                    <div class="value" id="stat-vod-today">--</div>
                    <div class="hint">今日</div>
                </div>
                <div class="icon green"><i class="fas fa-plus"></i></div>
            </a>
            <a class="stat-card" href="/admin/video/comments">
                <div>
                    <div class="label">评论总数</div>
                    <div class="value" id="stat-article-total">--</div>
                    <div class="hint">累计</div>
                </div>
                <div class="icon orange"><i class="fas fa-comments"></i></div>
            </a>
            <a class="stat-card" href="/admin/video/members">
                <div>
                    <div class="label">用户总数</div>
                    <div class="value" id="stat-user-total">--</div>
                    <div class="hint">累计</div>
                </div>
                <div class="icon purple"><i class="fas fa-users"></i></div>
            </a>
            <a class="stat-card" href="/admin/video/visits">
                <div>
                    <div class="label">今日访问</div>
                    <div class="value" id="stat-visit-today">--</div>
                    <div class="hint">今日</div>
                </div>
                <div class="icon red"><i class="fas fa-chart-line"></i></div>
            </a>
            <a class="stat-card" href="/admin/video/ulogs">
                <div>
                    <div class="label">今日播放</div>
                    <div class="value" id="stat-play-today">--</div>
                    <div class="hint">今日</div>
                </div>
                <div class="icon cyan"><i class="fas fa-play"></i></div>
            </a>
        </div>
    </div>

    <div class="card card-panel" id="dash-todos">
        <div class="card-header">
            <span>待处理</span>
            <span class="muted" id="stat-updated">加载中…</span>
        </div>
        <div class="todo-actions" id="todo-list">
            <span class="muted">加载中…</span>
        </div>
    </div>

    <div class="card card-panel">
        <div class="card-header"><span>接下来做什么</span></div>
        <div class="card-body">
            <div class="dash-actions">
                <a class="btn" href="/admin/video"><i class="fas fa-video"></i> 影片</a>
                <a class="btn btn-muted" href="/admin/video/collects"><i class="fas fa-cloud-download-alt"></i> 采集</a>
                <a class="btn btn-muted" href="/admin/video/comments"><i class="fas fa-comments"></i> 评论</a>
                <a class="btn btn-muted" href="/admin/video/members"><i class="fas fa-users"></i> 会员</a>
                <a class="btn btn-muted" href="/admin/video/settings"><i class="fas fa-cog"></i> 站点设置</a>
                <a class="btn btn-muted" href="/admin/more"><i class="fas fa-th-large"></i> 全部功能</a>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    function text(val) {
        return (val === null || val === undefined || val === '') ? '--' : String(val);
    }
    function setUpdated() {
        var d = new Date();
        function pad(n) { return (n < 10 ? '0' : '') + n; }
        document.getElementById('stat-updated').textContent =
            '更新于 ' + d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) +
            ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }
    function renderTodos(data) {
        var items = [
            {n: data.comment_pending, label: '待审评论', href: '/admin/video/comments'},
            {n: data.report_open, label: '未处理报错', href: '/admin/video/reports'},
            {n: data.playfail_open, label: '播放失败', href: '/admin/video/playfails'},
            {n: data.gbook_pending, label: '待审留言', href: '/admin/video/guestbooks'},
            {n: data.collect_fail, label: '今日采集失败', href: '/admin/video/collect_logs'}
        ];
        document.getElementById('todo-list').innerHTML = items.map(function (it) {
            var n = parseInt(it.n, 10) || 0;
            return '<a class="todo-chip" href="' + it.href + '">' + it.label +
                ' <span class="badge ' + (n > 0 ? 'badge-warn' : '') + '">' + n + '</span></a>';
        }).join('');
    }
    fetch('/admin/welcome/stats', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
        .then(function (res) { return res.json(); })
        .then(function (res) {
            var data = res && res.data ? res.data : {};
            document.getElementById('stat-vod-total').textContent = text(data.vod_total);
            document.getElementById('stat-vod-today').textContent = text(data.vod_today);
            document.getElementById('stat-article-total').textContent = text(data.comment_total ?? data.article_total);
            document.getElementById('stat-user-total').textContent = text(data.user_total);
            document.getElementById('stat-visit-today').textContent = text(data.visit_today);
            document.getElementById('stat-play-today').textContent = text(data.play_today);
            renderTodos(data);
            setUpdated();
        })
        .catch(function () {
            document.getElementById('stat-updated').textContent = '统计加载失败';
            document.getElementById('todo-list').innerHTML = '<span class="muted">暂时读不到待办。</span>';
        });
})();
</script>
</body>
</html>
