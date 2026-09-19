<?php

namespace Plugins\Novel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NovelComment extends Model
{
    protected $table = 'plugin_novel_comments';

    public $timestamps = false;

    protected $guarded = [];

    /** 关联小说作品。 */
    public function novel(): BelongsTo
    {
        return $this->belongsTo(Novel::class, 'novel_id');
    }
}
