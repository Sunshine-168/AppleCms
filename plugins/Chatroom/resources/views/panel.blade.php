@php
    $chatOn = (int) app(\App\Services\Video\VideoSettingService::class)->get('chatroom_enabled', '1') === 1;
    $videoId = (int) ($video->id ?? 0);
@endphp
@if($chatOn && $videoId > 0)
<div class="play-chat" id="play-chat" data-id="{{ $videoId }}">
    <h2>本片讨论</h2>
    <div class="play-chat-list" id="play-chat-list"></div>
    <form id="play-chat-form" autocomplete="off">
        <input id="play-chat-text" maxlength="200" placeholder="说两句，回车发送">
        <button type="submit">发送</button>
    </form>
</div>
<style>
.play-chat { margin: 20px 0; padding: 14px 16px; background: var(--card); border-radius: 8px; }
.play-chat h2 { margin: 0 0 10px; }
.play-chat-list { max-height: 240px; overflow: auto; margin-bottom: 10px; }
.play-chat-row { padding: 6px 0; border-bottom: 1px solid #222; font-size: 14px; }
.play-chat-row b { color: var(--muted); margin-right: 8px; font-weight: 600; }
.play-chat form { display: flex; gap: 8px; }
.play-chat input { flex: 1; }
</style>
<script>
(function () {
    var box = document.getElementById('play-chat');
    if (!box) return;
    var id = box.getAttribute('data-id');
    var listEl = document.getElementById('play-chat-list');
    var form = document.getElementById('play-chat-form');
    var input = document.getElementById('play-chat-text');
    var csrf = document.querySelector('meta[name="csrf-token"]');
    var token = csrf ? csrf.content : '';
    function row(d) {
        var el = document.createElement('div');
        el.className = 'play-chat-row';
        var name = document.createElement('b');
        name.textContent = d.name || '游客';
        var text = document.createElement('span');
        text.textContent = d.text || '';
        el.appendChild(name);
        el.appendChild(text);
        return el;
    }
    function load() {
        fetch(@json(url('/chatroom')) + '/' + id, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                listEl.innerHTML = '';
                var rows = (res && res.data && res.data.list) ? res.data.list : [];
                rows.forEach(function (d) { listEl.appendChild(row(d)); });
                listEl.scrollTop = listEl.scrollHeight;
            }).catch(function () {});
    }
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
                if (window.vodToast) window.vodToast((res && res.msg) || '发送失败', 'err');
                else alert((res && res.msg) || '发送失败');
                return;
            }
            input.value = '';
            listEl.appendChild(row(res.data || { name: '我', text: text }));
            listEl.scrollTop = listEl.scrollHeight;
        }).catch(function () {
            if (window.vodToast) window.vodToast('发送失败', 'err');
        });
    });
    load();
})();
</script>
@endif
