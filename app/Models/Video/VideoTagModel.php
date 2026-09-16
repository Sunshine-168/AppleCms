<?php

namespace App\Models\Video;

use App\Support\QueryCacheTrait;
use App\Support\QueryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VideoTagModel extends Model
{
    protected $table = 'video_tags';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    use QueryTrait, QueryCacheTrait;

    protected $appends = ['url'];

    public function videos(): BelongsToMany
    {
        return $this->belongsToMany(VideoModel::class, 'video_tag_rel', 'tag_id', 'video_id');
    }

    public function getUrlAttribute(): string
    {
        $slug = trim((string) $this->slug);

        return vod_url('tag', ['slug' => $slug !== '' ? $slug : $this->id]);
    }
}
