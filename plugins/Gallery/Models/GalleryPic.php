<?php

namespace Plugins\Gallery\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryPic extends Model
{
    protected $table = 'plugin_gallery_pics';
    public $timestamps = false;
    protected $guarded = [];
    /** 获取图片所属图集。 */
    public function gallery(): BelongsTo { return $this->belongsTo(Gallery::class,'gallery_id'); }
}
