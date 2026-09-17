<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoTypeModel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class SeoController extends Controller
{
    public function sitemap(Request $request): Response
    {
        return response($this->sitemapXml($request), 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    public function sitemapXml(Request $request): string
    {
        $q = VideoModel::query()->published()->orderByDesc('id');
        if ($request->boolean('inc')) {
            $q->where('updated_at', '>=', time() - 86400 * 2);
        }
        $videos = $q->limit(5000)->get(['id', 'title', 'updated_at']);
        $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $xml .= '<url><loc>'.e(url('/')).'</loc><changefreq>hourly</changefreq></url>';
        foreach (VideoTypeModel::query()->active()->get() as $type) {
            $xml .= '<url><loc>'.e($type->url).'</loc><changefreq>daily</changefreq></url>';
        }
        $xml .= '<url><loc>'.e(vod_url('latest')).'</loc><changefreq>hourly</changefreq></url>';
        $xml .= '<url><loc>'.e(vod_url('topics')).'</loc><changefreq>daily</changefreq></url>';
        if (\Illuminate\Support\Facades\Schema::hasTable('video_topics')) {
            $topicQ = \App\Models\Video\VideoTopicModel::query();
            if (\Illuminate\Support\Facades\Schema::hasColumn('video_topics', 'status')) {
                $topicQ->where('status', 1);
            }
            foreach ($topicQ->orderByDesc('id')->limit(500)->get() as $topic) {
                $xml .= '<url><loc>'.e($topic->url).'</loc><changefreq>weekly</changefreq></url>';
            }
        }
        $xml .= '<url><loc>'.e(vod_url('actors')).'</loc><changefreq>weekly</changefreq></url>';
        $xml .= '<url><loc>'.e(vod_url('arts')).'</loc><changefreq>daily</changefreq></url>';
        foreach ($videos as $video) {
            $xml .= '<url><loc>'.e($video->url).'</loc><lastmod>'.e(date('Y-m-d', (int) $video->updated_at)).'</lastmod></url>';
        }
        $xml .= '</urlset>';

        return $xml;
    }

    public function rss(Request $request, ?string $engine = null): Response
    {
        $engine = strtolower(trim((string) ($engine ?: $request->query('ac', $request->query('engine', '')))));
        $xml = $this->rssXml($engine);
        $type = $engine === 'google' ? 'application/atom+xml; charset=utf-8' : 'application/rss+xml; charset=utf-8';
        if ($engine === 'baidu') {
            $type = 'application/xml; charset=utf-8';
        }

        return response($xml, 200, ['Content-Type' => $type]);
    }

    public function rssXml(string $engine = ''): string
    {
        $videos = VideoModel::query()->published()->orderByDesc('id')->limit(50)->get();

        return match ($engine) {
            'baidu' => $this->rssBaidu($videos),
            'google' => $this->rssGoogle($videos),
            default => $this->rssDefault($videos),
        };
    }

    public function robots(): Response
    {
        $txt = "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /member\nSitemap: ".url('/sitemap.xml')."\n";

        return response($txt, 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    private function rssBaidu(Collection $videos): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset>';
        foreach ($videos as $video) {
            $xml .= '<url><loc>'.e($video->url).'</loc><lastmod>'.e(date('Y-m-d', (int) $video->updated_at)).'</lastmod></url>';
        }
        $xml .= '</urlset>';

        return $xml;
    }

    private function rssGoogle(Collection $videos): string
    {
        $site = config('video.site.title', config('app.name'));
        $xml = '<?xml version="1.0" encoding="UTF-8"?><feed xmlns="http://www.w3.org/2005/Atom">';
        $xml .= '<title>'.e($site).'</title><link href="'.e(url('/')).'"/>';
        $xml .= '<updated>'.e(date('c')).'</updated>';
        foreach ($videos as $video) {
            $xml .= '<entry><title>'.e($video->title).'</title><link href="'.e($video->url).'"/>';
            $xml .= '<updated>'.e(date('c', (int) $video->updated_at)).'</updated>';
            $xml .= '<summary>'.e(strip_tags((string) $video->description)).'</summary></entry>';
        }
        $xml .= '</feed>';

        return $xml;
    }

    private function rssDefault(Collection $videos): string
    {
        $site = config('video.site.title', config('app.name'));
        $xml = '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel>';
        $xml .= '<title>'.e($site).'</title><link>'.e(url('/')).'</link>';
        foreach ($videos as $video) {
            $xml .= '<item><title>'.e($video->title).'</title><link>'.e($video->url).'</link>';
            $xml .= '<pubDate>'.e(date('r', (int) $video->updated_at)).'</pubDate>';
            $xml .= '<description>'.e(strip_tags((string) $video->description)).'</description></item>';
        }
        $xml .= '</channel></rss>';

        return $xml;
    }
}
