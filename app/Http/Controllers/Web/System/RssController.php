<?php

namespace App\Http\Controllers\Web\System;

use App\Http\Controllers\Web\BaseController;
use App\Models\Art;
use App\Models\Vod;
use Illuminate\Http\Request;

class RssController extends BaseController
{
    public function index(Request $request)
    {
        $page = max((int) $request->input('page', 1), 1);
        $videos = $this->getVideos(20, $page);
        $articles = $this->getArticles(20, $page);

        return response()
            ->view('rss.index', compact('videos', 'articles', 'page'))
            ->header('Content-Type', 'text/xml; charset=utf-8');
    }

    public function baidu(Request $request)
    {
        return $this->sitemapResponse($request, 'baidu');
    }

    public function google(Request $request)
    {
        return $this->sitemapResponse($request, 'google');
    }

    public function so(Request $request)
    {
        return $this->sitemapResponse($request, 'so');
    }

    public function sogou(Request $request)
    {
        return $this->sitemapResponse($request, 'sogou');
    }

    public function bing(Request $request)
    {
        return $this->sitemapResponse($request, 'bing');
    }

    public function sm(Request $request)
    {
        return $this->sitemapResponse($request, 'sm');
    }

    protected function getVideos(int $limit, int $page = 1)
    {
        $offset = max($page - 1, 0) * $limit;

        return Vod::where('vod_status', 1)
            ->orderBy('vod_time', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    protected function getArticles(int $limit, int $page = 1)
    {
        $offset = max($page - 1, 0) * $limit;

        return Art::where('art_status', 1)
            ->orderBy('art_time', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    protected function sitemapResponse(Request $request, string $feed)
    {
        $page = max((int) $request->input('page', 1), 1);
        $videos = $this->getVideos(100, $page);

        return $this->xmlResponse('rss.sitemap', compact('videos', 'page', 'feed'));
    }

    protected function xmlResponse($view, $data = [])
    {
        return response()
            ->view($view, $data)
            ->header('Content-Type', 'text/xml; charset=utf-8');
    }
}
