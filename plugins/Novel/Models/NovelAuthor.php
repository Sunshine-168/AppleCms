<?php

namespace Plugins\Novel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class NovelAuthor extends Model
{
    protected $table = 'plugin_novel_authors';

    public $timestamps = false;

    protected $guarded = [];

    /** 关联小说作品。 */
    public function novels(): BelongsToMany
    {
        return $this->belongsToMany(Novel::class, 'plugin_novel_author_rel', 'author_id', 'novel_id');
    }
}
