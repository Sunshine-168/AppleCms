@php
    $settings = app(\App\Services\Video\VideoSettingService::class);
    $chatOn = (int) $settings->get('chatroom_enabled', '1') === 1;
    $needLogin = (int) $settings->get('chatroom_login', '0') === 1;
    $loggedIn = \Illuminate\Support\Facades\Auth::guard('member')->check();
    $videoId = (int) ($video->id ?? 0);
    $nick = $loggedIn ? (string) (\Illuminate\Support\Facades\Auth::guard('member')->user()->name ?? '会员') : '游客';
@endphp
@if($chatOn && $videoId > 0)
<div class="play-chat" id="play-chat" data-id="{{ $videoId }}" data-login="{{ $loggedIn ? '1' : '0' }}">
    <div class="play-chat-head">
        <h2>本片讨论</h2>
        <span class="muted play-chat-hint">边看边聊，文明发言</span>
    </div>
    <div class="play-chat-list" id="play-chat-list">
        <p class="play-chat-empty muted" id="play-chat-empty">还没有人发言，来抢沙发</p>
    </div>
    @if($needLogin && ! $loggedIn)
        <p class="play-chat-login muted">请<a href="{{ url('/member/login') }}">登录</a>后再发言</p>
    @else
        <form class="play-chat-form" id="play-chat-form" autocomplete="off">
            <span class="play-chat-who muted" title="{{ $nick }}">{{ $nick }}</span>
            <input id="play-chat-text" maxlength="500" placeholder="说两句，回车发送" aria-label="讨论内容">
            <button type="submit" class="btn-play btn-sm">发送</button>
        </form>
    @endif
</div>
<script>
(function () {
    var box = document.getElementById('play-chat');
    if (!box) return;
    var id = box.getAttribute('data-id');
    var canReport = box.getAttribute('data-login') === '1';
    var listEl = document.getElementById('play-chat-list');
    var emptyEl = document.getElementById('play-chat-empty');
    var form = document.getElementById('play-chat-form');
    var input = document.getElementById('play-chat-text');
    var csrf = document.querySelector('meta[name="csrf-token"]');
    var token = csrf ? csrf.content : '';
    var lastId = 0;
    var seen = {};
    function toast(msg, kind) {
        if (window.vodToast) window.vodToast(msg, kind || 'err');
    }
    function syncEmpty() {
        if (!emptyEl) return;
        var has = listEl.querySelector('.play-chat-row');
        emptyEl.hidden = !!has;
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
            listEl.querySelectorAll('.play-chat-row').forEach(function (n) { n.remove(); });
            seen = {};
        }
        (rows || []).forEach(function (d) {
            var rid = parseInt(d.id, 10) || 0;
            if (rid && seen[rid]) return;
            if (rid) seen[rid] = 1;
            if (rid > lastId) lastId = rid;
            listEl.appendChild(row(d));
        });
        syncEmpty();
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
            if (!text) {
                toast('先写点内容再发送');
                return;
            }
            var btn = form.querySelector('button[type=submit]');
            if (btn) btn.disabled = true;
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
            }).catch(function () { toast('发送失败'); })
            .finally(function () { if (btn) btn.disabled = false; });
        });
    }
    load(true);
    setInterval(function () { load(false); }, 8000);
})();
</script>
@endif
