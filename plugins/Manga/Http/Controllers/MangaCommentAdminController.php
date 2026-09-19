<?php

namespace Plugins\Manga\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Plugins\Manga\Models\MangaComment;
use Plugins\Manga\Services\MangaService;

class MangaCommentAdminController extends Controller
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

        return view('manga::admin.comment_form', [
            'comment' => [
                'manga_id' => (int) $request->query('manga_id', 0),
                'author_name' => '',
                'content' => '',
                'status' => 1,
            ],
            'isEdit' => false,
            'works' => $works,
        ]);
    }

    public function edit(int $id): View|Factory
    {
        if (! Schema::hasTable('plugin_manga_comments')) {
            abort(404);
        }
        $row = MangaComment::query()->find($id);
        if (! $row) {
            abort(404);
        }
        $works = [];
        try {
            $works = $this->manga->adminWorkOptions();
        } catch (\Throwable) {
            $works = [];
        }

        return view('manga::admin.comment_form', [
            'comment' => $row->toArray(),
            'isEdit' => true,
            'works' => $works,
        ]);
    }
}
