<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoTypeModel;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $videos = VideoModel::query()->published()->orderByDesc('id')->limit(5000)->get(['id', 'title', 'updated_at']);
        $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $xml .= '<url><loc>'.e(url('/')).'</loc><changefreq>hourly</changefreq></url>';
        foreach (VideoTypeModel::query()->active()->get() as $type) {
            $xml .= '<url><loc>'.e($type->url).'</loc><changefreq>daily</changefreq></url>';
        }
        foreach ($videos as $video) {
            $xml .= '<url><loc>'.e($video->url).'</loc><lastmod>'.e(date('Y-m-d', (int) $video->updated_at)).'</lastmod></url>';
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    public function rss(): Response
    {
        $site = config('video.site.title', config('app.name'));
        $videos = VideoModel::query()->published()->orderByDesc('id')->limit(50)->get();
        $xml = '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel>';
        $xml .= '<title>'.e($site).'</title><link>'.e(url('/')).'</link>';
        foreach ($videos as $video) {
            $xml .= '<item><title>'.e($video->title).'</title><link>'.e($video->url).'</link>';
            $xml .= '<description>'.e(strip_tags((string) $video->description)).'</description></item>';
        }
        $xml .= '</channel></rss>';

        return response($xml, 200, ['Content-Type' => 'application/rss+xml; charset=utf-8']);
    }

    public function robots(): Response
    {
        $txt = "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /member\nSitemap: ".url('/sitemap.xml')."\n";

        return response($txt, 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }
}
