<?php

namespace Plugins\Gallery\Services;

use App\Support\Utils\Result;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Plugins\Gallery\Models\Gallery;
use Plugins\Gallery\Models\GalleryComment;
use Plugins\Gallery\Models\GalleryFavor;
use Plugins\Gallery\Models\GalleryType;

class GalleryService
{
    /** 判断图集数据表是否可用。 */
    public function ready(): bool
    {
        return Schema::hasTable('plugin_galleries') && Schema::hasTable('plugin_gallery_pics');
    }

    /** 拆分标签字符串。 */
    public static function splitTags(string $tags): array
    {
        $parts = preg_split('/[,，|\/\s]+/u', trim($tags)) ?: [];

        return array_values(array_unique(array_filter(array_map('trim', $parts), static fn ($t) => $t !== '')));
    }

    /** 规范化标签存储。 */
    public static function normalizeTags(string $tags): string
    {
        return implode(',', self::splitTags($tags));
    }

    /** 获取前台图集列表。 */
    public function paginate(int $perPage = 24)
    {
        $q = Gallery::query()->published();
        $wd = trim((string) request()->query('wd', ''));
        if ($wd !== '') {
            $q->where(fn ($x) => $x->where('title', 'like', '%'.$wd.'%')
                ->orWhere('author', 'like', '%'.$wd.'%')
                ->orWhere('tags', 'like', '%'.$wd.'%'));
        }
        if ((int) request()->query('type', 0) > 0) {
            $q->where('type_id', (int) request()->query('type'));
        }
        $tag = trim((string) request()->query('tag', ''));
        if ($tag !== '') {
            $q->where('tags', 'like', '%'.$tag.'%');
        }
        $order = (string) request()->query('order', 'new');
        if ($order === 'hits') {
            $q->orderByDesc('hits')->orderByDesc('id');
        } elseif ($order === 'favor' && Schema::hasTable('plugin_gallery_favors')) {
            $q->orderByDesc(DB::raw('(select count(*) from plugin_gallery_favors where gallery_id = plugin_galleries.id)'))
                ->orderByDesc('id');
        } else {
            $q->orderByDesc('sort')->orderByDesc('id');
        }

        return $this->decorateFrontRows($q->paginate($perPage)->withQueryString());
    }

    /** 给列表附加收藏数与标签。 */
    public function decorateFrontRows($page)
    {
        $items = collect($page->items());
        $counts = $this->favorCounts($items->pluck('id')->map(fn ($id) => (int) $id)->all());
        foreach ($items as $row) {
            $row->favor_count = $counts[(int) $row->id] ?? (int) ($row->favor_count ?? 0);
            $row->tag_list = self::splitTags((string) ($row->tags ?? ''));
        }

        return $page;
    }

    /** 批量统计收藏数。 */
    public function favorCounts(array $ids): array
    {
        if ($ids === [] || ! Schema::hasTable('plugin_gallery_favors')) {
            return [];
        }

        return GalleryFavor::query()->whereIn('gallery_id', $ids)
            ->select('gallery_id', DB::raw('COUNT(*) as c'))
            ->groupBy('gallery_id')->pluck('c', 'gallery_id')
            ->map(fn ($c) => (int) $c)->all();
    }

    /** 单个图集收藏数。 */
    public function favorCount(int $galleryId): int
    {
        return Schema::hasTable('plugin_gallery_favors')
            ? (int) GalleryFavor::query()->where('gallery_id', $galleryId)->count()
            : 0;
    }

    /** 前台标签云。 */
    public function tagCloud(int $limit = 30): array
    {
        if (! Schema::hasColumn('plugin_galleries', 'tags')) {
            return [];
        }
        $map = [];
        foreach (Gallery::query()->published()->where('tags', '!=', '')->pluck('tags') as $raw) {
            foreach (self::splitTags((string) $raw) as $tag) {
                $map[$tag] = ($map[$tag] ?? 0) + 1;
            }
        }
        arsort($map);

        return array_slice(array_keys($map), 0, $limit);
    }

    /** 获取启用图集分类。 */
    public function types(): Collection
    {
        return Schema::hasTable('plugin_gallery_types')
            ? GalleryType::query()->published()->orderBy('sort')->orderBy('id')->get()
            : collect();
    }

