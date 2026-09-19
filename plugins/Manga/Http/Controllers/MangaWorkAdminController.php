<?php

namespace Plugins\Manga\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Plugins\Manga\Models\Manga;
use Plugins\Manga\Models\MangaAuthor;
use Plugins\Manga\Models\MangaTag;
use Plugins\Manga\Services\MangaAuthorService;
use Plugins\Manga\Services\MangaService;
use Plugins\Manga\Services\MangaTagService;

class MangaWorkAdminController extends Controller
{
    public function __construct(
        private readonly MangaService $manga,
        private readonly MangaTagService $tags,
        private readonly MangaAuthorService $authors,
    ) {}

    public function create(Request $request): View|Factory
    {
        $pending = (string) $request->query('desk', '') === 'pending'
            || (string) $request->query('yid', '') === '1';

        return view('manga::admin.work_form', $this->formPayload([
            'status' => 1,
            'yid' => $pending ? 1 : 0,
            'serialize' => 0,
            'recommend' => 0,
            'hits' => 0,
            'sort' => 0,
            'type_id' => 0,
            'tag_ids' => [],
            'author_ids' => [],
        ], false));
    }

    public function edit(int $id): View|Factory
    {
        $row = $this->manga->findAny($id);
        if (! $row) {
            abort(404);
        }

        return view('manga::admin.work_form', $this->formPayload($this->workArray($row), true));
    }

    /** @param  array<string, mixed>  $work */
    private function formPayload(array $work, bool $isEdit): array
    {
        $types = [];
        $tagsReady = false;
        $authorsReady = false;
        try {
            $types = $this->manga->adminTypes();
        } catch (\Throwable) {
            $types = [];
        }
        try {
            $tagsReady = $this->tags->ready();
        } catch (\Throwable) {
            $tagsReady = false;
        }
        try {
            $authorsReady = $this->authors->ready();
        } catch (\Throwable) {
            $authorsReady = false;
        }

        $tagIds = array_values(array_filter(array_map('intval', is_array($work['tag_ids'] ?? null) ? $work['tag_ids'] : [])));
        $authorIds = array_values(array_filter(array_map('intval', is_array($work['author_ids'] ?? null) ? $work['author_ids'] : [])));

        return [
            'work' => $work,
            'isEdit' => $isEdit,
            'types' => $types,
            'selectedTags' => $this->labels(MangaTag::class, $tagIds, $tagsReady),
            'selectedAuthors' => $this->labels(MangaAuthor::class, $authorIds, $authorsReady),
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

    /** @return array<string, mixed> */
    private function workArray(Manga $work): array
    {
        $tagIds = [];
        $authorIds = [];
        try {
            $tagIds = $this->tags->idsForManga((int) $work->id);
        } catch (\Throwable) {
            $tagIds = [];
        }
        try {
            $authorIds = $this->authors->idsForManga((int) $work->id);
        } catch (\Throwable) {
            $authorIds = [];
        }

        return [
            'id' => (int) $work->id,
            'title' => (string) $work->title,
            'author' => (string) ($work->author ?? ''),
            'cover' => (string) ($work->cover ?? ''),
            'status' => (int) ($work->status ?? 0),
            'yid' => (int) ($work->yid ?? 0),
            'serialize' => (int) ($work->serialize ?? 0),
            'type_id' => (int) ($work->type_id ?? 0),
            'tags' => (string) ($work->tags ?? ''),
            'tag_ids' => $tagIds,
            'author_ids' => $authorIds,
            'recommend' => (int) ($work->recommend ?? 0),
            'remarks' => (string) ($work->remarks ?? ''),
            'content' => (string) ($work->content ?? ''),
            'hits' => (int) ($work->hits ?? 0),
            'sort' => (int) ($work->sort ?? 0),
            'front_url' => url('/manga/'.$work->id),
        ];
    }
}
