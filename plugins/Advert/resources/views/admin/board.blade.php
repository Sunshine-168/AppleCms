@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('nav.advert'))

@php
    $desk = in_array((string) ($desk ?? ''), ['ads', 'clicks', 'stats'], true) ? (string) $desk : 'ads';
    $period = in_array((string) request()->query('period', 'day'), ['day', 'month', 'year'], true)
        ? (string) request()->query('period', 'day')
        : 'day';
@endphp

@section('plain')
<div class="card card-panel advert-board desk-board" id="advert-board">
    <div class="card-header">
        <span>广告 <em id="advert-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="advert-add-btn" @if($desk !== 'ads') hidden @endif>{{ admin_t('ui.add') }}</button>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">文字或图片。顶栏/底栏固定，播放器在播放框下，正文在主栏顶部。展示在渲染时加，点击由前台跳转产生。javascript: 会拒绝。</p>
        <div class="queue-chips">
            <a class="chip{{ $desk === 'ads' ? ' active' : '' }}" href="/admin/video/adverts">广告位</a>
            <a class="chip{{ $desk === 'clicks' ? ' active' : '' }}" href="/admin/video/adverts?desk=clicks">点击</a>
            <a class="chip{{ $desk === 'stats' ? ' active' : '' }}" href="/admin/video/adverts?desk=stats">统计</a>
        </div>
        <form class="filter-bar" id="advert-search" onsubmit="return false;">
            <input type="hidden" name="desk" value="{{ $desk }}">
            <input type="search" name="q" placeholder="{{ $desk === 'ads' ? '搜名称、标题' : ($desk === 'stats' ? '搜广告' : '搜 IP、页面') }}" autocomplete="off">
            <select name="slot" aria-label="位置" @if($desk !== 'ads') hidden @endif>
                <option value="">全部位置</option>
                <option value="top">顶栏</option>
                <option value="bottom">底栏</option>
                <option value="player">播放器</option>
                <option value="content">正文</option>
            </select>
            <select name="status" aria-label="状态" @if($desk !== 'ads') hidden @endif>
                <option value="">全部状态</option>
                <option value="1">启用</option>
                <option value="0">停用</option>
            </select>
            <select name="period" aria-label="周期" @if($desk !== 'stats') hidden @endif>
                <option value="day" @selected($period === 'day')>按日</option>
                <option value="month" @selected($period === 'month')>按月</option>
                <option value="year" @selected($period === 'year')>按年</option>
            </select>
            <button type="button" class="btn btn-sm" id="advert-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="advert-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div id="advert-table" class="desk-table"></div>
    </div>
