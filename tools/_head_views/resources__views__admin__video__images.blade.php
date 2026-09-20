fatal: path 'resources\views\admin\video\images.blade.php' exists on disk, but not in 'HEAD'
@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('page.tool_images'))

@php
    $remoteN = (int) ($remote_n ?? 0);
    $localN = (int) ($local_n ?? 0);
    $emptyN = (int) ($empty_n ?? 0);
    $picLocal = (bool) ($pic_local ?? false);
    $watermark = trim((string) ($watermark ?? ''));
@endphp

@section('plain')
<div class="card card-panel img-index">
    <div class="card-header">
        <span>远程图片 / 坏图</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video">影片列表</a>
            <a class="btn btn-muted btn-sm" href="/admin/video?empty_pic=1">无封面@if($emptyN > 0) · {{ $emptyN }}@endif</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/config/collect">内容接入</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/tools/annex">附件清理</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">扫描封面、下载到本站 <code>/uploads/vod/</code>。缺图去补无封面。扫描不改影片。</p>

        <div class="img-stock" id="img-stock">
            <span>外站封面 <strong id="img-remote-n">{{ $remoteN }}</strong></span>
            <span>本站封面 <strong id="img-local-n">{{ $localN }}</strong></span>
            <a href="/admin/video?empty_pic=1">还没封面 <strong id="img-empty-n">{{ $emptyN }}</strong></a>
        </div>
        <p class="muted img-note">上面的数字看的是库里的地址，不会去访问外站。片子多就多点几次扫描和下载，一次看最近 80 部、最多下 40 张。</p>
        @if(! $picLocal)
            <p class="muted img-note">采集时不会自动下载封面。要自动下，到「<a href="/admin/video/config/collect">内容接入</a>」打开「把封面下载到本站」。</p>
        @endif
        @if($watermark !== '')
            <p class="muted img-note">下载时会在图上打水印「{{ $watermark }}」。要改字去「<a href="/admin/video/settings">站点设置</a>」。</p>
        @else
            <p class="muted img-note">现在下载不打水印。需要的话在「<a href="/admin/video/settings">站点设置</a>」里写封面水印。</p>
        @endif

        <div class="hub-actions img-ops">
            <button type="button" class="btn" id="img-scan-btn">扫描封面</button>
            <button type="button" class="btn btn-muted" id="img-local-btn">下载到本站</button>
        </div>

        <div class="hub-result" id="img-result">
            <div class="hub-empty" id="img-empty">
                <p>还没扫描。</p>
                <p class="muted">点「扫描封面」看哪些还在资源站、哪些打不开。不会改片子。</p>
            </div>
            <div class="hub-fail" id="img-fail" hidden>
                <p class="hub-fail-title">没扫成</p>
                <p class="hub-fail-msg" id="img-fail-msg"></p>
                <p class="muted">常见原因：网络超时。可以再点一次，或先下一批外站封面。</p>
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
        var title = U.escape(item.title || ('影片 #' + id));
        var host = U.escape(item.host || item.cover || '');
        var badge = item.ok ? '' : '<span class="badge">打不开</span>';
        var link = id ? '<a class="btn-link" href="/admin/video/' + id + '/edit">改封面</a>' : '';
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
        document.getElementById('img-ok-stats').textContent = msg || '扫描完成';
        var remote = lastScan.remote || [];
        var missing = lastScan.missing || [];
        var hint = '';
        if (remote.length && lastScan.broken_n > 0) {
            hint = '外站封面资源站挂了就会打不开。下载到本站后，地址会改成 /uploads/vod/。';
        } else if (remote.length) {
            hint = '这些封面还在资源站。点「下载到本站」会改影片上的地址。';
        } else if (missing.length) {
            hint = '本站文件找不到了。去影片编辑里重传，或到无封面列表补图。';
        } else {
            hint = '这批封面都在本站。没有封面的片子在无封面列表里。';
        }
        document.getElementById('img-ok-hint').textContent = hint;
        document.getElementById('img-ok-lists').innerHTML =
            listHtml('外站封面', remote) + listHtml('本站文件丢失', missing);
        var actions = document.getElementById('img-ok-actions');
        actions.innerHTML = '';
        if (remote.length) {
            var down = document.createElement('button');
            down.type = 'button';
            down.className = 'btn';
            down.textContent = '下载到本站';
            down.addEventListener('click', localize);
            actions.appendChild(down);
        }
        var emptyLink = document.createElement('a');
        emptyLink.className = 'btn btn-muted';
        emptyLink.href = '/admin/video?empty_pic=1';
        emptyLink.textContent = '去无封面列表';
        actions.appendChild(emptyLink);
        show('ok');
    }
    function renderDone(data, msg) {
        setStock(data || {});
        document.getElementById('img-done-stats').textContent = msg || '已下载';
        var remaining = parseInt((data && data.remaining != null) ? data.remaining : (data && data.remote_n), 10) || 0;
        var done = parseInt((data && data.count), 10) || 0;
        var hint = done > 0
            ? (remaining > 0 ? '还剩外站封面，可再点一次下载。' : '外站封面这批已经下完。')
            : '没有改任何片子。';
        document.getElementById('img-done-hint').textContent = hint;
        var actions = document.getElementById('img-done-actions');
        actions.innerHTML = '';
        if (remaining > 0) {
            var again = document.createElement('button');
            again.type = 'button';
            again.className = 'btn';
            again.textContent = '再下一轮';
            again.addEventListener('click', localize);
            actions.appendChild(again);
        }
        var scanAgain = document.createElement('button');
        scanAgain.type = 'button';
        scanAgain.className = 'btn btn-muted';
        scanAgain.textContent = '再扫描';
        scanAgain.addEventListener('click', scan);
        actions.appendChild(scanAgain);
        var emptyLink = document.createElement('a');
        emptyLink.className = 'btn btn-muted';
        emptyLink.href = '/admin/video?empty_pic=1';
        emptyLink.textContent = '去无封面列表';
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
                failMsg.textContent = (res && res.msg) || '扫描失败';
                show('fail');
                U.toast((res && res.msg) || '扫描失败', 'err');
                return;
            }
            renderScan(res.data || {}, res.msg || '');
            U.toast(res.msg || '扫描完成', 'ok');
        }).catch(function () {
            U.loading(false);
            scanBtn.disabled = false;
            failMsg.textContent = '网络错误，稍后再试。';
            show('fail');
            U.toast('扫描失败', 'err');
        });
    }
    function localize() {
        if (!U.confirm('会把外站封面下到本站 /uploads/vod/，并改影片上的地址。一次最多 40 张。资源站挂了就下不下来。')) return;
        localBtn.disabled = true;
        U.loading(true);
        post('localize').then(function (res) {
            U.loading(false);
            localBtn.disabled = false;
            if (!res || res.code !== 0) {
                failMsg.textContent = (res && res.msg) || '下载失败';
                show('fail');
                U.toast((res && res.msg) || '下载失败', 'err');
                return;
            }
            lastScan = null;
            renderDone(res.data || {}, res.msg || '');
            U.toast(res.msg || '已下载', 'ok');
        }).catch(function () {
            U.loading(false);
            localBtn.disabled = false;
            failMsg.textContent = '网络错误，稍后再试。';
            show('fail');
            U.toast('下载失败', 'err');
        });
    }

    scanBtn.addEventListener('click', scan);
    localBtn.addEventListener('click', localize);
})();
</script>
@endpush