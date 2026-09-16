@php
    $dmOn = (int) app(\App\Services\Video\VideoSettingService::class)->get('danmaku_enabled', '1') === 1;
@endphp
@if($dmOn)
<div id="danmaku-layer" aria-hidden="true"></div>
<form id="danmaku-bar" autocomplete="off">
    <input id="danmaku-text" maxlength="80" placeholder="发弹幕，回车发送" />
    <input id="danmaku-color" type="color" value="#ffffff" title="颜色" />
    <button type="submit">发送</button>
</form>
<style>
#player-shell{position:relative;width:100%;height:100%;overflow:hidden}
#danmaku-layer{position:absolute;inset:0;pointer-events:none;overflow:hidden;z-index:3}
.dm-item{position:absolute;white-space:nowrap;font:600 18px/1.2 "Segoe UI","PingFang SC",sans-serif;text-shadow:0 0 2px #000,1px 1px 2px #000;left:100%;transition:transform 8s linear;will-change:transform}
.dm-item.is-top,.dm-item.is-bottom{left:50%;transform:translateX(-50%);transition:opacity .4s}
#danmaku-bar{position:absolute;left:0;right:0;bottom:0;z-index:4;display:flex;gap:6px;padding:8px;background:linear-gradient(transparent,rgba(0,0,0,.65))}
#danmaku-bar input[type=text],#danmaku-text{flex:1;height:32px;border:0;border-radius:4px;padding:0 10px;background:rgba(255,255,255,.92)}
#danmaku-color{width:36px;height:32px;border:0;background:transparent;padding:0}
#danmaku-bar button{height:32px;border:0;border-radius:4px;padding:0 12px;background:#1e9fff;color:#fff;cursor:pointer}
</style>
<script>
(function () {
    var videoId = {{ (int) $video->id }};
    var episodeId = {{ (int) ($episode?->id ?? 0) }};
    var csrf = @json(csrf_token());
    var listUrl = @json(url('/danmaku')) + '/' + videoId + '?episode_id=' + episodeId;
    var sendUrl = @json(url('/danmaku')) + '/' + videoId;
    var layer = document.getElementById('danmaku-layer');
    var form = document.getElementById('danmaku-bar');
    var input = document.getElementById('danmaku-text');
    var colorEl = document.getElementById('danmaku-color');
    if (!layer || !form) return;
    var pool = [];
    var fired = {};
    var wall = Date.now();
    function media() { return document.querySelector('video'); }
    function now() {
        var v = media();
        if (v && !isNaN(v.currentTime)) return v.currentTime;
        return (Date.now() - wall) / 1000;
    }
    function spawn(d) {
        var el = document.createElement('span');
        el.className = 'dm-item';
        if (d.mode === 1) el.classList.add('is-top');
        if (d.mode === 2) el.classList.add('is-bottom');
        el.textContent = d.text;
        el.style.color = d.color || '#fff';
        if (d.mode === 1) el.style.top = '8%';
        else if (d.mode === 2) el.style.bottom = '48px';
        else el.style.top = (6 + Math.floor(Math.random() * 62)) + '%';
        layer.appendChild(el);
        if (d.mode === 0) {
            requestAnimationFrame(function () { el.style.transform = 'translateX(calc(-100vw - 100%))'; });
        }
        setTimeout(function () { el.remove(); }, 8500);
    }
    function tick() {
        var t = now();
        pool.forEach(function (d, i) {
            if (!fired[i] && t >= d.time) {
                fired[i] = true;
                spawn(d);
            }
        });
        requestAnimationFrame(tick);
    }
    fetch(listUrl, { credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (res) {
        pool = (res && res.data && res.data.list) ? res.data.list : [];
        tick();
    }).catch(function () { tick(); });
    var v = media();
    if (v) {
        v.addEventListener('seeked', function () {
            var t = now();
            fired = {};
            pool.forEach(function (d, i) { if (d.time < t) fired[i] = true; });
        });
    }
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var text = (input.value || '').trim();
        if (!text) return;
        var payload = { text: text, color: colorEl.value, time: now(), episode_id: episodeId, mode: 0 };
        fetch(sendUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
        }).then(function (r) { return r.json(); }).then(function (res) {
            if (!res || res.code !== 0) { alert((res && res.msg) || '发送失败'); return; }
            input.value = '';
            spawn(res.data || payload);
        }).catch(function () { alert('发送失败'); });
    });
})();
</script>
@endif
