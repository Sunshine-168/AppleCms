<?php

namespace Plugins\Manga\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Utils\Ajax;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Plugins\Manga\Services\MangaAuthorService;

class MangaAuthorAdminController extends Controller
{
    public function __construct(private readonly MangaAuthorService $authors) {}

    public function index(): View|Factory
    {
        return view('manga::admin.authors', [
            'title' => '漫画作者',
            'ready' => $this->authors->ready(),
        ]);
    }

    public function create(): View|Factory
    {
        return view('manga::admin.author_form', [
            'author' => ['status' => 1, 'sort' => 0],
            'isEdit' => false,
        ]);
    }

    public function edit(int $id): View|Factory
    {
        $row = $this->authors->find($id);
        if (! $row) {
            abort(404);
        }
        $data = $row->toArray();
        $data['manga_count'] = (int) $row->mangas()->count();
        $slug = trim((string) ($data['slug'] ?? ''));
        $name = trim((string) ($data['name'] ?? ''));
        $data['url'] = '/manga?author='.rawurlencode($slug !== '' ? $slug : ($name !== '' ? $name : (string) $id));

        return view('manga::admin.author_form', [
            'author' => $data,
            'isEdit' => true,
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        $data = $this->authors->paginate($request->all());

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function save(Request $request): JsonResponse
    {
        $id = (int) $request->input('id', 0);
        $data = $this->authors->save($request->all(), $id > 0 ? $id : null);

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function delete(Request $request): JsonResponse
    {
        $data = $this->authors->delete((int) $request->input('id', 0));

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function batch(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }
        $data = $this->authors->batch(
            is_array($ids) ? $ids : [],
            (string) $request->input('action', ''),
            $request->input('value', '')
        );

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }
}
