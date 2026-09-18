<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $group['title'] }}</title>
    <style>
        body { font-family: sans-serif; background:#0b0d12; color:#eee; margin:0; }
        .pub { max-width: 480px; margin: 48px auto; padding: 0 16px; }
        h1 { font-size: 22px; }
        .muted { color:#9aa3b2; }
        .row { background:#12141a; padding:10px 12px; border-radius:8px; margin:8px 0; word-break:break-all; }
        a.btn { display:block; background:#16a34a; color:#fff; text-align:center; padding:12px; border-radius:8px; margin:16px 0 10px; text-decoration:none; }
        a.back { color:#9aa3b2; }
        .foot { margin-top: 24px; color:#6b7280; font-size:13px; }
    </style>
</head>
<body>
<div class="pub">
    <h1>{{ $group['title'] }}</h1>
    @if($group['hint'] !== '')
        <p class="muted">{{ $group['hint'] }}</p>
    @endif
    @foreach($group['urls'] as $u)
        <div class="row">{{ $u['name'] }}<br><a href="{{ $u['url'] }}">{{ $u['url'] }}</a></div>
    @endforeach
    <a class="btn" href="{{ $enter }}">进入本站</a>
    <p><a class="back" href="{{ url('/') }}">返回发布页</a></p>
    @if($permanent_url)
        <p><a href="{{ $permanent_url }}">{{ $permanent_text !== '' ? $permanent_text : $permanent_url }}</a></p>
    @endif
    @if($footer !== '')
        <p class="foot">{{ $footer }}</p>
    @endif
</div>
</body>
</html>
