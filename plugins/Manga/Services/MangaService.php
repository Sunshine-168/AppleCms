<?php

namespace Plugins\Manga\Services;

use App\Models\Member\Member;
use App\Services\Video\VideoSettingService;
use App\Support\Utils\Result;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Plugins\Manga\Models\Manga;
use Plugins\Manga\Models\MangaChapter;
use Plugins\Manga\Models\MangaComment;
use Plugins\Manga\Models\MangaFavor;
use Plugins\Manga\Models\MangaType;

class MangaService
{
    public function ready(): bool
    {
        return Schema::hasTable('plugin_mangas') && Schema::hasTable('plugin_manga_chapters');
    }

    /** @param array<string, mixed> $filters */
    public function paginate(int $perPage = 24, array $filters = []): LengthAwarePaginator
    {
        $q = Manga::query()->published();
        $this->applyFrontFilters($q, $filters);
        $page = $q->paginate($perPage)->withQueryString();
        $this->decorateFrontRows(collect($page->items()));

        return $page;
    }

    /** @return Collection<int, Manga> */
    public function rank(string $board, int $limit = 50): Collection
    {
        $q = Manga::query()->published();
        if ($board === 'end' && Schema::hasColumn('plugin_mangas', 'serialize')) {
            $q->where('serialize', 1)->orderByDesc('hits')->orderByDesc('id');
        } elseif ($board === 'new') {
            $q->orderByDesc('id');
        } else {
            $q->orderByDesc('hits')->orderByDesc('id');
        }
        $rows = $q->limit($limit)->get();
        $this->decorateFrontRows($rows);

        return $rows;
    }

    /** @return array{recommend: Collection<int, Manga>, hot: Collection<int, Manga>, newest: Collection<int, Manga>} */
    public function indexBlocks(int $limit = 6): array
    {
        $recommend = collect();
        if (Schema::hasColumn('plugin_mangas', 'recommend')) {
            $recommend = Manga::query()->published()->where('recommend', 1)
                ->orderByDesc('sort')->orderByDesc('id')->limit($limit)->get();
        }
        $hot = Manga::query()->published()->orderByDesc('hits')->orderByDesc('id')->limit($limit)->get();
        $newest = Manga::query()->published()->orderByDesc('updated_at')->orderByDesc('id')->limit($limit)->get();
        $this->decorateFrontRows($recommend->concat($hot)->concat($newest)->unique('id')->values());

        return compact('recommend', 'hot', 'newest');
    }

    /** @return Collection<int, Manga> */
    public function homeList(int $limit = 6): Collection
    {
        $rows = Manga::query()->published()->orderByDesc('updated_at')->orderByDesc('id')->limit($limit)->get();
        $this->decorateFrontRows($rows);

        return $rows;
    }

    /** @return list<array{name:string,count:int}> */
    public function tagCloud(int $limit = 24): array
    {
        if (! Schema::hasColumn('plugin_mangas', 'tags')) {
            return [];
        }
        $counts = [];
        foreach (Manga::query()->published()->where('tags', '!=', '')->pluck('tags') as $raw) {
            foreach ((new Manga(['tags' => (string) $raw]))->tagNames() as $name) {
                $counts[$name] = ($counts[$name] ?? 0) + 1;
            }
        }
        arsort($counts, SORT_NUMERIC);
        $out = [];
        foreach (array_slice($counts, 0, $limit, true) as $name => $count) {
            $out[] = ['name' => (string) $name, 'count' => (int) $count];
        }

        return $out;
    }

    public function isFiltered(array $filters): bool
    {
        return (int) ($filters['type'] ?? 0) > 0
            || in_array((string) ($filters['serialize'] ?? ''), ['0', '1'], true)
            || (string) ($filters['recommend'] ?? '') === '1'
            || trim((string) ($filters['wd'] ?? '')) !== ''
            || trim((string) ($filters['author'] ?? '')) !== ''
            || trim((string) ($filters['tag'] ?? '')) !== ''
            || ((string) ($filters['order'] ?? 'new') !== 'new' && (string) ($filters['order'] ?? '') !== '');
    }

    public function published(int $id): ?Manga
    {
        if (! $this->ready()) {
            return null;
        }

        return Manga::query()->published()->find($id);
    }

    public function chapter(Manga $manga, int $chapterId): ?MangaChapter
    {
        $q = MangaChapter::query()->where('manga_id', $manga->id)->where('id', $chapterId);
        if (Schema::hasTable('plugin_manga_pics')) {
            $q->with('pics');
        }

        return $q->first();
    }

