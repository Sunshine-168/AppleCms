<?php

namespace App\Models\Video;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoRole extends VideoOpsModel
{
    protected $table = 'video_roles';

    protected $appends = ['url'];

    public function video(): BelongsTo
    {
        return $this->belongsTo(VideoModel::class, 'video_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(ActorModel::class, 'actor_id');
    }

    public function getUrlAttribute(): string
    {
        return vod_url('role', ['id' => $this->id]);
    }
}
