<?php

namespace App\Http\Controllers\Web\Content;

use App\Http\Controllers\Web\BaseController;
use App\Models\Topic;
use App\Models\Vod;
use App\Models\Art;
use Illuminate\Http\Request;

class TopicController extends BaseController
{
    public function index()
    {
        $topics = Topic::where('topic_status', 1)
                      ->orderBy('topic_time', 'desc')
                      ->paginate(20);
        return view('topic.index', compact('topics'));
    }

    public function detail($id)
    {
        $topic = Topic::where('topic_id', $id)
                     ->orWhere('topic_en', $id)
                     ->where('topic_status', 1)
                     ->firstOrFail();

        $vod_list = $this->getRelatedVods($topic);
        $art_list = $this->getRelatedArts($topic);
        
        $topic->increment('topic_hits');

        return view('topic.detail', compact('topic', 'vod_list', 'art_list'));
    }

    public function search(Request $request)
    {
        $response = $this->ensureSearchAllowed($request);
        if ($response !== null) {
            return $response;
        }

        $wd = $this->normalizeSearchKeyword($request->input('wd', ''));
        if (empty($wd)) {
            return redirect()->route('topic.index');
        }

        $topics = Topic::where('topic_status', 1)
                     ->where('topic_name', 'like', '%' . $wd . '%')
                     ->orderBy('topic_time', 'desc')
                     ->paginate(20);

        return view('topic.search', compact('topics', 'wd'));
    }

    protected function getRelatedVods($topic)
    {
        $vod_list = collect([]);
        
        if (!empty($topic->topic_rel_vod)) {
            $vod_ids = array_filter(explode(',', $topic->topic_rel_vod));
            $vods = Vod::whereIn('vod_id', $vod_ids)
                      ->where('vod_status', 1)
                      ->orderBy('vod_time', 'desc')
                      ->get();
            $vod_list = $vod_list->merge($vods);
        }
        
        if (!empty($topic->topic_tag)) {
            $tags = array_filter(explode(',', $topic->topic_tag));
            $vods = Vod::where('vod_status', 1)
                      ->where(function($q) use ($tags) {
                          foreach($tags as $tag) {
                              $q->orWhere('vod_tag', 'like', '%' . trim($tag) . '%');
                          }
                      })
                      ->orderBy('vod_time', 'desc')
                      ->limit(50)
                      ->get();
            $vod_list = $vod_list->merge($vods);
        }
        
        return $vod_list->unique('vod_id');
    }

    protected function getRelatedArts($topic)
    {
        $art_list = collect([]);
        
        if (!empty($topic->topic_rel_art)) {
            $art_ids = array_filter(explode(',', $topic->topic_rel_art));
            $arts = Art::whereIn('art_id', $art_ids)
                      ->where('art_status', 1)
                      ->orderBy('art_time', 'desc')
                      ->get();
            $art_list = $art_list->merge($arts);
        }
        
        if (!empty($topic->topic_tag)) {
            $tags = array_filter(explode(',', $topic->topic_tag));
            $arts = Art::where('art_status', 1)
                      ->where(function($q) use ($tags) {
                          foreach($tags as $tag) {
                              $q->orWhere('art_tag', 'like', '%' . trim($tag) . '%');
                          }
                      })
                      ->orderBy('art_time', 'desc')
                      ->limit(50)
                      ->get();
            $art_list = $art_list->merge($arts);
        }
        
        return $art_list->unique('art_id');
    }
}
