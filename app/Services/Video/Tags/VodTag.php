<?php

namespace App\Services\Video\Tags;

use App\Models\Video\VideoModel;
use App\Models\Video\VideoTypeModel;
use App\Cms\CmsViewContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class VodTag
{
    public function __construct(private readonly CmsViewContext $context) {}

    /** @param  array<string, mixed>  $options */
    public function get(array $options = []): Collection|LengthAwarePaginator
    {
        $num = max(1, (int) ($options['num'] ?? ($this->context->type() ? config('video.per_page', 24) : 12)));
        $page = (bool) ($options['page'] ?? false);
        $order = (string) ($options['order'] ?? request('order') ?: 'time');
        $flag = (string) ($options['flag'] ?? '');
        if ($flag === 'hot' && $order === 'time') {
            $order = 'hits';
        }

        $query = VideoModel::query()->with(['type', 'stat'])->published();
        $this->applyTypeFilter($query, $options, (bool) ($options['insub'] ?? true));
        $this->applyFlagFilter($query, $options);
        $this->applyMetaFilter($query, $options);
        $this->applyIdsFilter($query, $options);
        $this->applyTagFilter($query, $options);
        $this->applyOrder($query, $order);

        if ($page) {
            $paginator = $query->paginate($num)->withQueryString();
            $this->context->setPaginator($paginator);

            return $paginator;
        }

        return $query->limit($num)->get();
    }

    private function applyTypeFilter(Builder $query, array $options, bool $insub): void
    {
        $typeId = $options['typeid'] ?? $options['type_id'] ?? $options['tid'] ?? null;
        if ($typeId === null || $typeId === '' || in_array($typeId, [0, '0', 'all'], true)) {
            $typeId = $this->context->type()?->id;
        }
        if (! $typeId) {
            return;
        }

        $type = is_numeric($typeId)
            ? VideoTypeModel::query()->find((int) $typeId)
            : VideoTypeModel::query()->where('slug', (string) $typeId)->first();
        if (! $type) {
            return;
        }

        $ids = $insub ? $type->descendantIds() : [(int) $type->id];
        $query->where(function (Builder $inner) use ($ids) {
            $inner->whereIn('type_id', $ids)->orWhereIn('type_pid', $ids);
        });
    }

    private function applyFlagFilter(Builder $query, array $options): void
    {
        $flag = (string) ($options['flag'] ?? '');
        if ($flag === 'recommend' || ! empty($options['recommend'])) {
            $query->where('is_recommend', 1);
        }
        if ($flag === 'slide' || $flag === 'level') {
            $query->where('level', '>=', (int) ($options['level'] ?? 1));
        }
        if (isset($options['level']) && $flag !== 'hot') {
            $query->where('level', '>=', (int) $options['level']);
        }
    }

    private function applyMetaFilter(Builder $query, array $options): void
    {
        $filters = array_merge($this->context->filters(), $options);
        foreach (['year', 'area', 'lang', 'letter', 'class', 'weekday', 'serial'] as $field) {
            if (in_array($field, ['weekday', 'serial'], true) && ! Schema::hasColumn('videos', $field)) {
                continue;
            }
            $value = $filters[$field] ?? request($field);
            if ($value !== null && $value !== '' && $value !== 'all') {
                if ($field === 'class') {
                    $query->where('class', 'like', '%'.$value.'%');
                } else {
                    $query->where($field, $value);
                }
            }
        }

        $kw = trim((string) ($options['wd'] ?? $options['q'] ?? $filters['wd'] ?? request('wd', request('q', ''))));
        if ($kw !== '') {
            $kw = app(\App\Services\Video\SynonymService::class)->expand($kw);
            $scoutIds = null;
            try {
                $scoutIds = app(\Plugins\Scout\Services\ScoutSearchService::class)->searchVideoIds($kw);
            } catch (\Throwable) {
                $scoutIds = null;
            }
            if (is_array($scoutIds)) {
                if ($scoutIds === []) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereIn('videos.id', $scoutIds);
                }
            } else {
                $query->where(function (Builder $inner) use ($kw) {
                    $inner->where('title', 'like', "%{$kw}%")
                        ->orWhere('subtitle', 'like', "%{$kw}%")
                        ->orWhere('director', 'like', "%{$kw}%");
                });
            }
        }
    }

    private function applyIdsFilter(Builder $query, array $options): void
    {
        if (! empty($options['id'])) {
            $query->where('id', (int) $options['id']);
        }
        if (! empty($options['ids'])) {
            $ids = is_array($options['ids'])
                ? $options['ids']
                : (preg_split('/\s*,\s*/', (string) $options['ids']) ?: []);
            $query->whereIn('id', array_map('intval', $ids));
        }
    }

    private function applyTagFilter(Builder $query, array $options): void
    {
        $tag = $options['tag'] ?? null;
        if ($tag === null || $tag === '') {
            return;
        }
        $query->whereHas('tags', function (Builder $inner) use ($tag) {
            if (is_numeric($tag)) {
                $inner->where('video_tags.id', (int) $tag);
            } else {
                $inner->where('video_tags.name', $tag)->orWhere('video_tags.slug', $tag);
            }
        });
    }

    private function applyOrder(Builder $query, string $order): void
    {
        $query->leftJoin('video_stats', 'video_stats.video_id', '=', 'videos.id');
        $query->select('videos.*');

        match ($order) {
            'hits', 'hot' => $query->orderByDesc('video_stats.hits')->orderByDesc('videos.id'),
            'score' => $query->orderByDesc('video_stats.score')->orderByDesc('videos.id'),
            'year' => $query->orderByDesc('videos.year')->orderByDesc('videos.id'),
            'sort' => $query->orderByDesc('videos.sort')->orderByDesc('videos.id'),
            default => $query->orderByDesc('videos.updated_at')->orderByDesc('videos.id'),
        };
    }
}
