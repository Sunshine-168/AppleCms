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
@php
    $raw = (string) ($episode?->url ?? '');
    $parsed = (string) ($playUrl ?? '');
    $useIframe = $parser && trim((string) $parser->parse) !== '' && $parsed !== '' && $parsed !== $raw;
@endphp
@if($useIframe)
    <iframe src="{{ $parsed }}" allowfullscreen allow="autoplay"></iframe>
@elseif($parsed)
    <video controls autoplay src="{{ $parsed }}"></video>
@else
    <p class="muted">暂无播放地址</p>
@endif
</body>
</html>
