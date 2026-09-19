<?php

namespace Plugins\Gallery\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryComment extends Model
{
    protected $table = 'plugin_gallery_comments';

    public $timestamps = false;

    protected $guarded = [];

    /** 关联图集。 */
    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class, 'gallery_id');
    }
}
