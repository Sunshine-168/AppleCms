@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('nav.flink'))

@php
    $desk = in_array((string) ($desk ?? ''), ['links', 'pending', 'cates', 'clicks', 'hits', 'stats', 'settings'], true)
        ? (string) $desk
        : 'links';
    $cates = is_array($cates ?? null) ? $cates : [];
    $options = is_array($options ?? null) ? $options : ['mode' => 'normal', 'min_referer' => 1, 'allow_apply' => 1, 'allow_edit' => 1];
    $period = in_array((string) request()->query('period', 'day'), ['day', 'month', 'year'], true)
        ? (string) request()->query('period', 'day')
        : 'day';
    $stats = is_array($stats ?? null) ? $stats : [];
    $today = is_array($stats['today'] ?? null) ? $stats['today'] : ['hits' => 0, 'clicks' => 0];
    $yesterday = is_array($stats['yesterday'] ?? null) ? $stats['yesterday'] : ['hits' => 0, 'clicks' => 0];
    $week = is_array($stats['week'] ?? null) ? $stats['week'] : ['hits' => 0, 'clicks' => 0];
    $month = is_array($stats['month'] ?? null) ? $stats['month'] : ['hits' => 0, 'clicks' => 0];
    $linksStat = is_array($stats['links'] ?? null) ? $stats['links'] : ['all' => 0, 'show' => 0, 'pending' => 0, 'reject' => 0, 'freeze' => 0];
    $topHits = is_array($stats['top_hits'] ?? null) ? $stats['top_hits'] : [];
    $topClicks = is_array($stats['top_clicks'] ?? null) ? $stats['top_clicks'] : [];
    $daily = is_array($stats['daily'] ?? null) ? $stats['daily'] : [];
    $hosts = is_array($stats['hosts'] ?? null) ? $stats['hosts'] : [];
@endphp

