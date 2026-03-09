<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('maccms.site.site_name', 'SiteMap') }} - SiteMap</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #333; }
        h1, h2 { margin-bottom: 12px; }
        .section { margin-bottom: 24px; }
        .links { display: flex; flex-wrap: wrap; gap: 10px 18px; }
        .links a { color: #1e9fff; text-decoration: none; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>{{ config('maccms.site.site_name', 'SiteMap') }}</h1>

    <div class="section">
        <h2>视频分类</h2>
        <div class="links">
            @foreach($vodTypes as $type)
                <a href="{{ route('vod.type', ['id' => $type->type_id]) }}">{{ $type->type_name }}</a>
            @endforeach
        </div>
    </div>

    <div class="section">
        <h2>文章分类</h2>
        <div class="links">
            @foreach($artTypes as $type)
                <a href="{{ route('art.type', ['id' => $type->type_id]) }}">{{ $type->type_name }}</a>
            @endforeach
        </div>
    </div>

    <div class="section">
        <h2>专题</h2>
        <div class="links">
            @foreach($topics as $topic)
                <a href="{{ route('topic.detail', ['id' => $topic->topic_id]) }}">{{ $topic->topic_name }}</a>
            @endforeach
        </div>
    </div>

    <div class="section">
        <h2>最新视频</h2>
        <div class="links">
            @foreach($videos as $video)
                <a href="{{ route('vod.detail', ['id' => $video->vod_id]) }}">{{ $video->vod_name }}</a>
            @endforeach
        </div>
    </div>

    <div class="section">
        <h2>最新文章</h2>
        <div class="links">
            @foreach($articles as $article)
                <a href="{{ route('art.detail', ['id' => $article->art_id]) }}">{{ $article->art_name }}</a>
            @endforeach
        </div>
    </div>
</body>
</html>
