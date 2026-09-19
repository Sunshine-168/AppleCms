<?php

namespace Plugins\Gallery\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class GalleryType extends Model
{
    protected $table = 'plugin_gallery_types';
    public $timestamps = false;
    protected $guarded = [];
    /** 获取启用分类。 */
    public function scopePublished(Builder $query): Builder { return $query->where('status',1); }
}
