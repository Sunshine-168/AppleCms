<?php

namespace App\Services\Video\Tags;

use App\Models\Video\VideoTypeModel;
use App\Cms\CmsViewContext;
use Illuminate\Support\Collection;

class TypeTag
{
    public function __construct(private readonly CmsViewContext $context) {}

    /** @param  array<string, mixed>  $options */
    public function get(array $options = []): Collection
    {
        $type = (string) ($options['type'] ?? 'top');
        $num = isset($options['num']) ? (int) $options['num'] : null;
        $parentId = $this->resolveParentId($type, $options['id'] ?? $options['name'] ?? null);

        $query = VideoTypeModel::query()->active()->orderByDesc('sort')->orderBy('id');
        if ($parentId === 0) {
            $query->where(function ($q) {
                $q->where('parent_id', 0)->orWhereNull('parent_id');
            });
        } elseif ($parentId !== null) {
            $query->where('parent_id', $parentId);
        }

        if ($num && $num > 0) {
            $query->limit($num);
        }

        return $query->get();
    }

    private function resolveParentId(string $type, mixed $id): ?int
    {
        if ($id !== null && $id !== '') {
            return (int) $id;
        }

        $current = $this->context->type();

        return match ($type) {
            'top' => 0,
            'son', 'selfson' => $current?->id,
            'peer' => (int) ($current?->parent_id ?? 0),
            default => 0,
        };
    }
}
