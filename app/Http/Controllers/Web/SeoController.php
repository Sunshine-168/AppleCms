<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoTypeModel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

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
        $xml .= $this->pluginSitemapXml($request);
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

    private function pluginSitemapXml(Request $request): string
    {
        $since = $request->boolean('inc') ? time() - 86400 * 2 : 0;
        $xml = '';
        $xml .= $this->appendPluginSitemap(
            \Plugins\Manga\Services\MangaService::class,
            [['/manga', 'hourly'], ['/manga/rank', 'daily'], ['/manga/update', 'hourly']],
            \Plugins\Manga\Models\MangaType::class,
            '/manga/type/',
            \Plugins\Manga\Models\Manga::class,
            '/manga/',
            $since
        );
        $xml .= $this->appendPluginSitemap(
            \Plugins\Gallery\Services\GalleryService::class,
            [['/gallery', 'hourly']],
            \Plugins\Gallery\Models\GalleryType::class,
            '/gallery/type/',
            \Plugins\Gallery\Models\Gallery::class,
            '/gallery/',
            $since
        );
        $xml .= $this->appendPluginSitemap(
            \Plugins\Novel\Services\NovelService::class,
            [['/novel', 'hourly']],
            \Plugins\Novel\Models\NovelType::class,
            '/novel/type/',
            \Plugins\Novel\Models\Novel::class,
            '/novel/',
            $since
        );
        $xml .= $this->appendPluginSitemap(
            \Plugins\Live\Services\LiveService::class,
            [['/live', 'hourly']],
            \Plugins\Live\Models\LiveCategory::class,
            '/live/cate/',
            \Plugins\Live\Models\LiveChannel::class,
            '/live/',
            $since
        );

        return $xml;
    }

    /**
     * @param  class-string  $svcClass
     * @param  list<array{0:string,1:string}>  $homes
     * @param  class-string  $typeClass
     * @param  class-string  $itemClass
     */
    private function appendPluginSitemap(string $svcClass, array $homes, string $typeClass, string $typePrefix, string $itemClass, string $itemPrefix, int $since): string
    {
        if (! class_exists($svcClass)) {
            return '';
        }
        try {
            $svc = app($svcClass);
            if (! method_exists($svc, 'ready') || ! $svc->ready()) {
                return '';
            }
            $xml = '';
            foreach ($homes as $home) {
                $xml .= '<url><loc>'.e(url($home[0])).'</loc><changefreq>'.$home[1].'</changefreq></url>';
            }
            if (class_exists($typeClass)) {
                $typeTable = (new $typeClass)->getTable();
                $tq = $typeClass::query()->orderBy('id');
                if (method_exists($typeClass, 'scopePublished')) {
                    $tq->published();
                } elseif (Schema::hasColumn($typeTable, 'status')) {
                    $tq->where('status', 1);
                }
                foreach ($tq->limit(500)->get(['id']) as $type) {
                    $xml .= '<url><loc>'.e(url($typePrefix.$type->id)).'</loc><changefreq>daily</changefreq></url>';
                }
            }
            if (class_exists($itemClass)) {
                $iq = $itemClass::query()->orderByDesc('id');
                if (method_exists($itemClass, 'scopePublished')) {
                    $iq->published();
                }
                $table = (new $itemClass)->getTable();
                if ($since > 0 && Schema::hasColumn($table, 'updated_at')) {
                    $iq->where('updated_at', '>=', $since);
                }
                $cols = ['id'];
                if (Schema::hasColumn($table, 'updated_at')) {
                    $cols[] = 'updated_at';
                }
                foreach ($iq->limit(2000)->get($cols) as $row) {
                    $last = '';
                    if (isset($row->updated_at) && (int) $row->updated_at > 0) {
                        $last = '<lastmod>'.e(date('Y-m-d', (int) $row->updated_at)).'</lastmod>';
                    }
                    $xml .= '<url><loc>'.e(url($itemPrefix.$row->id)).'</loc>'.$last.'</url>';
                }
            }

            return $xml;
        } catch (\Throwable) {
            return '';
        }
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