@section('plain')
<div class="card card-panel flink-board desk-board" id="flink-board">
    <div class="card-header">
        <span>{{ $desk === 'stats' ? '友链统计' : '友情链接' }} <em id="flink-count"></em></span>
        <div>
            @if(! in_array($desk, ['clicks', 'hits', 'stats', 'settings'], true))
                <button type="button" class="btn btn-sm" id="flink-add-btn">新增</button>
            @endif
            @if($desk === 'stats')
                <a class="btn btn-muted btn-sm" href="/admin/video/flinks?desk=hits">来路明细</a>
                <a class="btn btn-muted btn-sm" href="/admin/video/flinks?desk=clicks">出站明细</a>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="queue-chips" id="flink-desks">
            <a class="chip{{ $desk === 'links' ? ' active' : '' }}" href="/admin/video/flinks">链接</a>
            <a class="chip{{ $desk === 'pending' ? ' active' : '' }}" href="/admin/video/flinks?desk=pending">待审</a>
            <a class="chip{{ $desk === 'cates' ? ' active' : '' }}" href="/admin/video/flinks?desk=cates">分类</a>
            <a class="chip{{ $desk === 'clicks' ? ' active' : '' }}" href="/admin/video/flinks?desk=clicks">出站</a>
            <a class="chip{{ $desk === 'hits' ? ' active' : '' }}" href="/admin/video/flinks?desk=hits">来路</a>
            <a class="chip{{ $desk === 'stats' ? ' active' : '' }}" href="/admin/video/flinks?desk=stats">统计</a>
            <a class="chip{{ $desk === 'settings' ? ' active' : '' }}" href="/admin/video/flinks?desk=settings">设置</a>
        </div>

        @if($desk === 'stats')
            <p class="muted recycle-lead">按来路 / 出站汇总（前台访问自动产生）。明细在「来路」「出站」；链接库状态见下方卡片。</p>
            <div class="stat-grid dash" style="margin:12px 0 20px">
                <div class="stat-card">
                    <em>今日</em>
                    <strong>{{ (int) $today['hits'] }}</strong>
                    <span class="muted">来路 · 出站 {{ (int) $today['clicks'] }}</span>
                </div>
                <div class="stat-card">
                    <em>昨日</em>
                    <strong>{{ (int) $yesterday['hits'] }}</strong>
                    <span class="muted">来路 · 出站 {{ (int) $yesterday['clicks'] }}</span>
                </div>
                <div class="stat-card">
                    <em>近 7 日</em>
                    <strong>{{ (int) $week['hits'] }}</strong>
                    <span class="muted">来路 · 出站 {{ (int) $week['clicks'] }}</span>
                </div>
                <div class="stat-card">
                    <em>近 30 日</em>
                    <strong>{{ (int) $month['hits'] }}</strong>
                    <span class="muted">来路 · 出站 {{ (int) $month['clicks'] }}</span>
                </div>
            </div>
            <div class="stat-grid dash" style="margin:0 0 20px">
                <div class="stat-card">
                    <em>显示中</em>
                    <strong>{{ (int) ($linksStat['show'] ?? 0) }}</strong>
                    <span class="muted">共 {{ (int) ($linksStat['all'] ?? 0) }} 条</span>
                </div>
                <div class="stat-card">
                    <em>待审</em>
                    <strong>{{ (int) ($linksStat['pending'] ?? 0) }}</strong>
                </div>
                <div class="stat-card">
                    <em>拒绝</em>
                    <strong>{{ (int) ($linksStat['reject'] ?? 0) }}</strong>
                </div>
                <div class="stat-card">
                    <em>冻结</em>
                    <strong>{{ (int) ($linksStat['freeze'] ?? 0) }}</strong>
                </div>
            </div>

            <div class="flink-stats-split" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:8px">
                <div>
                    <h3 style="font-size:15px;margin:0 0 10px">近 30 日来路 TOP</h3>
                    @if($topHits === [])
                        <p class="muted">还没有来路。</p>
                    @else
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead><tr><th>友链</th><th>来路</th></tr></thead>
                                <tbody>
                                @foreach($topHits as $row)
                                    <tr>
                                        <td>
                                            {{ $row['name'] }}
                                            @if(($row['url'] ?? '') !== '')
                                                <div class="muted" style="font-size:12px">{{ $row['url'] }}</div>
                                            @endif
                                        </td>
                                        <td>{{ (int) $row['hits'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div>
                    <h3 style="font-size:15px;margin:0 0 10px">近 30 日出站 TOP</h3>
                    @if($topClicks === [])
                        <p class="muted">还没有出站点击。</p>
                    @else
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead><tr><th>友链</th><th>出站</th></tr></thead>
                                <tbody>
                                @foreach($topClicks as $row)
                                    <tr>
                                        <td>
                                            {{ $row['name'] }}
                                            @if(($row['url'] ?? '') !== '')
                                                <div class="muted" style="font-size:12px">{{ $row['url'] }}</div>
                                            @endif
                                        </td>
                                        <td>{{ (int) $row['clicks'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <h3 style="font-size:15px;margin:20px 0 10px">近 14 日趋势</h3>
            @if($daily === [])
                <p class="muted">暂无数据。</p>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead><tr><th>日期</th><th>来路</th><th>出站</th></tr></thead>
                        <tbody>
                        @foreach(array_reverse($daily) as $row)
                            <tr>
                                <td>{{ $row['day'] }}</td>
                                <td>{{ (int) $row['hits'] }}</td>
                                <td>{{ (int) $row['clicks'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <h3 style="font-size:15px;margin:20px 0 10px">近 30 日来路主机</h3>
            @if($hosts === [])
                <p class="muted">还没有带主机名的来路。</p>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead><tr><th>主机</th><th>次数</th></tr></thead>
                        <tbody>
                        @foreach($hosts as $row)
                            <tr>
                                <td>{{ $row['host'] }}</td>
                                <td>{{ (int) $row['hits'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <h3 style="font-size:15px;margin:20px 0 10px">周期明细</h3>
            <form class="filter-bar" id="flink-search" onsubmit="return false;">
                <input type="hidden" name="desk" value="stats">
                <input type="search" name="q" placeholder="搜名称" autocomplete="off">
                <select name="period" aria-label="周期">
                    <option value="day" @selected($period === 'day')>按日</option>
                    <option value="month" @selected($period === 'month')>按月</option>
                    <option value="year" @selected($period === 'year')>按年</option>
                </select>
                <button type="button" class="btn btn-sm" id="flink-search-btn">查询</button>
                <button type="reset" class="btn btn-muted btn-sm" id="flink-reset-btn">重置</button>
            </form>
            <div id="flink-table"></div>
        @elseif($desk === 'settings')
            <p class="muted recycle-lead">页脚友链。普通按排序；强化按来路。出站和来路由前台产生，不能手添。关掉插件后页脚改回核心友链表。</p>
            <form id="flink-settings" onsubmit="return false;">
                <input type="hidden" name="desk" value="settings">
                <label>模式</label>
                <select name="mode">
                    <option value="normal" @selected(($options['mode'] ?? '') === 'normal')>普通（按排序）</option>
                    <option value="strong" @selected(($options['mode'] ?? '') === 'strong')>强化（按来路）</option>
                </select>
                <p class="muted field-hint">强化模式：来路主机名对上友链网址，同一 IP 同一天只记一次；待审达到最少来路后才显示。不会假装激活。</p>
                <label>最少来路</label>
                <input type="number" name="min_referer" min="1" value="{{ (int) ($options['min_referer'] ?? 1) }}">
                <label>前台申请</label>
                <select name="allow_apply">
                    <option value="1" @selected((int) ($options['allow_apply'] ?? 1) === 1)>允许</option>
                    <option value="0" @selected((int) ($options['allow_apply'] ?? 1) !== 1)>关闭</option>
                </select>
                <label>自助修改</label>
                <select name="allow_edit">
                    <option value="1" @selected((int) ($options['allow_edit'] ?? 1) === 1)>允许</option>
                    <option value="0" @selected((int) ($options['allow_edit'] ?? 1) !== 1)>关闭</option>
                </select>
                <p><button type="button" class="btn btn-sm" id="flink-settings-save">保存</button></p>
            </form>
        @else
            <p class="muted recycle-lead">页脚友链。普通按排序；强化按来路。出站和来路由前台产生，不能手添。关掉插件后页脚改回核心友链表。汇总见「统计」。</p>
            <form class="filter-bar" id="flink-search" onsubmit="return false;">
                <input type="hidden" name="desk" value="{{ $desk }}">
                <input type="search" name="q" placeholder="{{ $desk === 'cates' ? '搜分类' : ($desk === 'clicks' || $desk === 'hits' ? '搜 IP、网址' : '搜名称、网址') }}" autocomplete="off">
                @if($desk === 'links')
                    <select name="status" aria-label="状态">
                        <option value="">全部状态</option>
                        <option value="1">显示</option>
                        <option value="2">拒绝</option>
                        <option value="3">冻结</option>
                        <option value="0">待审</option>
                    </select>
                @endif
                <button type="button" class="btn btn-sm" id="flink-search-btn">查询</button>
                <button type="reset" class="btn btn-muted btn-sm" id="flink-reset-btn">重置</button>
            </form>
            <div id="flink-table"></div>
        @endif
    </div>
</div>

<template id="flink-link-tpl">
    <form>
        <input type="hidden" name="id">
        <input type="hidden" name="desk" value="links">
        <label>名称</label>
        <input type="text" name="name" required>
        <label>网址</label>
        <input type="text" name="url" required placeholder="https://">
        <p class="muted field-hint">只接受 http 或 https。javascript: 会拒绝。前台出站走 /links/go/编号。</p>
        <label>分类</label>
        <select name="cate_id">
            <option value="0">未分类</option>
            @foreach($cates as $cate)
                <option value="{{ $cate['id'] }}">{{ $cate['name'] }}</option>
            @endforeach
        </select>
        <label>邮箱</label>
        <input type="text" name="email">
        <label>备注</label>
        <input type="text" name="remark">
        <label>排序</label>
        <input type="number" name="sort" value="0">
        <label>状态</label>
        <select name="status">
            <option value="0">待审</option>
            <option value="1">显示</option>
            <option value="2">拒绝</option>
            <option value="3">冻结</option>
        </select>
    </form>
</template>
<template id="flink-cate-tpl">
    <form>
        <input type="hidden" name="id">
        <input type="hidden" name="desk" value="cates">
        <label>名称</label>
        <input type="text" name="name" required>
        <label>排序</label>
        <input type="number" name="sort" value="0">
        <label>状态</label>
        <select name="status">
            <option value="1">显示</option>
            <option value="0">隐藏</option>
        </select>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var desk = @json($desk);
    if (desk === 'settings') {
        U.on('#flink-settings-save', 'click', function () {
            var form = document.getElementById('flink-settings');
            var data = U.formData(form);
            data.desk = 'settings';
            U.post('/admin/video/flinks/save', data).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                U.toast('已保存', 'ok');
            });
        });
        return;
    }
    var form = document.getElementById('flink-search');
    var countEl = document.getElementById('flink-count');
    var addBtn = document.getElementById('flink-add-btn');
    var addLabels = {links: '新增链接', pending: '新增链接', cates: '新增分类'};
    if (addBtn) addBtn.textContent = addLabels[desk] || '新增';
    if (!form) return;

    function cleanWhere(data) {
        var out = {};
        Object.keys(data || {}).forEach(function (k) {
            if (data[k] !== '' && data[k] != null) out[k] = data[k];
        });
        return out;
    }
    function queryWhere() {
        var data = cleanWhere(U.formData(form));
        data.limit = 20;
        data.desk = desk;
        return data;
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) {
            if (k === 'limit' || k === 'desk' || k === 'period') return false;
            return where[k] !== '' && where[k] != null;
        });
    }
    function emptyHtml(_parsed, where) {
        if (isFiltered(where)) {
            return '<div class="list-empty"><p>没有符合条件的记录</p><p><button type="button" class="btn btn-muted btn-sm" id="flink-empty-reset">清除筛选</button></p></div>';
        }
        var copy = {
            links: ['还没有友链', '新增链接'],
            pending: ['没有待审申请', ''],
            cates: ['还没有分类', '新增分类'],
            clicks: ['还没有出站记录', ''],
            hits: ['还没有来路', ''],
            stats: ['还没有来路统计', '']
        }[desk] || ['还没有记录', ''];
        if (!copy[1]) return '<div class="list-empty"><p>' + copy[0] + '</p></div>';
        return '<div class="list-empty"><p>' + copy[0] + '</p><p><button type="button" class="btn btn-primary btn-sm" id="flink-empty-add">' + copy[1] + '</button></p></div>';
    }

    var cols = [];
    if (desk === 'cates') {
        cols = [
            {title: '名称', html: function (d) { return '<a class="js-edit" href="#">' + U.escape(d.name || '未填写') + '</a>'; }},
            {title: '排序', width: 72, html: function (d) { return U.escape(String(d.sort || 0)); }},
            {title: '状态', width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '显示') : U.status(false, '隐藏');
            }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ];
    } else if (desk === 'clicks') {
        cols = [
            {title: '友链', html: function (d) { return U.escape(d.link_name || ('#' + (d.link_id || ''))); }},
            {title: 'IP', width: 120, html: function (d) { return U.escape(d.ip || ''); }},
            {title: '时间', width: 120, html: function (d) { return U.escape(String(d.created_at || '')); }}
        ];
    } else if (desk === 'hits') {
        cols = [
            {title: '友链', html: function (d) { return U.escape(d.link_name || ('#' + (d.link_id || ''))); }},
            {title: '来路主机', html: function (d) { return U.escape(d.from_host || ''); }},
            {title: 'IP', width: 120, html: function (d) { return U.escape(d.ip || ''); }},
            {title: '日期', width: 90, html: function (d) { return U.escape(d.day_key || ''); }}
        ];
    } else if (desk === 'stats') {
        cols = [
            {title: '周期', width: 110, html: function (d) { return U.escape(d.period_key || ''); }},
            {title: '友链', html: function (d) { return U.escape(d.link_name || ('#' + (d.link_id || ''))); }},
            {title: '来路', width: 72, html: function (d) { return U.escape(String(d.hits == null ? 0 : d.hits)); }}
        ];
    } else {
        cols = [
            {title: '名称', html: function (d) {
                return '<a class="entry-row-title js-edit" href="#">' + U.escape(d.name || '未填写') + '</a>';
            }},
            {title: '网址', html: function (d) { return U.escape(d.url || ''); }},
            {title: '分类', width: 88, html: function (d) { return U.escape(d.cate_name || ''); }},
            {title: '来路', width: 72, html: function (d) { return U.escape(String(d.referer_total == null ? 0 : d.referer_total)); }},
            {title: '出站', width: 72, html: function (d) { return U.escape(String(d.clicks == null ? 0 : d.clicks)); }},
            {title: '状态', width: 72, html: function (d) { return U.escape(d.status_label || ''); }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ];
    }

    var table = U.table({
        el: '#flink-table',
        url: '/admin/video/flinks/list',
        where: queryWhere(),
        emptyHtml: emptyHtml,
        onDraw: function (_wrap, list) {
            if (countEl && desk !== 'stats') countEl.textContent = list.length ? '· ' + list.length : '';
            var add = document.getElementById('flink-empty-add');
            var reset = document.getElementById('flink-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        cols: cols
    });

    function runSearch() { table.reload(queryWhere()); }
    function openDialog(mode, row) {
        row = row || {};
        var isCate = desk === 'cates';
        U.dialog({
            title: mode === 'edit' ? (isCate ? '编辑分类' : '编辑友链') : (isCate ? '新增分类' : '新增友链'),
            content: document.getElementById(isCate ? 'flink-cate-tpl' : 'flink-link-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), isCate ? {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? '1' : String(row.status)
                } : {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    url: row.url || '',
                    cate_id: row.cate_id == null ? 0 : row.cate_id,
                    email: row.email || '',
                    remark: row.remark || '',
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? (desk === 'pending' ? '0' : '1') : String(row.status)
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast('请填写名称', 'err'); return false; }
                data.desk = isCate ? 'cates' : 'links';
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/flinks/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast(mode === 'edit' ? '已保存' : '已创建', 'ok');
                    table.refresh();
                });
            }
        });
    }

    U.on('#flink-search-btn', 'click', runSearch);
    U.on('#flink-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    if (addBtn) U.on('#flink-add-btn', 'click', function () { openDialog('add'); });
    U.on('#flink-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('确认删除？')) return;
            U.post('/admin/video/flinks/delete', {id: row.id, desk: desk === 'cates' ? 'cates' : 'links'}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
<style>
@media (max-width: 900px) {
    .flink-stats-split { grid-template-columns: 1fr !important; }
}
</style>
@endpush
