<?php

namespace Plugins\Gallery\Services;

use App\Services\Video\ImageLocalizeService;
use App\Support\AdminPage;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\Schema;
use Plugins\Gallery\Models\Gallery;
use Plugins\Gallery\Models\GalleryComment;
use Plugins\Gallery\Models\GalleryFavor;
use Plugins\Gallery\Models\GalleryPic;
use Plugins\Gallery\Models\GalleryType;

class GalleryAdmin
{
    public function __construct(
        private readonly GalleryService $service,
        private readonly GalleryStatsService $stats,
        private readonly GalleryTagService $tags,
        private readonly GalleryAuthorService $authors,
        private readonly GalleryCommentAdminService $commentAdmin,
        private readonly ImageLocalizeService $images,
    ) {}

    /** 组装图集后台工作台数据。 */
    public function boardPayload(array $payload): array
    {
        $payload['works'] = Gallery::query()->orderByDesc('id')->limit(500)->get(['id', 'title']);
        $payload['types'] = GalleryType::query()->orderBy('sort')->get();
        $desk = (string) ($payload['desk'] ?? request('desk', 'works'));
        if ($desk === 'stats') {
            $payload['stats'] = $this->stats->summary();
        }
        if ($desk === 'comments') {
            $payload['commentQueues'] = $this->commentAdmin->queues();
        }

        return $payload;
    }

