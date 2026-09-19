<?php

namespace Plugins\Live\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveChannel extends Model
{
    protected $table = 'plugin_live_channels';
    public $timestamps = false;
    protected $guarded = [];

    /** 获取上线频道。 */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    /** 获取频道分类。 */
    public function category(): BelongsTo
    {
        return $this->belongsTo(LiveCategory::class, 'cate_id');
    }
}