    /** @return array{prev:?MangaChapter,next:?MangaChapter} */
    public function neighbors(Manga $manga, MangaChapter $chapter): array
    {
        $chapters = MangaChapter::query()
            ->where('manga_id', $manga->id)
            ->orderBy('sort')
            ->orderBy('id')
            ->get(['id', 'manga_id', 'name', 'sort']);
        $prev = null;
        $next = null;
        foreach ($chapters as $i => $ep) {
            if ((int) $ep->id !== (int) $chapter->id) {
                continue;
            }
            $prev = $chapters->get($i - 1);
            $next = $chapters->get($i + 1);
            break;
        }

        return ['prev' => $prev, 'next' => $next];
    }

    /** @return Collection<int, Manga> */
    public function related(Manga $manga, int $limit = 6): Collection
    {
        $q = Manga::query()->published()->where('id', '!=', $manga->id);
        $tags = $manga->tagNames();
        $typeId = (int) ($manga->type_id ?? 0);
        if ($tags === [] && $typeId < 1) {
            return collect();
        }
        $q->where(function (Builder $inner) use ($tags, $typeId): void {
            foreach ($tags as $tag) {
                $inner->orWhere('tags', 'like', '%'.$tag.'%');
            }
            if ($typeId > 0) {
                $inner->orWhere('type_id', $typeId);
            }
        });

        $rows = $q->orderByDesc('hits')->orderByDesc('id')->limit($limit)->get();
        $this->decorateFrontRows($rows);

        return $rows;
    }

    public function bumpHits(Manga $manga): void
    {
        Manga::query()->where('id', $manga->id)->increment('hits');
    }

    /** @return Collection<int, MangaType> */
    public function listedTypes(): Collection
    {
        if (! Schema::hasTable('plugin_manga_types')) {
            return collect();
        }

        return MangaType::query()->where('status', 1)->orderBy('sort')->orderBy('id')->get();
    }

    /**
     * @return array{top: Collection<int, MangaType>, sub: Collection<int, MangaType>}
     */
    public function typeRails(int $typeId): array
    {
        $types = $this->listedTypes();
        $top = $types->where('parent_id', 0)->values();
        $selected = $types->firstWhere('id', $typeId);
        $parent = 0;
        if ($selected) {
            $parent = (int) $selected->parent_id > 0 ? (int) $selected->parent_id : (int) $selected->id;
        }
        $sub = $parent > 0 ? $types->where('parent_id', $parent)->values() : collect();

        return ['top' => $top, 'sub' => $sub];
    }

    /** @return list<array{id:int,name:string,parent_id:int,status:int,label:string}> */
    public function adminTypes(): array
    {
        if (! Schema::hasTable('plugin_manga_types')) {
            return [];
        }

        $all = MangaType::query()
            ->orderBy('sort')
            ->orderBy('id')
            ->get(['id', 'name', 'parent_id', 'status']);
        $byParent = [];
        foreach ($all as $row) {
            $byParent[(int) $row->parent_id][] = $row;
        }
        $out = [];
        $walk = function (int $parent, int $depth) use (&$walk, &$out, $byParent): void {
            foreach ($byParent[$parent] ?? [] as $row) {
                $prefix = $depth > 0 ? str_repeat('　', $depth).'└ ' : '';
                $out[] = [
                    'id' => (int) $row->id,
                    'name' => (string) $row->name,
                    'parent_id' => (int) $row->parent_id,
                    'status' => (int) $row->status,
                    'label' => $prefix.$row->name,
                ];
                $walk((int) $row->id, $depth + 1);
            }
        };
        $walk(0, 0);
        $seen = array_column($out, 'id');
        foreach ($all as $row) {
            if (in_array((int) $row->id, $seen, true)) {
                continue;
            }
            $out[] = [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'parent_id' => (int) $row->parent_id,
                'status' => (int) $row->status,
                'label' => (string) $row->name,
            ];
        }

        return $out;
    }

    /** @return array<string, mixed> */
    public function frontFilters(): array
    {
        $order = (string) request()->query('order', 'new');
        if (! in_array($order, ['new', 'hits', 'update'], true)) {
            $order = 'new';
        }
        $board = (string) request()->query('board', 'hits');
        if (! in_array($board, ['hits', 'new', 'end'], true)) {
            $board = 'hits';
        }

        return [
            'type' => (int) request()->query('type', 0),
            'serialize' => (string) request()->query('serialize', ''),
            'recommend' => (string) request()->query('recommend', ''),
            'wd' => trim((string) request()->query('wd', '')),
            'author' => trim((string) request()->query('author', '')),
            'tag' => trim((string) request()->query('tag', '')),
            'day' => trim((string) request()->query('day', '')),
            'order' => $order,
            'board' => $board,
        ];
    }

