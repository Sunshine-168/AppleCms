<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $video->title ?? '播放' }}</title>
    <style>
        html, body { margin:0; height:100%; background:#000; }
        video, iframe { width:100%; height:100%; border:0; object-fit:contain; display:block; }
        .muted { color:#999; padding:20px; }
    </style>
</head>
<body>
<div id="player-shell">
@php
    $raw = (string) ($episode?->url ?? '');
    $parsed = (string) ($playUrl ?? '');
    $useIframe = $parser && trim((string) $parser->parse) !== '' && $parsed !== '' && $parsed !== $raw;
    $isHls = (bool) preg_match('/\.m3u8(\?|$)/i', $raw) || str_contains(strtolower($raw), 'application/vnd.apple.mpegurl');
    $encrypt = (int) ($playEncrypt ?? 0) === 1;
    $buffer = (int) ($playBuffer ?? 5);
@endphp
@if($useIframe)
    <iframe id="vod-player" data-buffer="{{ $buffer }}" @if(!$encrypt) src="{{ $parsed }}" @endif allowfullscreen allow="autoplay"></iframe>
@elseif($parsed)
    <video id="vod-player" data-buffer="{{ $buffer }}" controls autoplay playsinline @if(!$encrypt && !$isHls) src="{{ $parsed }}" @endif></video>
    @if($isHls)
        <script src="https://cdn.jsdelivr.net/npm/hls.js@1.5.17/dist/hls.min.js"></script>
    @endif
@else
    <p class="muted">{{ $payError ?? '暂无播放地址' }}</p>
@endif
@if($parsed)
    @includeIf('danmaku::overlay')
@endif
</div>
@if($parsed)
<script>
(function () {
    var el = document.getElementById('vod-player');
    if (!el) return;
    var src = @if($encrypt) atob(@json(base64_encode($parsed))) @else @json($parsed) @endif;
    var raw = @if($encrypt) atob(@json(base64_encode($raw))) @else @json($raw) @endif;
    @if($useIframe)
        if (!el.getAttribute('src')) { el.src = src; }
    @elseif($isHls)
        if (window.Hls && Hls.isSupported()) {
            var hls = new Hls();
            hls.loadSource(raw);
            hls.attachMedia(el);
        } else {
            el.src = raw;
        }
    @else
        if (!el.getAttribute('src')) { el.src = src; }
    @endif
})();
</script>
@endif
<script>
(function(){
    var tryseeSeconds = {{ (int) ($trysee ?? 0) }};
    if (tryseeSeconds > 0) {
        setTimeout(function(){
            document.querySelectorAll('iframe, video').forEach(function(el){ el.remove(); });
            document.body.innerHTML = '<p class="muted">试看已结束</p>';
        }, tryseeSeconds * 1000);
    }
})();
</script>
</body>
</html>
