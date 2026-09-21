<?php

namespace Plugins\Novel\Services;

use App\Models\Member\Member;
use App\Services\Video\VideoSettingService;
use App\Support\Utils\Result;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Plugins\Novel\Models\Novel;
use Plugins\Novel\Models\NovelComment;
use Plugins\Novel\Models\NovelChapter;
use Plugins\Novel\Models\NovelFavor;
use Plugins\Novel\Models\NovelHistory;
use Plugins\Novel\Models\NovelType;

class NovelService
{
    /** 判断小说数据表是否可用。 */
    public function ready(): bool
    {
        return Schema::hasTable('plugin_novels') && Schema::hasTable('plugin_novel_chapters');
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

    /** 获取前台小说列表。 */
    public function paginate(int $perPage = 24)
    {
        $q = Novel::query()->published();
        $wd = trim((string) request()->query('wd', ''));
        if ($wd !== '') {
            $q->where(fn ($x) => $x->where('title', 'like', '%'.$wd.'%')
                ->orWhere('author', 'like', '%'.$wd.'%')
                ->orWhere('tags', 'like', '%'.$wd.'%'));
        }
        if ((int) request()->query('type', request()->route('type', 0)) > 0) {
            $q->where('type_id', (int) request()->query('type', request()->route('type')));
        }
        $tag = trim((string) request()->query('tag', ''));
        if ($tag !== '') {
            $q->where('tags', 'like', '%'.$tag.'%');
        }
        $order = (string) request()->query('order', 'new');
        if ($order === 'hits') {
            $q->orderByDesc('hits')->orderByDesc('id');
        } elseif ($order === 'favor' && Schema::hasTable('plugin_novel_favors')) {
            $q->orderByDesc(DB::raw('(select count(*) from plugin_novel_favors where novel_id = plugin_novels.id)'))
                ->orderByDesc('id');
        } else {
            $q->orderByDesc('sort')->orderByDesc('id');
        }

        return $this->decorateFrontRows($q->paginate($perPage)->withQueryString());
    }

    /** @return Collection<int, Novel>|\Illuminate\Contracts\Pagination\LengthAwarePaginator */
    public function listForTag(array $options = []): Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        if (! $this->ready()) {
            return collect();
        }
        $num = max(1, (int) ($options['num'] ?? 12));
        $q = Novel::query()->published();
        if ((int) ($options['typeid'] ?? 0) > 0) {
            $q->where('type_id', (int) $options['typeid']);
        }
        if (($options['flag'] ?? '') === 'recommend' && Schema::hasColumn('plugin_novels', 'recommend')) {
            $q->where('recommend', 1);
        }
        $wd = trim((string) ($options['wd'] ?? ''));
        if ($wd !== '') {
            $q->where(fn ($x) => $x->where('title', 'like', '%'.$wd.'%')
                ->orWhere('author', 'like', '%'.$wd.'%')
                ->orWhere('tags', 'like', '%'.$wd.'%'));
        }
        $tag = trim((string) ($options['tag'] ?? ''));
        if ($tag !== '') {
            $q->where('tags', 'like', '%'.$tag.'%');
        }
        if (! empty($options['ids'])) {
            $ids = is_array($options['ids'])
                ? $options['ids']
                : (preg_split('/\s*,\s*/', (string) $options['ids']) ?: []);
            $q->whereIn('id', array_map('intval', $ids));
        }
        $order = (string) ($options['order'] ?? 'time');
        if (($options['flag'] ?? '') === 'hot' || $order === 'hits') {
            $q->orderByDesc('hits')->orderByDesc('id');
        } else {
            $q->orderByDesc('sort')->orderByDesc('id');
        }
        if (! empty($options['page'])) {
            $page = $q->paginate($num)->withQueryString();
            $this->decorateFrontRows($page);

            return $page;
        }
        $rows = $q->limit($num)->get();
        $this->decorateRowList($rows);

        return $rows;
    }

    /** 给列表附加收藏数。 */
    public function decorateFrontRows($page)
    {
        $this->decorateRowList(collect($page->items()));

        return $page;
    }

    /** @param  Collection<int, Novel>|Collection  $items */
    public function decorateRowList($items): void
    {
        $items = collect($items);
        $ids = $items->pluck('id')->map(fn ($id) => (int) $id)->all();
        $counts = $this->favorCounts($ids);
        foreach ($items as $row) {
            $row->favor_count = $counts[(int) $row->id] ?? (int) ($row->favor_count ?? 0);
            $row->tag_list = self::splitTags((string) ($row->tags ?? ''));
        }
    }

    /** 批量统计收藏数。 */
    public function favorCounts(array $ids): array
    {
        if ($ids === [] || ! Schema::hasTable('plugin_novel_favors')) {
            return [];
        }

        return NovelFavor::query()->whereIn('novel_id', $ids)
            ->select('novel_id', DB::raw('COUNT(*) as c'))
            ->groupBy('novel_id')->pluck('c', 'novel_id')
            ->map(fn ($c) => (int) $c)->all();
    }

