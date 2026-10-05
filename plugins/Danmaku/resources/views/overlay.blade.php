@php
    $settings = app(\App\Services\Video\VideoSettingService::class);
    $dmOn = (int) $settings->get('danmaku_enabled', '1') === 1;
    $needLogin = (int) $settings->get('danmaku_login', '0') === 1;
    $loggedIn = \Illuminate\Support\Facades\Auth::guard('member')->check();
@endphp
@if($dmOn)
<div id="danmaku-layer" aria-hidden="true"></div>
@if($needLogin && ! $loggedIn)
    <div id="danmaku-bar" class="is-login">
        <button type="button" id="danmaku-toggle" class="on" aria-pressed="true" title="开关弹幕">弹幕</button>
        <a href="{{ url('/member/login') }}" target="_top">登录后发弹幕</a>
    </div>
@else
<form id="danmaku-bar" autocomplete="off">
    <button type="button" id="danmaku-toggle" class="on" aria-pressed="true" title="开关弹幕">弹幕</button>
    <div class="dm-modes" id="danmaku-modes" role="group" aria-label="弹幕位置">
        <button type="button" data-mode="0" class="on" title="滚动">滚</button>
        <button type="button" data-mode="1" title="顶部">顶</button>
        <button type="button" data-mode="2" title="底部">底</button>
    </div>
    <div class="dm-compose">
        <input id="danmaku-text" type="text" maxlength="120" placeholder="发条弹幕" aria-label="弹幕内容" />
        <label class="dm-color" title="弹幕颜色">
            <input id="danmaku-color" type="color" value="#ffffff" />
            <span class="dm-color-dot" aria-hidden="true"></span>
        </label>
        <button type="submit" class="dm-send">发送</button>
    </div>
