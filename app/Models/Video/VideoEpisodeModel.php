<?php

namespace App\Models\Video;

use App\Models\Concerns\QueryCacheTrait;
use App\Models\Concerns\QueryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoEpisodeModel extends Model
{
    protected $table = 'video_episodes';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    use QueryTrait, QueryCacheTrait;

    protected $appends = ['play_url', 'down_url'];

    public function video(): BelongsTo
    {
        return $this->belongsTo(VideoModel::class, 'video_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(VideoSourceModel::class, 'source_id');
    }

    public function getPlayUrlAttribute(): string
    {
        return vod_url('play', [
            'id' => $this->video_id,
            'sid' => $this->source_id,
            'nid' => $this->id,
        ]);
    }

    public function getDownUrlAttribute(): string
    {
        return vod_url('down', [
            'id' => $this->video_id,
            'sid' => $this->source_id,
            'nid' => $this->id,
        ]);
    }

    public function getDisplayNameAttribute(): string
    {
        $name = trim((string) $this->episode_name);

        return $name !== '' ? $name : ('第'.$this->episode_num.'集');
    }
}
