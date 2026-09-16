<?php

namespace App\Models\Video;

use App\Models\Concerns\QueryCacheTrait;
use App\Models\Concerns\QueryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ActorModel extends Model
{
    protected $table = 'actors';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    use QueryTrait, QueryCacheTrait;

    protected $appends = ['url'];

    public function videos(): BelongsToMany
    {
        return $this->belongsToMany(VideoModel::class, 'video_actor_rel', 'actor_id', 'video_id');
    }

    public function getUrlAttribute(): string
    {
        return vod_url('actor', ['id' => $this->id]);
    }
}
