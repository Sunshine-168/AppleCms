<?php

namespace Plugins\Manga\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Plugins\Manga\Models\MangaPic;
use Plugins\Manga\Services\MangaService;

class MangaPicAdminController extends Controller
{
    public function __construct(private readonly MangaService $manga) {}

    public function create(Request $request): View|Factory
    {
        $works = [];
        try {
            $works = $this->manga->adminWorkOptions();
        } catch (\Throwable) {
            $works = [];
        }

        return view('manga::admin.pic_form', [
            'pic' => [
                'manga_id' => (int) $request->query('manga_id', 0),
                'chapter_id' => (int) $request->query('chapter_id', 0),
                'url' => '',
                'sort' => 0,
            ],
            'isEdit' => false,
            'works' => $works,
        ]);
    }

    public function edit(int $id): View|Factory
    {
        if (! Schema::hasTable('plugin_manga_pics')) {
            abort(404);
        }
        $row = MangaPic::query()->find($id);
        if (! $row) {
            abort(404);
        }
        $works = [];
        try {
            $works = $this->manga->adminWorkOptions();
        } catch (\Throwable) {
            $works = [];
        }

        return view('manga::admin.pic_form', [
            'pic' => $row->toArray(),
            'isEdit' => true,
            'works' => $works,
        ]);
    }
}
