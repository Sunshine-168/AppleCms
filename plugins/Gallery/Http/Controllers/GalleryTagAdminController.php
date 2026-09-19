<?php

namespace Plugins\Gallery\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Utils\Ajax;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Plugins\Gallery\Services\GalleryTagService;

class GalleryTagAdminController extends Controller
{
    public function __construct(private readonly GalleryTagService $tags) {}

    public function index(): View|Factory
    {
        return view('gallery::admin.tags', [
            'title' => '图集标签',
            'ready' => $this->tags->ready(),
        ]);
    }

    public function create(): View|Factory
    {
        return view('gallery::admin.tag_form', [
            'tag' => ['status' => 1, 'sort' => 0],
            'isEdit' => false,
        ]);
    }

    public function edit(int $id): View|Factory
    {
        $row = $this->tags->find($id);
        if (! $row) {
            abort(404);
        }
        $data = $row->toArray();
        $data['gallery_count'] = (int) $row->galleries()->count();
        $slug = trim((string) ($data['slug'] ?? ''));
        $data['url'] = '/gallery?tag='.rawurlencode($slug !== '' ? $slug : (string) $id);

        return view('gallery::admin.tag_form', [
            'tag' => $data,
            'isEdit' => true,
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        $data = $this->tags->paginate($request->all());

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function save(Request $request): JsonResponse
    {
        $id = (int) $request->input('id', 0);
        $data = $this->tags->save($request->all(), $id > 0 ? $id : null);

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function delete(Request $request): JsonResponse
    {
        $data = $this->tags->delete((int) $request->input('id', 0));

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function batch(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }
        $data = $this->tags->batch(
            is_array($ids) ? $ids : [],
            (string) $request->input('action', ''),
            $request->input('value', '')
        );

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }
}
