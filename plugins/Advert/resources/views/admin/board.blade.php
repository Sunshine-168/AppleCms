@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('nav.advert'))

@php
    $desk = in_array((string) ($desk ?? ''), ['ads', 'clicks', 'stats'], true) ? (string) $desk : 'ads';
    $period = in_array((string) request()->query('period', 'day'), ['day', 'month', 'year'], true)
        ? (string) request()->query('period', 'day')
        : 'day';
    $advertJsLang = [
        'no_match_rows' => admin_t('ui.no_match_rows'),
        'empty_ads' => admin_t('ui.empty_ads'),
        'add_ad' => admin_t('ui.add_ad'),
        'edit_ad' => admin_t('ui.edit_ad'),
        'empty_clicks' => admin_t('ui.empty_clicks'),
        'empty_stats_rows' => admin_t('ui.empty_stats_rows'),
        'please_fill_name' => admin_t('ui.please_fill_name'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'created' => admin_t('ui.created'),
        'deleted' => admin_t('ui.deleted'),
        'confirm_remove' => admin_t('ui.confirm_remove'),
    ];
@endphp

@section('plain')
<div class="card card-panel advert-board desk-board" id="advert-board">
    <div class="card-header">
        <span>{{ admin_t('nav.advert') }} <em id="advert-count"></em></span>
        <div>
            <button type="button" class="btn btn-sm" id="advert-add-btn" @if($desk !== 'ads') hidden @endif>{{ admin_t('ui.add') }}</button>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.advert_lead') }}</p>
        <div class="queue-chips">
            <a class="chip{{ $desk === 'ads' ? ' active' : '' }}" href="/admin/video/adverts">{{ admin_t('nav.advert_list') }}</a>
            <a class="chip{{ $desk === 'clicks' ? ' active' : '' }}" href="/admin/video/adverts?desk=clicks">{{ admin_t('nav.advert_clicks') }}</a>
            <a class="chip{{ $desk === 'stats' ? ' active' : '' }}" href="/admin/video/adverts?desk=stats">{{ admin_t('nav.advert_stats') }}</a>
        </div>
        <form class="filter-bar" id="advert-search" onsubmit="return false;">
            <input type="hidden" name="desk" value="{{ $desk }}">
            <input type="search" name="q" placeholder="{{ $desk === 'ads' ? admin_t('ui.ph_search_ad_name') : ($desk === 'stats' ? admin_t('ui.ph_search_ad_item') : admin_t('ui.ph_search_ad_click')) }}" autocomplete="off">
            <select name="slot" aria-label="{{ admin_t('ui.label_slot') }}" @if($desk !== 'ads') hidden @endif>
                <option value="">{{ admin_t('ui.all_positions') }}</option>
                <option value="top">{{ admin_t('ui.slot_top') }}</option>
                <option value="bottom">{{ admin_t('ui.slot_bottom_bar') }}</option>
                <option value="player">{{ admin_t('ui.slot_player_bar') }}</option>
                <option value="content">{{ admin_t('ui.slot_body') }}</option>
            </select>
            <select name="status" aria-label="{{ admin_t('ui.status') }}" @if($desk !== 'ads') hidden @endif>
                <option value="">{{ admin_t('ui.all_status') }}</option>
                <option value="1">{{ admin_t('ui.enabled') }}</option>
                <option value="0">{{ admin_t('ui.disabled') }}</option>
            </select>
            <select name="period" aria-label="{{ admin_t('ui.period') }}" @if($desk !== 'stats') hidden @endif>
                <option value="day" @selected($period === 'day')>{{ admin_t('ui.by_day') }}</option>
                <option value="month" @selected($period === 'month')>{{ admin_t('ui.by_month') }}</option>
                <option value="year" @selected($period === 'year')>{{ admin_t('ui.by_year') }}</option>
            </select>
            <button type="button" class="btn btn-sm" id="advert-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="advert-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div id="advert-table" class="desk-table"></div>
    </div>
</div>
<template id="advert-ad-tpl">
    <form class="admin-form">
        <input type="hidden" name="id">
        <input type="hidden" name="desk" value="ads">
        <label>{{ admin_t('ui.name') }}</label>
        <input type="text" name="name" required placeholder="{{ admin_t('ui.ph_ad_banner') }}">
        <label>{{ admin_t('ui.col_type') }}</label>
        <select name="type">
            <option value="text">{{ admin_t('ui.kind_text') }}</option>
            <option value="image">{{ admin_t('ui.kind_image') }}</option>
        </select>
        <label>{{ admin_t('ui.label_slot') }}</label>
        <select name="slot">
            <option value="top">{{ admin_t('ui.slot_top') }}</option>
            <option value="bottom">{{ admin_t('ui.slot_bottom_bar') }}</option>
            <option value="player">{{ admin_t('ui.slot_player_bar') }}</option>
            <option value="content">{{ admin_t('ui.slot_body') }}</option>
        </select>
        <label>{{ admin_t('ui.title_label') }}</label>
        <input type="text" name="title" placeholder="{{ admin_t('ui.optional') }}">
        <label>{{ admin_t('ui.label_url') }}</label>
        <input type="text" name="url" placeholder="{{ admin_t('ui.ph_https') }}">
        <p class="muted field-hint">{{ admin_t('ui.ad_https_hint') }}</p>
        <label>{{ admin_t('ui.kind_image') }}</label>
        <div class="field-inline">
            <input type="text" name="image" placeholder="{{ admin_t('ui.ph_image_if_image') }}">
            <button type="button" class="btn btn-sm advert-image-upload">{{ admin_t('ui.upload') }}</button>
        </div>
        <img class="img-preview advert-image-preview" alt="">
        <label>{{ admin_t('ui.start_unix') }}</label>
        <input type="number" name="start_at" value="0">
        <label>{{ admin_t('ui.end_unix') }}</label>
        <input type="number" name="expire_at" value="0">
        <p class="muted field-hint">{{ admin_t('ui.zero_unlimited') }}</p>
        <label>{{ admin_t('ui.sort') }}</label>
        <input type="number" name="sort" value="0">
        <label>{{ admin_t('ui.status') }}</label>
        <select name="status">
            <option value="1">{{ admin_t('ui.enabled') }}</option>
            <option value="0">{{ admin_t('ui.disabled') }}</option>
        </select>
    </form>
</template>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($advertJsLang, JSON_UNESCAPED_UNICODE);
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
        if (filtered) return '<div class="list-empty"><p>' + L.no_match_rows + '</p></div>';
        if (desk === 'ads') {
            return '<div class="list-empty"><p>' + L.empty_ads + '</p><p><button type="button" class="btn btn-primary btn-sm" id="advert-empty-add">' + L.add_ad + '</button></p></div>';
        }
        return '<div class="list-empty"><p>' + (desk === 'clicks' ? L.empty_clicks : L.empty_stats_rows) + '</p></div>';
    }
    var cols = [];
    if (desk === 'clicks') {
        cols = [
            {title: AdminUi.t('ads'), html: function (d) { return U.escape(d.ad_name || ('#' + (d.ad_id || ''))); }},
            {title: 'IP', width: 120, html: function (d) { return U.escape(d.ip || ''); }},
            {title: AdminUi.t('pages'), html: function (d) { return U.escape(d.page || ''); }},
            {title: AdminUi.t('time'), width: 120, html: function (d) { return U.escape(String(d.created_at || '')); }}
        ];
    } else if (desk === 'stats') {
        cols = [
            {title: AdminUi.t('period'), width: 110, html: function (d) { return U.escape(d.period_key || ''); }},
            {title: AdminUi.t('ads'), html: function (d) { return U.escape(d.ad_name || ('#' + (d.ad_id || ''))); }},
            {title: AdminUi.t('impressions'), width: 72, html: function (d) { return U.escape(String(d.impressions == null ? 0 : d.impressions)); }},
            {title: AdminUi.t('clicks'), width: 72, html: function (d) { return U.escape(String(d.clicks == null ? 0 : d.clicks)); }}
        ];
    } else {
        cols = [
            {title: AdminUi.t('name'), html: function (d) { return '<a class="js-edit" href="#">' + U.escape(d.name || AdminUi.t('not_filled')) + '</a>'; }},
            {title: AdminUi.t('label_slot'), width: 80, html: function (d) { return U.escape(d.slot_label || ''); }},
            {title: AdminUi.t('type'), width: 72, html: function (d) { return U.escape(d.type_label || ''); }},
            {title: AdminUi.t('impressions'), width: 72, html: function (d) { return U.escape(String(d.impressions == null ? 0 : d.impressions)); }},
            {title: AdminUi.t('clicks'), width: 72, html: function (d) { return U.escape(String(d.clicks == null ? 0 : d.clicks)); }},
            {title: AdminUi.t('status'), width: 72, html: function (d) { return String(d.status) === '1' ? U.status(true, AdminUi.t('enabled')) : U.status(false, AdminUi.t('disabled')); }},
            {title: AdminUi.t('actions'), cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-edit">' + AdminUi.t('edit') + '</a><a href="#" class="btn-link js-del">' + AdminUi.t('delete') + '</a>';
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
            title: mode === 'edit' ? L.edit_ad : L.add_ad,
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
                if (!data.name) { U.toast(L.please_fill_name, 'err'); return false; }
                data.desk = 'ads';
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/adverts/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    U.toast(mode === 'edit' ? L.saved : L.created, 'ok');
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
            if (!U.confirm(L.confirm_remove)) return;
            U.post('/admin/video/adverts/delete', {id: row.id, desk: 'ads'}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
