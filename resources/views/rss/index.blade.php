<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
<channel>
    <title><![CDATA[{{ config('maccms.site.site_name', 'MacCMS') }}]]></title>
    <link>{{ url('/') }}</link>
    <description><![CDATA[{{ config('maccms.site.site_description', config('maccms.site.site_name', 'MacCMS')) }}]]></description>
    <language>zh-cn</language>
    @foreach($videos as $video)
    <item>
        <title><![CDATA[{{ $video->vod_name }}]]></title>
        <link>{{ route('vod.detail', ['id' => $video->vod_id]) }}</link>
        <guid>{{ route('vod.detail', ['id' => $video->vod_id]) }}</guid>
        <description><![CDATA[{{ strip_tags((string) ($video->vod_blurb ?? $video->vod_content ?? '')) }}]]></description>
    </item>
    @endforeach
    @foreach($articles as $article)
    <item>
        <title><![CDATA[{{ $article->art_name }}]]></title>
        <link>{{ route('art.detail', ['id' => $article->art_id]) }}</link>
        <guid>{{ route('art.detail', ['id' => $article->art_id]) }}</guid>
        <description><![CDATA[{{ strip_tags((string) ($article->art_blurb ?? $article->art_content ?? '')) }}]]></description>
    </item>
    @endforeach
</channel>
</rss>
