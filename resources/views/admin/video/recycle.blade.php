@extends('admin.layouts.inner')
@section('title', $title)

@php
    $types = $types ?? [];
    $count = (int) ($count ?? 0);
@endphp

@section('plain')
<div class="card card-panel recycle-index">
    <div class="card-header">
        <span>回收站 <em id="recycle-count">@if($count > 0)· {{ $count }}@endif</em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video">返回影片</a>
            <button type="button" class="btn btn-danger btn-sm" id="recycle-empty-btn" @if($count < 1) hidden @endif>清空回收站</button>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="recycle-search" onsubmit="return false;">
            <input type="hidden" name="trash" value="1">
            <input type="search" name="q" placeholder="搜索已删影片的标题或 ID" autocomplete="off" aria-label="搜索回收站">
            <select name="type_id" aria-label="分类">
                <option value="">全部分类</option>
                @foreach($types as $type)
                    <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-sm" id="recycle-search-btn">搜索</button>
            <button type="reset" class="btn btn-muted btn-sm" id="recycle-reset-btn">重置</button>
        </form>
        <p class="muted recycle-lead">删除的影片先放这里。还原后回影片列表；彻底删除无法恢复。不会自动清空。</p>
        <div class="batch-bar" id="recycle-batch" hidden>
            <strong id="recycle-batch-count">已选 0 部</strong>
            <button type="button" class="btn btn-sm" id="recycle-batch-restore">还原所选</button>
            <button type="button" class="btn btn-danger btn-sm" id="recycle-batch-purge">彻底删除所选</button>
            <button type="button" class="btn btn-muted btn-sm" id="recycle-batch-clear">取消选择</button>
        </div>
        <div id="recycle-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('recycle-search');
    var batchBar = document.getElementById('recycle-batch');
    var batchCount = document.getElementById('recycle-batch-count');
    var countEl = document.getElementById('recycle-count');
    var emptyBtn = document.getElementById('recycle-empty-btn');

    function cleanWhere(data) {
        var out = {trash: 1, limit: 20};
        Object.keys(data || {}).forEach(function (k) {
            if (k === 'trash') return;
            if (data[k] !== '') out[k] = data[k];
        });
        return out;
    }
    function queryWhere() {
        return cleanWhere(U.formData(form));
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) {
            return k !== 'limit' && k !== 'trash' && where[k] !== '';
        });
    }
    function runSearch() {
        table.reload(queryWhere());
    }
    function fmtTime(ts) {
        ts = parseInt(ts, 10) || 0;
        if (!ts) return '—';
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
        var cover = String(d.cover || '').trim();
        var thumb = cover
            ? '<img class="vod-thumb" src="' + U.escape(cover) + '" alt="">'
            : '<span class="vod-thumb is-empty">无图</span>';
        var meta = (d.type_name ? U.escape(d.type_name) : '未分类') + ' · #' + U.escape(d.id);
        return '<div class="vod-cell">' + thumb + '<div><div class="entry-row-title">' + U.escape(d.title || '无标题') + '</div>'
            + '<div class="entry-row-meta">' + meta + '</div></div></div>';
    }

    var table = U.table({
        el: '#recycle-table',
        url: '/admin/video/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的已删影片。</p><p><button type="button" class="btn btn-muted btn-sm" id="recycle-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>回收站是空的。</p><p class="muted">从影片列表删除的片子会先放在这里，可以随时还原。不会自动清空。</p><p><a class="btn btn-muted btn-sm" href="/admin/video">去影片列表</a></p></div>';
        },
        onDraw: function (_wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            emptyBtn.hidden = list.length === 0 && !isFiltered(queryWhere());
            var reset = document.getElementById('recycle-empty-reset');
            if (reset) reset.addEventListener('click', function () { form.reset(); if (form.trash) form.trash.value = '1'; runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 部';
        },
        cols: [
            {check: true, width: 36},
            {title: '影片', html: titleHtml},
            {title: '删除时间', width: 140, html: function (d) { return fmtTime(d.deleted_at); }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-restore">还原</a><a href="#" class="btn-link js-purge">彻底删除</a>';
            }}
        ]
    });

    function selectedIds() { return table.selectedIds(); }
    function post(action, extra) {
        return U.post('/admin/video/tools/recycle/run', Object.assign({action: action}, extra || {}));
    }
    function after(res, okMsg) {
        if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
        table.refresh();
        U.toast((res && res.msg) || okMsg || '操作成功', 'ok');
    }

    U.on('#recycle-search-btn', 'click', runSearch);
    U.on('#recycle-reset-btn', 'click', function () { setTimeout(function () { if (form.trash) form.trash.value = '1'; runSearch(); }, 0); });
    U.on('#recycle-batch-restore', 'click', function () {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选影片', 'err'); return; }
        if (!U.confirm('还原已选的 ' + ids.length + ' 部到影片列表？')) return;
        post('restore', {ids: ids.join(',')}).then(function (res) { after(res, '已还原'); });
    });
    U.on('#recycle-batch-purge', 'click', function () {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选影片', 'err'); return; }
        if (!U.confirm('将彻底删除已选的 ' + ids.length + ' 部，无法恢复。确定吗？')) return;
        post('purge', {ids: ids.join(',')}).then(function (res) { after(res, '已彻底删除'); });
    });
    U.on('#recycle-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#recycle-empty-btn', 'click', function () {
        if (!U.confirm('将彻底删除回收站里的全部影片，不可恢复。确定清空？')) return;
        post('empty').then(function (res) { after(res, '已清空'); });
    });
    U.on('#recycle-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-restore')) {
            if (!U.confirm('还原「' + (row.title || '') + '」到影片列表？')) return;
            post('restore', {ids: String(row.id)}).then(function (res) { after(res, '已还原'); });
        }
        if (a.classList.contains('js-purge')) {
            if (!U.confirm('彻底删除「' + (row.title || '无标题') + '」后无法恢复，确定？')) return;
            post('purge', {ids: String(row.id)}).then(function (res) { after(res, '已彻底删除'); });
        }
    });
})();
</script>
@endpush
