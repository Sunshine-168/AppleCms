@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('page.tool_images'))

@php
    $remoteN = (int) ($remote_n ?? 0);
    $localN = (int) ($local_n ?? 0);
    $emptyN = (int) ($empty_n ?? 0);
    $picLocal = (bool) ($pic_local ?? false);
    $watermark = trim((string) ($watermark ?? ''));
    $jsLang = [
        'vod_hash' => admin_t('ui.video_hash', ['id' => '__ID__']),
        'broken' => admin_t('ui.img_broken'),
        'edit_cover' => admin_t('ui.img_edit_cover'),
        'scan_done' => admin_t('ui.img_scan_done'),
        'hint_broken' => admin_t('ui.img_hint_broken'),
        'hint_remote' => admin_t('ui.img_hint_remote'),
        'hint_missing' => admin_t('ui.img_hint_missing'),
        'hint_local' => admin_t('ui.img_hint_local'),
        'list_remote' => admin_t('ui.img_list_remote'),
        'list_missing' => admin_t('ui.img_list_missing'),
        'download_local' => admin_t('ui.img_download_local'),
        'go_empty_pic' => admin_t('ui.img_go_empty'),
        'downloaded' => admin_t('ui.img_downloaded'),
        'remain_more' => admin_t('ui.img_remain_more'),
        'remain_done' => admin_t('ui.img_remain_done'),
        'no_change_videos' => admin_t('ui.img_no_change'),
        'again' => admin_t('ui.img_again'),
        'scan_again' => admin_t('ui.img_scan_again'),
        'scan_fail' => admin_t('ui.img_scan_fail'),
        'net_retry' => admin_t('ui.net_retry'),
        'confirm_local' => admin_t('ui.img_confirm_local'),
        'dl_fail' => admin_t('ui.img_dl_fail'),
    ];
@endphp

