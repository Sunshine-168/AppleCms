<?php

namespace Plugins\Novel\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Plugins\Novel\Models\Novel;
use Plugins\Novel\Models\NovelAuthor;
use Plugins\Novel\Models\NovelTag;
use Plugins\Novel\Models\NovelType;
use Plugins\Novel\Services\NovelAuthorService;
use Plugins\Novel\Services\NovelTagService;

class NovelWorkAdminController extends Controller
{
    public function __construct(
        private readonly NovelTagService $tags,
        private readonly NovelAuthorService $authors,
    ) {}

    /** 打开新增作品页。 */
    public function create(Request $request): View|Factory
    {
        $pending = (string) $request->query('desk', '') === 'pending'
            || (string) $request->query('yid', '') === '1';

        return view('novel::admin.work_page', $this->formPayload([
            'status' => 1,
            'yid' => $pending ? 1 : 0,
            'serialize' => 0,
            'recommend' => 0,
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

    /** 打开编辑作品页。 */
    public function edit(int $id): View|Factory
    {
        $row = Novel::query()->find($id);
        if (! $row) {
            abort(404);
        }
        $tagIds = [];
        $authorIds = [];
        try {
            $tagIds = $this->tags->idsForNovel($id);
        } catch (\Throwable) {
        }
        try {
            $authorIds = $this->authors->idsForNovel($id);
        } catch (\Throwable) {
        }

        return view('novel::admin.work_page', $this->formPayload([
            'id' => (int) $row->id,
            'title' => (string) $row->title,
            'author' => (string) ($row->author ?? ''),
            'tags' => (string) ($row->tags ?? ''),
            'tag_ids' => $tagIds,
            'author_ids' => $authorIds,
            'cover' => (string) ($row->cover ?? ''),
            'status' => (int) ($row->status ?? 0),
            'yid' => (int) ($row->yid ?? 0),
            'serialize' => (int) ($row->serialize ?? 0),
            'recommend' => (int) ($row->recommend ?? 0),
            'type_id' => (int) ($row->type_id ?? 0),
            'remarks' => (string) ($row->remarks ?? ''),
            'content' => (string) ($row->content ?? ''),
            'hits' => (int) ($row->hits ?? 0),
            'sort' => (int) ($row->sort ?? 0),
            'front_url' => url('/novel/'.$row->id),
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
            'types' => NovelType::query()->orderBy('sort')->orderBy('id')->get(),
            'selectedTags' => $this->labels(NovelTag::class, $tagIds, $tagsReady),
            'selectedAuthors' => $this->labels(NovelAuthor::class, $authorIds, $authorsReady),
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
