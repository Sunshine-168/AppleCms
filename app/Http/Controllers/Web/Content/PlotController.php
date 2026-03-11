<?php

namespace App\Http\Controllers\Web\Content;

use App\Http\Controllers\Web\BaseController;
use App\Models\Vod;
use Illuminate\Http\Request;

class PlotController extends BaseController
{
    public function index()
    {
        $videos = Vod::query()
            ->where('vod_status', 1)
            ->where('vod_plot', 1)
            ->orderByDesc('vod_time')
            ->paginate(20);

        return view('plot.index', compact('videos'));
    }

    public function detail($id)
    {
        $video = Vod::query()
            ->where('vod_status', 1)
            ->where('vod_plot', 1)
            ->findOrFail($id);

        $plotNames = array_filter(explode('$$$', (string) $video->vod_plot_name));
        $plotDetails = array_filter(explode('$$$', (string) $video->vod_plot_detail));

        $plotList = [];
        foreach ($plotNames as $idx => $name) {
            $plotList[] = [
                'name' => $name,
                'detail' => $plotDetails[$idx] ?? '',
            ];
        }

        return view('plot.detail', compact('video', 'plotList'));
    }

    public function search(Request $request)
    {
        $response = $this->ensureSearchAllowed($request);
        if ($response !== null) {
            return $response;
        }

        $wd = $this->normalizeSearchKeyword($request->input('wd', ''));
        if ($wd === '') {
            return redirect()->route('plot.index');
        }

        $videos = Vod::query()
            ->where('vod_status', 1)
            ->where('vod_plot', 1)
            ->where(function ($query) use ($wd) {
                $query->where('vod_name', 'like', '%' . $wd . '%')
                    ->orWhere('vod_plot_name', 'like', '%' . $wd . '%')
                    ->orWhere('vod_plot_detail', 'like', '%' . $wd . '%');
            })
            ->orderByDesc('vod_time')
            ->paginate(20);

        return view('plot.search', compact('videos', 'wd'));
    }
}
