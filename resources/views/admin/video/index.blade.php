@extends('admin.layouts.inner')
@section('title', '视频管理')

@section('plain')
<div class="card card-panel">
    <div class="card-body">
        <form class="filter-bar" id="video-search" onsubmit="return false;">
            <input type="text" name="title" placeholder="标题">
            <select name="type_id" id="video-search-type"><option value="">分类</option></select>
            <select name="status">
                <option value="">状态</option>
                <option value="1">上架</option>
                <option value="0">下架</option>
                <option value="2">草稿</option>
                <option value="3">未通过</option>
                <option value="4">定时</option>
            </select>
            <select name="is_recommend">
                <option value="">推荐</option>
                <option value="1">是</option>
                <option value="0">否</option>
            </select>
            <select name="is_hot">
                <option value="">热门</option>
                <option value="1">是</option>
                <option value="0">否</option>
            </select>
            <select name="lock">
                <option value="">锁定</option>
                <option value="1">已锁</option>
                <option value="0">未锁</option>
            </select>
            <input type="text" name="year" placeholder="年份">
            <input type="text" name="area" placeholder="地区">
            <input type="text" name="weekday" placeholder="周期">
            <input type="number" name="points_min" placeholder="积分≥">
            <select name="empty_url"><option value="">播放地址</option><option value="1">无地址</option></select>
            <select name="repeat"><option value="">重名</option><option value="1">仅重名</option></select>
            <select name="need_points"><option value="">积分片</option><option value="1">需积分</option></select>
            <select name="has_plot"><option value="">剧情</option><option value="1">有分集剧情</option></select>
            <select name="empty_pic"><option value="">封面</option><option value="1">无封面</option></select>
            <select name="empty_content"><option value="">简介</option><option value="1">无简介</option></select>
            <select name="no_actor"><option value="">演员</option><option value="1">无演员</option></select>
            <button type="button" class="btn btn-sm" id="video-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="video-reset-btn">重置</button>
        </form>
    </div>
</div>
<div class="card card-panel">
    <div class="card-header">
        <span>影片列表</span>
        <div>
            <button type="button" class="btn btn-sm" id="video-add-btn">新增视频</button>
            <button type="button" class="btn btn-muted btn-sm" id="video-refresh-btn">刷新</button>
            <a class="btn btn-muted btn-sm" href="/admin/video/tools/recycle">回收站</a>
        </div>
    </div>
    <div class="card-body">
        <div class="toolbar">
            <button type="button" class="btn btn-sm" id="video-batch-on">批量上架</button>
            <button type="button" class="btn btn-muted btn-sm" id="video-batch-off">批量下架</button>
            <button type="button" class="btn btn-muted btn-sm" id="video-batch-rec">批量推荐</button>
            <button type="button" class="btn btn-muted btn-sm" id="video-batch-lock">批量锁定</button>
            <button type="button" class="btn btn-muted btn-sm" id="video-batch-type">改分类</button>
            <button type="button" class="btn btn-muted btn-sm" id="video-batch-points">改积分</button>
            <button type="button" class="btn btn-muted btn-sm" id="video-batch-merge">合并重复</button>
            <button type="button" class="btn btn-muted btn-sm" id="video-batch-replace-url">批量换播放地址</button>
            <button type="button" class="btn btn-danger btn-sm" id="video-batch-del">批量删除</button>
        </div>
        <div class="toolbar">
            <a class="btn btn-muted btn-sm" href="/admin/video?empty_url=1">无地址</a>
            <a class="btn btn-muted btn-sm" href="/admin/video?empty_pic=1">无封面</a>
            <a class="btn btn-muted btn-sm" href="/admin/video?empty_content=1">无简介</a>
            <a class="btn btn-muted btn-sm" href="/admin/video?no_actor=1">无演员</a>
            <a class="btn btn-muted btn-sm" href="/admin/video?status=0">待审</a>
            <a class="btn btn-muted btn-sm" href="/admin/video?status=2">草稿</a>
            <a class="btn btn-muted btn-sm" href="/admin/video?status=3">未通过</a>
            <a class="btn btn-muted btn-sm" href="/admin/video?status=4">定时</a>
            <a class="btn btn-muted btn-sm" href="/admin/video?repeat=1">重名</a>
            <a class="btn btn-muted btn-sm" href="/admin/video?has_plot=1">有分集剧情</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/tools/images">远程图片</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/tools/quality">内容质量</a>
        </div>
        <div id="video-table"></div>
    </div>