@section('plain')
<div class="card card-panel img-index">
    <div class="card-header">
        <span>{{ admin_t('ui.img_title') }}</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video">{{ admin_t('ui.video_list') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video?empty_pic=1">{{ admin_t('ui.no_cover') }}@if($emptyN > 0) · {{ $emptyN }}@endif</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/config/collect">{{ admin_t('ui.config_collect') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/tools/annex">{{ admin_t('ui.annex') }}</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">{{ admin_t('ui.img_lead_before') }}<code>/uploads/vod/</code>{{ admin_t('ui.img_lead_after') }}</p>

        <div class="img-stock" id="img-stock">
            <span>{{ admin_t('ui.img_remote_cover') }} <strong id="img-remote-n">{{ $remoteN }}</strong></span>
            <span>{{ admin_t('ui.img_local_cover') }} <strong id="img-local-n">{{ $localN }}</strong></span>
            <a href="/admin/video?empty_pic=1">{{ admin_t('ui.img_no_cover_yet') }} <strong id="img-empty-n">{{ $emptyN }}</strong></a>
        </div>
        <p class="muted img-note">{{ admin_t('ui.img_stock_note') }}</p>
        @if(! $picLocal)
            <p class="muted img-note">{{ admin_t('ui.img_auto_off_before') }}<a href="/admin/video/config/collect">{{ admin_t('ui.config_collect') }}</a>{{ admin_t('ui.img_auto_off_mid') }}</p>
        @endif
        @if($watermark !== '')
            <p class="muted img-note">{{ admin_t('ui.img_wm_on_before', ['name' => $watermark]) }}<a href="/admin/video/settings">{{ admin_t('ui.site_settings') }}</a>{{ admin_t('ui.img_wm_on_after') }}</p>
        @else
            <p class="muted img-note">{{ admin_t('ui.img_wm_off_before') }}<a href="/admin/video/settings">{{ admin_t('ui.site_settings') }}</a>{{ admin_t('ui.img_wm_off_after') }}</p>
        @endif

        <div class="hub-actions img-ops">
            <button type="button" class="btn" id="img-scan-btn">{{ admin_t('ui.img_scan') }}</button>
            <button type="button" class="btn btn-muted" id="img-local-btn">{{ admin_t('ui.img_download_local') }}</button>
        </div>

        <div class="hub-result" id="img-result">
            <div class="hub-empty" id="img-empty">
                <p>{{ admin_t('ui.img_empty') }}</p>
                <p class="muted">{{ admin_t('ui.img_empty_hint') }}</p>
            </div>
            <div class="hub-fail" id="img-fail" hidden>
                <p class="hub-fail-title">{{ admin_t('ui.img_fail_title') }}</p>
                <p class="hub-fail-msg" id="img-fail-msg"></p>
                <p class="muted">{{ admin_t('ui.img_fail_hint') }}</p>
            </div>
            <div class="hub-ok" id="img-ok" hidden>
                <p class="hub-ok-stats" id="img-ok-stats"></p>
                <p class="muted" id="img-ok-hint"></p>
                <div id="img-ok-lists"></div>
                <div class="hub-actions" id="img-ok-actions"></div>
            </div>
            <div class="hub-ok" id="img-done" hidden>
                <p class="hub-ok-stats" id="img-done-stats"></p>
                <p class="muted" id="img-done-hint"></p>
                <div class="hub-actions" id="img-done-actions"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var L = @json($jsLang, JSON_UNESCAPED_UNICODE);
    var scanBtn = document.getElementById('img-scan-btn');
    var localBtn = document.getElementById('img-local-btn');
    var emptyEl = document.getElementById('img-empty');
    var failEl = document.getElementById('img-fail');
    var failMsg = document.getElementById('img-fail-msg');
    var okEl = document.getElementById('img-ok');
    var doneEl = document.getElementById('img-done');
    var lastScan = null;

    function show(which) {
        emptyEl.hidden = which !== 'empty';
        failEl.hidden = which !== 'fail';
        okEl.hidden = which !== 'ok';
        doneEl.hidden = which !== 'done';
    }
    function setStock(data) {
        if (!data) return;
        var remote = document.getElementById('img-remote-n');
        var local = document.getElementById('img-local-n');
        var empty = document.getElementById('img-empty-n');
        if (remote && data.remote_n != null) remote.textContent = String(data.remote_n);
        if (local && data.local_n != null) local.textContent = String(data.local_n);
        if (empty && data.empty_n != null) empty.textContent = String(data.empty_n);
    }
    function rowHtml(item) {
        var id = parseInt(item.id, 10) || 0;
        var title = U.escape(item.title || String(L.vod_hash || '').replace('__ID__', String(id)));
        var host = U.escape(item.host || item.cover || '');
        var badge = item.ok ? '' : '<span class="badge">' + U.escape(L.broken) + '</span>';
        var link = id ? '<a class="btn-link" href="/admin/video/' + id + '/edit">' + U.escape(L.edit_cover) + '</a>' : '';
        return '<div class="img-row"><div><strong>' + title + '</strong> ' + badge + '<div class="muted">' + host + '</div></div><div class="img-row-ops">' + link + '</div></div>';
    }
    function listHtml(title, rows) {
        if (!rows || !rows.length) return '';
        var html = '<p class="img-list-title">' + U.escape(title) + '</p><div class="img-list">';
        rows.forEach(function (item) { html += rowHtml(item); });
        html += '</div>';
        return html;
    }
    function renderScan(data, msg) {
        lastScan = data || {};
        setStock(lastScan);
        document.getElementById('img-ok-stats').textContent = msg || L.scan_done;
        var remote = lastScan.remote || [];
        var missing = lastScan.missing || [];
        var hint = '';
        if (remote.length && lastScan.broken_n > 0) {
            hint = L.hint_broken;
        } else if (remote.length) {
            hint = L.hint_remote;
        } else if (missing.length) {
            hint = L.hint_missing;
        } else {
            hint = L.hint_local;
        }
        document.getElementById('img-ok-hint').textContent = hint;
        document.getElementById('img-ok-lists').innerHTML =
            listHtml(L.list_remote, remote) + listHtml(L.list_missing, missing);
        var actions = document.getElementById('img-ok-actions');
        actions.innerHTML = '';
        if (remote.length) {
            var down = document.createElement('button');
            down.type = 'button';
            down.className = 'btn';
            down.textContent = L.download_local;
            down.addEventListener('click', localize);
            actions.appendChild(down);
        }
        var emptyLink = document.createElement('a');
        emptyLink.className = 'btn btn-muted';
        emptyLink.href = '/admin/video?empty_pic=1';
        emptyLink.textContent = L.go_empty_pic;
        actions.appendChild(emptyLink);
        show('ok');
    }
    function renderDone(data, msg) {
        setStock(data || {});
        document.getElementById('img-done-stats').textContent = msg || L.downloaded;
        var remaining = parseInt((data && data.remaining != null) ? data.remaining : (data && data.remote_n), 10) || 0;
        var done = parseInt((data && data.count), 10) || 0;
        var hint = done > 0
            ? (remaining > 0 ? L.remain_more : L.remain_done)
            : L.no_change_videos;
        document.getElementById('img-done-hint').textContent = hint;
        var actions = document.getElementById('img-done-actions');
        actions.innerHTML = '';
        if (remaining > 0) {
            var again = document.createElement('button');
            again.type = 'button';
            again.className = 'btn';
            again.textContent = L.again;
            again.addEventListener('click', localize);
            actions.appendChild(again);
        }
        var scanAgain = document.createElement('button');
        scanAgain.type = 'button';
        scanAgain.className = 'btn btn-muted';
        scanAgain.textContent = L.scan_again;
        scanAgain.addEventListener('click', scan);
        actions.appendChild(scanAgain);
        var emptyLink = document.createElement('a');
        emptyLink.className = 'btn btn-muted';
        emptyLink.href = '/admin/video?empty_pic=1';
        emptyLink.textContent = L.go_empty_pic;
        actions.appendChild(emptyLink);
        show('done');
    }
    function post(action) {
        return U.post('/admin/video/tools/images/run', {action: action});
    }
    function scan() {
        scanBtn.disabled = true;
        U.loading(true);
        post('scan').then(function (res) {
            U.loading(false);
            scanBtn.disabled = false;
            if (!res || res.code !== 0) {
                failMsg.textContent = (res && res.msg) || L.scan_fail;
                show('fail');
                U.toast((res && res.msg) || L.scan_fail, 'err');
                return;
            }
            renderScan(res.data || {}, res.msg || '');
            U.toast(res.msg || L.scan_done, 'ok');
        }).catch(function () {
            U.loading(false);
            scanBtn.disabled = false;
            failMsg.textContent = L.net_retry;
            show('fail');
            U.toast(L.scan_fail, 'err');
        });
    }
    function localize() {
        if (!U.confirm(L.confirm_local)) return;
        localBtn.disabled = true;
        U.loading(true);
        post('localize').then(function (res) {
            U.loading(false);
            localBtn.disabled = false;
            if (!res || res.code !== 0) {
                failMsg.textContent = (res && res.msg) || L.dl_fail;
                show('fail');
                U.toast((res && res.msg) || L.dl_fail, 'err');
                return;
            }
            lastScan = null;
            renderDone(res.data || {}, res.msg || '');
            U.toast(res.msg || L.downloaded, 'ok');
        }).catch(function () {
            U.loading(false);
            localBtn.disabled = false;
            failMsg.textContent = L.net_retry;
            show('fail');
            U.toast(L.dl_fail, 'err');
        });
    }

    scanBtn.addEventListener('click', scan);
    localBtn.addEventListener('click', localize);
})();
</script>
@endpush
