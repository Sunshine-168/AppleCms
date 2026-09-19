@extends('admin.layouts.inner')
@section('title', $title)

@php
    $queues = $queues ?? ['all' => 0, 'header' => 0, 'footer' => 0, 'play' => 0, 'expired' => 0, 'off' => 0];
    $types = $types ?? [];
    $q = fn (string $k) => (int) ($queues[$k] ?? 0);
    $adJsLang = [
        'status' => admin_t('ui.status'),
        'actions' => admin_t('ui.actions'),
        'edit' => admin_t('ui.edit'),
        'delete' => admin_t('ui.delete'),
        'enabled' => admin_t('ui.enabled'),
        'disabled' => admin_t('ui.disabled'),
        'fail' => admin_t('ui.fail'),
        'saved' => admin_t('ui.saved'),
        'created' => admin_t('ui.created'),
        'deleted' => admin_t('ui.deleted'),
        'copy' => admin_t('ui.copy'),
        'copied' => admin_t('ui.copied'),
        'sort' => admin_t('ui.sort'),
        'clear_filter' => admin_t('ui.clear_filter'),
        'selected_rows' => admin_t('ui.selected_rows', ['n' => '__N__']),
        'op_fail' => admin_t('manga.op_fail'),
        'op_ok' => admin_t('manga.op_ok'),
        'unnamed' => admin_t('ui.unnamed'),
        'all_categories' => admin_t('ui.all_categories'),
        'ad_expired' => admin_t('ui.ad_expired'),
        'no_expire' => admin_t('ui.no_expire'),
        'thumb_code' => admin_t('ui.thumb_code'),
        'copy_call' => admin_t('ui.copy_call'),
        'col_ad' => admin_t('ui.col_ad'),
        'add_ad' => admin_t('ui.add_ad'),
        'edit_ad' => admin_t('ui.edit_ad'),
        'empty_ads' => admin_t('ui.empty_ads'),
        'empty_ads_hint' => admin_t('ui.empty_ads_hint'),
        'no_match_ads' => admin_t('ui.no_match_ads'),
        'please_select_ads' => admin_t('ui.please_select_ads'),
        'please_fill_name' => admin_t('ui.please_fill_name'),
        'please_fill_slot' => admin_t('ui.please_fill_slot'),
        'please_pick_slot' => admin_t('ui.please_pick_slot'),
        'confirm_batch_del_ads' => admin_t('ui.confirm_batch_del_ads'),
        'confirm_del_ad' => admin_t('ui.confirm_del_ad', ['name' => '__NAME__']),
        'inserted_ok' => admin_t('ui.inserted_ok'),
        'upload_fail' => admin_t('ui.upload_fail'),
        'copied_call' => admin_t('ui.copied_call'),
    ];
@endphp

