<?php

namespace App\Http\Controllers\Web\Content;

use App\Http\Controllers\Web\BaseController;
use App\Models\Type;
use App\Models\Website;
use Illuminate\Http\Request;

class WebsiteController extends BaseController
{
    public function index()
    {
        $websites = Website::query()
            ->where('website_status', 1)
            ->orderByDesc('website_time')
            ->paginate(24);

        return view('website.index', compact('websites'));
    }

    public function type($id)
    {
        $type = Type::query()->findOrFail($id);

        $websites = Website::query()
            ->where('website_status', 1)
            ->where('type_id', $id)
            ->orderByDesc('website_time')
            ->paginate(24);

        return view('website.type', compact('websites', 'type'));
    }

    public function detail($id)
    {
        $website = Website::query()
            ->where('website_status', 1)
            ->findOrFail($id);

        return view('website.detail', compact('website'));
    }

    public function search(Request $request)
    {
        $response = $this->ensureSearchAllowed($request);
        if ($response !== null) {
            return $response;
        }

        $wd = $this->normalizeSearchKeyword($request->input('wd', ''));
        if ($wd === '') {
            return redirect()->route('website.index');
        }

        $websites = Website::query()
            ->where('website_status', 1)
            ->where(function ($query) use ($wd) {
                $query->where('website_name', 'like', '%' . $wd . '%')
                    ->orWhere('website_tag', 'like', '%' . $wd . '%')
                    ->orWhere('website_class', 'like', '%' . $wd . '%');
            })
            ->orderByDesc('website_time')
            ->paginate(24);

        return view('website.search', compact('websites', 'wd'));
    }
}
