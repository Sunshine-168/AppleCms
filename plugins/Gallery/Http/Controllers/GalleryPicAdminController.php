<?php

namespace Plugins\Gallery\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Plugins\Gallery\Models\Gallery;
use Plugins\Gallery\Models\GalleryPic;

class GalleryPicAdminController extends Controller
{
    /** 打开新增图片页。 */
    public function create(Request $request): View|Factory
    {
        $galleryId = (int) $request->query('gallery_id', 0);

        return view('gallery::admin.pic_page', [
            'pic' => [
                'gallery_id' => $galleryId,
                'url' => '',
                'title' => '',
                'sort' => 0,
            ],
            'isEdit' => false,
            'works' => Gallery::query()->orderByDesc('id')->limit(500)->get(['id', 'title']),
        ]);
    }

    /** 打开编辑图片页。 */
    public function edit(int $id): View|Factory
    {
        $row = GalleryPic::query()->find($id);
        if (! $row) {
            abort(404);
        }

        return view('gallery::admin.pic_page', [
            'pic' => [
                'id' => (int) $row->id,
                'gallery_id' => (int) $row->gallery_id,
                'url' => (string) $row->url,
                'title' => (string) ($row->title ?? ''),
                'sort' => (int) ($row->sort ?? 0),
            ],
            'isEdit' => true,
            'works' => Gallery::query()->orderByDesc('id')->limit(500)->get(['id', 'title']),
        ]);
    }
}
