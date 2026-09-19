@extends('admin.layouts.inner')
@section('title', admin_t('page.collects'))

@section('plain')
<div class="card card-panel collect-index desk-board">
    <div class="card-header">
        <span>采集源</span>
        <div>
            <button type="button" class="btn btn-sm" id="collect-source-add-btn">新增采集源</button>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="collect-source-search" onsubmit="return false;">
            <input type="hidden" name="empty_bind">
            <input type="hidden" name="has_error">
            <input type="hidden" name="status">
            <input type="hidden" name="mid">
            <input type="text" name="name" placeholder="搜名称或先贴接口" autocomplete="off">
            <button type="button" class="btn btn-sm" id="collect-source-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="collect-source-reset-btn">重置</button>
        </form>
        <div class="queue-chips" id="collect-queues">
            <button type="button" class="chip" data-queue="">全部</button>
            <button type="button" class="chip" data-queue="status" data-value="1">启用</button>
            <button type="button" class="chip" data-queue="mid" data-value="1">影片</button>
            @if($mangaReady ?? false)
                <button type="button" class="chip" data-queue="mid" data-value="2">漫画</button>
            @endif
            <button type="button" class="chip" data-queue="empty_bind" data-value="1">未绑定</button>
            <button type="button" class="chip" data-queue="has_error" data-value="1">有失败</button>
        </div>
        <p class="muted recycle-lead">影片接口填 <code>api.php/provide/vod/</code>，漫画接口填 <code>api.php/provide/manga/</code>。先绑定分类，再采当天；未绑定的分类会跳过。漫画章节地址必须是图片 URL，阅读页链接不会再去抓图。不确定接口先去「<a href="/admin/video/tools/hub">试试接口</a>」，现成的站从「<a href="/admin/video/unions">推荐资源</a>」接入。网页列表请用「<a href="/admin/video/cj">网站采集</a>」。</p>
        <div id="collect-source-table" class="desk-table"></div>
    </div>
