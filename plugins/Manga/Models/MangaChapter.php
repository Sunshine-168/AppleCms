<?php

namespace Plugins\Manga\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MangaChapter extends Model
{
    protected $table = 'plugin_manga_chapters';

    public $timestamps = false;

    protected $guarded = [];

    public function manga(): BelongsTo
    {
        return $this->belongsTo(Manga::class, 'manga_id');
    }

    /** @return list<string> */
    public function picList(): array
    {
        $out = [];
        foreach (preg_split('/\r\n|\r|\n/', (string) $this->pics) ?: [] as $line) {
            $url = trim($line);
            if ($url === '') {
                continue;
            }
            if (preg_match('#^(https?:)?//#i', $url) || str_starts_with($url, '/')) {
                $out[] = $url;
            }
        }

        return $out;
    }
}
