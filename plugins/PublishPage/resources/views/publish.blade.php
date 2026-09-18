<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <style>
        body { font-family: sans-serif; background:#0b0d12; color:#eee; margin:0; }
        .pub { max-width: 480px; margin: 48px auto; padding: 0 16px; }
        h1 { font-size: 22px; }
        .muted { color:#9aa3b2; }
        a.btn { display:block; background:#16a34a; color:#fff; text-align:center; padding:12px; border-radius:8px; margin:10px 0; text-decoration:none; }
        a.btn.alt { background:#223; }
        .foot { margin-top: 24px; color:#6b7280; font-size:13px; }
    </style>
</head>
<body>
<div class="pub">
    <h1>{{ $title }}</h1>
    @if($subtitle !== '')
        <p class="muted">{{ $subtitle }}</p>
    @endif
    @if($bookmark !== '')
        <p class="muted">{{ $bookmark }}</p>
    @endif
    @foreach($groups as $g)
        <a class="btn alt" href="{{ url('/publish/'.$g['id']) }}">{{ $g['title'] }}</a>
    @endforeach
    <a class="btn" href="{{ $enter }}">进入本站</a>
    @if($permanent_url)
        <p><a href="{{ $permanent_url }}">{{ $permanent_text !== '' ? $permanent_text : $permanent_url }}</a></p>
    @endif
    @if($footer !== '')
        <p class="foot">{{ $footer }}</p>
    @endif
</div>
</body>
</html>
