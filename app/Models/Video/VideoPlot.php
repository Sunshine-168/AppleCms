<?php

namespace App\Models\Video;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoPlot extends VideoOpsModel
{
    protected $table = 'video_plots';

    protected $appends = ['url'];

    public function video(): BelongsTo
    {
        return $this->belongsTo(VideoModel::class, 'video_id');
    }

    public function getUrlAttribute(): string
    {
        return vod_url('plot', ['id' => $this->id]);
    }
}