</div>
<template id="collect-source-dialog-tpl">
    <form>
        <input type="hidden" name="id">
        <label>名称</label>
        <input type="text" name="name" placeholder="如 最大资源">
        <label>接口地址</label>
        <input type="text" name="api_url" placeholder="https://xxx/api.php/provide/vod/">
        <label>格式</label>
        <select name="api_type">
            <option value="auto">自动识别</option>
            <option value="json">JSON</option>
            <option value="xml">XML</option>
        </select>
        <label>写入到</label>
        <select name="mid">
            <option value="1">影片</option>
            @if($mangaReady ?? false)
                <option value="2">漫画</option>
            @endif
        </select>
        <p class="muted field-hint">漫画走插件库。关掉漫画插件后这里不能再采漫画。</p>
        <label>附加参数</label>
        <input type="text" name="param" placeholder="一般留空">
        <label>状态</label>
        <select name="status"><option value="1">启用</option><option value="0">禁用</option></select>
        <label>排序</label>
        <input type="number" name="sort" value="0">
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var QUEUE_KEYS = ['empty_bind', 'has_error', 'mid'];
    var form = document.getElementById('collect-source-search');

    function cleanWhere(data) {
        var out = {};
        Object.keys(data).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return where[k] !== ''; });
    }
    function markChips() {
        var status = form.status.value;
        U.qa('#collect-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '') {
                on = status === '' && QUEUE_KEYS.every(function (k) { return !form[k] || form[k].value === ''; });
            } else if (key === 'status') {
                on = status === val && QUEUE_KEYS.every(function (k) { return !form[k] || form[k].value === ''; });
            } else if (form[key]) {
                on = form[key].value === val && status === '';
            }
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        form.status.value = '';
        if (key === 'status') {
            form.status.value = value || '';
        } else if (key && form[key]) {
            form[key].value = value || '';
        }
        runSearch();
    }
    function runSearch() {
        table.reload(cleanWhere(U.formData(form)));
        markChips();
    }
    function sourceHtml(d) {
        var badges = [];
        if (parseInt(d.mid, 10) === 2) badges.push('<span class="badge badge-search">漫画</span>');
        else badges.push('<span class="badge">影片</span>');
        if (!(parseInt(d.bind_count, 10) > 0)) badges.push('<span class="badge badge-warn">未绑定</span>');
        else badges.push('<span class="badge badge-ok">已绑 ' + U.escape(d.bind_count) + ' 类</span>');
        if (d.has_error) badges.push('<span class="badge badge-off">失败</span>');
        if (d.has_break && parseInt(d.last_page, 10) > 1) badges.push('<span class="badge badge-search">断点 p' + U.escape(d.last_page) + '</span>');
        var meta = U.escape(d.api_host || d.api_url || '');
        if (d.api_type && d.api_type !== 'auto') meta += ' · ' + U.escape(d.api_type);
        if (d.last_collect_at_text) meta += ' · ' + U.escape(d.last_collect_at_text);
        else meta += ' · 从未采集';
        if (d.last_error) meta += ' · ' + U.escape(d.last_error);
        return '<div class="collect-cell"><a class="vod-title js-edit" href="#">' + U.escape(d.name || '') + '</a>'
            + '<div class="muted collect-url" title="' + U.escape(d.api_url || '') + '">' + meta + '</div>'
            + (badges.length ? '<div class="vod-badges">' + badges.join('') + '</div>' : '')
            + '</div>';
    }
    function collectHtml(d) {
        var html = '<a href="#" class="btn-link js-today">当天</a><a href="#" class="btn-link js-week">本周</a><a href="#" class="btn-link js-all">全部</a>';
        if (d.has_break) html += '<a href="#" class="btn-link js-resume">续采</a>';
        if (d.has_error) html += '<a href="#" class="btn-link js-retry">重试</a>';
        return html;
    }

    var table = U.table({
        el: '#collect-source-table',
        queueKeys: QUEUE_KEYS,
        url: '/admin/video/collects/list',
        where: cleanWhere(U.formData(form)),
        pager: false,
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的采集源</p><p><button type="button" class="btn btn-muted btn-sm" id="collect-empty-reset">清除筛选</button></p></div>';
            }
                return '<div class="list-empty"><p>还没有采集源</p><p class="muted">填苹果 CMS 兼容接口即可入库。</p><p><button type="button" class="btn btn-primary btn-sm" id="collect-empty-add">新增采集源</button></p></div>';
        },
        onDraw: function () {
            var add = document.getElementById('collect-empty-add');
            var reset = document.getElementById('collect-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; }); runSearch(); });
        },
        cols: [
            {title: '采集源', html: sourceHtml},
            {title: '状态', width: 72, html: function (d) {
                return String(d.status) === '1' ? U.status(true, '启用') : U.status(false, '禁用');
            }},
            {title: '采集', cls: 'actions collect-ops', html: collectHtml},
            {title: '操作', cls: 'actions', html: function (d) {
                return '<a href="#" class="btn-link js-bind">绑定</a>'
                    + '<a class="btn-link js-logs" href="/admin/video/collect_logs?collect_source_id=' + encodeURIComponent(d.id || '') + '">日志</a>'
                    + '<a class="btn-link" href="/admin/video/collect_temps?collect_source_id=' + encodeURIComponent(d.id || '') + '">待审</a>'
                    + '<a class="btn-link js-task" href="/admin/video/collect_tasks/create?collect_source_id=' + encodeURIComponent(d.id || '') + '">定时</a>'
                    + '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });
    markChips();

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '编辑采集源' : '新增采集源',
            content: document.getElementById('collect-source-dialog-tpl').innerHTML,
            onOpen: function (body) {
                U.fillForm(body.querySelector('form'), {
                    id: row.id || '',
                    name: row.name || '',
                    api_url: row.api_url || '',
                    api_type: row.api_type || 'auto',
                    mid: row.mid == null ? '1' : String(row.mid),
                    param: row.param || '',
                    status: row.status == null ? '1' : String(row.status),
                    sort: row.sort == null ? 0 : row.sort
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast('请填写名称', 'err'); return false; }
                if (!data.api_url) { U.toast('请填写接口地址', 'err'); return false; }
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/collects/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast('保存成功', 'ok');
                    table.refresh();
                });
            }
        });
    }

    function runCollect(row, hours, pages, confirmText) {
        if (String(row.status) === '0') { U.toast('请先启用该采集源', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.loading(true);
        U.post('/admin/video/collects/run', {id: row.id, page: 1, pages: pages || 999, hours: hours || 0}).then(function (res) {
            U.loading(false);
            table.refresh();
            U.toast((res && res.msg) || '采集完成', res && res.code === 0 ? 'ok' : 'err');
        });
    }

    function openBind(row) {
        U.loading(true);
        U.get('/admin/video/collects/classes', {id: row.id}).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '拉取分类失败', 'err'); return; }
            var data = res.data || {};
            var types = data.types || [];
            var locals = data.local_types || [];
            if (!types.length) { U.toast('接口没有返回分类', 'err'); return; }
            var html = parseInt(row.mid, 10) === 2
                ? '<p class="hint">资源站分类对到漫画分类。未绑定的入库时会跳过。章节只要图片地址，不要阅读页链接。</p>'
                : '<p class="hint">资源站分类对到本地栏目。未绑定的入库时会跳过。</p>';
            html += '<p><button type="button" class="btn btn-muted btn-sm" id="collect-suggest-btn">按同名自动绑定</button></p>';
            html += '<table class="data"><thead><tr><th>资源分类</th><th>本地栏目</th></tr></thead><tbody>';
            types.forEach(function (t) {
                html += '<tr><td>' + U.escape(t.name) + ' <span class="muted">#' + U.escape(t.remote_id) + '</span></td><td><select data-remote="' + U.escape(t.remote_id) + '"><option value="0">不采集</option>';
                locals.forEach(function (l) {
                    var pad = parseInt(l.parent_id, 10) > 0 ? '└ ' : '';
                    html += '<option value="' + U.escape(l.id) + '"' + (String(l.id) === String(t.local_id) ? ' selected' : '') + '>' + pad + U.escape(l.name) + '</option>';
                });
                html += '</select></td></tr>';
            });
            html += '</tbody></table>';
            U.dialog({
                title: '绑定分类 · ' + (row.name || ''),
                wide: true,
                okText: '保存绑定',
                content: html,
                onOpen: function (body) {
                    var btn = body.querySelector('#collect-suggest-btn');
                    if (!btn) return;
                    btn.addEventListener('click', function () {
                        U.loading(true);
                        U.post('/admin/video/collects/suggest', {id: row.id}).then(function (r) {
                            U.loading(false);
                            U.toast((r && r.msg) || '已按同名绑定', r && r.code === 0 ? 'ok' : 'err');
                            if (r && r.code === 0) {
                                body.closest('.ui-mask').remove();
                                openBind(row);
                            }
                        });
                    });
                },
                onSave: function (body) {
                    var bind = {};
                    U.qa('select[data-remote]', body).forEach(function (sel) {
                        bind[sel.getAttribute('data-remote')] = sel.value;
                    });
                    return U.post('/admin/video/collects/bind', {id: row.id, bind: JSON.stringify(bind)}).then(function (r) {
                        if (!r || r.code !== 0) { U.toast((r && r.msg) || '失败', 'err'); return false; }
                        U.toast('绑定已保存', 'ok');
                        table.refresh();
                    });
                }
            });
        });
    }

    U.on('#collect-source-search-btn', 'click', runSearch);
    U.on('#collect-source-reset-btn', 'click', function () {
        setTimeout(function () {
            QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
            runSearch();
        }, 0);
    });
    U.on('#collect-source-add-btn', 'click', function () { openDialog('add', {}); });
    document.getElementById('collect-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#collect-source-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        if (a.classList.contains('js-logs') || a.classList.contains('js-task')) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-bind')) openBind(row);
        if (a.classList.contains('js-today')) runCollect(row, 24, 999);
        if (a.classList.contains('js-week')) runCollect(row, 168, 999);
        if (a.classList.contains('js-all')) runCollect(row, 0, 999, '会按页拉完全库，可能较慢。确认采集全部？');
        if (a.classList.contains('js-resume')) {
            U.loading(true);
            U.post('/admin/video/collects/resume', {id: row.id, pages: 999, hours: 0}).then(function (res) {
                U.loading(false);
                table.refresh();
                U.toast((res && res.msg) || '续采完成', res && res.code === 0 ? 'ok' : 'err');
            });
        }
        if (a.classList.contains('js-retry')) {
            U.loading(true);
            U.post('/admin/video/collects/retry', {id: row.id}).then(function (res) {
                U.loading(false);
                table.refresh();
                U.toast((res && res.msg) || '重试完成', res && res.code === 0 ? 'ok' : 'err');
            });
        }
        if (a.classList.contains('js-del') && U.confirm('确认删除该采集源？绑定关系一并去掉。')) {
            U.post('/admin/video/collects/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('删除成功', 'ok');
            });
        }
    });
})();
</script>
@endpush
