<?php

namespace App\Models\Video;

use App\Support\QueryCacheTrait;
use App\Support\QueryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VideoTopicModel extends Model
{
    protected $table = 'video_topics';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    use QueryTrait, QueryCacheTrait;

    protected $appends = ['url'];

    public function videos(): BelongsToMany
    {
        return $this->belongsToMany(VideoModel::class, 'video_topic_rel', 'topic_id', 'video_id')
            ->withPivot(['sort'])
            ->orderByPivot('sort', 'desc');
    }

    public function getUrlAttribute(): string
    {
        $slug = trim((string) $this->slug);

        return vod_url('topic', ['id' => $slug !== '' ? $slug : $this->id]);
    }
}
