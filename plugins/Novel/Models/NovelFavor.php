<?php

namespace Plugins\Novel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NovelFavor extends Model
{
    protected $table = 'plugin_novel_favors';

    public $timestamps = false;

    protected $guarded = [];

    /** 关联作品。 */
    public function novel(): BelongsTo
    {
        return $this->belongsTo(Novel::class, 'novel_id');
    }
}
