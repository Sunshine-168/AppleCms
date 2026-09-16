<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title>{{ conf('name') }} - 全部功能</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.staticfile.net/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ @filemtime(public_path('css/admin.css')) ?: '1' }}">
</head>
<body class="iframe-body">
<div class="dash-iframe">
    <div class="card card-panel">
        <div class="card-header">
            <span>全部功能</span>
        </div>
        <div class="card-body">
            <p class="muted" style="margin:0">侧栏只保留日常入口。缺封面、无地址、待审这类筛选在「影片」页顶部。下面是其余低频功能。</p>
        </div>
    </div>

    @foreach($catalog as $block)
        <div class="card card-panel">
            <div class="card-header">
                <span>{{ $block['title'] }}</span>
                @if(! empty($block['hint']))
                    <span class="muted">{{ $block['hint'] }}</span>
                @endif
            </div>
            <div class="card-body">
                <div class="tool-grid">
                    @foreach($block['items'] as $item)
                        <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>
</body>
</html>
