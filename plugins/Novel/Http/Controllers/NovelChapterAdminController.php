<?php

namespace Plugins\Novel\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Plugins\Novel\Models\Novel;
use Plugins\Novel\Models\NovelChapter;

class NovelChapterAdminController extends Controller
{
    /** 打开新增章节页。 */
    public function create(Request $request): View|Factory
    {
        $novelId = (int) $request->query('novel_id', 0);

        return view('novel::admin.chapter_page', [
            'chapter' => [
                'novel_id' => $novelId,
                'name' => '',
                'content' => '',
                'sort' => 0,
                'vip' => 0,
            ],
            'isEdit' => false,
            'works' => Novel::query()->orderByDesc('id')->limit(500)->get(['id', 'title']),
        ]);
    }

    /** 打开编辑章节页。 */
    public function edit(int $id): View|Factory
    {
        $row = NovelChapter::query()->find($id);
        if (! $row) {
            abort(404);
        }

        return view('novel::admin.chapter_page', [
            'chapter' => [
                'id' => (int) $row->id,
                'novel_id' => (int) $row->novel_id,
                'name' => (string) $row->name,
                'content' => (string) ($row->content ?? ''),
                'sort' => (int) ($row->sort ?? 0),
                'vip' => (int) ($row->vip ?? 0),
            ],
            'isEdit' => true,
            'works' => Novel::query()->orderByDesc('id')->limit(500)->get(['id', 'title']),
        ]);
    }
}
