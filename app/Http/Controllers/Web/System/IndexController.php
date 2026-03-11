<?php

namespace App\Http\Controllers\Web\System;

use App\Http\Controllers\Web\BaseController;
use App\Models\Vod;
use App\Models\Art;

class IndexController extends BaseController
{
    public function index()
    {
        $new_videos = Vod::where('vod_status', 1)
                        ->orderBy('vod_time', 'desc')
                        ->take(12)
                        ->get();
        
        $hot_videos = Vod::where('vod_status', 1)
                        ->orderBy('vod_hits', 'desc')
                        ->take(12)
                        ->get();
        
        $new_articles = Art::where('art_status', 1)
                          ->orderBy('art_time', 'desc')
                          ->take(5)
                          ->get();

        return view('index.index', compact('new_videos', 'hot_videos', 'new_articles'));
    }
}