@section('plain')
<div class="card card-panel ad-index">
    <div class="card-header">
        <span>{{ admin_t('ui.ads') }} <em id="ad-count"></em></span>
        <button type="button" class="btn btn-sm" id="ad-add-btn">{{ admin_t('ui.add_ad') }}</button>
    </div>
    <div class="card-body">
        <form class="filter-bar" id="ad-search" onsubmit="return false;">
            <input type="hidden" name="slot">
            <input type="hidden" name="expired">
            <input type="text" name="name" placeholder="{{ admin_t('ui.ph_ad') }}" autocomplete="off" aria-label="{{ admin_t('ui.ads') }}">
            <select name="status" aria-label="{{ admin_t('ui.status') }}">
                <option value="">{{ admin_t('ui.status') }}</option>
                <option value="1">{{ admin_t('ui.enabled') }}</option>
                <option value="0">{{ admin_t('ui.disabled') }}</option>
            </select>
            <button type="button" class="btn btn-sm" id="ad-search-btn">{{ admin_t('ui.search') }}</button>
            <button type="reset" class="btn btn-muted btn-sm" id="ad-reset-btn">{{ admin_t('ui.reset') }}</button>
        </form>
        <div class="queue-chips" id="ad-queues">
            <button type="button" class="chip" data-queue="">{{ admin_t('ui.all') }}@if($q('all') > 0)<em>{{ $q('all') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="slot" data-value="header">{{ admin_t('ui.slot_header') }}@if($q('header') > 0)<em>{{ $q('header') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="slot" data-value="footer">{{ admin_t('ui.slot_footer') }}@if($q('footer') > 0)<em>{{ $q('footer') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="slot" data-value="play">{{ admin_t('ui.slot_play') }}@if($q('play') > 0)<em>{{ $q('play') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="expired" data-value="1">{{ admin_t('ui.ad_expired') }}@if($q('expired') > 0)<em>{{ $q('expired') }}</em>@endif</button>
            <button type="button" class="chip" data-queue="status" data-value="0">{{ admin_t('ui.deactivated') }}@if($q('off') > 0)<em>{{ $q('off') }}</em>@endif</button>
        </div>
        <p class="muted recycle-lead">{{ admin_t('ui.ads_lead') }}</p>
        <div class="batch-bar" id="ad-batch" hidden>
            <strong id="ad-batch-count">{{ admin_t('ui.selected_rows', ['n' => 0]) }}</strong>
            <button type="button" class="btn btn-sm" id="ad-batch-on">{{ admin_t('ui.enabled') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="ad-batch-off">{{ admin_t('ui.disabled') }}</button>
            <select id="ad-batch-slot" class="batch-select" aria-label="{{ admin_t('ui.target_slot') }}">
                <option value="">{{ admin_t('ui.move_to_slot') }}</option>
                <option value="header">{{ admin_t('ui.slot_header') }}</option>
                <option value="footer">{{ admin_t('ui.slot_footer') }}</option>
                <option value="play">{{ admin_t('ui.slot_play') }}</option>
            </select>
            <button type="button" class="btn btn-muted btn-sm" id="ad-batch-move">{{ admin_t('ui.move') }}</button>
            <button type="button" class="btn btn-danger btn-sm" id="ad-batch-del">{{ admin_t('ui.delete') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="ad-batch-clear">{{ admin_t('ui.clear_selection') }}</button>
        </div>
        <div id="ad-table"></div>
    </div>
</div>
<template id="ad-dialog-tpl">
    <form class="admin-form">
        <input type="hidden" name="id">
        <label>{{ admin_t('ui.label_name') }}</label>
        <input type="text" name="name" placeholder="{{ admin_t('ui.ph_ad_name') }}">
        <p class="muted field-hint">{{ admin_t('ui.hint_ad_name') }}</p>
        <label>{{ admin_t('ui.label_slot') }}</label>
        <select name="slot_pick">
            <option value="header">{{ admin_t('ui.slot_header') }}</option>
            <option value="footer">{{ admin_t('ui.slot_footer') }}</option>
            <option value="play">{{ admin_t('ui.slot_play') }}</option>
            <option value="custom">{{ admin_t('ui.slot_custom') }}</option>
        </select>
        <input type="text" name="slot_custom" placeholder="{{ admin_t('ui.ph_slot_custom') }}" hidden autocomplete="off">
        <p class="muted field-hint ad-call-hint">{{ admin_t('ui.hint_ad_slot') }}</p>
        <label>{{ admin_t('ui.label_call') }}</label>
        <div class="ad-call">
            <input type="text" id="ad-call-code" readonly>
            <button type="button" class="btn btn-muted btn-sm" id="ad-call-copy">{{ admin_t('ui.copy') }}</button>
        </div>
        <label>{{ admin_t('ui.label_code') }}</label>
        <div class="field-inline">
            <span class="muted">{{ admin_t('ui.hint_ad_insert') }}</span>
            <button type="button" class="btn btn-muted btn-sm" id="ad-insert-btn">{{ admin_t('ui.insert_image') }}</button>
        </div>
        <img class="img-preview ad-insert-preview" alt="">
        <textarea name="content" class="ad-content" placeholder="{{ admin_t('ui.ph_ad_content') }}"></textarea>
        <label>{{ admin_t('ui.label_type_only') }}</label>
        <select name="type_id">
            <option value="0">{{ admin_t('ui.all_categories') }}</option>
            @foreach($types as $t)
                <option value="{{ $t['id'] }}">{{ $t['name'] }}</option>
            @endforeach
        </select>
        <p class="muted field-hint">{{ admin_t('ui.hint_ad_type') }}</p>
        <label>{{ admin_t('ui.label_expire') }}</label>
        <input type="datetime-local" name="expire_at">
        <p class="muted field-hint">{{ admin_t('ui.hint_ad_expire') }}</p>
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
    var L = @json($adJsLang);
    var QUEUE_KEYS = ['slot', 'expired'];
    var KNOWN = {header: 1, footer: 1, play: 1};
    var form = document.getElementById('ad-search');
    var batchBar = document.getElementById('ad-batch');
    var batchCount = document.getElementById('ad-batch-count');
    var countEl = document.getElementById('ad-count');

    function cleanWhere(data) {
        var out = {};
        Object.keys(data).forEach(function (k) { if (data[k] !== '') out[k] = data[k]; });
        return out;
    }
    function queryWhere() {
        return Object.assign({limit: 20}, cleanWhere(U.formData(form)));
    }
    function isFiltered(where) {
        return Object.keys(where || {}).some(function (k) { return k !== 'limit' && where[k] !== ''; });
    }
    function markChips() {
        var status = form.status.value;
        var slot = form.slot.value;
        var expired = form.expired.value;
        U.qa('#ad-queues .chip').forEach(function (chip) {
            var key = chip.getAttribute('data-queue') || '';
            var val = chip.getAttribute('data-value') || '';
            var on = false;
            if (key === '' && status === '' && slot === '' && expired === '') on = true;
            else if (key === 'slot' && status === '' && expired === '' && slot === val) on = true;
            else if (key === 'expired' && status === '' && slot === '' && expired === val) on = true;
            else if (key === 'status' && slot === '' && expired === '' && status === val) on = true;
            chip.classList.toggle('active', on);
        });
    }
    function applyQueue(key, value) {
        QUEUE_KEYS.forEach(function (k) { if (form[k]) form[k].value = ''; });
        form.status.value = '';
        if (key === 'status') form.status.value = value || '';
        else if (key && form[key]) form[key].value = value || '';
        runSearch();
    }
    function runSearch() {
        table.reload(queryWhere());
        markChips();
    }
    function copyText(text) {
        if (!text) return Promise.resolve();
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text).catch(function () {});
        }
        var ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(ta);
        return Promise.resolve();
    }
    function callCode(slot) {
        slot = String(slot || 'header');
        return "@@vodAd(['slot' => '" + slot + "'])";
    }
    function nameHtml(d) {
        var pic = String(d.preview_img || '').trim();
        var thumb = pic
            ? '<img class="ad-thumb" src="' + U.escape(pic) + '" alt="">'
            : '<span class="ad-thumb is-empty">' + U.escape(L.thumb_code) + '</span>';
        var badges = '';
        if (String(d.status) !== '1') badges += '<span class="badge badge-off">' + U.escape(L.disabled) + '</span>';
        if (d.is_expired) badges += '<span class="badge badge-off">' + U.escape(L.ad_expired) + '</span>';
        var meta = [U.escape(d.slot_label || d.slot || ''), U.escape(d.type_name || L.all_categories), U.escape(d.expire_text || L.no_expire)].join(' · ');
        var snip = d.preview_text ? '<div class="ad-snippet">' + U.escape(d.preview_text) + '</div>' : '';
        return '<div class="vod-cell">' + thumb + '<div><div class="entry-row-title-line"><a class="entry-row-title js-edit" href="#">' + U.escape(d.name || L.unnamed) + '</a> ' + badges + '</div>'
            + '<div class="entry-row-meta">' + meta + '</div>' + snip + '</div></div>';
    }

    var table = U.table({
        el: '#ad-table',
        queueKeys: QUEUE_KEYS,
        countEl: countEl,
        url: '/admin/video/ads/list',
        where: queryWhere(),
        pager: false,
        emptyHtml: function (_parsed, where) {
            if (isFiltered(where)) {
                return '<div class="list-empty"><p>' + U.escape(L.no_match_ads) + '</p><p><button type="button" class="btn btn-muted btn-sm" id="ad-empty-reset">' + U.escape(L.clear_filter) + '</button></p></div>';
            }
            return '<div class="list-empty"><p>' + U.escape(L.empty_ads) + '</p><p class="muted">' + U.escape(L.empty_ads_hint) + '</p><p><button type="button" class="btn btn-primary btn-sm" id="ad-empty-add">' + U.escape(L.add_ad) + '</button></p></div>';
        },
        onDraw: function (_wrap, list) {
            var add = document.getElementById('ad-empty-add');
            var reset = document.getElementById('ad-empty-reset');
            if (add) add.addEventListener('click', function () { openDialog('add'); });
            if (reset) reset.addEventListener('click', function () { form.reset(); runSearch(); });
        },
        onCheck: function (ids) {
            batchBar.hidden = ids.length === 0;
            batchCount.textContent = String(L.selected_rows || '').replace('__N__', String(ids.length));
        },
        cols: [
            {check: true, width: 36},
            {title: L.col_ad, html: nameHtml},
            {key: 'sort', title: L.sort, width: 64},
            {title: L.status, width: 72, html: function (d) {
                if (d.is_expired) return U.status(false, L.ad_expired);
                return String(d.status) === '1' ? U.status(true, L.enabled) : U.status(false, L.disabled);
            }},
            {title: L.actions, cls: 'actions', html: function () {
                return '<a href="#" class="btn-link js-copy">' + U.escape(L.copy_call) + '</a><a href="#" class="btn-link js-edit">' + U.escape(L.edit) + '</a><a href="#" class="btn-link js-del">' + U.escape(L.delete) + '</a>';
            }}
        ]
    });
    markChips();

    function insertAt(el, text) {
        if (!el || !text) return;
        el.focus();
        var start = el.selectionStart, end = el.selectionEnd, val = el.value;
        el.value = val.slice(0, start) + text + val.slice(end);
        el.selectionStart = el.selectionEnd = start + text.length;
    }
    function bindSlot(formEl) {
        var pick = formEl.querySelector('[name=slot_pick]');
        var custom = formEl.querySelector('[name=slot_custom]');
        var code = formEl.querySelector('#ad-call-code');
        function slot() {
            return pick.value === 'custom' ? String(custom.value || '').trim() : pick.value;
        }
        function sync() {
            custom.hidden = pick.value !== 'custom';
            code.value = callCode(slot() || 'header');
        }
        pick.addEventListener('change', sync);
        custom.addEventListener('input', sync);
        formEl._adSlot = slot;
        formEl._adSync = sync;
        sync();
    }
    function bindInsert(formEl) {
        var ta = formEl.querySelector('[name=content]');
        var btn = formEl.querySelector('#ad-insert-btn');
        var preview = formEl.querySelector('.ad-insert-preview');
        function syncPreview(url) {
            url = String(url || '').trim();
            if (!preview) return;
            if (url) { preview.src = url; preview.style.display = 'block'; }
            else { preview.removeAttribute('src'); preview.style.display = 'none'; }
        }
        var m = String(ta.value || '').match(/<img[^>]+src=["']([^"']+)["']/i);
        syncPreview(m ? m[1] : '');
        btn.addEventListener('click', function () {
            U.pickFile('image/*').then(function (file) {
                if (!file) return;
                U.loading(true);
                return U.upload(file).then(function (res) {
                    U.loading(false);
                    if (res && res.code === 0 && res.data && res.data.url) {
                        insertAt(ta, '<a href="" target="_blank" rel="nofollow"><img src="' + res.data.url + '" alt=""></a>');
                        syncPreview(res.data.url);
                        U.toast(L.inserted_ok, 'ok');
                    } else U.toast((res && res.msg) || L.upload_fail, 'err');
                });
            });
        });
    }

    function openDialog(mode, row) {
        row = row || {};
        U.dialog({
            title: mode === 'edit' ? L.edit_ad : L.add_ad,
            wide: true,
            content: document.getElementById('ad-dialog-tpl').innerHTML,
            onOpen: function (body) {
                var formEl = body.querySelector('form');
                var slot = String(row.slot || 'header');
                var known = !!KNOWN[slot];
                U.fillForm(formEl, {
                    id: mode === 'edit' ? (row.id || '') : '',
                    name: row.name || '',
                    content: row.content || '',
                    type_id: row.type_id == null ? 0 : row.type_id,
                    expire_at: row.expire_local || '',
                    sort: row.sort == null ? 0 : row.sort,
                    status: row.status == null ? '1' : String(row.status),
                    slot_pick: known ? slot : 'custom',
                    slot_custom: known ? '' : slot
                });
                bindSlot(formEl);
                bindInsert(formEl);
                body.querySelector('#ad-call-copy').addEventListener('click', function () {
                    copyText(body.querySelector('#ad-call-code').value).then(function () { U.toast(L.copied, 'ok'); });
                });
            },
            onSave: function (body) {
                var formEl = body.querySelector('form');
                var data = U.formData(formEl);
                var slot = formEl._adSlot ? formEl._adSlot() : data.slot_pick;
                if (!data.name) { U.toast(L.please_fill_name, 'err'); return false; }
                if (!slot) { U.toast(L.please_fill_slot, 'err'); return false; }
                data.slot = slot;
                delete data.slot_pick;
                delete data.slot_custom;
                if (mode !== 'edit') delete data.id; else data.id = row.id;
                return U.post('/admin/video/ads/save', data).then(function (res) {
                    if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return false; }
                    U.toast(mode === 'edit' ? L.saved : L.created, 'ok');
                    table.refresh();
                });
            }
        });
    }

    function selectedIds() { return table.selectedIds(); }
    function batch(action, value, confirmText) {
        var ids = selectedIds();
        if (!ids.length) { U.toast(L.please_select_ads, 'err'); return; }
        if (confirmText && !U.confirm(confirmText)) return;
        U.post('/admin/video/ads/batch', {ids: ids.join(','), action: action, value: value}).then(function (res) {
            if (!res || res.code !== 0) { U.toast((res && res.msg) || L.op_fail, 'err'); return; }
            table.refresh();
            U.toast((res && res.msg) || L.op_ok, 'ok');
        });
    }

    U.on('#ad-search-btn', 'click', runSearch);
    U.on('#ad-reset-btn', 'click', function () { setTimeout(runSearch, 0); });
    U.on('#ad-add-btn', 'click', function () { openDialog('add'); });
    document.getElementById('ad-queues').addEventListener('click', function (e) {
        var chip = e.target.closest('[data-queue]');
        if (!chip) return;
        applyQueue(chip.getAttribute('data-queue') || '', chip.getAttribute('data-value') || '');
    });
    U.on('#ad-batch-on', 'click', function () { batch('status', 1); });
    U.on('#ad-batch-off', 'click', function () { batch('status', 0); });
    U.on('#ad-batch-move', 'click', function () {
        var val = document.getElementById('ad-batch-slot').value;
        if (!val) { U.toast(L.please_pick_slot, 'err'); return; }
        batch('slot', val);
    });
    U.on('#ad-batch-del', 'click', function () { batch('delete', '', L.confirm_batch_del_ads); });
    U.on('#ad-batch-clear', 'click', function () { table.clearSelection(); });
    U.on('#ad-table', 'click', function (e) {
        var a = e.target.closest('a');
        if (!a) return;
        var tr = e.target.closest('tr');
        var row = (table.rows() || [])[tr ? tr.getAttribute('data-idx') : -1];
        if (!row) return;
        e.preventDefault();
        if (a.classList.contains('js-copy')) {
            copyText(callCode(row.slot)).then(function () { U.toast(L.copied_call, 'ok'); });
        }
        if (a.classList.contains('js-edit')) openDialog('edit', row);
        if (a.classList.contains('js-del')) {
            if (!U.confirm(String(L.confirm_del_ad || '').replace('__NAME__', row.name || ''))) return;
            U.post('/admin/video/ads/delete', {id: row.id}).then(function (res) {
                if (!res || res.code !== 0) { U.toast((res && res.msg) || L.fail, 'err'); return; }
                table.refresh();
                U.toast(L.deleted, 'ok');
            });
        }
    });
})();
</script>
@endpush
