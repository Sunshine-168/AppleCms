<?php

namespace App\Services\Video\Tags;

use App\Cms\CmsViewContext;
use App\Models\Video\VideoArt;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoTopicModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class TopicTag
{
    public function __construct(private readonly CmsViewContext $context) {}

    /** @param  array<string, mixed>  $options */
    public function get(array $options = []): Collection|LengthAwarePaginator
    {
        if (! Schema::hasTable('video_topics')) {
            return collect();
        }

        $paging = $this->isPaging($options);
        if ($paging) {
            $options = $this->mergeRequest($options);
        }
        $num = max(1, (int) ($options['num'] ?? ($paging ? 20 : 10)));

        $query = VideoTopicModel::query()->where('status', 1);
        $this->applyWithCount($query);
        $this->applyFilters($query, $options);
        $this->applyOrder($query, $options);

        if ($paging) {
            $paginator = $query->paginate($num)->withQueryString();
            $this->context->setPaginator($paginator);

            return $paginator;
        }

        $start = (int) ($options['start'] ?? 0);
        if ($start >= 1) {
            $query->skip($start - 1);
        }

        return $query->limit($num)->get();
    }

    /** @param  array<string, mixed>  $options */
    private function isPaging(array $options): bool
    {
        return $this->isTruthy($options['page'] ?? false) || $this->isTruthy($options['paging'] ?? false);
    }

    private function isTruthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value)) {
            return (int) $value === 1;
        }
        $s = strtolower(trim((string) $value));

        return in_array($s, ['1', 'true', 'yes', 'on', 'y'], true);
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function mergeRequest(array $options): array
    {
        foreach (['id', 'ids', 'level', 'letter', 'wd', 'tag', 'class', 'by', 'order'] as $key) {
            $current = $options[$key] ?? null;
            if ($current !== null && $current !== '') {
                continue;
            }
            $fromReq = request()->query($key, request()->input($key));
            if ($fromReq !== null && $fromReq !== '') {
                $options[$key] = $fromReq;
            }
        }

        return $options;
    }

    private function applyWithCount(Builder $query): void
    {
        $counts = [];
        if (Schema::hasTable('video_topic_rel')) {
            $counts['videos'] = function ($q) {
                if (method_exists(VideoModel::class, 'scopePublished')) {
                    $q->published();
                }
            };
        }
        if (
            class_exists(VideoArt::class)
            && Schema::hasTable('video_topic_art_rel')
            && Schema::hasTable('video_arts')
        ) {
            $counts['arts'] = function ($q) {
                $q->listed();
            };
        }
        if ($counts !== []) {
            $query->withCount($counts);
        }
    }

    /** @param  array<string, mixed>  $options */
    private function applyFilters(Builder $query, array $options): void
    {
        $id = $options['id'] ?? null;
        if ($id !== null && $id !== '' && $id !== 'all') {
            $query->where('id', (int) $id);
        }

        $ids = $options['ids'] ?? null;
        if ($ids !== null && $ids !== '' && $ids !== 'all') {
            $query->whereIn('id', $this->intList($ids));
        }

        $not = $options['not'] ?? null;
        if ($not !== null && $not !== '' && $not !== 'all') {
            $query->whereNotIn('id', $this->intList($not));
        }

        $wd = trim((string) ($options['wd'] ?? ''));
        if ($wd !== '') {
            $query->where(function (Builder $inner) use ($wd) {
                $inner->where('name', 'like', '%'.$wd.'%')
                    ->orWhere('slug', 'like', '%'.$wd.'%');
                if (Schema::hasColumn('video_topics', 'sub')) {
                    $inner->orWhere('sub', 'like', '%'.$wd.'%');
                }
            });
        }

        $letter = trim((string) ($options['letter'] ?? ''));
        if ($letter !== '' && Schema::hasColumn('video_topics', 'letter')) {
            $query->where('letter', $letter);
        }

        $level = $options['level'] ?? null;
        if ($level !== null && $level !== '' && Schema::hasColumn('video_topics', 'level')) {
            $query->whereIn('level', $this->intList($level));
        }

        $class = trim((string) ($options['class'] ?? ''));
        if ($class !== '' && Schema::hasColumn('video_topics', 'type')) {
            $query->where('type', 'like', '%'.$class.'%');
        }

        $tag = trim((string) ($options['tag'] ?? ''));
        if ($tag !== '' && Schema::hasColumn('video_topics', 'tag')) {
            $query->where('tag', 'like', '%'.$tag.'%');
        }

        $this->applyTimeFilter($query, 'created_at', $options['timeadd'] ?? null);
        $this->applyTimeFilter($query, 'updated_at', $options['time'] ?? null);
        if (Schema::hasColumn('video_topics', 'time_hits')) {
            $this->applyTimeFilter($query, 'time_hits', $options['timehits'] ?? $options['time_hits'] ?? null);
        }

        $hitMap = [
            'hits' => 'hits',
            'hitsday' => 'hits_day',
            'hits_day' => 'hits_day',
            'hitsweek' => 'hits_week',
            'hits_week' => 'hits_week',
            'hitsmonth' => 'hits_month',
            'hits_month' => 'hits_month',
        ];
        foreach ($hitMap as $opt => $col) {
            if (array_key_exists($opt, $options) && $options[$opt] !== null && $options[$opt] !== '') {
                $this->applyHitsCompare($query, $col, $options[$opt]);
            }
        }
    }

    private function applyTimeFilter(Builder $query, string $column, mixed $value): void
    {
        if ($value === null || $value === '') {
            return;
        }
        if (! Schema::hasColumn('video_topics', $column)) {
            return;
        }
        $ts = strtotime((string) $value);
        if ($ts === false) {
            return;
        }
        $query->where($column, '>', $ts);
    }

    private function applyHitsCompare(Builder $query, string $column, mixed $value): void
    {
        if (! Schema::hasColumn('video_topics', $column)) {
            return;
        }
        $value = trim((string) $value);
        if ($value === '') {
            return;
        }
        if (preg_match('/^gt\s+(\d+)$/i', $value, $m)) {
            $query->where($column, '>', (int) $m[1]);

            return;
        }
        if (preg_match('/^lt\s+(\d+)$/i', $value, $m)) {
            $query->where($column, '<', (int) $m[1]);

            return;
        }
        if (preg_match('/^between\s+(\d+)\s*,\s*(\d+)$/i', $value, $m)) {
            $query->whereBetween($column, [(int) $m[1], (int) $m[2]]);

            return;
        }
        if (is_numeric($value)) {
            $query->where($column, '>', (int) $value);
        }
    }

    /** @param  array<string, mixed>  $options */
    private function applyOrder(Builder $query, array $options): void
    {
        $by = trim((string) ($options['by'] ?? ''));
        $dir = strtolower(trim((string) ($options['order'] ?? 'desc')));
        $vodStyle = ['time', 'hits', 'hot', 'score', 'sort', 'id', 'rnd'];
        if ($by === '' && in_array($dir, $vodStyle, true)) {
            $by = $dir === 'hot' ? 'hits' : $dir;
            $dir = 'desc';
        }
        if (! in_array($dir, ['asc', 'desc'], true)) {
            $dir = 'desc';
        }

        if ($by === 'rnd') {
            $rand = $query->getConnection()->getDriverName() === 'sqlite' ? 'RANDOM()' : 'RAND()';
            $query->orderByRaw($rand)->orderBy('id', $dir);

            return;
        }

        $map = [
            'id' => 'id',
            'sort' => 'sort',
            'time' => 'updated_at',
            'time_add' => 'created_at',
            'score' => 'score',
            'hits' => 'hits',
            'hits_day' => 'hits_day',
            'hits_week' => 'hits_week',
            'hits_month' => 'hits_month',
            'up' => 'up',
            'down' => 'down',
            'level' => 'level',
        ];
        if ($by === '' || ! isset($map[$by])) {
            $query->orderByDesc('sort')->orderByDesc('id');

            return;
        }
        $col = $map[$by];
        if ($col === 'id' || Schema::hasColumn('video_topics', $col)) {
            $query->orderBy($col, $dir);
        }
        $query->orderBy('id', $dir);
    }

    /** @return list<int> */
    private function intList(mixed $value): array
    {
        $items = is_array($value)
            ? $value
            : (preg_split('/\s*,\s*/', (string) $value) ?: []);

        return array_values(array_filter(array_map('intval', $items), fn (int $id) => $id > 0));
    }
}
