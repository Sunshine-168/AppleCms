<?php

namespace App\Services\Video\Tags;

use App\Models\Video\VideoArt;
use App\Models\Video\VideoTypeModel;
use App\Cms\CmsViewContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ArtTag
{
    public function __construct(private readonly CmsViewContext $context) {}

    /** @param  array<string, mixed>  $options */
    public function get(array $options = []): Collection|LengthAwarePaginator
    {
        if (! Schema::hasTable('video_arts')) {
            return collect();
        }
        $num = max(1, (int) ($options['num'] ?? 12));
        $query = VideoArt::query()->listed();
        $this->applyTypeFilter($query, $options);
        $this->applyTagFilter($query, $options);
        $flag = strtolower(trim((string) ($options['flag'] ?? '')));
        if ($flag !== '') {
            $query->withFlag($flag);
        }
        $this->applyOrder($query, $options);
        if (! empty($options['page'])) {
            $paginator = $query->paginate($num)->withQueryString();
            $this->context->setPaginator($paginator);

            return $paginator;
        }

        return $query->limit($num)->get();
    }

    private function applyTypeFilter(Builder $query, array $options): void
    {
        $typeId = $options['typeid'] ?? $options['type_id'] ?? $options['tid'] ?? null;
        if ($typeId === null || $typeId === '' || in_array($typeId, [0, '0', 'all'], true)) {
            return;
        }
        if (! Schema::hasTable('video_types')) {
            $query->where('type_id', (int) $typeId);

            return;
        }
        $type = is_numeric($typeId)
            ? VideoTypeModel::query()->find((int) $typeId)
            : VideoTypeModel::query()->where('slug', (string) $typeId)->first();
        if (! $type) {
            return;
        }
        $ids = $type->descendantIds();
        $query->whereIn('video_arts.type_id', $ids);
    }

    /** @param  array<string, mixed>  $options */
    private function applyTagFilter(Builder $query, array $options): void
    {
        $tagId = (int) ($options['tag_id'] ?? 0);
        $tagKey = trim((string) ($options['tag'] ?? $options['bytag'] ?? ''));
        if ($tagId < 1 && $tagKey === '') {
            return;
        }
        if (! Schema::hasTable('video_art_tag_rel') || ! Schema::hasTable('video_art_tags')) {
            if ($tagKey !== '' && Schema::hasColumn('video_arts', 'tag')) {
                $query->where('video_arts.tag', 'like', '%'.$tagKey.'%');
            } elseif ($tagId > 0) {
                $query->whereRaw('0 = 1');
            }

            return;
        }
        $query->whereHas('tags', function (Builder $inner) use ($tagId, $tagKey) {
            $inner->where('video_art_tags.status', 1);
            if ($tagId > 0) {
                $inner->where('video_art_tags.id', $tagId);

                return;
            }
            if (ctype_digit($tagKey)) {
                $inner->where('video_art_tags.id', (int) $tagKey);

                return;
            }
            $inner->where(function (Builder $name) use ($tagKey) {
                $name->where('video_art_tags.slug', $tagKey)->orWhere('video_art_tags.name', $tagKey);
            });
        });
    }

    /** @param  array<string, mixed>  $options */
    private function applyOrder(Builder $query, array $options): void
    {
        $by = strtolower(trim((string) ($options['by'] ?? '')));
        if ($by === '') {
            $by = strtolower(trim((string) ($options['order'] ?? 'id')));
        }
        $table = (new VideoArt)->getTable();
        match ($by) {
            'hits' => $query->orderByDesc($table.'.hits')->orderByDesc($table.'.id'),
            'time', 'updated_at' => $query->orderByDesc($table.'.updated_at')->orderByDesc($table.'.id'),
            'sort' => Schema::hasColumn($table, 'sort')
                ? $query->orderByDesc($table.'.sort')->orderByDesc($table.'.id')
                : $query->orderByDesc($table.'.id'),
            default => $query->orderByDesc($table.'.id'),
        };
    }
}