    /** 获取图集后台列表。 */
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
            'pics' => GalleryPic::query()->with('gallery'),
            'types' => GalleryType::query(),
            'favors' => GalleryFavor::query()->with('gallery'),
            'comments' => GalleryComment::query()->with('gallery'),
            default => Gallery::query()->when($desk === 'pending', fn ($x) => $x->where('yid', 1)),
        };
        if ($desk === 'pics' && (int) ($params['gallery_id'] ?? 0) > 0) {
            $q->where('gallery_id', (int) $params['gallery_id']);
        }
        $kw = trim((string) ($params['q'] ?? ''));
        if ($kw !== '') {
            if ($desk === 'pics') {
                $q->where('url', 'like', '%'.$kw.'%');
            } elseif ($desk === 'types') {
                $q->where('name', 'like', '%'.$kw.'%');
            } elseif ($desk === 'favors') {
                $q->where(fn ($x) => $x->where('member_id', (int) $kw)->orWhere('gallery_id', (int) $kw));
            } elseif ($desk === 'comments') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('content', 'like', '%'.$kw.'%')
                        ->orWhere('author_name', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw)->orWhere('gallery_id', (int) $kw);
                    }
                    $galleryIds = Gallery::query()->where('title', 'like', '%'.$kw.'%')->pluck('id')->all();
                    if ($galleryIds !== []) {
                        $inner->orWhereIn('gallery_id', $galleryIds);
                    }
                });
            } else {
                $q->where(fn ($x) => $x->where('title', 'like', '%'.$kw.'%')
                    ->orWhere('author', 'like', '%'.$kw.'%')
                    ->orWhere('tags', 'like', '%'.$kw.'%'));
            }
        }
        if ($desk === 'comments') {
            if ((int) ($params['gallery_id'] ?? 0) > 0) {
                $q->where('gallery_id', (int) $params['gallery_id']);
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
        $rows = collect($page->items())->map(function ($r) use ($desk, $favorCounts) {
            $a = $r->toArray();
            if ($desk === 'pics') {
                $a['gallery_title'] = (string) ($r->gallery?->title ?? '');
            }
            if ($desk === 'favors') {
                $a['gallery_title'] = (string) ($r->gallery?->title ?? '');
            }
            if ($desk === 'comments') {
                $a['gallery_title'] = (string) ($r->gallery?->title ?? '');
                $a['created_label'] = (int) ($r->created_at ?? 0) > 0
                    ? date('Y-m-d H:i', (int) $r->created_at)
                    : '';
            }
            if (in_array($desk, ['works', 'pending'], true)) {
                $a['favor_count'] = $favorCounts[(int) $r->id] ?? 0;
                $a['author_label'] = (string) ($r->author ?? '');
                $a['tags_label'] = (string) ($r->tags ?? '');
            }

            return $a;
        })->all();

        return Result::success(AdminPage::of($page, $rows));
    }

    /** 保存图集后台数据。 */
    public function save(array $data, ?int $id = null): array
    {
        $desk = $this->desk($data);
        if ($desk === 'favors' || $desk === 'stats') {
            return Result::fail($desk === 'favors' ? '收藏由会员产生' : '统计只读');
        }
        if ($desk === 'comments') {
            return $this->saveComment($data, $id);
        }
        if ($desk === 'pics' && ! $id && trim((string) ($data['urls'] ?? '')) !== '') {
            $galleryId = (int) ($data['gallery_id'] ?? 0);
            if ($galleryId < 1) {
                return Result::fail('请选择图集');
            }
            $count = 0;
            $stamp = time();
            foreach (preg_split('/\r\n|\r|\n/', (string) $data['urls']) ?: [] as $line) {
                $url = trim($line);
                if (! self::safeUrl($url)) {
                    continue;
                }
                $count++;
                $url = $this->localizePicUrl($url, $galleryId, $stamp, $count);
                GalleryPic::query()->create(['gallery_id' => $galleryId, 'url' => $url, 'title' => '', 'sort' => $count, 'created_at' => $stamp]);
            }

            return Result::success(['count' => $count], '已添加');
        }
        $class = match ($desk) {
            'pics' => GalleryPic::class,
            'types' => GalleryType::class,
            default => Gallery::class,
        };
        $row = $id ? $class::query()->find($id) : new $class;
        if ($id && ! $row) {
            return Result::fail('数据不存在');
        }
        if ($desk === 'pics') {
            if (! $id && ((int) ($data['gallery_id'] ?? 0) < 1 || ! self::safeUrl((string) ($data['url'] ?? '')))) {
                return Result::fail('请选择图集并填写有效图片地址');
            }
            foreach (['gallery_id', 'sort'] as $f) {
                if (array_key_exists($f, $data) || ! $id) {
                    $row->{$f} = max(0, (int) ($data[$f] ?? 0));
                }
            }
            if (array_key_exists('url', $data)) {
                $url = trim((string) $data['url']);
                $gid = (int) ($row->gallery_id ?: ($data['gallery_id'] ?? 0));
                $row->url = $this->localizePicUrl($url, $gid, time(), (int) ($row->id ?: 0));
            }
            if (array_key_exists('title', $data)) {
                $row->title = trim((string) $data['title']);
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
            foreach (['sort', 'status'] as $f) {
                if (array_key_exists($f, $data) || ! $id) {
                    $row->{$f} = max(0, (int) ($data[$f] ?? ($f === 'status' ? 1 : 0)));
                }
            }
        } else {
            if (! $id && trim((string) ($data['title'] ?? '')) === '') {
                return Result::fail('请填写图集名');
            }
            foreach (['title', 'cover', 'author', 'remarks', 'content'] as $f) {
                if (array_key_exists($f, $data)) {
                    $row->{$f} = trim((string) $data[$f]);
                }
            }
            if (array_key_exists('tags', $data) || Schema::hasColumn('plugin_galleries', 'tags')) {
                if (array_key_exists('tags', $data) || ! $id) {
                    $row->tags = GalleryService::normalizeTags((string) ($data['tags'] ?? ($row->tags ?? '')));
                }
            }
            foreach (['type_id', 'yid', 'hits', 'sort', 'status'] as $f) {
                if (array_key_exists($f, $data) || ! $id) {
                    $row->{$f} = max(0, (int) ($data[$f] ?? ($f === 'status' ? 1 : 0)));
                }
            }
            $row->updated_at = time();
        }
        if (! $id) {
            $row->created_at = time();
        }
        $row->save();
        if ($class === Gallery::class) {
            $this->syncRelations((int) $row->id, $data);
        }

        return Result::success(['id' => (int) $row->id], '已保存');
    }

    /** 删除图集后台数据。 */
    public function delete(int $id): array
    {
        $desk = $this->desk(request()->all());
        $class = match ($desk) {
            'pics' => GalleryPic::class,
            'types' => GalleryType::class,
            'favors' => GalleryFavor::class,
            'comments' => GalleryComment::class,
            default => Gallery::class,
        };
        $row = $class::query()->find($id);
        if (! $row) {
            return Result::fail('数据不存在');
        }
        if ($class === Gallery::class) {
            GalleryPic::query()->where('gallery_id', $id)->delete();
            if (Schema::hasTable('plugin_gallery_favors')) {
                GalleryFavor::query()->where('gallery_id', $id)->delete();
            }
            if (Schema::hasTable('plugin_gallery_comments')) {
                GalleryComment::query()->where('gallery_id', $id)->delete();
            }
            try {
                $this->tags->detachGallery($id);
                $this->authors->detachGallery($id);
            } catch (\Throwable) {
            }
        }
        $row->delete();

        return Result::success([], '已删除');
    }

    /** 批量处理图集后台数据。 */
    public function batch(array $ids, string $action, mixed $value): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if ($ids === []) {
            return Result::fail('请选择数据');
        }
        $desk = $this->desk(request()->all());
        $class = match ($desk) {
            'pics' => GalleryPic::class,
            'types' => GalleryType::class,
            'comments' => GalleryComment::class,
            default => Gallery::class,
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

    /** @param  array<string, mixed>  $data */
    private function syncRelations(int $galleryId, array $data): void
    {
        if ($galleryId < 1) {
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
                $this->tags->syncGallery($galleryId, $data);
            }
            if ($hasAuthors) {
                $this->authors->syncGallery($galleryId, $data);
            }
        } catch (\Throwable) {
        }
    }

    /** @param  array<string, mixed>  $data */
    private function saveComment(array $data, ?int $id): array
    {
        if (! Schema::hasTable('plugin_gallery_comments')) {
            return Result::fail('请先执行数据库迁移');
        }
        $galleryId = array_key_exists('gallery_id', $data) || $id === null
            ? (int) ($data['gallery_id'] ?? 0)
            : null;
        if ($galleryId !== null && ($galleryId < 1 || ! Gallery::query()->where('id', $galleryId)->exists())) {
            return Result::fail('图集不存在');
        }
        $content = trim((string) ($data['content'] ?? ''));
        if ($id === null && $content === '') {
            return Result::fail('请填写评论');
        }
        $row = $id ? GalleryComment::query()->find($id) : new GalleryComment;
        if ($id && ! $row) {
            return Result::fail('数据不存在');
        }
        if ($galleryId !== null) {
            $row->gallery_id = $galleryId;
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

    /** 远程图片下载到本地 uploads/gallery。 */
    private function localizePicUrl(string $url, int $galleryId, int $stamp, int $seq): string
    {
        $url = trim($url);
        if (! $this->images->isRemote($url)) {
            return $url;
        }
        $basename = 'gallery_'.$galleryId.'_'.$stamp.'_'.$seq;

        return $this->images->download($url, $basename, 'gallery');
    }

    /** 验证图片地址。 */
    public static function safeUrl(string $url): bool
    {
        return preg_match('#^https?://#i', trim($url)) === 1 || str_starts_with(trim($url), '/');
    }

    /** 解析当前工作台。 */
    private function desk(array $data): string
    {
        $d = (string) ($data['desk'] ?? request()->input('desk', 'works'));

        return in_array($d, ['works', 'pending', 'pics', 'types', 'favors', 'comments', 'stats'], true) ? $d : 'works';
    }
}