</form>
@endif
<style>
#danmaku-layer{position:absolute;inset:0;pointer-events:none;overflow:hidden;z-index:4}
#player-stage #danmaku-layer,#player-shell>#danmaku-layer{position:absolute;top:0;left:0;right:0;bottom:0}
#player-shell>#danmaku-layer{bottom:auto;height:calc(100% - 44px)}
.dm-item{
    position:absolute;white-space:nowrap;
    font:600 16px/1.25 "PingFang SC","Microsoft YaHei","Segoe UI",sans-serif;
    text-shadow:0 1px 2px rgba(0,0,0,.85),0 0 6px rgba(0,0,0,.45);
    left:100%;transition:transform 8s linear;will-change:transform;pointer-events:none;
}
.dm-item.is-top,.dm-item.is-bottom{left:50%;transform:translateX(-50%);transition:opacity .35s ease}
#danmaku-bar{
    flex:0 0 44px;
    height:44px;
    z-index:6;
    display:flex;gap:8px;align-items:center;flex-wrap:nowrap;
    padding:6px 8px;
    background:#12151c;
    border-top:1px solid rgba(255,255,255,.08);
    box-sizing:border-box;
}
#danmaku-bar.is-login{justify-content:flex-start}
#danmaku-bar.is-login a{
    color:#fff;text-decoration:none;padding:6px 12px;border-radius:999px;
    background:rgba(61,139,253,.28);border:1px solid rgba(61,139,253,.5);font-size:13px
}
#danmaku-toggle{
    flex:0 0 auto;height:32px;padding:0 10px;border:0;border-radius:8px;
    background:rgba(255,255,255,.08);color:rgba(255,255,255,.55);
    font:700 12px/1 "PingFang SC","Microsoft YaHei",sans-serif;
    cursor:pointer;
}
#danmaku-toggle.on{background:#2f7ef0;color:#fff}
.dm-modes{
    display:inline-flex;flex:0 0 auto;
    padding:2px;gap:2px;
    border-radius:8px;
    background:rgba(255,255,255,.06);
}
.dm-modes button{
    height:28px;min-width:28px;border:0;border-radius:6px;
    padding:0 8px;background:transparent;color:rgba(255,255,255,.55);
    font:500 12px/1 "PingFang SC","Microsoft YaHei",sans-serif;
    cursor:pointer;
}
.dm-modes button.on{background:rgba(61,139,253,.95);color:#fff}
.dm-compose{
    flex:1;min-width:0;display:flex;align-items:center;gap:6px;
    height:32px;padding:2px 2px 2px 10px;
    border-radius:9px;
    background:rgba(255,255,255,.07);
    border:1px solid rgba(255,255,255,.08);
}
#danmaku-text{
    flex:1;min-width:0;height:100%;border:0;outline:none;padding:0;
    background:transparent;color:#fff;
    font:400 13px/1.2 "PingFang SC","Microsoft YaHei",sans-serif;
}
#danmaku-text::placeholder{color:rgba(255,255,255,.38)}
.dm-color{
    position:relative;width:24px;height:24px;flex:0 0 auto;
    border-radius:7px;overflow:hidden;cursor:pointer;
    border:1px solid rgba(255,255,255,.18);
}
.dm-color input{
    position:absolute;inset:0;opacity:0;width:100%;height:100%;cursor:pointer;border:0;padding:0;
}
.dm-color-dot{
    display:block;width:100%;height:100%;
    background:radial-gradient(circle at 50% 50%, var(--dm-color,#fff) 0 42%, transparent 44%),
               conic-gradient(#f44,#ff0,#4f4,#0ff,#44f,#f4f,#f44);
}
.dm-send{
    height:28px;flex:0 0 auto;border:0;border-radius:7px;
    padding:0 12px;cursor:pointer;
    background:linear-gradient(180deg,#4d97fd,#2f7ef0);
    color:#fff;font:600 12px/1 "PingFang SC","Microsoft YaHei",sans-serif;
}
@media (max-width:560px){
    .dm-item{font-size:14px}
    .dm-modes{display:none}
    #danmaku-toggle{padding:0 8px}
    .dm-send{padding:0 10px}
}
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
    var toggle = document.getElementById('danmaku-toggle');
    var input = document.getElementById('danmaku-text');
    var colorEl = document.getElementById('danmaku-color');
    var colorDot = document.querySelector('.dm-color-dot');
    var modes = document.getElementById('danmaku-modes');
    var stage = document.getElementById('player-stage');
    if (!layer) return;
    if (stage && layer.parentNode !== stage) stage.appendChild(layer);
    var mode = 0;
    var pool = [];
    var fired = {};
    var wall = Date.now();
    var show = true;
    try { show = localStorage.getItem('vod_danmaku_on') !== '0'; } catch (e) {}
    function setShow(on) {
        show = !!on;
        if (layer) layer.hidden = !show;
        if (toggle) {
            toggle.classList.toggle('on', show);
            toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
        }
        try { localStorage.setItem('vod_danmaku_on', show ? '1' : '0'); } catch (e) {}
    }
    setShow(show);
    if (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            setShow(!show);
        });
    }
    function syncColor() {
        if (colorDot && colorEl) colorDot.style.setProperty('--dm-color', colorEl.value || '#ffffff');
    }
    syncColor();
    if (colorEl) colorEl.addEventListener('input', syncColor);
    function media() { return document.querySelector('#player-stage video, video'); }
    function now() {
        var v = media();
        if (v && !isNaN(v.currentTime)) return v.currentTime;
        return (Date.now() - wall) / 1000;
    }
    function spawn(d) {
        if (!show) return;
        var el = document.createElement('span');
        el.className = 'dm-item';
        if (d.mode === 1) el.classList.add('is-top');
        if (d.mode === 2) el.classList.add('is-bottom');
        el.textContent = d.text;
        el.style.color = d.color || '#fff';
        if (d.mode === 1) el.style.top = '10%';
        else if (d.mode === 2) el.style.bottom = '12%';
        else el.style.top = (8 + Math.floor(Math.random() * 62)) + '%';
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
    function bindSeek() {
        var v = media();
        if (!v || v._dmSeek) return;
        v._dmSeek = true;
        v.addEventListener('seeked', function () {
            var t = now();
            fired = {};
            pool.forEach(function (d, i) { if (d.time < t) fired[i] = true; });
        });
    }
    bindSeek();
    setInterval(bindSeek, 1500);
    if (modes) {
        modes.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-mode]');
            if (!btn) return;
            mode = parseInt(btn.getAttribute('data-mode'), 10) || 0;
            Array.prototype.forEach.call(modes.querySelectorAll('[data-mode]'), function (b) {
                b.classList.toggle('on', b === btn);
            });
        });
    }
    if (form && form.tagName === 'FORM' && input) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var text = (input.value || '').trim();
            if (!text) return;
            var payload = { text: text, color: colorEl ? colorEl.value : '#ffffff', time: now(), episode_id: episodeId, mode: mode };
            fetch(sendUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            }).then(function (r) { return r.json(); }).then(function (res) {
                if (!res || res.code !== 0) {
                    if (window.vodToast) window.vodToast((res && res.msg) || '发送失败', 'err');
                    return;
                }
                input.value = '';
                spawn(res.data || payload);
            }).catch(function () {
                if (window.vodToast) window.vodToast('发送失败', 'err');
            });
        });
    }
})();
</script>
@endif
