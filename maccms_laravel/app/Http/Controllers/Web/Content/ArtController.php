<?php

namespace App\Http\Controllers\Web\Content;

use App\Http\Controllers\Web\BaseController;
use App\Models\Art;
use App\Models\Type;
use Illuminate\Http\Request;

class ArtController extends BaseController
{
    public function index()
    {
        $articles = Art::with('type')
                      ->where('art_status', 1)
                      ->orderBy('art_time', 'desc')
                      ->paginate(20);
        $types = $this->getTypes(2);
        
        return view('art.index', compact('articles', 'types'));
    }

    public function type($id)
    {
        $type = Type::findOrFail($id);
        $articles = Art::where('type_id', $id)
                      ->where('art_status', 1)
                      ->orderBy('art_time', 'desc')
                      ->paginate(20);
        $types = $this->getTypes(2);
        
        return view('art.type', compact('type', 'articles', 'types'));
    }

    public function detail($id)
    {
        $article = Art::with('type')
                     ->where('art_status', 1)
                     ->findOrFail($id);
        $types = $this->getTypes(2);
        
        return view('art.detail', compact('article', 'types'));
    }

    public function search(Request $request)
    {
        $response = $this->ensureSearchAllowed($request);
        if ($response !== null) {
            return $response;
        }

        $wd = $this->normalizeSearchKeyword($request->input('wd', ''));
        if (empty($wd)) {
            return redirect()->route('art.index');
        }

        $articles = Art::where('art_status', 1)
                      ->where('art_name', 'like', '%' . $wd . '%')
                      ->orderBy('art_time', 'desc')
                      ->paginate(20);
        $types = $this->getTypes(2);

        return view('art.search', compact('articles', 'types', 'wd'));
    }
}
