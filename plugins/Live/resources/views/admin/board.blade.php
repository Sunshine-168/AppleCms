@extends('admin.layouts.inner')
@section('title', '直播')

@php
    $desk = in_array(request('desk', 'channels'), ['channels', 'pending', 'categories', 'stats'], true)
        ? request('desk', 'channels')
        : 'channels';
    $categories = $categories ?? collect();
    $stats = is_array($stats ?? null) ? $stats : [];
    $chStat = is_array($stats['channels'] ?? null) ? $stats['channels'] : ['all' => 0, 'on' => 0, 'pending' => 0];
    $cateId = (int) request('cate_id', 0);
@endphp

@section('plain')
<div class="card card-panel" id="live-board">
    <div class="card-header">
        <span>直播 <em id="live-count"></em></span>
        <div>
            @if(in_array($desk, ['channels', 'pending'], true))
                <a class="btn btn-sm" href="/admin/video/live-channels/create{{ $desk === 'pending' ? '?desk=pending' : '' }}">完整表单</a>
                <button type="button" class="btn btn-muted btn-sm" id="live-add">快捷添加</button>
            @elseif($desk === 'categories')
                <a class="btn btn-sm" href="/admin/video/live-categories/create">新建分类</a>
                <button type="button" class="btn btn-muted btn-sm" id="live-add">快捷添加</button>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="queue-chips">
            <a class="chip{{ $desk === 'channels' ? ' active' : '' }}" href="/admin/video/lives">频道</a>
            <a class="chip{{ $desk === 'pending' ? ' active' : '' }}" href="?desk=pending">待审</a>
            <a class="chip{{ $desk === 'categories' ? ' active' : '' }}" href="?desk=categories">分类</a>
            <a class="chip{{ $desk === 'stats' ? ' active' : '' }}" href="?desk=stats">统计</a>
        </div>

        @if($desk === 'stats')
            <p class="muted recycle-lead">频道目录运营概况。人气来自前台打开播放页。</p>
            <div class="stat-grid">
                <div class="stat-card"><span>频道</span><strong>{{ (int) ($chStat['all'] ?? 0) }}</strong><span class="muted">上架 {{ (int) ($chStat['on'] ?? 0) }} · 待审 {{ (int) ($chStat['pending'] ?? 0) }}</span></div>
                <div class="stat-card"><span>分类</span><strong>{{ (int) ($stats['cate_total'] ?? 0) }}</strong><span class="muted">总人气 {{ (int) ($stats['hit_total'] ?? 0) }}</span></div>
            </div>
            <div class="stat-split" style="margin-top:20px">
                <div>
                    <h3>分类分布</h3>
                    <ul class="plain-list">
                        @forelse(($stats['by_cate'] ?? []) as $row)
                            <li><a href="/admin/video/lives?cate_id={{ (int) $row['id'] }}">{{ $row['name'] }}</a> <em>{{ (int) $row['count'] }}</em></li>
                        @empty
                            <li class="muted">暂无</li>
                        @endforelse
                    </ul>
                </div>
                <div>
                    <h3>人气 TOP</h3>
                    <ul class="plain-list">
                        @forelse(($stats['top_hits'] ?? []) as $row)
                            <li><a href="/admin/video/live-channels/{{ (int) $row['id'] }}/edit">{{ $row['title'] }}</a> <em>{{ (int) $row['hits'] }}</em></li>
                        @empty
                            <li class="muted">暂无</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @else
            <p class="muted recycle-lead">
                @if($desk === 'categories')
                    分类给频道分组。点「新建分类」用完整表单（可上传图片）；快捷添加只填名称。
                @elseif($desk === 'pending')
                    待审 / 下架频道。上架后出现在前台 /live。
                @else
                    IPTV 频道目录。完整表单可上传封面、填多线路；快捷添加只填频道名。
                @endif
            </p>

            @if(in_array($desk, ['channels', 'pending'], true))
                <form class="filter-bar" id="live-search" onsubmit="return false;">
                    <input type="hidden" name="desk" value="{{ $desk }}">
                    <input type="search" name="q" placeholder="搜频道名" autocomplete="off" aria-label="搜索">
                    @if($categories->isNotEmpty())
                        <select name="cate_id" aria-label="分类">
                            <option value="">全部分类</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected($cateId === (int) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    <button type="button" class="btn btn-sm" id="live-search-btn">查询</button>
                    <button type="reset" class="btn btn-muted btn-sm" id="live-reset-btn">重置</button>
                </form>
            @elseif($desk === 'categories')
                <form class="filter-bar" id="live-search" onsubmit="return false;">
                    <input type="hidden" name="desk" value="categories">
                    <input type="search" name="q" placeholder="搜分类名" autocomplete="off" aria-label="搜索">
                    <button type="button" class="btn btn-sm" id="live-search-btn">查询</button>
                    <button type="reset" class="btn btn-muted btn-sm" id="live-reset-btn">重置</button>
                </form>
            @endif

            <div class="batch-bar" id="live-batch" hidden>
                <strong id="live-batch-count">已选 0 个</strong>
                @if($desk !== 'categories')
                    <button type="button" class="btn btn-sm batch" data-action="status" data-value="1">上架</button>
                    <button type="button" class="btn btn-muted btn-sm batch" data-action="status" data-value="0">下架</button>
                @endif
                <button type="button" class="btn btn-danger btn-sm batch" data-action="delete">删除</button>
                <button type="button" class="btn btn-muted btn-sm" id="live-batch-clear">取消选择</button>
            </div>
            <div id="live-table"></div>
        @endif
    </div>
</div>

<template id="channel-form">
    <form class="tag-form">
        <input type="hidden" name="desk" value="channels">
        <p class="muted field-hint">快捷添加。封面、多线路、推荐请用 <a href="/admin/video/live-channels/create">完整表单</a>。</p>
        <label>频道名</label>
        <input name="title" required placeholder="如 CCTV-1 综合" autofocus>
        <label>分类</label>
        <select name="cate_id">
            <option value="0">未分类</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </select>
        <label>播放地址</label>
        <textarea name="urls" rows="4" placeholder="高清$https://example.com/live.m3u8"></textarea>
        <p class="muted field-hint">格式：线路名$地址。多线路用 # 或换行。推荐 HLS（.m3u8）。</p>
        <label>状态</label>
        <select name="status">
            <option value="1">上架</option>
            <option value="0">待审 / 下架</option>
        </select>
        <input type="hidden" name="play_from" value="hls">
        <input type="hidden" name="sort" value="0">
    </form>
</template>
<template id="category-form">
    <form class="tag-form">
        <input type="hidden" name="desk" value="categories">
        <p class="muted field-hint">快捷添加。上传图片请用 <a href="/admin/video/live-categories/create">完整表单</a>。</p>
        <label>分类名</label>
        <input name="name" required placeholder="如 央视、卫视" autofocus>
        <p class="muted field-hint">出现在前台直播分类条。</p>
        <label>标识</label>
        <input name="slug" placeholder="可空，按名称生成">
        <label>排序</label>
        <input type="number" name="sort" value="0">
        <label>状态</label>
        <select name="status">
            <option value="1">启用</option>
            <option value="0">停用</option>
        </select>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var desk = @json($desk);
    var url = '/admin/video/lives';
    if (desk === 'stats') return;

    var form = document.getElementById('live-search');
    var batchBar = document.getElementById('live-batch');
    var batchCount = document.getElementById('live-batch-count');
    var countEl = document.getElementById('live-count');
    var prefillCate = @json($cateId > 0 ? $cateId : 0);

    function cleanWhere(data) {
        var out = { desk: desk, limit: 20 };
        Object.keys(data || {}).forEach(function (k) {
            if (data[k] !== '' && data[k] != null) out[k] = data[k];
        });
        out.desk = desk;
        return out;
    }
    function queryWhere() {
        return cleanWhere(form ? U.formData(form) : { desk: desk });
    }

    var cols;
    if (desk === 'categories') {
        cols = [
            { check: true, width: 36 },
            { title: '分类', html: function (d) {
                return '<a class="btn-link" href="/admin/video/live-categories/' + d.id + '/edit">' + U.escape(d.name || '') + '</a>'
                    + '<div class="muted">#' + U.escape(d.id) + (d.slug ? ' · ' + U.escape(d.slug) : '') + ' · ' + U.escape(String(d.channel_count || 0)) + ' 个频道</div>';
            }},
            { key: 'sort', title: '排序', width: 64 },
            { title: '状态', width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '启用') : U.status(false, '停用');
            }},
            { title: '操作', cls: 'actions', html: function (d) {
                return '<a class="btn-link" href="/admin/video/live-categories/' + d.id + '/edit">编辑</a> <a href="#" class="btn-link js-del">删除</a>';
            }}
        ];
    } else {
        cols = [
            { check: true, width: 36 },
            { title: '频道', html: function (d) {
                var badge = String(d.status) === '1' ? '' : '<span class="badge badge-off">待审</span>';
                return '<div class="entry-row-title-line"><a class="btn-link" href="/admin/video/live-channels/' + d.id + '/edit">' + U.escape(d.title || '') + '</a> ' + badge + '</div>'
                    + '<div class="muted">' + U.escape(d.sub || d.cate_name || '') + '</div>';
            }},
            { title: '分类', width: 120, html: function (d) { return U.escape(d.cate_name || '未分类'); }},
            { key: 'recommend', title: '推荐', width: 64 },
            { key: 'hits', title: '人气', width: 72 },
            { key: 'sort', title: '排序', width: 64 },
            { title: '状态', width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '上架') : U.status(false, '待审');
            }},
            { title: '操作', cls: 'actions', html: function (d) {
                var html = '';
                if (d.front_url) html += '<a href="' + U.escape(d.front_url) + '" target="_blank" rel="noopener" class="btn-link">前台</a> ';
                html += '<a class="btn-link" href="/admin/video/live-channels/' + d.id + '/edit">编辑</a> <a href="#" class="btn-link js-del">删除</a>';
                return html;
            }}
        ];
    }

    var table = U.table({
        el: '#live-table',
        countEl: countEl,
        url: url + '/list',
        where: queryWhere(),
        emptyHtml: function (_p, where) {
            var filtered = Object.keys(where || {}).some(function (k) {
                return k !== 'limit' && k !== 'desk' && where[k] !== '' && where[k] != null;
            });
            if (filtered) {
                return '<div class="list-empty"><p>没有符合条件的记录</p><p><button type="button" class="btn btn-muted btn-sm" id="live-empty-reset">清除筛选</button></p></div>';
            }
            if (desk === 'categories') {
                return '<div class="list-empty"><p>还没有分类</p><p class="muted">先建央视、卫视这种分组，再给频道挂上。</p><p><a class="btn btn-primary btn-sm" href="/admin/video/live-categories/create">新建分类</a></p></div>';
            }
            return '<div class="list-empty"><p>还没有频道</p><p class="muted">填名称和 m3u8 地址即可。会出现在前台 /live。</p><p><a class="btn btn-primary btn-sm" href="/admin/video/live-channels/create">完整表单</a> <button type="button" class="btn btn-muted btn-sm" id="live-empty-add">快捷添加</button></p></div>';
        },
        onDraw: function () {
            var add = document.getElementById('live-empty-add');
            var reset = document.getElementById('live-empty-reset');
            if (add) add.addEventListener('click', function () { openQuick(); });
            if (reset) reset.addEventListener('click', function () {
                if (form) form.reset();
                if (prefillCate && form && form.cate_id) form.cate_id.value = String(prefillCate);
                table.reload(queryWhere());
            });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 个';
        },
        cols: cols
    });

    function openQuick(row) {
        row = row || {};
        var tpl = desk === 'categories' ? 'category-form' : 'channel-form';
        U.dialog({
            title: row.id ? '快捷编辑' : '快捷添加',
            className: 'is-wide',
            content: document.getElementById(tpl).innerHTML,
            onOpen: function (body) {
                var f = body.querySelector('form');
                U.fillForm(f, row);
                if (!row.id && desk !== 'categories' && prefillCate && f.cate_id) {
                    f.cate_id.value = String(prefillCate);
                }
                if (desk === 'pending' && !row.id && f.status) f.status.value = '0';
            },
            onSave: function (body) {
                var d = U.formData(body.querySelector('form'));
                if (row.id) d.id = row.id;
                d.desk = desk === 'pending' ? 'channels' : desk;
                return U.post(url + '/save', d).then(function (r) {
                    if (!r || r.code !== 0) { U.toast((r && r.msg) || '失败', 'err'); return false; }
                    U.toast('已保存', 'ok');
                    table.refresh();
                });
            }
        });
    }

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value) {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选', 'err'); return; }
        if (action === 'delete' && !U.confirm(desk === 'categories' ? '删除分类后，频道会改成未分类。确认？' : '确认删除选中频道？')) return;
        U.post(url + '/batch', { ids: ids.join(','), desk: desk === 'pending' ? 'channels' : desk, action: action, value: value || '' }).then(function (r) {
            if (!r || r.code !== 0) { U.toast((r && r.msg) || '失败', 'err'); return; }
            table.refresh();
            U.toast((r && r.msg) || '已处理', 'ok');
        });
    }

    U.on('#live-add', 'click', function () { openQuick(); });
    U.on('#live-search-btn', 'click', function () { table.reload(queryWhere()); });
    U.on('#live-reset-btn', 'click', function () {
        setTimeout(function () {
            if (prefillCate && form && form.cate_id) form.cate_id.value = String(prefillCate);
            table.reload(queryWhere());
        }, 0);
    });
    U.on('#live-batch-clear', 'click', function () { table.clearSelection(); });
    document.querySelectorAll('.batch').forEach(function (btn) {
        btn.addEventListener('click', function () { batch(btn.dataset.action, btn.dataset.value); });
    });
    U.on('#live-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a || !a.classList.contains('js-del')) return;
        e.preventDefault();
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        if (!U.confirm('删除「' + (row.title || row.name || '') + '」？')) return;
        U.post(url + '/delete', { id: row.id, desk: desk === 'pending' ? 'channels' : desk }).then(function (r) {
            if (!r || r.code !== 0) { U.toast((r && r.msg) || '失败', 'err'); return; }
            table.refresh();
            U.toast('已删除', 'ok');
        });
    });
})();
</script>
@endpush