    /** 获取已发布图集。 */
    public function published(int $id): ?Gallery
    {
        return $this->ready() ? Gallery::query()->published()->with(['pics', 'type'])->find($id) : null;
    }

    /** 切换会员收藏。 */
    public function toggleFavor(int $memberId, int $galleryId): array
    {
        if ($memberId < 1) {
            return Result::fail('请先登录');
        }
        if (! Schema::hasTable('plugin_gallery_favors')) {
            return Result::fail('请先执行数据库迁移');
        }
        $row = GalleryFavor::query()->where('member_id', $memberId)->where('gallery_id', $galleryId)->first();
        if ($row) {
            $row->delete();

            return Result::success(['on' => 0], '已取消收藏');
        }
        GalleryFavor::query()->create(['member_id' => $memberId, 'gallery_id' => $galleryId, 'created_at' => time()]);

        return Result::success(['on' => 1], '已收藏');
    }

    /** 判断会员是否已收藏。 */
    public function favored(int $memberId, int $galleryId): bool
    {
        return $memberId > 0 && Schema::hasTable('plugin_gallery_favors')
            && GalleryFavor::query()->where('member_id', $memberId)->where('gallery_id', $galleryId)->exists();
    }

    /** 获取会员收藏夹。 */
    public function shelf(int $memberId): Collection
    {
        if (! Schema::hasTable('plugin_gallery_favors')) {
            return collect();
        }
        $ids = GalleryFavor::query()->where('member_id', $memberId)->orderByDesc('id')->pluck('gallery_id');
        $rows = Gallery::query()->published()->whereIn('id', $ids)->orderByDesc('updated_at')->get();
        $counts = $this->favorCounts($rows->pluck('id')->all());
        foreach ($rows as $row) {
            $row->favor_count = $counts[(int) $row->id] ?? 0;
            $row->tag_list = self::splitTags((string) ($row->tags ?? ''));
        }

        return $rows;
    }

    /** 获取前台展示评论。 */
    public function listedComments(Gallery $gallery): Collection
    {
        if (! Schema::hasTable('plugin_gallery_comments')) {
            return collect();
        }

        return GalleryComment::query()
            ->where('gallery_id', $gallery->id)
            ->where('status', 1)
            ->orderByDesc('id')
            ->limit(50)
            ->get();
    }

    /** 统计前台可见评论数。 */
    public function commentCount(Gallery $gallery): int
    {
        if (! Schema::hasTable('plugin_gallery_comments')) {
            return 0;
        }

        return (int) GalleryComment::query()->where('gallery_id', $gallery->id)->where('status', 1)->count();
    }

    /** 前台提交评论。 */
    public function addComment(int $galleryId, string $content, ?\App\Models\Member\Member $member, string $guestName, string $ip): array
    {
        if (! Schema::hasTable('plugin_gallery_comments')) {
            return Result::fail('请先执行数据库迁移');
        }
        $gallery = $this->published($galleryId);
        if (! $gallery) {
            return Result::fail('图集不存在');
        }
        $content = trim($content);
        if ($content === '') {
            return Result::fail('请填写评论');
        }
        $settings = app(\App\Services\Video\VideoSettingService::class);
        if ((int) $settings->get('member_comment_login', '0') === 1 && ! $member) {
            return Result::fail('请先登录后评论');
        }
        $banned = trim((string) $settings->get('banned_words', ''));
        if ($banned !== '') {
            foreach (preg_split('/[\r\n,，]+/u', $banned) ?: [] as $word) {
                $word = trim((string) $word);
                if ($word !== '' && mb_stripos($content, $word) !== false) {
                    return Result::fail('评论包含违禁词');
                }
            }
        }
        $name = $member?->name ?: trim($guestName);
        if ($name === '') {
            $name = '游客';
        }
        $row = GalleryComment::query()->create([
            'gallery_id' => $galleryId,
            'member_id' => (int) ($member?->id ?: 0),
            'author_name' => mb_substr($name, 0, 80),
            'content' => mb_substr($content, 0, 2000),
            'status' => (int) $settings->get('comment_audit', '0') === 1 ? 0 : 1,
            'ip' => mb_substr($ip, 0, 45),
            'created_at' => time(),
        ]);
        $msg = (int) $row->status === 1 ? '评论已发布' : '评论已提交，等待审核';

        return Result::success(['id' => (int) $row->id], $msg);
    }
}
