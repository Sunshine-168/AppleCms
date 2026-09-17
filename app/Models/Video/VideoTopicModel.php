<?php

namespace App\Models\Video;

use App\Support\QueryCacheTrait;
use App\Support\QueryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class VideoTopicModel extends Model
{
    protected $table = 'video_topics';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    use QueryTrait, QueryCacheTrait;

    protected $appends = ['url'];

    /** @var array<string, string> */
    private const TOPIC_ALIASES = [
        'topic_id' => 'id',
        'topic_name' => 'name',
        'topic_en' => 'slug',
        'topic_sub' => 'sub',
        'topic_status' => 'status',
        'topic_sort' => 'sort',
        'topic_letter' => 'letter',
        'topic_color' => 'color',
        'topic_tpl' => 'tpl',
        'topic_type' => 'type',
        'topic_pic' => 'cover',
        'topic_pic_thumb' => 'cover_thumb',
        'topic_pic_slide' => 'cover_slide',
        'topic_key' => 'seo_key',
        'topic_des' => 'seo_des',
        'topic_title' => 'seo_title',
        'topic_blurb' => 'blurb',
        'topic_remarks' => 'remarks',
        'topic_level' => 'level',
        'topic_up' => 'up',
        'topic_down' => 'down',
        'topic_score' => 'score',
        'topic_score_all' => 'score_all',
        'topic_score_num' => 'score_num',
        'topic_hits' => 'hits',
        'topic_hits_day' => 'hits_day',
        'topic_hits_week' => 'hits_week',
        'topic_hits_month' => 'hits_month',
        'topic_time' => 'updated_at',
        'topic_time_add' => 'created_at',
        'topic_time_hits' => 'time_hits',
        'topic_content' => 'content',
        'topic_extend' => 'extend',
        'topic_tag' => 'tag',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            try {
                if (! Schema::hasColumn($model->getTable(), 'letter')) {
                    return;
                }
            } catch (\Throwable) {
                return;
            }
            if (trim((string) ($model->attributes['letter'] ?? '')) !== '') {
                return;
            }
            $src = trim((string) (($model->attributes['slug'] ?? '') !== '' ? $model->attributes['slug'] : ($model->attributes['name'] ?? '')));
            if (preg_match('/[A-Za-z0-9]/', $src, $m)) {
                $model->setAttribute('letter', strtoupper($m[0]));
            } else {
                $model->setAttribute('letter', '#');
            }
        });
    }

    public function getAttribute($key)
    {
        if (is_string($key) && isset(self::TOPIC_ALIASES[$key])) {
            $key = self::TOPIC_ALIASES[$key];
        }

        return parent::getAttribute($key);
    }

    public function videos(): BelongsToMany
    {
        return $this->belongsToMany(VideoModel::class, 'video_topic_rel', 'topic_id', 'video_id')
            ->withPivot(['sort'])
            ->orderByPivot('sort', 'desc');
    }

    public function arts(): BelongsToMany
    {
        return $this->belongsToMany(VideoArt::class, 'video_topic_art_rel', 'topic_id', 'art_id')
            ->withPivot(['sort'])
            ->orderByPivot('sort', 'desc');
    }

    public function getUrlAttribute(): string
    {
        $slug = trim((string) $this->slug);

        return vod_url('topic', ['id' => $slug !== '' ? $slug : $this->id]);
    }

    public function getTopicRelVodCountAttribute(): int
    {
        if (array_key_exists('video_count', $this->attributes)) {
            return (int) $this->attributes['video_count'];
        }
        if (array_key_exists('videos_count', $this->attributes)) {
            return (int) $this->attributes['videos_count'];
        }

        return (int) $this->videos()->count();
    }

    public function getTopicRelArtCountAttribute(): int
    {
        if (array_key_exists('art_count', $this->attributes)) {
            return (int) $this->attributes['art_count'];
        }
        if (array_key_exists('arts_count', $this->attributes)) {
            return (int) $this->attributes['arts_count'];
        }
        try {
            if (! Schema::hasTable('video_topic_art_rel')) {
                return 0;
            }

            return (int) $this->arts()->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    public function getVodListAttribute(): Collection
    {
        return $this->relationLoaded('videos') ? $this->videos : collect();
    }

    public function getArtListAttribute(): Collection
    {
        return $this->relationLoaded('arts') ? $this->arts : collect();
    }
}
