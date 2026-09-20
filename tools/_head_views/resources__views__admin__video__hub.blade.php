fatal: path 'resources\views\admin\video\hub.blade.php' exists on disk, but not in 'HEAD'
@extends('admin.layouts.inner')
@section('title', admin_t('page.tool_hub'))

@php
    $unions = is_array($unions ?? null) ? $unions : [];
    $unionCount = (int) ($union_count ?? count($unions));
    $collectCount = (int) ($collect_count ?? 0);
    $pendingCount = (int) ($pending_count ?? 0);
    $prefill = (string) ($prefill ?? '');
@endphp

@section('plain')
<div class="card card-panel hub-index">
    <div class="card-header">
        <span>试试资源接口</span>
        <div>
            <a class="btn btn-muted btn-sm" href="/admin/video/unions">推荐资源@if($unionCount > 0) · {{ $unionCount }}@endif</a>
            <a class="btn btn-muted btn-sm" href="/admin/video/collects">采集源@if($collectCount > 0) · {{ $collectCount }}@endif</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">探测接口，通了再收藏或接入采集源。绑定分类后采集。只读，不改片库。</p>

        <form class="hub-probe" id="hub-form" onsubmit="return false;">
            <label for="hub-url">接口地址</label>
            <div class="field-inline hub-probe-row">
                <input id="hub-url" type="text" name="api_url" value="{{ $prefill }}" placeholder="https://资源站/api.php/provide/vod/" autocomplete="off" spellcheck="false">
                <button type="submit" class="btn" id="hub-probe-btn">探测</button>
            </div>
            <p class="muted field-hint">一般是 <code>https://域名/api.php/provide/vod/</code>。没写 http 会按 https 补上。</p>
        </form>

        <div class="hub-result" id="hub-result">
            <div class="hub-empty" id="hub-empty">
                <p>还没探测。</p>
                <p class="muted">把资源站给的地址贴上来，点「探测」。成功后可以收藏，或直接接入采集源。</p>
            </div>
            <div class="hub-fail" id="hub-fail" hidden>
                <p class="hub-fail-title">没探通</p>
                <p class="hub-fail-msg" id="hub-fail-msg"></p>
                <p class="muted">常见原因：地址抄错、资源站关掉了、不是苹果 CMS 兼容接口。</p>
            </div>
            <div class="hub-ok" id="hub-ok" hidden>
                <div class="hub-ok-head">
                    <strong id="hub-ok-title">接口可用</strong>
                    <span class="badge badge-ok" id="hub-ok-host"></span>
                    <span class="muted" id="hub-ok-format"></span>
                </div>
                <p class="hub-ok-stats" id="hub-ok-stats"></p>
                <div class="hub-tags" id="hub-ok-types"></div>
                <p class="muted hub-samples" id="hub-ok-samples"></p>
                <div class="hub-actions" id="hub-ok-actions"></div>
            </div>
        </div>

        <div class="hub-saved">
            <div class="hub-saved-head">
                <strong>已收藏的接口</strong>
                @if($pendingCount > 0)
                    <span class="muted">{{ $pendingCount }} 条还没接入采集源</span>
                @endif
            </div>
            @if($unions === [])
                <p class="muted hub-saved-empty">还没有收藏。探测成功后点「收藏」，下次就不用再找地址。</p>
            @else
                <div class="hub-saved-list">
                    @foreach($unions as $u)
                        @php
                            $name = trim((string) ($u['name'] ?? ''));
                            $url = trim((string) ($u['api_url'] ?? ''));
                            $host = trim((string) ($u['host'] ?? ''));
                            $adopted = (int) ($u['adopted'] ?? 0) === 1;
                        @endphp
                        <div class="hub-saved-row">
                            <div>
                                <strong>{{ $name !== '' ? $name : ($host !== '' ? $host : '未命名') }}</strong>
                                @if($adopted)
                                    <span class="badge badge-ok">已接入</span>
                                @else
                                    <span class="badge">未接入</span>
                                @endif
                                <div class="muted">{{ $host !== '' ? $host : $url }}</div>
                            </div>
                            <div class="hub-saved-ops">
                                <button type="button" class="btn-link hub-fill" data-url="{{ $url }}">探测</button>
                                @if($adopted)
                                    <a class="btn-link" href="/admin/video/collects">去采集源</a>
                                @else
                                    <button type="button" class="btn-link hub-adopt" data-id="{{ (int) ($u['id'] ?? 0) }}" data-url="{{ $url }}">接入</button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var U = AdminUi;
    var form = document.getElementById('hub-form');
    var input = document.getElementById('hub-url');
    var probeBtn = document.getElementById('hub-probe-btn');
    var emptyEl = document.getElementById('hub-empty');
    var failEl = document.getElementById('hub-fail');
    var failMsg = document.getElementById('hub-fail-msg');
    var okEl = document.getElementById('hub-ok');
    var last = null;

    function show(which) {
        emptyEl.hidden = which !== 'empty';
        failEl.hidden = which !== 'fail';
        okEl.hidden = which !== 'ok';
    }
    function fmtCount(n) {
        n = parseInt(n, 10) || 0;
        if (n <= 0) return '0';
        if (n > 99999) return '10万+';
        return String(n);
    }
    function renderOk(data) {
        last = data || {};
        document.getElementById('hub-ok-title').textContent = '接口可用';
        document.getElementById('hub-ok-host').textContent = last.host || '未知站点';
        var fmt = last.format === 'xml' ? 'XML' : (last.format === 'json' ? 'JSON' : '');
        document.getElementById('hub-ok-format').textContent = fmt ? ('· ' + fmt) : '';
        var stats = [];
        stats.push(fmtCount(last.type_count) + ' 个分类');
        if ((parseInt(last.record_count, 10) || 0) > 0) stats.push('约 ' + fmtCount(last.record_count) + ' 部');
        document.getElementById('hub-ok-stats').textContent = stats.join(' · ');
        var typesWrap = document.getElementById('hub-ok-types');
        typesWrap.innerHTML = '';
        (last.type_names || []).forEach(function (name) {
            var tag = document.createElement('span');
            tag.className = 'hub-tag';
            tag.textContent = name;
            typesWrap.appendChild(tag);
        });
        var samples = last.sample_titles || [];
        document.getElementById('hub-ok-samples').textContent = samples.length
            ? ('样例：' + samples.join('、'))
            : '接口通了，但这页没有返回片名样例。';
        var actions = document.getElementById('hub-ok-actions');
        actions.innerHTML = '';
        if (last.collect_id) {
            var go = document.createElement('a');
            go.className = 'btn';
            go.href = '/admin/video/collects';
            go.textContent = '去采集源绑定分类';
            actions.appendChild(go);
            var note = document.createElement('span');
            note.className = 'muted';
            note.textContent = '这个接口已经在采集源里了。';
            actions.appendChild(note);
        } else {
            if (!last.union_id) {
                var save = document.createElement('button');
                save.type = 'button';
                save.className = 'btn btn-muted';
                save.textContent = '收藏';
                save.addEventListener('click', saveCurrent);
                actions.appendChild(save);
            }
            var adopt = document.createElement('button');
            adopt.type = 'button';
            adopt.className = 'btn';
            adopt.textContent = '接入采集源';
            adopt.addEventListener('click', adoptCurrent);
            actions.appendChild(adopt);
            var hint = document.createElement('span');
            hint.className = 'muted';
            hint.textContent = '接入后还要绑定分类才会采片。';
            actions.appendChild(hint);
        }
        show('ok');
    }
    function probe(url) {
        url = String(url || input.value || '').trim();
        if (!url) {
            U.toast('请先粘贴接口地址', 'err');
            input.focus();
            return;
        }
        input.value = url;
        probeBtn.disabled = true;
        U.loading(true);
        U.post('/admin/video/tools/hub/run', {action: 'probe', api_url: url}).then(function (res) {
            U.loading(false);
            probeBtn.disabled = false;
            if (!res || res.code !== 0) {
                failMsg.textContent = (res && res.msg) || '探测失败';
                last = null;
                show('fail');
                U.toast((res && res.msg) || '探测失败', 'err');
                return;
            }
            if (res.data && res.data.api_url) input.value = res.data.api_url;
            renderOk(res.data || {});
            U.toast((res && res.msg) || '接口可用', 'ok');
        }).catch(function () {
            U.loading(false);
            probeBtn.disabled = false;
            failMsg.textContent = '网络错误，稍后再试。';
            show('fail');
            U.toast('探测失败', 'err');
        });
    }
    function saveUnion(thenAdopt) {
        if (!last || !last.api_url) {
            U.toast('请先探测成功', 'err');
            return;
        }
        var payload = {
            name: last.host || '资源站',
            api_url: last.api_url,
            status: 1
        };
        if (last.union_id) {
            if (thenAdopt) return adoptUnion(last.union_id);
            U.toast('已经收藏过', 'ok');
            return;
        }
        U.loading(true);
        U.post('/admin/video/unions/save', payload).then(function (res) {
            if (!res || res.code !== 0) {
                U.loading(false);
                U.toast((res && res.msg) || '收藏失败', 'err');
                return;
            }
            last.union_id = (res.data && res.data.id) || last.union_id;
            if (thenAdopt && last.union_id) return adoptUnion(last.union_id);
            U.loading(false);
            renderOk(last);
            U.toast('已收藏，下次直接点列表里的「探测」', 'ok');
        }).catch(function () {
            U.loading(false);
            U.toast('收藏失败', 'err');
        });
    }
    function saveCurrent() { saveUnion(false); }
    function adoptCurrent() { saveUnion(true); }
    function adoptUnion(id) {
        U.loading(true);
        return U.post('/admin/video/unions/adopt', {id: id}).then(function (res) {
            U.loading(false);
            if (!res || res.code !== 0) {
                U.toast((res && res.msg) || '接入失败', 'err');
                return;
            }
            U.toast((res && res.msg) || '已接入采集源', 'ok');
            location.href = '/admin/video/collects';
        }).catch(function () {
            U.loading(false);
            U.toast('接入失败', 'err');
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        probe(input.value);
    });
    document.querySelectorAll('.hub-fill').forEach(function (btn) {
        btn.addEventListener('click', function () {
            probe(btn.getAttribute('data-url') || '');
        });
    });
    document.querySelectorAll('.hub-adopt').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = parseInt(btn.getAttribute('data-id'), 10) || 0;
            if (!id) {
                U.toast('这条收藏不完整', 'err');
                return;
            }
            if (!U.confirm('接入采集源？不会立刻采片，还要去绑定分类。')) return;
            adoptUnion(id);
        });
    });
    if (String(input.value || '').trim()) probe(input.value);
})();
</script>
@endpush