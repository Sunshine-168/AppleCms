<?php

namespace Plugins\Gallery\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GalleryAuthor extends Model
{
    protected $table = 'plugin_gallery_authors';

    public $timestamps = false;

    protected $guarded = [];

    /** 关联图集。 */
    public function galleries(): BelongsToMany
    {
        return $this->belongsToMany(Gallery::class, 'plugin_gallery_author_rel', 'author_id', 'gallery_id');
    }
}
