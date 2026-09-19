<?php

namespace Plugins\Gallery\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GalleryTag extends Model
{
    protected $table = 'plugin_gallery_tags';

    public $timestamps = false;

    protected $guarded = [];

    /** 关联图集。 */
    public function galleries(): BelongsToMany
    {
        return $this->belongsToMany(Gallery::class, 'plugin_gallery_tag_rel', 'tag_id', 'gallery_id');
    }
}
