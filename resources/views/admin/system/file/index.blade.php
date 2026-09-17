@extends('admin.layouts.inner')
@section('title', admin_t('page.files'))

@php
    $ui = $ui ?? [];
    $queues = $queues ?? ['all' => 0, 'image' => 0, 'video' => 0, 'file' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
@endphp

@section('plain')
<div class="card card-panel file-index" id="file-index">
    <div class="card-header">
        <span>{{ $ui['title'] ?? '附件' }} <em id="file-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="upload-btn">{{ $ui['upload'] ?? '上传文件' }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="batch-del-btn">批量删除</button>
            <a class="btn btn-muted btn-sm" href="/admin/video/templates">{{ $ui['templates'] ?? '模板' }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/settings">{{ $ui['settings'] ?? '站点设置' }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/tools/annex">{{ $ui['annex'] ?? '附件清理' }}</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ $ui['lead'] ?? '' }}</p>
        <p class="file-note">{{ $ui['note'] ?? '' }}</p>

        <form class="filter-bar" id="file-search" onsubmit="return false;">
            <input type="hidden" name="kind" value="">
            <input type="search" name="keyword" placeholder="{{ $ui['find'] ?? '搜名称、类型或地址' }}" autocomplete="off" aria-label="搜索附件">
            <button type="button" class="btn btn-sm" id="file-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="file-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="file-kinds">
            <button type="button" class="chip active" data-kind="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-kind="image">图片@if($q('image') > 0)<em>{{ $q('image') }}</em>@endif</button>
            <button type="button" class="chip" data-kind="video">视频@if($q('video') > 0)<em>{{ $q('video') }}</em>@endif</button>
            <button type="button" class="chip" data-kind="file">其它@if($q('file') > 0)<em>{{ $q('file') }}</em>@endif</button>
        </div>
        <div id="file-table"></div>
    </div>
