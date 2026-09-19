<?php

namespace Plugins\Novel\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Novel extends Model
{
    protected $table = 'plugin_novels';
    public $timestamps = false;
    protected $guarded = [];

    /** 获取已发布作品。 */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 1)->where('yid', 0);
    }

    /** 获取作品章节。 */
    public function chapters(): HasMany
    {
        return $this->hasMany(NovelChapter::class, 'novel_id')->orderBy('sort')->orderBy('id');
    }

    /** 获取作品分类。 */
    public function type(): BelongsTo
    {
        return $this->belongsTo(NovelType::class, 'type_id');
    }

    /** 标签关联。 */
    public function tagRels(): BelongsToMany
    {
        return $this->belongsToMany(NovelTag::class, 'plugin_novel_tag_rel', 'novel_id', 'tag_id');
    }

    /** 作者关联。 */
    public function authorRels(): BelongsToMany
    {
        return $this->belongsToMany(NovelAuthor::class, 'plugin_novel_author_rel', 'novel_id', 'author_id');
    }
}
