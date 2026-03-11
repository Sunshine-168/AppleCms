<?php

namespace App\Http\Controllers\Web\System;

use App\Http\Controllers\Web\BaseController;
use App\Models\Art;
use App\Models\Topic;
use App\Models\Type;
use App\Models\Vod;

class MapController extends BaseController
{
    public function index()
    {
        $vodTypes = Type::where('type_status', 1)
            ->where('type_mid', 1)
            ->orderBy('type_sort')
            ->limit(30)
            ->get();
        $artTypes = Type::where('type_status', 1)
            ->where('type_mid', 2)
            ->orderBy('type_sort')
            ->limit(30)
            ->get();
        $topics = Topic::where('topic_status', 1)
            ->orderByDesc('topic_time')
            ->limit(30)
            ->get();
        $videos = Vod::where('vod_status', 1)
            ->orderByDesc('vod_time')
            ->limit(50)
            ->get();
        $articles = Art::where('art_status', 1)
            ->orderByDesc('art_time')
            ->limit(50)
            ->get();

        return view('map.index', compact('vodTypes', 'artTypes', 'topics', 'videos', 'articles'));
    }
}
