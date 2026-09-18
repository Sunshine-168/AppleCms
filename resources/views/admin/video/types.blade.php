@extends('admin.layouts.inner')
@section('title', ($scope ?? 'vod') === 'art' ? '栏目' : admin_t('page.types'))

@php
    $scope = ($scope ?? 'vod') === 'art' ? 'art' : 'vod';
    $isArt = $scope === 'art';
    $base = $isArt ? '/admin/video/art-types' : '/admin/video/types';
    $contentUrl = $isArt ? '/admin/video/arts' : '/admin/video';
    $noun = $isArt ? '栏目' : '分类';
    $unit = $isArt ? '篇' : '部';
    $contentLabel = $isArt ? '文章' : '影片';
@endphp

@section('plain')
<div class="card card-panel type-index">
    <div class="card-header">
        <span>{{ $noun }} <em id="type-count"></em></span>
            <a class="btn btn-sm" href="{{ $base }}/create">{{ $isArt ? '新建栏目' : '新增分类' }}</a>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="video-type-search" onsubmit="return false;">
            <input type="text" name="name" placeholder="搜{{ $noun }}名" autocomplete="off">
            <button type="button" class="btn btn-sm" id="video-type-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="video-type-reset-btn">重置</button>
        </form>
        <p class="muted recycle-lead">
            @if($isArt)
                这是文章自己的栏目，不是影片分类。类型可以是列表、频道、单页或外链。下级会缩进。点「添加下级」挂到这一栏下面；有文章时请先移走再删。
            @else
                下级缩进显示。先建电影 / 剧集这种一级，再在下面加动作片、国产剧。文章栏目请去「文章 → 栏目」。
            @endif
        </p>
        <div class="batch-bar" id="type-batch" hidden>
            <strong id="type-batch-count">已选 0 个</strong>
            <button type="button" class="btn btn-sm" id="type-batch-on">启用</button>
            <button type="button" class="btn btn-muted btn-sm" id="type-batch-off">禁用</button>
            <select id="type-batch-parent" class="batch-select"><option value="">改到上级</option></select>
            <button type="button" class="btn btn-muted btn-sm" id="type-batch-move">移动</button>
            <button type="button" class="btn btn-danger btn-sm" id="type-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="type-batch-clear">取消选择</button>
        </div>
        <div id="video-type-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('video-type-search');
    var batchBar = document.getElementById('type-batch');
    var batchCount = document.getElementById('type-batch-count');
    var countEl = document.getElementById('type-count');
    var isArt = @json($isArt);
    var base = @json($base);
    var contentUrl = @json($contentUrl);
    var noun = @json($noun);
    var unit = @json($unit);
    var contentLabel = @json($contentLabel);
    var countKey = isArt ? 'art_count' : 'video_count';

    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return where[k] !== ''; });
    }
    function fillParentSelect(sel, excludeId, selected, placeholder) {
        if (!sel) return;
        excludeId = parseInt(excludeId, 10) || 0;
        var skip = {};
        if (excludeId) skip[excludeId] = true;
        var rows = table.rows() || [];
        rows.forEach(function (r) {
            var id = parseInt(r.id, 10) || 0;
            var pid = parseInt(r.parent_id, 10) || 0;
            if (skip[pid]) skip[id] = true;
        });
        var html = placeholder ? '<option value="">' + U.escape(placeholder) + '</option>' : '';
        html += '<option value="0">顶级</option>';
        rows.forEach(function (r) {
            var id = parseInt(r.id, 10) || 0;
            if (skip[id]) return;
            var pad = '';
            var d = parseInt(r.depth, 10) || 0;
            while (d-- > 0) pad += '└ ';
            html += '<option value="' + U.escape(r.id) + '">' + pad + U.escape(r.name || '') + '</option>';
        });
        sel.innerHTML = html;
        if (placeholder && (selected === '' || selected == null)) {
            sel.value = '';
            return;
        }
        sel.value = selected == null || selected === '' ? '0' : String(selected);
    }
    function fillBatchParent() {
        fillParentSelect(document.getElementById('type-batch-parent'), 0, '', '改到上级');
    }
    function nameHtml(d) {
        var depth = parseInt(d.depth, 10) || 0;
        var branch = depth > 0 ? '<span class="cat-branch">└</span>' : '';
        var n = parseInt(d[countKey], 10) || 0;
        var meta = '#' + U.escape(d.id);
        if (d.slug) meta += ' · /' + U.escape(d.slug);
        if (n > 0) {
            meta += ' · <a href="' + contentUrl + '?type_id=' + encodeURIComponent(d.id) + '">' + U.escape(String(n)) + ' ' + unit + '</a>';
        } else {
            meta += ' · 0 ' + unit;
        }
        if (isArt && d.kind_label && d.kind && d.kind !== 'list') {
            meta += ' · ' + U.escape(d.kind_label);
        }
        if (parseInt(d.child_count, 10) > 0) meta += ' · ' + U.escape(d.child_count) + ' 个子类';
        return '<div class="cat-cell" style="padding-left:' + (depth * 22) + 'px">' + branch
            + '<div><a class="vod-title" href="' + base + '/' + encodeURIComponent(d.id) + '/edit">' + U.escape(d.name || '') + '</a>'
            + '<div class="muted">' + meta + '</div></div></div>';
    }

    var table = U.table({
        el: '#video-type-table',
        url: base + '/list',
        pager: false,
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合名称的' + noun + '</p><p><button type="button" class="btn btn-muted btn-sm" id="type-empty-reset">清除筛选</button></p></div>';
            }
            if (isArt) {
                return '<div class="list-empty"><p>还没有栏目。</p><p class="muted">栏目是文章的目录。先建一级栏目，再在它下面「添加下级」做多级频道。</p><p><a class="btn btn-primary btn-sm" href="' + base + '/create">新建栏目</a></p></div>';
            }
            return '<div class="list-empty"><p>还没有分类</p><p class="muted">栏目是片库的目录。先建一级，再点「下级」挂动作片、国产剧。</p><p><a class="btn btn-primary btn-sm" href="' + base + '/create">新增分类</a></p></div>';
        },
        onDraw: function (_wrap, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            fillBatchParent();
            var reset = document.getElementById('type-empty-reset');
            if (reset) reset.addEventListener('click', function () { form.reset(); table.reload({}); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 个';
        },
        cols: (function () {
            var cols = [
                {check: true, width: 36},
                {title: noun, html: nameHtml}
            ];
            if (isArt) {
                cols.push({title: '类型', width: 72, html: function (d) {
                    return d.kind_label ? '<span class="badge">' + U.escape(d.kind_label) + '</span>' : '—';
                }});
            }
            cols.push(
                {key: 'sort', title: '排序', width: 64},
                {title: '状态', width: 72, html: function (d) {
                    return String(d.status) === '1' ? U.status(true, '启用') : U.status(false, '禁用');
                }},
                {title: '操作', cls: 'actions', html: function (d) {
                    var id = encodeURIComponent(d.id);
                    var html = '';
                    var kind = String(d.kind || 'list');
                    if (isArt && String(d.status) === '1') {
                        var front = (kind === 'link' && d.jump_url) ? String(d.jump_url) : ('/art/type/' + id);
                        html += '<a href="' + U.escape(front) + '" target="_blank" rel="noopener">前台</a>';
                    }
                    html += '<a class="js-child" href="' + base + '/create?parent_id=' + id + '">' + (isArt ? '添加下级' : '下级') + '</a>';
                    html += '<a href="' + contentUrl + '?type_id=' + id + '">' + contentLabel + '</a>';
                    if (isArt && kind !== 'hub' && kind !== 'link') {
                        html += '<a href="/admin/video/arts/create?type_id=' + id + '">写文章</a>';
                    }
                    html += '<a href="' + base + '/' + id + '/edit">编辑</a>';
                    html += '<a href="#" class="js-del">删除</a>';
                    return html;
                }}
            );
            return cols;
        })()
    });

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请先勾选' + noun, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post(base + '/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || '操作成功', 'ok');
        });
    }

    U.on('#video-type-search-btn', 'click', function () {
        var where = {};
        var name = (form.name.value || '').trim();
        if (name) where.name = name;
        table.reload(where);
    });
    U.on('#video-type-reset-btn', 'click', function () {
        setTimeout(function () { table.reload({}); }, 0);
    });
    U.on('#type-batch-on', 'click', function () { batch('status', 1); });
    U.on('#type-batch-off', 'click', function () { batch('status', 0); });
    U.on('#type-batch-move', 'click', function () {
        var val = document.getElementById('type-batch-parent').value;
        if (val === '') { U.toast('请选择目标上级', 'err'); return; }
        batch('parent', val);
    });
    U.on('#type-batch-del', 'click', function () { batch('delete', '', '确认删除选中' + noun + '？有下级或' + contentLabel + '的会跳过。'); });
    U.on('#type-batch-clear', 'click', function () { table.clearSelection(); });

    U.on('#video-type-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a || !a.classList.contains('js-del')) return;
        e.preventDefault();
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        if (!U.confirm('删除「' + (row.name || '') + '」？有下级或' + contentLabel + '时无法删除。')) return;
        U.post(base + '/delete', {id: row.id}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
            table.refresh();
            U.toast('删除成功', 'ok');
        });
    });
})();
</script>
@endpush
