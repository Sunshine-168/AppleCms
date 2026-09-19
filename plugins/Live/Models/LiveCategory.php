<?php

namespace Plugins\Live\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiveCategory extends Model
{
    protected $table = 'plugin_live_categories';
    public $timestamps = false;
    protected $guarded = [];

    /** 获取启用分类。 */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    /** 获取分类频道。 */
    public function channels(): HasMany
    {
        return $this->hasMany(LiveChannel::class, 'cate_id');
    }
}
