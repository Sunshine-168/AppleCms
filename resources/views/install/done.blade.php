<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>安装完成</title>
    <link rel="stylesheet" href="{{ asset('css/install.css') }}">
</head>
<body>
<div class="wrap">
    <div class="brand"><span class="logo">V</span> 影视系统安装</div>
    <div class="card" style="display:block">
        <div class="done">
            <div class="mark">✓</div>
            <h1>{{ $siteName }} 已经装好</h1>
            <p class="muted">后台账号 <strong>{{ $username }}</strong>，用刚才设置的密码登录。</p>
            @if($demo)
                <p class="hint">已写入默认分类和示例影片，采集资源前请先在「采集资源」里绑定分类。</p>
            @endif
            <div class="btns">
                <a class="btn" href="{{ url('/admin/login') }}">进入后台</a>
                <a class="btn-muted" href="{{ url('/') }}">打开前台</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