</div>
<div class="file-lightbox" id="file-lightbox" hidden>
    <button type="button" class="file-lightbox-close" id="file-lightbox-close">关闭</button>
    <img id="file-lightbox-img" alt="">
    <p class="file-lightbox-name" id="file-lightbox-name"></p>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var ui = @json($ui);
    var table = U.table({
        el: '#file-table',
        url: '/admin/system/attachments/list',
        cols: [
            {check: true, width: 36},
            {title: ui.preview || '预览', width: 88, cls: 'file-preview-cell', html: previewCell},
            {key: 'name', title: '名称', html: nameCell},
            {key: 'kind_label', title: '类型', width: 80},
            {key: 'size_text', title: '大小', width: 90},
            {key: 'create_time', title: '上传时间', width: 160},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-open">打开</a><a href="#" class="btn-link js-copy">复制地址</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ],
        emptyHtml: function (parsed, where) {
            if (where && String(where.keyword || '').trim()) {
                return '<div class="list-empty"><p>' + U.escape(ui.empty_search || '没有叫这个名字的文件') + '</p></div>';
            }
            if (where && String(where.kind || '').trim()) {
                return '<div class="list-empty"><p>' + U.escape(ui.empty_kind || '这一类还没有文件') + '</p></div>';
            }
            return '<div class="list-empty"><p>' + U.escape(ui.empty || '还没有附件') + '</p><p class="muted">' + U.escape(ui.empty_hint || '点右上角上传。图片会出现缩略图。') + '</p></div>';
        },
        onDraw: function (wrap, list, parsed) {
            var total = (parsed && parsed.total) || 0;
            var countEl = document.getElementById('file-count');
            if (countEl) countEl.textContent = total ? ('共 ' + total + ' 个') : '';
            var queues = (parsed && parsed.queues) || {};
            U.qa('#file-kinds .chip').forEach(function (chip) {
                var key = chip.getAttribute('data-kind') || 'all';
                var n = parseInt(queues[key], 10) || 0;
                var em = chip.querySelector('em');
                if (n > 0) {
                    if (!em) {
                        em = document.createElement('em');
                        chip.appendChild(em);
                    }
                    em.textContent = String(n);
                } else if (em) {
                    em.remove();
                }
            });
        }
    });

    function previewCell(row) {
        if (row.is_image && row.preview_url) {
            var src = U.escape(row.preview_url);
            var name = U.escape(row.name || '');
            var fallback = U.escape(row.open_url || row.preview_url);
            return '<button type="button" class="file-thumb js-preview" data-src="' + src + '" data-fallback="' + fallback + '" data-name="' + name + '" title="点开会放大">'
                + '<img src="' + src + '" alt="' + name + '"></button>';
        }
        var label = row.is_video ? '视频' : (row.ext || '文件');
        return '<span class="file-kind' + (row.is_video ? ' is-video' : '') + '">' + U.escape(label) + '</span>'
            + '<span class="muted file-no-preview">' + U.escape(ui.no_preview || '不能预览') + '</span>';
    }
    function nameCell(row) {
        return '<div class="file-name"><strong>' + U.escape(row.name || '') + '</strong>'
            + '<span class="muted">' + U.escape(row.url || '') + '</span></div>';
    }
    function currentWhere() {
        return U.formData('#file-search');
    }
    function reload() {
        table.reload(currentWhere());
    }
    U.qa('#file-kinds .chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            U.qa('#file-kinds .chip').forEach(function (c) { c.classList.remove('active'); });
            chip.classList.add('active');
            var kind = document.querySelector('#file-search [name=kind]');
            if (kind) kind.value = chip.getAttribute('data-kind') || '';
            reload();
        });
    });
    U.on('#file-search-btn', 'click', function () { reload(); });
    U.on('#file-reset-btn', 'click', function () {
        setTimeout(function () {
            U.qa('#file-kinds .chip').forEach(function (c) { c.classList.toggle('active', c.getAttribute('data-kind') === ''); });
            reload();
        }, 0);
    });
    U.on('#upload-btn', 'click', function () {
        U.pickFile('*/*').then(function (file) {
            if (!file) return;
            U.loading(true);
            return U.upload(file).then(function (res) {
                U.loading(false);
                if (res && res.code === 0) { U.toast('上传成功', 'ok'); table.refresh(); }
                else U.toast((res && res.msg) || '上传失败', 'err');
            });
        });
    });
    U.on('#batch-del-btn', 'click', function () {
        var ids = table.selectedIds();
        if (!ids.length) { U.toast('请勾选要删除的文件', 'err'); return; }
        if (!U.confirm('确认删除选中的文件？')) return;
        U.post('/admin/system/attachments/delete', {ids: ids}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
            table.refresh(); U.toast('删除成功', 'ok');
        });
    });

    var box = document.getElementById('file-lightbox');
    var boxImg = document.getElementById('file-lightbox-img');
    var boxName = document.getElementById('file-lightbox-name');
    function closePreview() {
        if (!box) return;
        box.hidden = true;
        if (boxImg) boxImg.removeAttribute('src');
    }
    function openPreview(src, name, fallback) {
        if (!box || !boxImg) return;
        boxName.textContent = name || '';
        boxImg.alt = name || '';
        boxImg.onerror = function () {
            if (fallback && fallback !== src) {
                boxImg.onerror = function () {
                    closePreview();
                    U.toast('打不开这张图', 'err');
                };
                boxImg.src = fallback;
                return;
            }
            closePreview();
            U.toast('打不开这张图', 'err');
        };
        boxImg.src = src;
        box.hidden = false;
    }
    U.on('#file-lightbox-close', 'click', closePreview);
    if (box) {
        box.addEventListener('click', function (e) {
            if (e.target === box) closePreview();
        });
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && box && !box.hidden) closePreview();
    });

    U.on('#file-table', 'click', function (e) {
        var previewBtn = e.target.closest('.js-preview');
        if (previewBtn) {
            e.preventDefault();
            openPreview(
                previewBtn.getAttribute('data-src') || '',
                previewBtn.getAttribute('data-name') || '',
                previewBtn.getAttribute('data-fallback') || ''
            );
            return;
        }
        var a = e.target.closest('a'); if (!a) return;
        var tr = e.target.closest('tr');
        if (!tr) return;
        var row = (table.rows() || [])[tr.getAttribute('data-idx')];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-open')) {
            var href = row.open_url || (row.id ? '/admin/system/attachments/open?id=' + row.id : row.url);
            if (href) window.open(href, '_blank');
            else U.toast('无可用链接', 'err');
        }
        if (a.classList.contains('js-copy')) {
            var url = row.url || row.open_url || '';
            if (!url) { U.toast('无可用链接', 'err'); return; }
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(function () { U.toast('已复制地址', 'ok'); }).catch(function () { U.toast('复制失败', 'err'); });
            } else {
                U.toast(url, 'ok');
            }
        }
        if (a.classList.contains('js-del') && U.confirm('确认删除该文件？')) {
            U.post('/admin/system/attachments/delete', {ids: [row.id]}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh(); U.toast('删除成功', 'ok');
            });
        }
    });
    var tableEl = document.getElementById('file-table');
    if (tableEl) {
        tableEl.addEventListener('error', function (e) {
            var img = e.target;
            if (!img || img.tagName !== 'IMG') return;
            var wrap = img.closest('.file-thumb');
            if (!wrap || wrap.classList.contains('is-broken')) return;
            var fallback = wrap.getAttribute('data-fallback') || '';
            var src = wrap.getAttribute('data-src') || '';
            if (fallback && fallback !== src && img.getAttribute('src') !== fallback) {
                img.src = fallback;
                return;
            }
            wrap.classList.add('is-broken');
        }, true);
    }
})();
</script>
@endpush
