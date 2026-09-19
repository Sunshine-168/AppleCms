<?php

namespace Plugins\Novel\Services;

use App\Support\AdminPage;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\Schema;
use Plugins\Novel\Models\Novel;
use Plugins\Novel\Models\NovelChapter;
use Plugins\Novel\Models\NovelComment;
use Plugins\Novel\Models\NovelFavor;
use Plugins\Novel\Models\NovelType;

class NovelAdmin
{
    public function __construct(
        private readonly NovelService $service,
        private readonly NovelStatsService $stats,
        private readonly NovelTagService $tags,
        private readonly NovelAuthorService $authors,
        private readonly NovelCommentAdminService $commentAdmin,
    ) {}

    /** 组装小说后台工作台数据。 */
    public function boardPayload(array $payload): array
    {
        $payload['types'] = NovelType::query()->orderBy('sort')->orderBy('id')->get();
        $payload['works'] = Novel::query()->orderByDesc('id')->limit(500)->get(['id', 'title']);
        $desk = (string) ($payload['desk'] ?? request('desk', 'works'));
        if ($desk === 'stats') {
            $payload['stats'] = $this->stats->summary();
        }
        if ($desk === 'comments') {
            $payload['commentQueues'] = $this->commentAdmin->queues();
        }

        return $payload;
    }

