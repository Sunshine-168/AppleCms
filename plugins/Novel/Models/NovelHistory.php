<?php

namespace Plugins\Novel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NovelHistory extends Model
{
    protected $table = 'plugin_novel_histories';
    public $timestamps = false;
    protected $guarded = [];

    /** 获取历史对应作品。 */
    public function novel(): BelongsTo
    {
        return $this->belongsTo(Novel::class, 'novel_id');
    }

    /** 获取历史对应章节。 */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(NovelChapter::class, 'chapter_id');
    }
}
