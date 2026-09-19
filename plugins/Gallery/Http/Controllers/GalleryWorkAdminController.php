<?php

namespace Plugins\Gallery\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Plugins\Gallery\Models\Gallery;
use Plugins\Gallery\Models\GalleryAuthor;
use Plugins\Gallery\Models\GalleryTag;
use Plugins\Gallery\Models\GalleryType;
use Plugins\Gallery\Services\GalleryAuthorService;
use Plugins\Gallery\Services\GalleryTagService;

class GalleryWorkAdminController extends Controller
{
    public function __construct(
        private readonly GalleryTagService $tags,
        private readonly GalleryAuthorService $authors,
    ) {}

    /** 打开新增图集页。 */
    public function create(Request $request): View|Factory
    {
        $pending = (string) $request->query('desk', '') === 'pending'
            || (string) $request->query('yid', '') === '1';

        return view('gallery::admin.work_page', $this->formPayload([
            'status' => 1,
            'yid' => $pending ? 1 : 0,
            'hits' => 0,
            'sort' => 0,
            'type_id' => 0,
            'title' => '',
            'author' => '',
            'tags' => '',
            'tag_ids' => [],
            'author_ids' => [],
            'cover' => '',
            'remarks' => '',
            'content' => '',
        ], false));
    }

    /** 打开编辑图集页。 */
    public function edit(int $id): View|Factory
    {
        $row = Gallery::query()->find($id);
        if (! $row) {
            abort(404);
        }
        $tagIds = [];
        $authorIds = [];
        try {
            $tagIds = $this->tags->idsForGallery($id);
        } catch (\Throwable) {
        }
        try {
            $authorIds = $this->authors->idsForGallery($id);
        } catch (\Throwable) {
        }

        return view('gallery::admin.work_page', $this->formPayload([
            'id' => (int) $row->id,
            'title' => (string) $row->title,
            'author' => (string) ($row->author ?? ''),
            'tags' => (string) ($row->tags ?? ''),
            'tag_ids' => $tagIds,
            'author_ids' => $authorIds,
            'cover' => (string) ($row->cover ?? ''),
            'status' => (int) ($row->status ?? 0),
            'yid' => (int) ($row->yid ?? 0),
            'type_id' => (int) ($row->type_id ?? 0),
            'remarks' => (string) ($row->remarks ?? ''),
            'content' => (string) ($row->content ?? ''),
            'hits' => (int) ($row->hits ?? 0),
            'sort' => (int) ($row->sort ?? 0),
            'front_url' => url('/gallery/'.$row->id),
        ], true));
    }

    /** @param  array<string, mixed>  $work */
    private function formPayload(array $work, bool $isEdit): array
    {
        $tagsReady = false;
        $authorsReady = false;
        try {
            $tagsReady = $this->tags->ready();
        } catch (\Throwable) {
        }
        try {
            $authorsReady = $this->authors->ready();
        } catch (\Throwable) {
        }
        $tagIds = array_values(array_filter(array_map('intval', is_array($work['tag_ids'] ?? null) ? $work['tag_ids'] : [])));
        $authorIds = array_values(array_filter(array_map('intval', is_array($work['author_ids'] ?? null) ? $work['author_ids'] : [])));

        return [
            'work' => $work,
            'isEdit' => $isEdit,
            'types' => GalleryType::query()->orderBy('sort')->orderBy('id')->get(),
            'selectedTags' => $this->labels(GalleryTag::class, $tagIds, $tagsReady),
            'selectedAuthors' => $this->labels(GalleryAuthor::class, $authorIds, $authorsReady),
            'tagsReady' => $tagsReady,
            'authorsReady' => $authorsReady,
        ];
    }

    /**
     * @param  class-string  $model
     * @param  list<int>  $ids
     * @return list<array{id:int,name:string}>
     */
    private function labels(string $model, array $ids, bool $ready): array
    {
        if (! $ready || $ids === []) {
            return [];
        }
        $rows = $model::query()->whereIn('id', $ids)->get(['id', 'name']);
        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row->id] = (string) $row->name;
        }
        $out = [];
        foreach ($ids as $id) {
            if (isset($map[$id])) {
                $out[] = ['id' => $id, 'name' => $map[$id]];
            }
        }

        return $out;
    }
}
