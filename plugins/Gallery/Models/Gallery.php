<?php

namespace Plugins\Gallery\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gallery extends Model
{
    protected $table = 'plugin_galleries';
    public $timestamps = false;
    protected $guarded = [];

    /** 获取已发布图集。 */
    public function scopePublished(Builder $query): Builder { return $query->where('status',1)->where('yid',0); }
    /** 获取图集图片。 */
    public function pics(): HasMany { return $this->hasMany(GalleryPic::class,'gallery_id')->orderBy('sort')->orderBy('id'); }
    /** 获取图集分类。 */
    public function type(): BelongsTo { return $this->belongsTo(GalleryType::class,'type_id'); }

    /** 标签关联。 */
    public function tagRels(): BelongsToMany
    {
        return $this->belongsToMany(GalleryTag::class, 'plugin_gallery_tag_rel', 'gallery_id', 'tag_id');
    }

    /** 作者关联。 */
    public function authorRels(): BelongsToMany
    {
        return $this->belongsToMany(GalleryAuthor::class, 'plugin_gallery_author_rel', 'gallery_id', 'author_id');
    }
}
