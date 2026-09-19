<?php

namespace Plugins\Novel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class NovelTag extends Model
{
    protected $table = 'plugin_novel_tags';

    public $timestamps = false;

    protected $guarded = [];

    /** 关联小说作品。 */
    public function novels(): BelongsToMany
    {
        return $this->belongsToMany(Novel::class, 'plugin_novel_tag_rel', 'tag_id', 'novel_id');
    }
}
