<?php

namespace App\Models\Video;

use App\Support\PlayLineName;
use App\Support\QueryCacheTrait;
use App\Support\QueryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VideoSourceModel extends Model
{
    protected $table = 'video_sources';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
    protected $appends = ['display_name'];

    use QueryTrait, QueryCacheTrait;

    public function getDisplayNameAttribute(): string
    {
        $player = (string) ($this->attributes['player'] ?? '');
        $name = (string) ($this->attributes['name'] ?? '');

        return PlayLineName::label($player, $name);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(VideoModel::class, 'video_id');
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(VideoEpisodeModel::class, 'source_id')->orderBy('episode_num')->orderBy('id');
    }
}