    /** @param array<string, mixed> $over */
    public function listUrl(array $over = []): string
    {
        $f = array_merge($this->frontFilters(), $over);
        $q = [];
        if ((int) ($f['type'] ?? 0) > 0) {
            $q['type'] = (int) $f['type'];
        }
        $serialize = (string) ($f['serialize'] ?? '');
        if ($serialize === '0' || $serialize === '1') {
            $q['serialize'] = $serialize;
        }
        if ((string) ($f['recommend'] ?? '') === '1') {
            $q['recommend'] = '1';
        }
        $wd = trim((string) ($f['wd'] ?? ''));
        if ($wd !== '') {
            $q['wd'] = $wd;
        }
        $author = trim((string) ($f['author'] ?? ''));
        if ($author !== '') {
            $q['author'] = $author;
        }
        $tag = trim((string) ($f['tag'] ?? ''));
        if ($tag !== '') {
            $q['tag'] = $tag;
        }
        $order = (string) ($f['order'] ?? 'new');
        if ($order !== 'new' && $order !== '') {
            $q['order'] = $order;
        }

        return url('/manga'.($q !== [] ? '?'.http_build_query($q) : ''));
    }

    /** @return Collection<int, MangaComment> */
    public function listedComments(Manga $manga): Collection
    {
        if (! Schema::hasTable('plugin_manga_comments')) {
            return collect();
        }

        return MangaComment::query()
            ->where('manga_id', $manga->id)
            ->where('status', 1)
            ->orderByDesc('id')
            ->limit(50)
            ->get();
    }

    public function commentCount(Manga $manga): int
    {
        if (! Schema::hasTable('plugin_manga_comments')) {
            return 0;
        }

        return (int) MangaComment::query()->where('manga_id', $manga->id)->where('status', 1)->count();
    }

