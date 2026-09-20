@extends('admin.layouts.inner')
@section('title', admin_t('page.files'))

@php
    $ui = $ui ?? [];
    $queues = $queues ?? ['all' => 0, 'image' => 0, 'video' => 0, 'file' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $hideExtras = (bool) ($hide_extras ?? false);
    $api = is_array($api ?? null) ? $api : [];
    $api = array_merge([
        'list' => '/admin/system/attachments/list',
        'upload' => '/admin/system/attachments/upload',
        'delete' => '/admin/system/attachments/delete',
        'open' => '/admin/system/attachments/open',
    ], $api);
@endphp

@section('plain')
<div class="card card-panel file-index" id="file-index">
    <div class="card-header">
        <span>{{ $ui['title'] ?? admin_t('ui.files_title') }} <em id="file-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="upload-btn">{{ $ui['upload'] ?? admin_t('ui.upload_file') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="batch-del-btn">{{ admin_t('ui.batch_delete') }}</button>
            @unless($hideExtras)
                <a class="btn btn-muted btn-sm" href="/admin/video/templates">{{ $ui['templates'] ?? admin_t('nav.templates') }}</a>
                <a class="btn btn-muted btn-sm" href="/admin/video/settings">{{ $ui['settings'] ?? admin_t('nav.settings') }}</a>
                <a class="btn btn-muted btn-sm" href="/admin/video/tools/annex">{{ $ui['annex'] ?? admin_t('nav.annex') }}</a>
            @endunless
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ $ui['lead'] ?? '' }}</p>
        <p class="file-note">{{ $ui['note'] ?? '' }}</p>

        <form class="filter-bar" id="file-search" onsubmit="return false;">
            <input type="hidden" name="kind" value="">
            <input type="search" name="keyword" placeholder="{{ $ui['find'] ?? admin_t('ui.ph_search_file') }}" autocomplete="off" aria-label="{{ admin_t('ui.aria_search_files') }}">
            <button type="button" class="btn btn-sm" id="file-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="file-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="file-kinds">
            <button type="button" class="chip active" data-kind="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-kind="image">{{ admin_t('ui.pics') }}@if($q('image') > 0)<em>{{ $q('image') }}</em>@endif</button>
            <button type="button" class="chip" data-kind="video">{{ admin_t('ui.kind_video') }}@if($q('video') > 0)<em>{{ $q('video') }}</em>@endif</button>
            <button type="button" class="chip" data-kind="file">{{ admin_t('ui.kind_other') }}@if($q('file') > 0)<em>{{ $q('file') }}</em>@endif</button>
        </div>
        <div id="file-table"></div>
    </div>
</div>
<div class="file-lightbox" id="file-lightbox" hidden>
    <button type="button" class="file-lightbox-close" id="file-lightbox-close">{{ admin_t('ui.close') }}</button>
    <img id="file-lightbox-img" alt="">
    <p class="file-lightbox-name" id="file-lightbox-name"></p>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var ui = @json($ui, JSON_UNESCAPED_UNICODE);
    var api = {!! json_encode($api, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
    var table = U.table({
        el: '#file-table',
        url: api.list,
        cols: [
            {check: true, width: 36},
            {title: ui.preview || '预览', width: 88, cls: 'file-preview-cell', html: previewCell},
            {key: 'name', title: AdminUi.t('name'), html: nameCell},
            {key: 'kind_label', title: AdminUi.t('type'), width: 80},
            {key: 'size_text', title: AdminUi.t('size'), width: 90},
            {key: 'create_time', title: AdminUi.t('upload_time'), width: 160},
            {title: AdminUi.t('actions'), cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-open">' + AdminUi.t('open_link') + '</a><a href="#" class="btn-link js-copy">' + AdminUi.t('copy_url') + '</a><a href="#" class="btn-link js-del">' + AdminUi.t('delete') + '</a>';
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
            if (countEl) countEl.textContent = total ? String(ui.n_files || '').replace(':n', String(total)) : '';
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
            return '<button type="button" class="file-thumb js-preview" data-src="' + src + '" data-fallback="' + fallback + '" data-name="' + name + '" title="' + U.escape(ui.click_to_enlarge || '点开会放大') + '">'
                + '<img src="' + src + '" alt="' + name + '"></button>';
        }
        var label = row.is_video ? (ui.kind_video || '视频') : (row.ext || ui.kind_file || '文件');
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
            return U.upload(file, api.upload).then(function (res) {
                U.loading(false);
                if (res && res.code === 0) { U.toast(ui.uploaded || '上传成功', 'ok'); table.refresh(); }
                else U.toast((res && res.msg) || ui.upload_fail || '上传失败', 'err');
            });
        });
    });
    U.on('#batch-del-btn', 'click', function () {
        var ids = table.selectedIds();
        if (!ids.length) { U.toast(ui.please_select_files || '请勾选要删除的文件', 'err'); return; }
        if (!U.confirm(ui.confirm_batch_del_files || '确认删除选中的文件？')) return;
        U.post(api.delete, {ids: ids}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || ui.fail || '失败', 'err'); return; }
            table.refresh(); U.toast(ui.deleted || '删除成功', 'ok');
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
                    U.toast(ui.cannot_open_image || '打不开这张图', 'err');
                };
                boxImg.src = fallback;
                return;
            }
            closePreview();
            U.toast(ui.cannot_open_image || '打不开这张图', 'err');
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
            var href = row.open_url || (row.id ? api.open + '?id=' + row.id : row.url);
            if (href) window.open(href, '_blank');
            else U.toast(ui.no_usable_link || '无可用链接', 'err');
        }
        if (a.classList.contains('js-copy')) {
            var url = row.url || row.open_url || '';
            if (!url) { U.toast(ui.no_usable_link || '无可用链接', 'err'); return; }
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(function () { U.toast(AdminUi.t('copied') || ui.copied || '', 'ok'); }).catch(function () { U.toast(AdminUi.t('copy_fail') || ui.copy_fail || '', 'err'); });
            } else {
                U.toast(url, 'ok');
            }
        }
        if (a.classList.contains('js-del') && U.confirm(ui.confirm_del_file || '确认删除该文件？')) {
            U.post(api.delete, {ids: [row.id]}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || ui.fail || '失败', 'err'); return; }
                table.refresh(); U.toast(ui.deleted || '删除成功', 'ok');
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
