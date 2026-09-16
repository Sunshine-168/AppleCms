<?php

namespace App\Models\Video;

class VideoArt extends VideoOpsModel
{
    protected $table = 'video_arts';

    protected $appends = ['url'];

    public function getUrlAttribute(): string
    {
        return vod_url('art', ['id' => $this->id]);
    }
}
