@extends('admin.layouts.inner')
@section('title', admin_t('page.videos'))

@php
    $queues = $queues ?? ['all' => 0, 'pending' => 0, 'empty_url' => 0, 'empty_pic' => 0, 'repeat' => 0];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
@endphp

@section('plain')
<div class="card card-panel video-index">
    <div class="card-header">
        <span>影片列表</span>
        <div>
            <button type="button" class="btn btn-sm" id="video-add-btn">新增影片</button>
            <a class="btn btn-muted btn-sm" href="/admin/video/tools/recycle">回收站</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="video-search" onsubmit="return false;">
            <input type="hidden" name="empty_url">
            <input type="hidden" name="empty_pic">
            <input type="hidden" name="empty_content">
            <input type="hidden" name="no_actor">
            <input type="hidden" name="repeat">
            <input type="hidden" name="need_points">
            <input type="hidden" name="has_plot">
            <input type="hidden" name="actor_id">
            <input type="hidden" name="tag_id">
            <input type="text" name="title" placeholder="搜标题" autocomplete="off">
            <select name="type_id" id="video-search-type"><option value="">分类</option></select>
            <select name="status">
                <option value="">状态</option>
                <option value="1">上架</option>
                <option value="0">下架</option>
                <option value="2">草稿</option>
                <option value="3">未通过</option>
                <option value="4">定时</option>
            </select>
            <button type="button" class="btn btn-sm" id="video-search-btn">查询</button>
            <button type="reset" class="btn btn-muted btn-sm" id="video-reset-btn">重置</button>
            <button type="button" class="btn btn-muted btn-sm" id="video-more-toggle">更多筛选</button>
            <div class="filter-more" id="video-filter-more">
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
            </div>
        </form>

        <div class="queue-chips" id="video-queues">
            <button type="button" class="chip" data-queue="">全部@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">下架@if($q('pending') > 0)<em>{{ $q('pending') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="empty_url" data-value="1">无地址@if($q('empty_url') > 0)<em>{{ $q('empty_url') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="empty_pic" data-value="1">无封面@if($q('empty_pic') > 0)<em>{{ $q('empty_pic') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="repeat" data-value="1">重名@if($q('repeat') > 0)<em>{{ $q('repeat') }}</em>@endif</button>
        </div>
        <details class="queue-more">
            <summary>补资料与工具</summary>
            <div class="queue-chips">
                <button type="button" class="chip" data-queue="empty_content" data-value="1">无简介</button>
                <button type="button" class="chip" data-queue="no_actor" data-value="1">无演员</button>
                <button type="button" class="chip" data-queue="status" data-value="2">草稿</button>
                <button type="button" class="chip" data-queue="status" data-value="3">未通过</button>
                <button type="button" class="chip" data-queue="status" data-value="4">定时</button>
                <button type="button" class="chip" data-queue="need_points" data-value="1">需积分</button>
                <button type="button" class="chip" data-queue="has_plot" data-value="1">有分集剧情</button>
                <a class="chip" href="/admin/video/tools/images">远程图片</a>
                <a class="chip" href="/admin/video/tools/quality">内容质量</a>
            </div>
        </details>

        <div class="batch-bar" id="video-batch" hidden>
            <strong id="video-batch-count">已选 0 部</strong>
            <button type="button" class="btn btn-sm" id="video-batch-on">上架</button>
            <button type="button" class="btn btn-muted btn-sm" id="video-batch-off">下架</button>
            <button type="button" class="btn btn-muted btn-sm" id="video-batch-rec">推荐</button>
            <button type="button" class="btn btn-muted btn-sm" id="video-batch-lock">锁定</button>
            <select id="video-batch-type" class="batch-select"><option value="">改到分类</option></select>
            <button type="button" class="btn btn-muted btn-sm" id="video-batch-type-go">改分类</button>
            <button type="button" class="btn btn-muted btn-sm" id="video-batch-points">改积分</button>
            <button type="button" class="btn btn-muted btn-sm" id="video-batch-merge">合并重复</button>
            <button type="button" class="btn btn-muted btn-sm" id="video-batch-replace-url">换播放地址</button>
            <button type="button" class="btn btn-danger btn-sm" id="video-batch-del">删除</button>
            <button type="button" class="btn btn-muted btn-sm" id="video-batch-clear">取消选择</button>
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
    var QUEUE_KEYS = ['empty_url', 'empty_pic', 'empty_content', 'no_actor', 'repeat', 'need_points', 'has_plot'];
    var form = document.getElementById('video-search');
    var moreBox = document.getElementById('video-filter-more');
    var batchBar = document.getElementById('video-batch');
    var batchCount = document.getElementById('video-batch-count');

    function unixToDatetimeLocal(ts) {
        ts = parseInt(ts, 10) || 0;
        if (!ts) return '';
        var d = new Date(ts * 1000);
        var pad = function (n) { return n < 10 ? '0' + n : '' + n; };
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }
    function fillSelect(sel, options, selected, emptyLabel, disabledSuffix) {
        if (!sel) return;
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
            typeOptions = (res && res.code === 0) ? (res.data || []) : [];
            cb(typeOptions);
        });
    }
    function loadCollects(cb) {
        if (collectOptions) { cb(collectOptions); return; }
        U.get('/admin/video/collect/options').then(function (res) {
            collectOptions = (res && res.code === 0) ? (res.data || []) : [];
            cb(collectOptions);
        });
    }
    loadTypes(function (opts) {
        fillSelect(document.getElementById('video-search-type'), opts, '', '分类');
        fillSelect(document.getElementById('video-batch-type'), opts, '', '改到分类');
        var qsType = new URLSearchParams(location.search).get('type_id');
        if (qsType) document.getElementById('video-search-type').value = qsType;
    });

    var qs = new URLSearchParams(location.search);
    ['title','type_id','status','is_recommend','is_hot','lock','year','area','weekday','points_min','actor_id','tag_id'].concat(QUEUE_KEYS).forEach(function (k) {
        var v = qs.get(k);
        if (v && form[k]) form[k].value = v;
    });
    if (['is_recommend','is_hot','lock','year','area','weekday','points_min'].some(function (k) { return qs.get(k); })) {
        moreBox.classList.add('is-open');
        document.getElementById('video-more-toggle').classList.add('is-on');
    }
    if (['empty_content','no_actor','need_points','has_plot'].some(function (k) { return qs.get(k); })
        || ['2','3','4'].indexOf(qs.get('status') || '') >= 0) {
        document.querySelector('.queue-more').open = true;
    }

    function cleanWhere(data) {
        var out = {};
        Object.keys(data).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function syncUrl(where) {
        var qs = new URLSearchParams();
        Object.keys(where).forEach(function (k) { qs.set(k, where[k]); });
        var s = qs.toString();
        history.replaceState(null, '', s ? (location.pathname + '?' + s) : location.pathname);
    }
    function markChips() {
        var status = form.status.value;
        var active = '';
        QUEUE_KEYS.forEach(function (k) {
            if (form[k] && form[k].value === '1') active = k;
        });
        U.qa('#video-queues .chip, .queue-more .chip[data-queue]').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && !active && status === '') on = true;
            else if (key === 'status' && !active && status === val) on = true;
            else if (key && key !== 'status' && active === key) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        if (key === 'status') {
            form.status.value = value || '';
        } else {
            if (key === '') form.status.value = '';
            if (key && form[key]) form[key].value = value || '1';
        }
        runSearch();
    }
    function runSearch() {
        var where = cleanWhere(U.formData(form));
        table.reload(where);
        syncUrl(where);
        markChips();
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return where[k] !== ''; });
    }

    function statusHtml(d) {
        var map = {1: ['status-ok', '上架'], 2: ['status-warn', '草稿'], 3: ['status-off', '未通过'], 4: ['status-info', '定时'], 0: ['status-off', '下架']};
        var s = map[String(d.status)] || map[0];
        return '<span class="status ' + s[0] + '">' + s[1] + '</span>';
    }
    function titleHtml(d) {
        var cover = String(d.cover || '').trim();
        var thumb = cover
            ? '<img class="vod-thumb" src="' + U.escape(cover) + '" alt="">'
            : '<span class="vod-thumb is-empty">无图</span>';
        var badges = [];
        if (!cover) badges.push('<span class="badge badge-warn">无封面</span>');
        if (!d.has_play) badges.push('<span class="badge badge-tool">无地址</span>');
        if (String(d.is_recommend) === '1') badges.push('<span class="badge badge-ok">荐</span>');
        if (String(d.is_hot) === '1') badges.push('<span class="badge badge-search">热</span>');
        if (String(d.lock) === '1') badges.push('<span class="badge badge-off">锁</span>');
        var meta = U.escape(d.type_name || '未分类');
        if (d.year) meta += ' · ' + U.escape(d.year);
        if (d.hits) meta += ' · ' + U.escape(d.hits) + ' 次';
        return '<div class="vod-cell">' + thumb + '<div><a class="vod-title js-edit" href="#">' + U.escape(d.title || '') + '</a>'
            + '<div class="muted">' + meta + '</div>'
            + (badges.length ? '<div class="vod-badges">' + badges.join('') + '</div>' : '')
            + '</div></div>';
    }

    var table = U.table({
        el: '#video-table',
        url: '/admin/video/list',
        where: cleanWhere(U.formData(form)),
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>没有符合条件的影片</p><p class="muted">换个关键词，或清掉待办筛选。</p><p><button type="button" class="btn btn-muted btn-sm" id="video-empty-reset">清除筛选</button></p></div>';
            }
            return '<div class="list-empty"><p>片库还是空的</p><p class="muted">先接一个采集源，或手动加一部片子。</p><p><a class="btn btn-primary btn-sm" href="/admin/video/collects">去采集</a> <button type="button" class="btn btn-muted btn-sm" id="video-empty-add">新增影片</button></p></div>';
        },
        onDraw: function () {
            var add = document.getElementById('video-empty-add');
            var reset = document.getElementById('video-empty-reset');
            if (add) add.addEventListener('click', function () { openVideoDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; }); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = '已选 ' + ids.length + ' 部';
        },
        cols: [
            {check: true, width: 36},
            {title: '影片', html: titleHtml},
            {title: '状态', width: 80, html: statusHtml},
            {key: 'points', title: '积分', width: 70},
            {key: 'updated_at_text', title: '更新', width: 160},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-src">线路</a><a href="#" class="btn-link js-ep">剧集</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ]
    });
    markChips();

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
            title: mode === 'edit' ? '编辑影片' : '新增影片',
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
        if (!ids.length) { U.toast('请先勾选影片', 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast('操作成功', 'ok');
        });
    }

    U.on('#video-search-btn', 'click', runSearch);
    U.on('#video-reset-btn', 'click', function () {
        setTimeout(function () {
            QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
            runSearch();
        }, 0);
    });
    U.on('#video-more-toggle', 'click', function () {
        moreBox.classList.toggle('is-open');
        this.classList.toggle('is-on', moreBox.classList.contains('is-open'));
    });
    U.on('#video-add-btn', 'click', function () { openVideoDialog('add'); });
    document.getElementById('video-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip || chip.tagName === 'A') return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    document.querySelector('.queue-more').addEventListener('click', function (e) {
        var chip = e.target.closest('button[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });

    U.on('#video-batch-on', 'click', function () { batch('status', 1); });
    U.on('#video-batch-off', 'click', function () { batch('status', 0); });
    U.on('#video-batch-rec', 'click', function () { batch('recommend', 1); });
    U.on('#video-batch-lock', 'click', function () { batch('lock', 1); });
    U.on('#video-batch-type-go', 'click', function () {
        var val = document.getElementById('video-batch-type').value;
        if (!val) { U.toast('请选择目标分类', 'err'); return; }
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
        if (!ids.length) { U.toast('请先勾选影片', 'err'); return; }
        var val = U.prompt('替换播放地址 from|to');
        if (val == null) return;
        U.post('/admin/video/batch-replace-url', {value: val, ids: ids}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || '操作失败', 'err'); return; }
            table.refresh();
            U.toast('操作成功', 'ok');
        });
    });
    U.on('#video-batch-del', 'click', function () { batch('delete', '', '确认删除选中影片？将进入回收站。'); });
    U.on('#video-batch-clear', 'click', function () { table.clearSelection(); });

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
            if (!U.confirm('确定删除该影片吗？将进入回收站。')) return;
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
