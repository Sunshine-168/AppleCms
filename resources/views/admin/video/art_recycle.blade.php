@extends('admin.layouts.inner')
@section('title', $title)

@php
    $ready = (bool) ($ready ?? false);
    $count = (int) ($count ?? 0);
@endphp

@section('plain')
<div class="card card-panel recycle-index">
    <div class="card-header">
        <span>回收站 <em id="art-recycle-count"></em></span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/arts">返回文章</a>
            @if($ready && $count > 0)
                <button type="button" class="btn btn-danger btn-sm" id="art-recycle-empty">清空回收站</button>
            @endif
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="art-recycle-search" onsubmit="return false;">
            <input type="hidden" name="trash" value="1">
            <input type="search" name="q" placeholder="搜索已删内容的标题或 ID" autocomplete="off" aria-label="搜索回收站">
            <button type="button" class="btn btn-sm" id="art-recycle-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="art-recycle-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <p class="muted recycle-lead">还原后回到文章，状态与栏目保持删除前的样子。彻底删除无法恢复。</p>
        <div class="batch-bar" id="art-recycle-batch" hidden>
            <strong id="art-recycle-batch-count">已选 0 篇</strong>
            <button type="button" class="btn btn-sm" id="art-recycle-restore">还原所选</button>
            <button type="button" class="btn btn-danger btn-sm" id="art-recycle-purge">彻底删除所选</button>
            <button type="button" class="btn btn-muted btn-sm" id="art-recycle-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="art-recycle-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('art-recycle-search');
    var batchBar = document.getElementById('art-recycle-batch');
    var batchCount = document.getElementById('art-recycle-batch-count');
    var countEl = document.getElementById('art-recycle-count');
    var ready = @json($ready);

    function cleanWhere(data) {
        var out = {};
        Object.keys(data).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function queryWhere() {
        return Object.assign({limit: 15, trash: 1}, cleanWhere(U.formData(form)));
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return k !== 'limit' && k !== 'trash' && where[k] !== ''; });
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
    function runSearch() { table.reload(queryWhere()); }

    var table = U.table({
        el: '#art-recycle-table',
        countEl: countEl,
        url: '/admin/video/arts/list',
        where: queryWhere(),
        emptyHtml: function (_parsed, where) {
            if (!ready) return '<div class="list-empty"><p>请先执行数据库迁移</p></div>';
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的已删内容。</p><p><button type="button" class="btn btn-muted btn-sm" id="art-recycle-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>回收站是空的。</p><p class="muted">从文章里删除的稿件会先放在这里，可以随时还原。不会自动清空。</p><p><a class="btn btn-muted btn-sm" href="/admin/video/arts">去文章</a></p></div>';
        },
        onDraw: function (_wrap, list) {
            var reset = document.getElementById('art-recycle-empty-reset');
            if (reset) reset.addEventListener('click', function () { form.reset(); if (form.trash) form.trash.value = '1'; runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 篇';
        },
        cols: [
            {check: true, width: 36},
            {title: AdminUi.t('title'), html: function (d) {
                return '<div class="entry-row-title">' + U.escape(d.title || '无标题') + '</div>'
                    + '<div class="entry-row-meta">' + U.escape(d.type_name || '未分栏') + ' · #' + U.escape(d.id) + '</div>';
            }},
            {title: '删除时间', width: 140, html: function (d) {
                return '<span class="muted">' + U.escape(fmtTime(d.deleted_at_unix || d.deleted_at)) + '</span>';
            }},
            {title: AdminUi.t('actions'), cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-restore">还原</a><a href="#" class="btn-link js-purge">彻底删除</a>';
            }}
        ]
    });

    function selectedIds() { return table.selectedIds(); }
    function batch(action, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选内容', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/arts/batch', {ids: ids.join(','), action: action}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }
    U.on('#art-recycle-search-btn', 'click', runSearch);
    U.on('#art-recycle-reset-btn', 'click', function () { setTimeout(function () { if (form.trash) form.trash.value = '1'; runSearch(); }, 0); });
    U.on('#art-recycle-restore', 'click', function () { batch('restore', '还原已选文章到列表？'); });
    U.on('#art-recycle-purge', 'click', function () {
        var n = selectedIds().length;
        batch('purge', '将彻底删除已选的 ' + n + ' 篇，无法恢复。确定吗？');
    });
    U.on('#art-recycle-clear', 'click', function () { table.clearSelection(); });
    U.on('#art-recycle-empty', 'click', function () {
        if (!U.confirm('将彻底删除回收站里的全部文章，不可恢复。确定清空？')) return;
        U.post('/admin/video/art-recycle/empty', {}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '已清空', 'ok');
        });
    });
    U.on('#art-recycle-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        if (a.classList.contains('js-restore')) {
            e.preventDefault();
            U.post('/admin/video/arts/batch', {ids: String(row.id), action: 'restore'}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已还原', 'ok');
            });
        }
        if (a.classList.contains('js-purge')) {
            e.preventDefault();
            if (!U.confirm('彻底删除「' + (row.title || '') + '」后无法恢复，确定？')) return;
            U.post('/admin/video/arts/batch', {ids: String(row.id), action: 'purge'}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已彻底删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
