<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach($videos as $video)
    <url>
        <loc>{{ route('vod.detail', ['id' => $video->vod_id]) }}</loc>
        <lastmod>{{ date('c', (int) ($video->vod_time ?? time())) }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach
</urlset>
