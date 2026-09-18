@php
    $settings = app(\App\Services\Video\VideoSettingService::class);
    $chatOn = (int) $settings->get('chatroom_enabled', '1') === 1;
    $needLogin = (int) $settings->get('chatroom_login', '0') === 1;
    $loggedIn = \Illuminate\Support\Facades\Auth::guard('member')->check();
    $videoId = (int) ($video->id ?? 0);
@endphp
@if($chatOn && $videoId > 0)
<div class="play-chat" id="play-chat" data-id="{{ $videoId }}" data-login="{{ $loggedIn ? '1' : '0' }}">
    <h2>本片讨论</h2>
    <div class="play-chat-list" id="play-chat-list"></div>
    @if($needLogin && ! $loggedIn)
        <p class="muted">请<a href="{{ url('/member/login') }}">登录</a>后再发言</p>
    @else
        <form id="play-chat-form" autocomplete="off">
            <input id="play-chat-text" maxlength="500" placeholder="说两句，回车发送">
            <button type="submit">发送</button>
        </form>
    @endif
</div>
<style>
.play-chat { margin: 20px 0; padding: 14px 16px; background: var(--card); border-radius: 8px; }
.play-chat h2 { margin: 0 0 10px; }
.play-chat-list { max-height: 240px; overflow: auto; margin-bottom: 10px; }
.play-chat-row { padding: 6px 0; border-bottom: 1px solid #222; font-size: 14px; display: flex; gap: 8px; align-items: flex-start; }
.play-chat-row b { color: var(--muted); font-weight: 600; flex: none; }
.play-chat-row span { flex: 1; }
.play-chat-row .chat-report { flex: none; font-size: 12px; color: var(--muted); }
.play-chat form { display: flex; gap: 8px; }
.play-chat input { flex: 1; }
</style>
<script>
(function () {
    var box = document.getElementById('play-chat');
    if (!box) return;
    var id = box.getAttribute('data-id');
    var canReport = box.getAttribute('data-login') === '1';
    var listEl = document.getElementById('play-chat-list');
    var form = document.getElementById('play-chat-form');
    var input = document.getElementById('play-chat-text');
    var csrf = document.querySelector('meta[name="csrf-token"]');
    var token = csrf ? csrf.content : '';
    var lastId = 0;
    var seen = {};
    function toast(msg, kind) {
        if (window.vodToast) window.vodToast(msg, kind || 'err');
        else alert(msg);
    }
    function row(d) {
        var el = document.createElement('div');
        el.className = 'play-chat-row';
        el.setAttribute('data-id', String(d.id || 0));
        var name = document.createElement('b');
        name.textContent = d.name || '游客';
        var text = document.createElement('span');
        text.textContent = d.text || '';
        el.appendChild(name);
        el.appendChild(text);
        if (canReport && d.id) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'chat-report';
            btn.textContent = '举报';
            btn.addEventListener('click', function () {
                fetch(@json(url('/chatroom/report')), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ id: d.id })
                }).then(function (r) { return r.json(); }).then(function (res) {
                    toast((res && res.msg) || '已提交', (!res || res.code !== 0) ? 'err' : 'ok');
                }).catch(function () { toast('举报失败'); });
            });
            el.appendChild(btn);
        }
        return el;
    }
    function append(rows, replace) {
        if (replace) {
            listEl.innerHTML = '';
            seen = {};
        }
        (rows || []).forEach(function (d) {
            var rid = parseInt(d.id, 10) || 0;
            if (rid && seen[rid]) return;
            if (rid) seen[rid] = 1;
            if (rid > lastId) lastId = rid;
            listEl.appendChild(row(d));
        });
        if (rows && rows.length) listEl.scrollTop = listEl.scrollHeight;
    }
    function load(initial) {
        var url = @json(url('/chatroom')) + '/' + id;
        if (!initial && lastId > 0) url += '?after_id=' + lastId;
        fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res || res.code !== 0) return;
                var rows = (res.data && res.data.list) ? res.data.list : [];
                if (res.data && res.data.last_id) lastId = Math.max(lastId, parseInt(res.data.last_id, 10) || 0);
                append(rows, !!initial);
            }).catch(function () {});
    }
    if (form && input) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var text = (input.value || '').trim();
            if (!text) return;
            fetch(@json(url('/chatroom')) + '/' + id, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ text: text })
            }).then(function (r) { return r.json(); }).then(function (res) {
                if (!res || res.code !== 0) {
                    toast((res && res.msg) || '发送失败');
                    return;
                }
                input.value = '';
                append([res.data || { name: '我', text: text }], false);
            }).catch(function () { toast('发送失败'); });
        });
    }
    load(true);
    setInterval(function () { load(false); }, 8000);
})();
</script>
@endif
