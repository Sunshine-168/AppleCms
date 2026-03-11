<?php

namespace App\Http\Controllers\Web\Content;

use App\Http\Controllers\Web\BaseController;
use App\Models\Vod;
use App\Models\Type;
use Illuminate\Http\Request;

class VodController extends BaseController
{
    public function index()
    {
        $videos = Vod::with('type')
                    ->where('vod_status', 1)
                    ->orderBy('vod_time', 'desc')
                    ->paginate(24);
        $types = $this->getTypes(1);
        
        return view('vod.index', compact('videos', 'types'));
    }

    public function type($id)
    {
        $type = Type::findOrFail($id);
        $videos = Vod::where('type_id', $id)
                    ->where('vod_status', 1)
                    ->orderBy('vod_time', 'desc')
                    ->paginate(24);
        $types = $this->getTypes(1);
        
        return view('vod.type', compact('type', 'videos', 'types'));
    }

    public function detail($id)
    {
        $video = Vod::with('type')
                   ->where('vod_status', 1)
                   ->findOrFail($id);
        $types = $this->getTypes(1);
        
        $playList = $this->parsePlayList($video->vod_play_from, $video->vod_play_url);
        
        return view('vod.detail', compact('video', 'types', 'playList'));
    }

    public function play($id, $sid, $nid)
    {
        $video = Vod::with('type')
                   ->where('vod_status', 1)
                   ->findOrFail($id);
        $types = $this->getTypes(1);
        
        $playList = $this->parsePlayList($video->vod_play_from, $video->vod_play_url);
        
        $currentPlayer = $playList[$sid - 1] ?? null;
        $currentEpisode = $currentPlayer['urls'][$nid - 1] ?? null;

        if (!$currentPlayer || !$currentEpisode) {
            abort(404, 'Video source not found');
        }

        return view('vod.play', compact('video', 'types', 'playList', 'currentPlayer', 'currentEpisode', 'sid', 'nid'));
    }

    public function search(Request $request)
    {
        $response = $this->ensureSearchAllowed($request);
        if ($response !== null) {
            return $response;
        }

        $wd = $this->normalizeSearchKeyword($request->input('wd', ''));
        if (empty($wd)) {
            return redirect()->route('vod.index');
        }

        $videos = Vod::where('vod_status', 1)
                    ->where('vod_name', 'like', '%' . $wd . '%')
                    ->orderBy('vod_time', 'desc')
                    ->paginate(24);
        $types = $this->getTypes(1);

        return view('vod.search', compact('videos', 'types', 'wd'));
    }
}