</div>
<template id="advert-ad-tpl">
    <form>
        <input type="hidden" name="id">
        <input type="hidden" name="desk" value="ads">
        <label>名称</label>
        <input type="text" name="name" required placeholder="如 顶栏横幅">
        <label>类型</label>
        <select name="type">
            <option value="text">文字</option>
            <option value="image">图片</option>
        </select>
        <label>位置</label>
        <select name="slot">
            <option value="top">顶栏</option>
            <option value="bottom">底栏</option>
            <option value="player">播放器</option>
            <option value="content">正文</option>
        </select>
        <label>标题</label>
        <input type="text" name="title" placeholder="可选">
        <label>网址</label>
        <input type="text" name="url" placeholder="https://">
        <p class="muted field-hint">只接受 http 或 https。空则前台跳转 404。javascript: 会拒绝。</p>
        <label>图片</label>
        <div class="field-inline">
            <input type="text" name="image" placeholder="图片地址，类型为图片时填写">
            <button type="button" class="btn btn-sm advert-image-upload">上传</button>
        </div>
        <img class="img-preview advert-image-preview" alt="">
        <label>开始时间戳</label>
        <input type="number" name="start_at" value="0">
        <label>结束时间戳</label>
        <input type="number" name="expire_at" value="0">
        <p class="muted field-hint">0 表示不限制。到点后不再展示。</p>
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
    var form = document.getElementById('advert-search');
    var countEl = document.getElementById('advert-count');
    function queryWhere() {
        var data = U.formData(form);
        var out = {desk: desk, limit: 20};
        Object.keys(data || {}).forEach(function (k) {
            if (data[k] === '' || data[k] == null) return;
            var el = form.elements[k];
            if (el && el.hidden) return;
            out[k] = data[k];
        });
        out.desk = desk;
        return out;
    }
    function emptyHtml(_p, where) {
        var filtered = Object.keys(where || {}).some(function (k) {
            return k !== 'limit' && k !== 'desk' && k !== 'period' && where[k] !== '' && where[k] != null;
        });
        if (filtered) return '<div class="list-empty"><p>没有符合条件的记录</p></div>';
        if (desk === 'ads') {
            return '<div class="list-empty"><p>还没有广告</p><p><button type="button" class="btn btn-primary btn-sm" id="advert-empty-add">新增广告</button></p></div>';
        }
        return '<div class="list-empty"><p>' + (desk === 'clicks' ? '还没有点击记录' : '还没有统计') + '</p></div>';
    }
    var cols = [];
    if (desk === 'clicks') {
        cols = [
            {title: '广告', html: function (d) { return U.escape(d.ad_name || ('#' + (d.ad_id || ''))); }},
            {title: 'IP', width: 120, html: function (d) { return U.escape(d.ip || ''); }},
            {title: '页面', html: function (d) { return U.escape(d.page || ''); }},
            {title: '时间', width: 120, html: function (d) { return U.escape(String(d.created_at || '')); }}
        ];
    } else if (desk === 'stats') {
        cols = [
            {title: '周期', width: 110, html: function (d) { return U.escape(d.period_key || ''); }},
            {title: '广告', html: function (d) { return U.escape(d.ad_name || ('#' + (d.ad_id || ''))); }},
            {title: '展示', width: 72, html: function (d) { return U.escape(String(d.impressions == null ? 0 : d.impressions)); }},
            {title: '点击', width: 72, html: function (d) { return U.escape(String(d.clicks == null ? 0 : d.clicks)); }}
        ];
    } else {
        cols = [
            {title: '名称', html: function (d) { return '<a class="js-edit" href="#">' + U.escape(d.name || '未填写') + '</a>'; }},
            {title: '位置', width: 80, html: function (d) { return U.escape(d.slot_label || ''); }},
            {title: '类型', width: 72, html: function (d) { return U.escape(d.type_label || ''); }},
            {title: '展示', width: 72, html: function (d) { return U.escape(String(d.impressions == null ? 0 : d.impressions)); }},
            {title: '点击', width: 72, html: function (d) { return U.escape(String(d.clicks == null ? 0 : d.clicks)); }},
            {title: '状态', width: 72, html: function (d) { return String(d.status) === '1' ? U.status(true, '启用') : U.status(false, '停用'); }},
            {title: '操作', cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">编辑</a><a href="#" class="btn-link js-del">删除</a>';
            }}
        ];
    }
    var table = U.table({
        el: '#advert-table',
        url: '/admin/video/adverts/list',
        where: queryWhere(),
        emptyHtml: emptyHtml,
        onDraw: function (_w, list) {
            countEl.textContent = list.length ? '· ' + list.length : '';
            var add = document.getElementById('advert-empty-add');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
        },
        cols: cols
    });
    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? '编辑广告' : '新增广告',
            content: document.getElementById('advert-ad-tpl').innerHTML,
            onOpen: function (body) {
                var formEl = body.querySelector('form');
                U.fillForm(formEl, {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    type: row.type || 'text',
                    slot: row.slot || 'top',
                    title: row.title || '',
                    url: row.url || '',
                    image: row.image || '',
                    start_at: row.start_at == null ? 0 : row.start_at,
                    expire_at: row.expire_at == null ? 0 : row.expire_at,
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? '1' : String(row.status)
                });
                U.bindImageField(formEl, {
                    input: 'input[name=image]',
                    btn: '.advert-image-upload',
                    preview: '.advert-image-preview'
                });
            },
            onSave: function (body) {
                var data = U.formData(body.querySelector('form'));
                if (!data.name) { U.toast('请填写名称', 'err'); return false; }
                data.desk = 'ads';
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/adverts/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return false; }
                    U.toast(mode === 'edit' ? '已保存' : '已创建', 'ok');
                    table.refresh();
                });
            }
        });
    }
    U.on('#advert-search-btn', 'click', function () { table.reload(queryWhere()); });
    U.on('#advert-reset-btn', 'click', function () { setTimeout(function () { table.reload(queryWhere()); }, 0); });
    U.on('#advert-add-btn', 'click', function () { openDialog('add'); });
    U.on('#advert-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm('确认删除？')) return;
            U.post('/admin/video/adverts/delete', {id: row.id, desk: 'ads'}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || '失败', 'err'); return; }
                table.refresh();
                U.toast('已删除', 'ok');
            });
        }
    });
})();
</script>
@endpush
