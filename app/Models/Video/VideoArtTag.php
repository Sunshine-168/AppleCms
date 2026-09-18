<?php

namespace App\Models\Video;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VideoArtTag extends VideoOpsModel
{
    protected $table = 'video_art_tags';

    protected $appends = ['url'];

    public function arts(): BelongsToMany
    {
        return $this->belongsToMany(VideoArt::class, 'video_art_tag_rel', 'tag_id', 'art_id');
    }

    public function getUrlAttribute(): string
    {
        $slug = trim((string) $this->slug);

        return vod_url('art_tag', ['slug' => $slug !== '' ? $slug : $this->id]);
    }
}