</div>
<template id="video-dialog-tpl">
    <form id="video-form">
        <input type="hidden" name="id">
        <label>标题</label>
        <input type="text" name="title">
        <label>副标题</label>
        <input type="text" name="subtitle">
        <label>分类</label>
        <select name="type_id" id="video-form-type"><option value="">请选择</option></select>
        <label>年份</label>
        <input type="text" name="year">
        <label>地区</label>
        <input type="text" name="area">
        <label>语言</label>
        <input type="text" name="lang">
        <label>周期</label>
        <input type="text" name="weekday" placeholder="一,二,三">
        <label>导演</label>
        <input type="text" name="director">
        <label>备注</label>
        <input type="text" name="remarks">
        <label>点播积分</label>
        <input type="number" name="points" value="0">
        <label>锁定</label>
        <select name="lock"><option value="0">否</option><option value="1">是</option></select>
        <label>封面</label>
        <div class="field-inline">
            <input type="text" name="cover" class="video-cover-input" placeholder="图片URL">
            <button type="button" class="btn btn-muted video-cover-upload-btn">上传</button>
        </div>
        <img class="img-preview video-cover-preview" alt="">
        <label>横幅</label>
        <div class="field-inline">
            <input type="text" name="banner" class="video-banner-input" placeholder="图片URL">
            <button type="button" class="btn btn-muted video-banner-upload-btn">上传</button>
        </div>
        <img class="img-preview video-banner-preview" alt="">
        <label>评分</label>
        <input type="number" name="score" value="0" step="0.1">
        <label>排序</label>
        <input type="number" name="sort" value="0">
        <label>状态</label>
        <select name="status">
            <option value="1">上架</option>
            <option value="0">下架</option>
            <option value="2">草稿</option>
            <option value="3">未通过</option>
            <option value="4">定时</option>
        </select>
        <label>定时发布时间</label>
        <input type="datetime-local" name="publish_at">
        <label>推荐</label>
        <select name="is_recommend"><option value="0">否</option><option value="1">是</option></select>
        <label>热门</label>
        <select name="is_hot"><option value="0">否</option><option value="1">是</option></select>
        <label>采集源</label>
        <select name="collect_source_id" id="video-form-collect-source"><option value="">无</option></select>
        <label>采集ID</label>
        <input type="text" name="collect_id">
        <label>标签</label>
        <input type="text" name="tags_text">
        <label>主演</label>
        <input type="text" name="actors_text">
        <label>简介</label>
        <textarea name="description"></textarea>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    function unixToDatetimeLocal(ts) {
        ts = parseInt(ts, 10) || 0;
        if (!ts) return '';
        var d = new Date(ts * 1000);
        var pad = function (n) { return n < 10 ? '0' + n : '' + n; };
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }
    function fillSelect(sel, options, selected, emptyLabel, disabledSuffix) {
        var html = '<option value="">' + U.escape(emptyLabel) + '</option>';
        (options || []).forEach(function (o) {
            var name = o.name || '';
            if (disabledSuffix && String(o.status) === '0') name += '（禁用）';
            html += '<option value="' + U.escape(o.id) + '">' + U.escape(name) + '</option>';
        });
        sel.innerHTML = html;
        sel.value = selected == null || selected === '' ? '' : String(selected);
    }
    var typeOptions = null, collectOptions = null;
    function loadTypes(cb) {
        if (typeOptions) { cb(typeOptions); return; }
        U.get('/admin/video/types/options').then(function (res) {
            if (res && res.code === 0) typeOptions = res.data || [];
            else typeOptions = [];
            cb(typeOptions);
        });
    }
    function loadCollects(cb) {
        if (collectOptions) { cb(collectOptions); return; }
        U.get('/admin/video/collect/options').then(function (res) {
            if (res && res.code === 0) collectOptions = res.data || [];
            else collectOptions = [];
            cb(collectOptions);
        });
    }
    loadTypes(function (opts) { fillSelect(document.getElementById('video-search-type'), opts, '', '分类'); });

    var qs = new URLSearchParams(location.search);
    var form = document.getElementById('video-search');
    ['empty_url','repeat','need_points','has_plot','empty_pic','empty_content','no_actor','weekday','status','trash'].forEach(function (k) {
        var v = qs.get(k);
        if (v && form[k]) form[k].value = v;
    });
    var where = U.formData(form);
    Object.keys(where).forEach(function (k) { if (where[k] === '') delete where[k]; });

    function statusHtml(d) {
        var map = {1: ['status-ok', '上架'], 2: ['status-warn', '草稿'], 3: ['status-off', '未通过'], 4: ['status-info', '定时'], 0: ['status-off', '下架']};
        var s = map[String(d.status)] || map[0];
        return '<span class="status ' + s[0] + '">' + s[1] + '</span>';
    }
    var table = U.table({
        el: '#video-table',
        url: '/admin/video/list',
        where: where,
        cols: [
            {check: true, width: 36},
            {key: 'id', title: 'ID', width: 70},
            {key: 'title', title: '标题'},
            {key: 'type_name', title: '分类', width: 120},
            {key: 'score', title: '评分', width: 70},
            {key: 'points', title: '积分', width: 70},
            {key: 'year', title: '年份', width: 70},
            {title: '状态', width: 80, html: statusHtml},
            {title: '推荐', width: 70, html: function (d) { return String(d.is_recommend) === '1' ? U.status(true, '是') : U.status(false, '否'); }},
            {title: '热门', width: 70, html: function (d) { return String(d.is_hot) === '1' ? '<span class="status status-warn">是</span>' : U.status(false, '否'); }},
            {key: 'updated_at_text', title: '更新时间', width: 160},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-src">线路</a><a href="#" class="btn-link js-ep">剧集</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });

    function bindImage(formEl, field) {
        var input = formEl.querySelector('input[name=' + field + ']');
        var btn = formEl.querySelector('.video-' + field + '-upload-btn');
        var preview = formEl.querySelector('.video-' + field + '-preview');
        function sync(url) {
            url = String(url || '').trim();
            if (url) { preview.src = url; preview.style.display = 'block'; }
            else { preview.removeAttribute('src'); preview.style.display = 'none'; }
        }
        sync(input.value);
        input.addEventListener('input', function () { sync(input.value); });
        btn.addEventListener('click', function () {
            U.pickFile('image/*').then(function (file) {
                if (!file) return;
                U.loading(true);
                return U.upload(file).then(function (res) {
                    U.loading(false);
                    if (res && res.code === 0 && res.data && res.data.url) {
                        input.value = res.data.url;
                        sync(res.data.url);
                        U.toast('上传成功', 'ok');
                    } else U.toast((res && res.msg) || '上传失败', 'err');
                });
            });
        });
    }

    function openVideoDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '编辑视频' : '新增视频',
            wide: true,
            content: document.getElementById('video-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var formEl = body.querySelector('form');
                U.fillForm(formEl, {
                    id: mode === 'edit' ? (row.id || '') : '',
                    title: row.title || '',
                    subtitle: row.subtitle || '',
                    cover: row.cover || '',
                    banner: row.banner || '',
                    year: row.year || '',
                    area: row.area || '',
                    lang: row.lang || '',
                    weekday: row.weekday || '',
                    director: row.director || '',
                    remarks: row.remarks || '',
                    points: row.points == null ? 0 : row.points,
                    score: row.score == null ? 0 : row.score,
                    sort: row.sort == null ? 0 : row.sort,
                    description: row.description || '',
                    collect_id: row.collect_id || '',
                    tags_text: row.tags_text || '',
                    actors_text: row.actors_text || '',
                    status: row.status == null ? 1 : row.status,
                    publish_at: unixToDatetimeLocal(row.publish_at),
                    is_recommend: row.is_recommend == null ? 0 : row.is_recommend,
                    is_hot: row.is_hot == null ? 0 : row.is_hot,
                    lock: row.lock == null ? 0 : row.lock
                });
                loadTypes(function (opts) { fillSelect(body.querySelector('#video-form-type'), opts, row.type_id, '请选择'); });
                loadCollects(function (opts) { fillSelect(body.querySelector('#video-form-collect-source'), opts, row.collect_source_id, '无', true); });
                bindImage(formEl, 'cover');
                bindImage(formEl, 'banner');
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.title) { U.toast('请输入标题', 'err'); return false; }
                return U.post('/admin/video/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return false; }
                    U.toast('保存成功', 'ok');
                    table.refresh();
                });
            }
        });
    }

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请选择数据', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast('操作成功', 'ok');
        });
    }

    U.on('#video-search-btn', 'click', function () { table.reload(U.formData(form)); });
    U.on('#video-reset-btn', 'click', function () { setTimeout(function () { table.reload({}); }, 0); });
    U.on('#video-refresh-btn', 'click', function () { table.refresh(); });
    U.on('#video-add-btn', 'click', function () { openVideoDialog('add'); });
    U.on('#video-batch-on', 'click', function () { batch('status', 1); });
    U.on('#video-batch-off', 'click', function () { batch('status', 0); });
    U.on('#video-batch-rec', 'click', function () { batch('recommend', 1); });
    U.on('#video-batch-lock', 'click', function () { batch('lock', 1); });
    U.on('#video-batch-type', 'click', function () {
        var val = U.prompt('目标分类ID');
        if (val == null || val === '') return;
        batch('type', val);
    });
    U.on('#video-batch-points', 'click', function () {
        var val = U.prompt('积分', '0');
        if (val == null) return;
        batch('points', val);
    });
    U.on('#video-batch-merge', 'click', function () {
        var ids = selectedIds();
        if (ids.length < 2) { U.toast('请至少选两部', 'err'); return; }
        var keep = U.prompt('保留的影片ID', String(Math.min.apply(null, ids.map(Number))));
        if (keep == null) return;
        batch('merge', keep, '确认把选中影片合并到 ID ' + keep + '？线路会迁过去，其余片删除。');
    });
    U.on('#video-batch-replace-url', 'click', function () {
        var ids = selectedIds();
        if (!ids.length) { U.toast('请选择数据', 'err'); return; }
        var val = U.prompt('替换播放地址 from|to');
        if (val == null) return;
        U.post('/admin/video/batch-replace-url', {value: val, ids: ids}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast('操作成功', 'ok');
        });
    });
    U.on('#video-batch-del', 'click', function () { batch('delete', '', '确认删除选中视频？将进入回收站。'); });
    U.on('#video-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) {
            U.get('/admin/video/info', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '加载失败', 'err'); return; }
                openVideoDialog('edit', res.data || row);
            });
        }
        if (a.classList.contains('js-del')) {
            if (!U.confirm('确定删除该视频吗？将进入回收站。')) return;
            U.post('/admin/video/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('删除成功', 'ok');
            });
        }
        if (a.classList.contains('js-src')) location.href = '/admin/video/sources?video_id=' + encodeURIComponent(row.id);
        if (a.classList.contains('js-ep')) location.href = '/admin/video/sources?video_id=' + encodeURIComponent(row.id) + '&open_episode=1';
    });
})();
</script>
@endpush
