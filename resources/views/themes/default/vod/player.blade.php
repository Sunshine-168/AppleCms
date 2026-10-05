<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $video->title ?? '播放' }}</title>
    @if(($engine ?? '') === 'videojs')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/video.js@8.23.4/dist/video-js.min.css">
    @elseif(($engine ?? '') === 'dplayer')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dplayer@1.27.1/dist/DPlayer.min.css">
    @endif
    <style>
        html, body { margin:0; height:100%; background:#000; overflow:hidden; }
        #player-shell { width:100%; height:100%; position:relative; overflow:hidden; background:#000; }
        #vod-player {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
        }
        #vod-player iframe {
            width: 100%;
            height: 100%;
            border: 0;
            display: block;
            background: #000;
        }
        #vod-player .art-video-player,
        #vod-player .dplayer,
        #vod-player .video-js {
            width: 100% !important;
            height: 100% !important;
        }
        #vod-player .art-bottom,
        #vod-player .art-controls,
        #vod-player .dplayer-controller,
        #vod-player .vjs-control-bar {
            z-index: 20;
        }
        .muted { color:#999; padding:20px; }
    </style>
</head>
<body>
<div id="player-shell">
@php
    $engine = (string) ($engine ?? 'artplayer');
    $raw = (string) ($rawUrl ?? $episode?->url ?? '');
    $parsed = (string) ($playUrl ?? '');
    $media = $engine === 'iframe'
        ? ($parsed !== '' ? $parsed : $raw)
        : ($raw !== '' ? $raw : $parsed);
    $iframeFallback = (string) ($iframeFallback ?? '');
    $encrypt = (int) ($playEncrypt ?? 0) === 1;
    $buffer = (int) ($playBuffer ?? 5);
@endphp
@if($payError)
    <p class="muted">{{ $payError }}</p>
@elseif($engine === 'iframe' && ($parsed || $raw))
    <iframe id="vod-player" data-buffer="{{ $buffer }}" @if(!$encrypt) src="{{ $parsed !== '' ? $parsed : $raw }}" @endif allowfullscreen allow="autoplay; fullscreen"></iframe>
@elseif($media)
    <div id="vod-player" data-buffer="{{ $buffer }}"></div>
@else
    <p class="muted">暂无播放地址</p>
@endif
@if($media)
    @includeIf('danmaku::overlay')
@endif
</div>
@if($media && $engine !== 'iframe')
    <script src="https://cdn.jsdelivr.net/npm/hls.js@1.5.17/dist/hls.min.js"></script>
    @if($engine === 'artplayer')
        <script src="https://cdn.jsdelivr.net/npm/mpegts.js@1.7.3/dist/mpegts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/artplayer@5.2.3/dist/artplayer.js"></script>
    @elseif($engine === 'dplayer')
        <script src="https://cdn.jsdelivr.net/npm/flv.js@1.6.2/dist/flv.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/dplayer@1.27.1/dist/DPlayer.min.js"></script>
    @elseif($engine === 'videojs')
        <script src="https://cdn.jsdelivr.net/npm/video.js@8.23.4/dist/video.min.js"></script>
    @endif
@endif
@if($media)
<script>
(function () {
    var el = document.getElementById('vod-player');
    if (!el) return;
    var src = @if($encrypt) atob(@json(base64_encode($media))) @else @json($media) @endif;
    var iframeFallback = @if($encrypt && $iframeFallback !== '') atob(@json(base64_encode($iframeFallback))) @else @json($iframeFallback) @endif;
    var engine = @json($engine);
    var kind = /\.m3u8(\?|$)/i.test(src) || /mpegurl/i.test(src) ? 'hls' : (/\.flv(\?|$)/i.test(src) ? 'flv' : 'mp4');
    var inst = null;
    var fellBack = false;

    function mountIframe(url) {
        if (!url || fellBack) return false;
        fellBack = true;
        try {
            if (inst && typeof inst.destroy === 'function') inst.destroy();
            if (inst && typeof inst.dispose === 'function') inst.dispose();
        } catch (e) {}
        var shell = document.getElementById('player-shell');
        if (!shell) return false;
        shell.innerHTML = '';
        var frame = document.createElement('iframe');
        frame.id = 'vod-player';
        frame.src = url;
        frame.allow = 'autoplay; fullscreen';
        frame.setAttribute('allowfullscreen', '');
        shell.appendChild(frame);
        return true;
    }

    function customHls(video, url) {
        if (window.Hls && Hls.isSupported()) {
            var hls = new Hls({
                enableWorker: true,
                xhrSetup: function (xhr) {
                    try { xhr.withCredentials = false; } catch (e) {}
                }
            });
            hls.on(Hls.Events.ERROR, function (_e, data) {
                if (!data || !data.fatal) return;
                try { hls.destroy(); } catch (err) {}
                if (mountIframe(iframeFallback)) return;
                var tip = document.createElement('p');
                tip.className = 'muted';
                tip.textContent = '线路加载失败，请切换到其它线路重试';
                el.innerHTML = '';
                el.appendChild(tip);
            });
            hls.loadSource(url);
            hls.attachMedia(video);
            video._hls = hls;
        } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
            video.src = url;
        } else {
            video.src = url;
        }
    }
    function customFlv(video, url, lib) {
        if (lib === 'mpegts' && window.mpegts && mpegts.isSupported()) {
            var p = mpegts.createPlayer({ type: 'flv', url: url, isLive: false });
            p.attachMediaElement(video);
            p.load();
            video._flv = p;
            return;
        }
        if (window.flvjs && flvjs.isSupported()) {
            var flv = flvjs.createPlayer({ type: 'flv', url: url });
            flv.attachMediaElement(video);
            flv.load();
            video._flv = flv;
            return;
        }
        video.src = url;
    }

    if (engine === 'iframe') {
        if (!el.getAttribute('src')) el.src = src;
        return;
    }
    if (engine === 'dplayer' && window.DPlayer) {
        inst = new DPlayer({
            container: el,
            autoplay: true,
            hotkey: true,
            preload: 'auto',
            volume: 0.8,
            lang: 'zh-cn',
            playbackSpeed: [0.5, 0.75, 1, 1.25, 1.5, 2],
            video: { url: src, type: kind === 'mp4' ? 'auto' : kind }
        });
        window.__vodPlayer = inst;
        return;
    }
    if (engine === 'videojs' && window.videojs) {
        el.innerHTML = '<video id="vod-vjs" class="video-js vjs-big-play-centered vjs-fill" controls playsinline></video>';
        inst = videojs('vod-vjs', {
            autoplay: true,
            controls: true,
            preload: 'auto',
            fill: true,
            playbackRates: [0.5, 0.75, 1, 1.25, 1.5, 2],
            controlBar: {
                volumePanel: { inline: false },
                pictureInPictureToggle: true
            }
        });
        var type = kind === 'hls' ? 'application/x-mpegURL' : (kind === 'flv' ? 'video/x-flv' : 'video/mp4');
        inst.src({ src: src, type: type });
        window.__vodPlayer = inst;
        return;
    }
    if (window.Artplayer) {
        inst = new Artplayer({
            container: el,
            url: src,
            volume: 0.8,
            autoplay: true,
            muted: false,
            pip: true,
            screenshot: false,
            setting: true,
            loop: false,
            flip: true,
            playbackRate: true,
            aspectRatio: true,
            fullscreen: true,
            fullscreenWeb: true,
            miniProgressBar: true,
            mutex: true,
            backdrop: true,
            playsInline: true,
            autoPlayback: true,
            airplay: true,
            hotkey: true,
            lock: true,
            fastForward: true,
            autoOrientation: true,
            lang: 'zh-cn',
            theme: '#10b981',
            type: kind === 'mp4' ? '' : kind,
            moreVideoAttr: {
                playsInline: true,
                'webkit-playsinline': true,
                controls: false,
                preload: 'auto'
            },
            customType: {
                m3u8: function (video, url) { customHls(video, url); },
                hls: function (video, url) { customHls(video, url); },
                flv: function (video, url) { customFlv(video, url, 'mpegts'); }
            }
        });
        inst.on('error', function () {
            mountIframe(iframeFallback);
        });
        window.__vodPlayer = inst;
        return;
    }
    el.innerHTML = '<video controls autoplay playsinline src="' + src.replace(/"/g, '&quot;') + '"></video>';
})();
</script>
@endif
<script>
(function(){
    var tryseeSeconds = {{ (int) ($trysee ?? 0) }};
    if (tryseeSeconds < 1) return;
    setTimeout(function(){
        try {
            if (window.__vodPlayer && typeof window.__vodPlayer.destroy === 'function') window.__vodPlayer.destroy();
            if (window.__vodPlayer && typeof window.__vodPlayer.dispose === 'function') window.__vodPlayer.dispose();
        } catch (e) {}
        document.body.innerHTML = '<p class="muted">试看已结束</p>';
    }, tryseeSeconds * 1000);
})();
</script>
</body>
</html>
