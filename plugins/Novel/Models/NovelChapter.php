<?php

namespace Plugins\Novel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NovelChapter extends Model
{
    protected $table = 'plugin_novel_chapters';
    public $timestamps = false;
    protected $guarded = [];

    /** 获取章节所属作品。 */
    public function novel(): BelongsTo
    {
        return $this->belongsTo(Novel::class, 'novel_id');
    }
}
