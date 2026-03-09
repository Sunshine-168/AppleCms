<?php

namespace App\Http\Controllers\Web\Content;

use App\Http\Controllers\Web\BaseController;
use App\Models\Actor;
use App\Models\Vod;
use Illuminate\Http\Request;

class ActorController extends BaseController
{
    public function index()
    {
        $actors = Actor::where('actor_status', 1)
                      ->orderBy('actor_time', 'desc')
                      ->paginate(24);
        return view('actor.index', compact('actors'));
    }

    public function detail($id)
    {
        $actor = Actor::with('type')
                     ->where('actor_status', 1)
                     ->findOrFail($id);
        
        $related_vods = Vod::where('vod_status', 1)
                          ->where('vod_actor', 'like', '%' . $actor->actor_name . '%')
                          ->orderBy('vod_time', 'desc')
                          ->take(12)
                          ->get();

        return view('actor.detail', compact('actor', 'related_vods'));
    }

    public function search(Request $request)
    {
        $response = $this->ensureSearchAllowed($request);
        if ($response !== null) {
            return $response;
        }

        $wd = $this->normalizeSearchKeyword($request->input('wd', ''));
        if (empty($wd)) {
            return redirect()->route('actor.index');
        }

        $actors = Actor::where('actor_status', 1)
                     ->where('actor_name', 'like', '%' . $wd . '%')
                     ->orderBy('actor_time', 'desc')
                     ->paginate(24);

        return view('actor.search', compact('actors', 'wd'));
    }
}