    /** 获取小说后台列表。 */
    public function lists(array $params): array
    {
        if (! $this->service->ready()) {
            return Result::fail('请先执行数据库迁移');
        }
        $desk = $this->desk($params);
        if ($desk === 'stats') {
            return Result::success(['list' => [], 'count' => 0]);
        }
        $q = match ($desk) {
            'chapters' => NovelChapter::query()->with('novel'),
            'types' => NovelType::query(),
            'favors' => NovelFavor::query()->with('novel'),
            'comments' => NovelComment::query()->with('novel'),
            default => Novel::query()->when($desk === 'pending', fn ($x) => $x->where('yid', 1)),
        };
        $kw = trim((string) ($params['q'] ?? ''));
        if ($kw !== '') {
            if ($desk === 'chapters' || $desk === 'types') {
                $q->where('name', 'like', '%'.$kw.'%');
            } elseif ($desk === 'favors') {
                $q->where(fn ($x) => $x->where('member_id', (int) $kw)->orWhere('novel_id', (int) $kw));
            } elseif ($desk === 'comments') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('content', 'like', '%'.$kw.'%')
                        ->orWhere('author_name', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw)->orWhere('novel_id', (int) $kw);
                    }
                    $novelIds = Novel::query()->where('title', 'like', '%'.$kw.'%')->pluck('id')->all();
                    if ($novelIds !== []) {
                        $inner->orWhereIn('novel_id', $novelIds);
                    }
                });
            } else {
                $q->where(fn ($x) => $x->where('title', 'like', '%'.$kw.'%')
                    ->orWhere('author', 'like', '%'.$kw.'%')
                    ->orWhere('tags', 'like', '%'.$kw.'%'));
            }
        }
        if ($desk === 'chapters' && (int) ($params['novel_id'] ?? 0) > 0) {
            $q->where('novel_id', (int) $params['novel_id']);
        }
        if ($desk === 'comments') {
            if ((int) ($params['novel_id'] ?? 0) > 0) {
                $q->where('novel_id', (int) $params['novel_id']);
            }
            if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
                $q->where('status', (int) $params['status']);
            }
        }
        $page = $q->orderByDesc($desk === 'types' ? 'sort' : 'id')->paginate(max(1, (int) ($params['limit'] ?? 20)));
        $favorCounts = [];
        if (in_array($desk, ['works', 'pending'], true)) {
            $favorCounts = $this->service->favorCounts(collect($page->items())->pluck('id')->all());
        }
        $rows = collect($page->items())->map(function ($row) use ($desk, $favorCounts) {
            $out = $row->toArray();
            if ($desk === 'chapters') {
                $out['novel_title'] = (string) ($row->novel?->title ?? '');
            }
            if ($desk === 'favors') {
                $out['novel_title'] = (string) ($row->novel?->title ?? '');
            }
            if ($desk === 'comments') {
                $out['novel_title'] = (string) ($row->novel?->title ?? '');
                $out['created_label'] = (int) ($row->created_at ?? 0) > 0
                    ? date('Y-m-d H:i', (int) $row->created_at)
                    : '';
            }
            if (in_array($desk, ['works', 'pending'], true)) {
                $out['favor_count'] = $favorCounts[(int) $row->id] ?? 0;
                $out['author_label'] = (string) ($row->author ?? '');
                $out['tags_label'] = (string) ($row->tags ?? '');
            }

            return $out;
        })->all();
        if (in_array($desk, ['works', 'pending'], true)) {
            $this->decorateWorkRows($rows);
        }

        return Result::success(AdminPage::of($page, $rows));
    }

    /** 保存小说后台数据。 */
    public function save(array $data, ?int $id = null): array
    {
        $desk = $this->desk($data);
        if ($desk === 'favors' || $desk === 'stats') {
            return Result::fail($desk === 'favors' ? '收藏由会员产生' : '统计只读');
        }
        if ($desk === 'comments') {
            return $this->saveComment($data, $id);
        }
        $class = $desk === 'chapters' ? NovelChapter::class : ($desk === 'types' ? NovelType::class : Novel::class);
        $row = $id ? $class::query()->find($id) : new $class;
        if ($id && ! $row) {
            return Result::fail('数据不存在');
        }
        $now = time();
        if ($desk === 'chapters') {
            $name = trim((string) ($data['name'] ?? ''));
            if (! $id && ($name === '' || (int) ($data['novel_id'] ?? 0) < 1)) {
                return Result::fail('请选择作品并填写章节名');
            }
            foreach (['novel_id', 'sort', 'vip'] as $f) {
                if (array_key_exists($f, $data) || ! $id) {
                    $row->{$f} = max(0, (int) ($data[$f] ?? 0));
                }
            }
            foreach (['name', 'content'] as $f) {
                if (array_key_exists($f, $data)) {
                    $row->{$f} = trim((string) $data[$f]);
                }
            }
        } elseif ($desk === 'types') {
            if (! $id && trim((string) ($data['name'] ?? '')) === '') {
                return Result::fail('请填写分类名');
            }
            foreach (['name', 'slug'] as $f) {
                if (array_key_exists($f, $data)) {
                    $row->{$f} = trim((string) $data[$f]);
                }
            }
            foreach (['parent_id', 'sort', 'status'] as $f) {
                if (array_key_exists($f, $data) || ! $id) {
                    $row->{$f} = max(0, (int) ($data[$f] ?? ($f === 'status' ? 1 : 0)));
                }
            }
        } else {
            if (! $id && trim((string) ($data['title'] ?? '')) === '') {
                return Result::fail('请填写作品名');
            }
            foreach (['title', 'cover', 'author', 'remarks', 'content'] as $f) {
                if (array_key_exists($f, $data)) {
                    $row->{$f} = trim((string) $data[$f]);
                }
            }
            if (array_key_exists('tags', $data) || Schema::hasColumn('plugin_novels', 'tags')) {
                if (array_key_exists('tags', $data) || ! $id) {
                    $row->tags = NovelService::normalizeTags((string) ($data['tags'] ?? ($row->tags ?? '')));
                }
            }
            foreach (['type_id', 'serialize', 'yid', 'recommend', 'hits', 'sort', 'status'] as $f) {
                if (array_key_exists($f, $data) || ! $id) {
                    $row->{$f} = max(0, (int) ($data[$f] ?? ($f === 'status' ? 1 : 0)));
                }
            }
            $row->updated_at = $now;
        }
        if (! $id) {
            $row->created_at = $now;
        }
        $row->save();
        if ($class === Novel::class) {
            $this->syncRelations((int) $row->id, $data);
        }

        return Result::success(['id' => (int) $row->id], '已保存');
    }

    /** 删除小说后台数据。 */
    public function delete(int $id): array
    {
        $desk = $this->desk(request()->all());
        $class = match ($desk) {
            'chapters' => NovelChapter::class,
            'types' => NovelType::class,
            'favors' => NovelFavor::class,
            'comments' => NovelComment::class,
            default => Novel::class,
        };
        $row = $class::query()->find($id);
        if (! $row) {
            return Result::fail('数据不存在');
        }
        if ($class === Novel::class) {
            NovelChapter::query()->where('novel_id', $id)->delete();
            NovelFavor::query()->where('novel_id', $id)->delete();
            if (Schema::hasTable('plugin_novel_comments')) {
                NovelComment::query()->where('novel_id', $id)->delete();
            }
            try {
                $this->tags->detachNovel($id);
                $this->authors->detachNovel($id);
            } catch (\Throwable) {
            }
        }
        $row->delete();

        return Result::success([], '已删除');
    }

    /** 批量处理小说后台数据。 */
    public function batch(array $ids, string $action, mixed $value): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if ($ids === []) {
            return Result::fail('请选择数据');
        }
        $desk = $this->desk(request()->all());
        $class = match ($desk) {
            'chapters' => NovelChapter::class,
            'types' => NovelType::class,
            'comments' => NovelComment::class,
            default => Novel::class,
        };
        if ($action === 'delete') {
            $class::query()->whereIn('id', $ids)->delete();
        } elseif (in_array($action, ['status', 'yid'], true)) {
            $class::query()->whereIn('id', $ids)->update([$action => (int) $value]);
        } else {
            return Result::fail('不支持的操作');
        }

        return Result::success([], '已处理');
    }

    /** @param  list<array<string, mixed>>  $rows */
    private function decorateWorkRows(array &$rows): void
    {
        foreach ($rows as &$row) {
            $author = trim((string) ($row['author_label'] ?? $row['author'] ?? ''));
            $tags = trim((string) ($row['tags_label'] ?? $row['tags'] ?? ''));
            if ($author !== '') {
                $row['author_label'] = $author;
            }
            if ($tags !== '') {
                $row['tags_label'] = $tags;
            }
        }
        unset($row);
    }

    /** @param  array<string, mixed>  $data */
    private function syncRelations(int $novelId, array $data): void
    {
        if ($novelId < 1) {
            return;
        }
        if (! isset($data['tag_ids']) && isset($data['tag_ids[]'])) {
            $data['tag_ids'] = $data['tag_ids[]'];
        }
        if (! isset($data['author_ids']) && isset($data['author_ids[]'])) {
            $data['author_ids'] = $data['author_ids[]'];
        }
        $hasTags = array_key_exists('tags', $data) || array_key_exists('tag_ids', $data) || array_key_exists('tag', $data);
        $hasAuthors = array_key_exists('author', $data) || array_key_exists('author_ids', $data) || array_key_exists('authors', $data);
        try {
            if ($hasTags) {
                $this->tags->syncNovel($novelId, $data);
            }
            if ($hasAuthors) {
                $this->authors->syncNovel($novelId, $data);
            }
        } catch (\Throwable) {
        }
    }

    /** @param  array<string, mixed>  $data */
    private function saveComment(array $data, ?int $id): array
    {
        if (! Schema::hasTable('plugin_novel_comments')) {
            return Result::fail('请先执行数据库迁移');
        }
        $novelId = array_key_exists('novel_id', $data) || $id === null
            ? (int) ($data['novel_id'] ?? 0)
            : null;
        if ($novelId !== null && ($novelId < 1 || ! Novel::query()->where('id', $novelId)->exists())) {
            return Result::fail('作品不存在');
        }
        $content = trim((string) ($data['content'] ?? ''));
        if ($id === null && $content === '') {
            return Result::fail('请填写评论');
        }
        $row = $id ? NovelComment::query()->find($id) : new NovelComment;
        if ($id && ! $row) {
            return Result::fail('数据不存在');
        }
        if ($novelId !== null) {
            $row->novel_id = $novelId;
        }
        if (array_key_exists('author_name', $data)) {
            $row->author_name = mb_substr(trim((string) $data['author_name']), 0, 80);
        }
        if ($content !== '') {
            $row->content = mb_substr($content, 0, 2000);
        }
        if (array_key_exists('status', $data)) {
            $row->status = (int) $data['status'] === 1 ? 1 : 0;
        } elseif (! $id) {
            $row->status = 1;
        }
        if (! $id) {
            $row->member_id = max(0, (int) ($data['member_id'] ?? 0));
            $row->ip = mb_substr(trim((string) ($data['ip'] ?? '')), 0, 45);
            $row->created_at = time();
        }
        $row->save();

        return Result::success(['id' => (int) $row->id], '已保存');
    }

    /** 解析当前工作台。 */
    private function desk(array $data): string
    {
        $desk = (string) ($data['desk'] ?? request()->input('desk', 'works'));

        return in_array($desk, ['works', 'pending', 'chapters', 'types', 'favors', 'comments', 'stats'], true) ? $desk : 'works';
    }
}
