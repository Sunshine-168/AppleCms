fatal: path 'resources\views\admin\video\batch_players.blade.php' exists on disk, but not in 'HEAD'
@extends('admin.layouts.inner')
@section('title', $title ?? admin_t('page.tool_players'))

@php
    $players = is_array($players ?? null) ? $players : [];
    $usages = is_array($usages ?? null) ? $usages : [];
    $sourceN = (int) ($source_n ?? 0);
    $unknownN = (int) ($unknown_n ?? 0);
@endphp

@section('plain')
<div class="card card-panel play-index">
    <div class="card-header">
        <span>批量更换播放器</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/players">播放器</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/config/player">播放器参数</a>
            <a class="btn btn-muted btn-sm" href="/admin/video">影片列表</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">查看线路标识后整批更换或下线，不改播放地址。到播放器页核对播放器。</p>

        @if($unknownN > 0)
            <p class="muted play-note">有 {{ $unknownN }} 条线路的标识不在播放器表里，前台可能播不了。换成站内播放器即可。</p>
        @endif

        <form class="play-form" id="play-form" onsubmit="return false;">
            <div class="play-fields">
                <div>
                    <label for="play-from">原标识</label>
                    <input id="play-from" type="text" name="from" placeholder="点下面的标识，或自己填" autocomplete="off" spellcheck="false">
                </div>
                <div>
                    <label for="play-to">换成</label>
                    <input id="play-to" type="text" name="to" list="play-to-list" placeholder="选站内播放器，或填新标识" autocomplete="off" spellcheck="false">
                    <datalist id="play-to-list">
                        @foreach($players as $p)
                            @php $code = trim((string) ($p['code'] ?? '')); @endphp
                            @if($code !== '')
                                <option value="{{ $code }}">{{ trim((string) ($p['name'] ?? $code)) }}</option>
                            @endif
                        @endforeach
                    </datalist>
                </div>
            </div>
            <p class="muted field-hint">下线不用填「换成」。填了新标识且表里没有，会自动加一条播放器，记得去选内核。</p>
            <div class="hub-actions play-ops">
                <button type="button" class="btn" id="play-rename-btn">更换播放器</button>
                <button type="button" class="btn btn-danger" id="play-off-btn">下线这些线路</button>
            </div>
        </form>

        <div class="hub-result" id="play-result">
            <div class="hub-empty" id="play-empty">
                <p>还没改。</p>
                <p class="muted">点下面线路上的标识填到「原标识」，再选要换成的播放器。片子和播放地址不变。</p>
            </div>
            <div class="hub-fail" id="play-fail" hidden>
                <p class="hub-fail-title">没改成</p>
                <p class="hub-fail-msg" id="play-fail-msg"></p>
                <p class="muted">常见原因：标识抄错、没有线路用这个标识、原标识和目标一样。</p>
            </div>
            <div class="hub-ok" id="play-ok" hidden>
                <p class="hub-ok-stats" id="play-ok-stats"></p>
                <p class="muted" id="play-ok-hint"></p>
                <div class="hub-actions" id="play-ok-actions"></div>
            </div>
        </div>

        <div class="play-block">
            <div class="hub-saved-head">
                <strong>线路上在用的标识</strong>
                @if($sourceN > 0)
                    <span class="muted">{{ $sourceN }} 条线路</span>
                @endif
            </div>
            <div id="play-usages">
                @if($usages === [])
                    <p class="muted hub-saved-empty">还没有线路。采片或加影片后，这里会列出线路上的播放器标识。</p>
                @else
                    <div class="img-list">
                        @foreach($usages as $u)
                            @php
                                $code = trim((string) ($u['code'] ?? ''));
                                $count = (int) ($u['count'] ?? 0);
                                $onN = (int) ($u['on_n'] ?? $count);
                                $known = (bool) ($u['known'] ?? false);
                                $name = trim((string) ($u['name'] ?? ''));
                                $engine = trim((string) ($u['engine'] ?? ''));
                            @endphp
                            <div class="img-row play-use" data-code="{{ $code }}" data-count="{{ $count }}">
                                <div>
                                    <strong>{{ $code !== '' ? $code : '（空标识）' }}</strong>
                                    @if($known)
                                        <span class="badge badge-ok">{{ $name !== '' ? $name : '在播放器表' }}</span>
                                    @elseif($code !== '')
                                        <span class="badge">不在播放器表</span>
                                    @endif
                                    @if($engine !== '')
                                        <span class="muted">{{ $engine }}</span>
                                    @endif
                                    <div class="muted">{{ $count }} 条线路@if($onN < $count) · {{ $onN }} 条在播@endif</div>
                                </div>
                                <div class="img-row-ops">
                                    @if($code !== '')
                                        <button type="button" class="btn-link play-fill-from" data-code="{{ $code }}">用作原标识</button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="play-block">
            <div class="hub-saved-head">
                <strong>站内播放器</strong>
                @if(count($players) > 0)
                    <span class="muted">点一下填到「换成」</span>
                @endif
            </div>
            <div id="play-catalog">
                @if($players === [])
                    <p class="muted hub-saved-empty">还没有播放器。先去「<a href="/admin/video/players">播放器</a>」补齐内置，再回来换线路。</p>
                @else
                    <div class="hub-tags play-tags">
                        @foreach($players as $p)
                            @php
                                $code = trim((string) ($p['code'] ?? ''));
                                $name = trim((string) ($p['name'] ?? ''));
                                $engine = trim((string) ($p['engine'] ?? ''));
                                $on = (int) ($p['status'] ?? 1) === 1;
                            @endphp
                            @if($code !== '')
                                <button type="button" class="hub-tag play-fill-to{{ $on ? '' : ' is-off' }}" data-code="{{ $code }}" title="{{ $engine }}">
                                    {{ $name !== '' && $name !== $code ? $name.' · '.$code : $code }}
                                </button>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var fromEl = document.getElementById('play-from');
    var toEl = document.getElementById('play-to');
    var emptyEl = document.getElementById('play-empty');
    var failEl = document.getElementById('play-fail');
    var failMsg = document.getElementById('play-fail-msg');
    var okEl = document.getElementById('play-ok');
    var usagesWrap = document.getElementById('play-usages');

    function show(which) {
        emptyEl.hidden = which !== 'empty';
        failEl.hidden = which !== 'fail';
        okEl.hidden = which !== 'ok';
    }
    function usageCount(code) {
        var n = 0;
        document.querySelectorAll('.play-use').forEach(function (row) {
            if ((row.getAttribute('data-code') || '') === code) {
                n = parseInt(row.getAttribute('data-count'), 10) || 0;
            }
        });
        return n;
    }
    function renderUsages(usages) {
        if (!usagesWrap) return;
        if (!usages || !usages.length) {
            usagesWrap.innerHTML = '<p class="muted hub-saved-empty">还没有线路。采片或加影片后，这里会列出线路上的播放器标识。</p>';
            return;
        }
        var html = '<div class="img-list">';
        usages.forEach(function (u) {
            var code = String(u.code || '');
            var count = parseInt(u.count, 10) || 0;
            var onN = parseInt(u.on_n, 10);
            if (isNaN(onN)) onN = count;
            var title = code !== '' ? U.escape(code) : '（空标识）';
            var badge = u.known
                ? '<span class="badge badge-ok">' + U.escape(u.name || '在播放器表') + '</span>'
                : (code !== '' ? '<span class="badge">不在播放器表</span>' : '');
            var engine = u.engine ? '<span class="muted">' + U.escape(u.engine) + '</span>' : '';
            var extra = onN < count ? ' · ' + onN + ' 条在播' : '';
            var op = code !== ''
                ? '<button type="button" class="btn-link play-fill-from" data-code="' + U.escape(code) + '">用作原标识</button>'
                : '';
            html += '<div class="img-row play-use" data-code="' + U.escape(code) + '" data-count="' + count + '">';
            html += '<div><strong>' + title + '</strong> ' + badge + ' ' + engine;
            html += '<div class="muted">' + count + ' 条线路' + extra + '</div></div>';
            html += '<div class="img-row-ops">' + op + '</div></div>';
        });
        html += '</div>';
        usagesWrap.innerHTML = html;
        bindFill();
    }
    function renderOk(data, msg) {
        document.getElementById('play-ok-stats').textContent = msg || '已完成';
        var hint = '';
        if (data && data.mode === 'disable') {
            hint = '这些线路停了，影片还在。要再播，把原标识换成站内播放器，或到影片编辑里打开线路。';
        } else if (data && data.created) {
            hint = '新标识已经加到播放器表，去选内核（ArtPlayer / DPlayer / Video.js / 解析）。';
        } else {
            hint = '播放地址没变。到播放器页看这个标识用的是哪个内核。';
        }
        document.getElementById('play-ok-hint').textContent = hint;
        var actions = document.getElementById('play-ok-actions');
        actions.innerHTML = '';
        var go = document.createElement('a');
        go.className = 'btn';
        go.href = '/admin/video/players';
        go.textContent = '去播放器核对';
        actions.appendChild(go);
        renderUsages((data && data.usages) || []);
        show('ok');
    }
    function run(mode) {
        var from = String(fromEl.value || '').trim();
        var to = String(toEl.value || '').trim();
        if (!from) {
            U.toast('请填写原标识，或点下面线路上的标识', 'err');
            fromEl.focus();
            return;
        }
        if (mode === 'rename' && !to) {
            U.toast('请填写要换成的播放器标识', 'err');
            toEl.focus();
            return;
        }
        var n = usageCount(from);
        var confirmMsg = mode === 'disable'
            ? ('会把标识是「' + from + '」的线路停掉，前台不播这些线' + (n ? '，大约 ' + n + ' 条' : '') + '。不删片子。')
            : ('会把标识是「' + from + '」的线路改成「' + to + '」' + (n ? '，大约 ' + n + ' 条' : '') + '。不改播放地址。');
        if (!U.confirm(confirmMsg)) return;
        U.loading(true);
        U.post('/admin/video/tools/players/run', {action: 'replace', from: from, to: to, mode: mode}).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) {
                failMsg.textContent = (res && res.msg) || '操作失败';
                if (res && res.data && res.data.usages) renderUsages(res.data.usages);
                show('fail');
                U.toast((res && res.msg) || '操作失败', 'err');
                return;
            }
            renderOk(res.data || {}, res.msg || '');
            U.toast(res.msg || '已完成', 'ok');
        }).catch(function () {
            U.loading(false);
            failMsg.textContent = '网络错误，稍后再试。';
            show('fail');
            U.toast('操作失败', 'err');
        });
    }
    function bindFill() {
        document.querySelectorAll('.play-fill-from').forEach(function (btn) {
            btn.addEventListener('click', function () {
                fromEl.value = btn.getAttribute('data-code') || '';
                fromEl.focus();
            });
        });
    }
    document.querySelectorAll('.play-fill-to').forEach(function (btn) {
        btn.addEventListener('click', function () {
            toEl.value = btn.getAttribute('data-code') || '';
            toEl.focus();
        });
    });
    bindFill();
    document.getElementById('play-rename-btn').addEventListener('click', function () { run('rename'); });
    document.getElementById('play-off-btn').addEventListener('click', function () { run('disable'); });
})();
</script>
@endpush