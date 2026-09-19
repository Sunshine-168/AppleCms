<?php

namespace App\Models\Video;

use App\Support\QueryCacheTrait;
use App\Support\QueryTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Schema;
use Laravel\Scout\Searchable;

class VideoModel extends Model
{
    protected $table = 'videos';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    use QueryTrait, QueryCacheTrait, Searchable;

    protected $appends = ['url', 'play_url'];

    /**
     * 写入 Scout 索引的字段。
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $row = [
            'id' => (int) $this->id,
            'title' => (string) ($this->title ?? ''),
            'subtitle' => (string) ($this->subtitle ?? ''),
            'director' => (string) ($this->director ?? ''),
        ];
        foreach (['actor', 'actors', 'remarks'] as $col) {
            if (Schema::hasColumn($this->getTable(), $col)) {
                $row[$col] = (string) ($this->{$col} ?? '');
            }
        }

        return $row;
    }

    /**
     * 插件关闭或未开搜时不写索引。
     */
    public function shouldBeSearchable(): bool
    {
        try {
            return app(\Plugins\Scout\Services\ScoutSearchService::class)->indexingEnabled();
        } catch (\Throwable) {
            return false;
        }
    }

    protected static function booted(): void
    {
        static::addGlobalScope('alive', function (Builder $query) {
            try {
                if (! Schema::hasColumn('videos', 'deleted_at')) {
                    return;
                }
            } catch (\Throwable) {
                return;
            }
            $query->where(function (Builder $inner) {
                $inner->where('deleted_at', 0)->orWhereNull('deleted_at');
            });
        });
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(VideoTypeModel::class, 'type_id');
    }

    public function sources(): HasMany
    {
        return $this->hasMany(VideoSourceModel::class, 'video_id')->orderByDesc('sort')->orderBy('id');
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(VideoEpisodeModel::class, 'video_id')->orderBy('episode_num')->orderBy('id');
    }

    public function roles(): HasMany
    {
        return $this->hasMany(VideoRole::class, 'video_id')->orderByDesc('sort')->orderBy('id');
    }

    public function plots(): HasMany
    {
        return $this->hasMany(VideoPlot::class, 'video_id')->orderBy('episode_num')->orderBy('sort');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(VideoTagModel::class, 'video_tag_rel', 'video_id', 'tag_id');
    }

    public function actors(): BelongsToMany
    {
        return $this->belongsToMany(ActorModel::class, 'video_actor_rel', 'video_id', 'actor_id')
            ->withPivot(['role_type', 'sort'])
            ->orderByPivot('sort', 'desc');
    }

    public function stat(): HasOne
    {
        return $this->hasOne(VideoStatModel::class, 'video_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    public function getUrlAttribute(): string
    {
        return vod_url('detail', ['id' => $this->id]);
    }

    public function getPlayUrlAttribute(): string
    {
        return vod_url('play', ['id' => $this->id]);
    }

    public function getHitsAttribute($value): int
    {
        if ($value !== null) {
            return (int) $value;
        }

        return (int) ($this->stat?->hits ?? 0);
    }
}