    public function addComment(int $mangaId, string $content, ?Member $member, string $guestName, string $ip): array
    {
        if (! Schema::hasTable('plugin_manga_comments')) {
            return Result::fail('请先执行数据库迁移');
        }
        $manga = $this->published($mangaId);
        if (! $manga) {
            return Result::fail('漫画不存在');
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
        $row = MangaComment::query()->create([
            'manga_id' => $mangaId,
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

    public function favored(int $memberId, int $mangaId): bool
    {
        if ($memberId < 1 || $mangaId < 1 || ! Schema::hasTable('plugin_manga_favors')) {
            return false;
        }

        return MangaFavor::query()->where('member_id', $memberId)->where('manga_id', $mangaId)->exists();
    }

    public function toggleFavor(int $memberId, int $mangaId): array
    {
        if (! Schema::hasTable('plugin_manga_favors')) {
            return Result::fail('请先执行数据库迁移');
        }
        if ($memberId < 1) {
            return Result::fail('请先登录');
        }
        if (! $this->published($mangaId)) {
            return Result::fail('漫画不存在');
        }
        $row = MangaFavor::query()->where('member_id', $memberId)->where('manga_id', $mangaId)->first();
        if ($row) {
            $row->delete();

            return Result::success(['on' => 0], '已移出书架');
        }
        MangaFavor::query()->create([
            'member_id' => $memberId,
            'manga_id' => $mangaId,
            'created_at' => time(),
        ]);

        return Result::success(['on' => 1], '已加入书架');
    }

    /** @return Collection<int, Manga> */
    public function shelf(int $memberId): Collection
    {
        if ($memberId < 1 || ! Schema::hasTable('plugin_manga_favors')) {
            return collect();
        }
        $ids = MangaFavor::query()
            ->where('member_id', $memberId)
            ->orderByDesc('id')
            ->pluck('manga_id')
            ->all();
        if ($ids === []) {
            return collect();
        }
        $rows = Manga::query()->published()->whereIn('id', $ids)->get()->keyBy('id');
        $out = collect();
        foreach ($ids as $id) {
            $row = $rows->get((int) $id);
            if ($row) {
                $out->push($row);
            }
        }
        $this->decorateFrontRows($out);

        return $out;
    }

    /** @param Collection<int, Manga> $rows */
    public function decorateFrontRows(Collection $rows): void
    {
        $ids = $rows->pluck('id')->map(static fn ($id): int => (int) $id)->filter()->unique()->values()->all();
        if ($ids === [] || ! Schema::hasTable('plugin_manga_chapters')) {
            return;
        }
        $latest = [];
        $counts = [];
        $all = MangaChapter::query()
            ->whereIn('manga_id', $ids)
            ->orderByDesc('sort')
            ->orderByDesc('id')
            ->get(['id', 'manga_id', 'name']);
        foreach ($all as $chapter) {
            $mangaId = (int) $chapter->manga_id;
            $counts[$mangaId] = ($counts[$mangaId] ?? 0) + 1;
            if (! isset($latest[$mangaId])) {
                $latest[$mangaId] = [
                    'id' => (int) $chapter->id,
                    'name' => (string) ($chapter->name ?: ('第'.$chapter->id.'话')),
                ];
            }
        }
        foreach ($rows as $row) {
            $id = (int) $row->id;
            $row->setAttribute('latest_chapter', $latest[$id] ?? null);
            $row->setAttribute('chapter_count', (int) ($counts[$id] ?? 0));
        }
    }

    /** @return list<array{label:string,value:string}> */
    public function updateDays(int $days = 7): array
    {
        $out = [];
        $today = strtotime('today');
        for ($i = 0; $i < $days; $i++) {
            $ts = $today - ($i * 86400);
            $out[] = [
                'label' => $i === 0 ? '今天' : date('n月j日', $ts),
                'value' => date('Y-m-d', $ts),
            ];
        }

        return $out;
    }

    /** @param array<string, mixed> $filters */
    private function applyFrontFilters(Builder $q, array $filters): void
    {
        $typeId = (int) ($filters['type'] ?? 0);
        if ($typeId > 0 && Schema::hasColumn('plugin_mangas', 'type_id')) {
            $ids = [$typeId];
            if (Schema::hasTable('plugin_manga_types')) {
                $ids = array_merge(
                    $ids,
                    MangaType::query()->where('parent_id', $typeId)->pluck('id')->all()
                );
            }
            $q->whereIn('type_id', array_values(array_unique(array_map('intval', $ids))));
        }
        $serialize = $filters['serialize'] ?? '';
        if (($serialize === '0' || $serialize === '1' || $serialize === 0 || $serialize === 1)
            && Schema::hasColumn('plugin_mangas', 'serialize')) {
            $q->where('serialize', (int) $serialize);
        }
        if ((string) ($filters['recommend'] ?? '') === '1' && Schema::hasColumn('plugin_mangas', 'recommend')) {
            $q->where('recommend', 1);
        }
        $author = trim((string) ($filters['author'] ?? ''));
        if ($author !== '') {
            $q->where('author', $author);
        }
        $tag = trim((string) ($filters['tag'] ?? ''));
        if ($tag !== '' && Schema::hasColumn('plugin_mangas', 'tags')) {
            $q->where(function (Builder $inner) use ($tag): void {
                $inner->where('tags', $tag)
                    ->orWhere('tags', 'like', $tag.',%')
                    ->orWhere('tags', 'like', '%,'.$tag)
                    ->orWhere('tags', 'like', '%,'.$tag.',%');
            });
        }
        $day = trim((string) ($filters['day'] ?? ''));
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $day) === 1) {
            $start = strtotime($day.' 00:00:00');
            if ($start) {
                $q->where('updated_at', '>=', $start)->where('updated_at', '<', $start + 86400);
            }
        }
        $wd = trim((string) ($filters['wd'] ?? ''));
        if ($wd !== '') {
            $q->where(function (Builder $inner) use ($wd): void {
                $inner->where('title', 'like', '%'.$wd.'%')
                    ->orWhere('author', 'like', '%'.$wd.'%')
                    ->orWhere('remarks', 'like', '%'.$wd.'%');
                if (Schema::hasColumn('plugin_mangas', 'tags')) {
                    $inner->orWhere('tags', 'like', '%'.$wd.'%');
                }
            });
        }
        $order = (string) ($filters['order'] ?? 'new');
        if ($order === 'hits') {
            $q->orderByDesc('hits')->orderByDesc('id');
        } elseif ($order === 'update') {
            $q->orderByDesc('updated_at')->orderByDesc('id');
        } else {
            $q->orderByDesc('sort')->orderByDesc('id');
        }
    }
}
