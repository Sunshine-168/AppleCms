<?php

namespace App\Models\Video;

use App\Models\Concerns\QueryCacheTrait;
use App\Models\Concerns\QueryTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VideoTypeModel extends Model
{
    protected $table = 'video_types';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    use QueryTrait, QueryCacheTrait;

    protected $appends = ['url'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->where('status', 1)->orderByDesc('sort')->orderBy('id');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(VideoModel::class, 'type_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    public function getUrlAttribute(): string
    {
        $slug = trim((string) $this->slug);

        return vod_url('type', ['id' => $slug !== '' ? $slug : $this->id]);
    }

    public function descendantIds(): array
    {
        $ids = [(int) $this->id];
        $pending = [(int) $this->id];
        $guard = 0;
        while ($pending && $guard < 50) {
            $children = self::query()->whereIn('parent_id', $pending)->pluck('id')->map(fn ($id) => (int) $id)->all();
            $children = array_values(array_diff($children, $ids));
            $ids = array_merge($ids, $children);
            $pending = $children;
            $guard++;
        }

        return array_values(array_unique($ids));
    }
}
