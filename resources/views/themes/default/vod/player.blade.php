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
        html, body { margin:0; height:100%; background:#000; }
        #player-shell { width:100%; height:100%; position:relative; }
        video, iframe, .video-js, .dplayer, .artplayer-app { width:100%; height:100%; border:0; display:block; }
        .video-js { position:absolute; inset:0; }
        .muted { color:#999; padding:20px; }
    </style>
</head>
<body>
<div id="player-shell">
@php
    $engine = (string) ($engine ?? 'artplayer');
    $raw = (string) ($rawUrl ?? $episode?->url ?? '');
    $parsed = (string) ($playUrl ?? '');
    $media = $engine === 'iframe' ? $parsed : $raw;
    $encrypt = (int) ($playEncrypt ?? 0) === 1;
    $buffer = (int) ($playBuffer ?? 5);
@endphp
@if($payError)
    <p class="muted">{{ $payError }}</p>
@elseif($engine === 'iframe' && $parsed)
    <iframe id="vod-player" data-buffer="{{ $buffer }}" @if(!$encrypt) src="{{ $parsed }}" @endif allowfullscreen allow="autoplay"></iframe>
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
    var engine = @json($engine);
    var kind = /\.m3u8(\?|$)/i.test(src) || /mpegurl/i.test(src) ? 'hls' : (/\.flv(\?|$)/i.test(src) ? 'flv' : 'mp4');
    var inst = null;

    function customHls(video, url) {
        if (window.Hls && Hls.isSupported()) {
            var hls = new Hls();
            hls.loadSource(url);
            hls.attachMedia(video);
            video._hls = hls;
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
            video: { url: src, type: kind === 'mp4' ? 'auto' : kind }
        });
        window.__vodPlayer = inst;
        return;
    }
    if (engine === 'videojs' && window.videojs) {
        el.innerHTML = '<video id="vod-vjs" class="video-js vjs-big-play-centered vjs-fill" controls playsinline></video>';
        inst = videojs('vod-vjs', { autoplay: true, controls: true, preload: 'auto', fill: true });
        var type = kind === 'hls' ? 'application/x-mpegURL' : (kind === 'flv' ? 'video/x-flv' : 'video/mp4');
        inst.src({ src: src, type: type });
        window.__vodPlayer = inst;
        return;
    }
    if (window.Artplayer) {
        inst = new Artplayer({
            container: el,
            url: src,
            autoplay: true,
            mutex: true,
            fullscreen: true,
            playbackRate: true,
            theme: '#10b981',
            type: kind === 'mp4' ? '' : kind,
            customType: {
                m3u8: function (video, url) { customHls(video, url); },
                hls: function (video, url) { customHls(video, url); },
                flv: function (video, url) { customFlv(video, url, 'mpegts'); }
            }
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
