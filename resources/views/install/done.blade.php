<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>安装完毕</title>
    <link rel="stylesheet" href="{{ asset('css/install.css') }}?v={{ @filemtime(public_path('css/install.css')) ?: '1' }}">
</head>
<body>
<div class="wrap install-done">
    <div class="brand"><span class="logo">苹</span> 苹果v12 安装</div>
    <div class="card" style="display:block">
        <div class="done">
            <div class="mark">✓</div>
            <h1>安装完毕</h1>
            <p class="muted"><strong>{{ $siteName }}</strong> 可以使用了。请用刚才设置的密码登录后台。</p>
            <p class="account">后台账号 <strong>{{ $username }}</strong></p>
            @if($demo)
                <p class="hint">已写入默认分类和示例影片。采集资源前，先在「采集资源」里绑定分类。</p>
            @endif
            <div class="btns">
                <a class="btn" href="{{ url('/admin/login') }}">进入后台</a>
                <a class="btn-muted" href="{{ url('/') }}">打开前台</a>
                <a class="btn-muted" href="{{ url('/install?way=deploy') }}">上线部署说明</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