    /** 单个作品收藏数。 */
    public function favorCount(int $novelId): int
    {
        return Schema::hasTable('plugin_novel_favors')
            ? (int) NovelFavor::query()->where('novel_id', $novelId)->count()
            : 0;
    }

    /** 前台标签云。 */
    public function tagCloud(int $limit = 30): array
    {
        if (! Schema::hasColumn('plugin_novels', 'tags')) {
            return [];
        }
        $map = [];
        foreach (Novel::query()->published()->where('tags', '!=', '')->pluck('tags') as $raw) {
            foreach (self::splitTags((string) $raw) as $tag) {
                $map[$tag] = ($map[$tag] ?? 0) + 1;
            }
        }
        arsort($map);

        return array_slice(array_keys($map), 0, $limit);
    }

    /** 获取前台启用分类。 */
    public function types(): Collection
    {
        return Schema::hasTable('plugin_novel_types')
            ? NovelType::query()->published()->orderBy('sort')->orderBy('id')->get()
            : collect();
    }

    /** 获取已发布小说。 */
    public function published(int $id): ?Novel
    {
        return $this->ready() ? Novel::query()->published()->find($id) : null;
    }

    /** 获取小说章节。 */
    public function chapter(Novel $novel, int $id): ?NovelChapter
    {
        return NovelChapter::query()->where('novel_id', $novel->id)->find($id);
    }

    /** 获取相邻章节。 */
    public function neighbors(Novel $novel, NovelChapter $chapter): array
    {
        $rows = NovelChapter::query()->where('novel_id', $novel->id)->orderBy('sort')->orderBy('id')->get();
        $at = $rows->search(fn ($row) => (int) $row->id === (int) $chapter->id);

        return ['prev' => $at !== false ? $rows->get($at - 1) : null, 'next' => $at !== false ? $rows->get($at + 1) : null];
    }

    /** 切换会员书架收藏。 */
    public function toggleFavor(int $memberId, int $novelId): array
    {
        if ($memberId < 1) {
            return Result::fail('请先登录');
        }
        $row = NovelFavor::query()->where('member_id', $memberId)->where('novel_id', $novelId)->first();
        if ($row) {
            $row->delete();

            return Result::success(['on' => 0], '已移出书架');
        }
        NovelFavor::query()->create(['member_id' => $memberId, 'novel_id' => $novelId, 'created_at' => time()]);

        return Result::success(['on' => 1], '已加入书架');
    }

    /** 判断会员是否已收藏。 */
    public function favored(int $memberId, int $novelId): bool
    {
        return $memberId > 0 && NovelFavor::query()->where('member_id', $memberId)->where('novel_id', $novelId)->exists();
    }

    /** 获取会员书架。 */
    public function shelf(int $memberId): Collection
    {
        $ids = NovelFavor::query()->where('member_id', $memberId)->orderByDesc('id')->pluck('novel_id');
        $rows = Novel::query()->published()->whereIn('id', $ids)->orderByDesc('updated_at')->get();
        $counts = $this->favorCounts($rows->pluck('id')->all());
        foreach ($rows as $row) {
            $row->favor_count = $counts[(int) $row->id] ?? 0;
            $row->tag_list = self::splitTags((string) ($row->tags ?? ''));
        }

        return $rows;
    }

    /** 记录阅读历史。 */
    public function recordHistory(int $memberId, Novel $novel, NovelChapter $chapter, int $progress = 0): void
    {
        if ($memberId < 1) {
            return;
        }
        $now = time();
        NovelHistory::query()->updateOrCreate(
            ['member_id' => $memberId, 'novel_id' => (int) $novel->id],
            ['chapter_id' => (int) $chapter->id, 'progress' => max(0, $progress), 'created_at' => $now, 'updated_at' => $now]
        );
    }

    /** 获取会员阅读历史。 */
    public function history(int $memberId): Collection
    {
        return NovelHistory::query()->where('member_id', $memberId)->with(['novel', 'chapter'])
            ->orderByDesc('updated_at')->limit(50)->get();
    }

    /** 获取前台展示评论。 */
    public function listedComments(Novel $novel): Collection
    {
        if (! Schema::hasTable('plugin_novel_comments')) {
            return collect();
        }

        return NovelComment::query()
            ->where('novel_id', $novel->id)
            ->where('status', 1)
            ->orderByDesc('id')
            ->limit(50)
            ->get();
    }

    /** 统计前台可见评论数。 */
    public function commentCount(Novel $novel): int
    {
        if (! Schema::hasTable('plugin_novel_comments')) {
            return 0;
        }

        return (int) NovelComment::query()->where('novel_id', $novel->id)->where('status', 1)->count();
    }

    /** 前台提交评论。 */
    public function addComment(int $novelId, string $content, ?Member $member, string $guestName, string $ip): array
    {
        if (! Schema::hasTable('plugin_novel_comments')) {
            return Result::fail('请先执行数据库迁移');
        }
        $novel = $this->published($novelId);
        if (! $novel) {
            return Result::fail('小说不存在');
        }
        $content = trim($content);
        if ($content === '') {
            return Result::fail('请填写评论');
        }
        $settings = app(VideoSettingService::class);
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
        $row = NovelComment::query()->create([
            'novel_id' => $novelId,
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
