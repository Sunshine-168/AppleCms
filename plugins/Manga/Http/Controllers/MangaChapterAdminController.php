<?php

namespace Plugins\Manga\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Plugins\Manga\Models\MangaChapter;
use Plugins\Manga\Services\MangaService;

class MangaChapterAdminController extends Controller
{
    public function __construct(private readonly MangaService $manga) {}

    public function create(Request $request): View|Factory
    {
        $mangaId = (int) $request->query('manga_id', 0);
        $works = [];
        try {
            $works = $this->manga->adminWorkOptions();
        } catch (\Throwable) {
            $works = [];
        }

        return view('manga::admin.chapter_form', [
            'chapter' => [
                'manga_id' => $mangaId,
                'name' => '',
                'sort' => 0,
                'vip' => 0,
                'pics' => '',
            ],
            'isEdit' => false,
            'works' => $works,
        ]);
    }

    public function edit(int $id): View|Factory
    {
        if (! Schema::hasTable('plugin_manga_chapters')) {
            abort(404);
        }
        $row = MangaChapter::query()->find($id);
        if (! $row) {
            abort(404);
        }
        $works = [];
        try {
            $works = $this->manga->adminWorkOptions();
        } catch (\Throwable) {
            $works = [];
        }
        $data = $row->toArray();
        if (! isset($data['pics']) || $data['pics'] === null || $data['pics'] === '') {
            $lines = [];
            if (Schema::hasTable('plugin_manga_pics')) {
                $lines = \Plugins\Manga\Models\MangaPic::query()
                    ->where('chapter_id', $id)
                    ->orderBy('sort')
                    ->orderBy('id')
                    ->pluck('url')
                    ->all();
            }
            $data['pics'] = implode("\n", array_map('strval', $lines));
        }

        return view('manga::admin.chapter_form', [
            'chapter' => $data,
            'isEdit' => true,
            'works' => $works,
        ]);
    }
}
