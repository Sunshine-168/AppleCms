<?php

namespace Plugins\Manga\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class MangaChapter extends Model
{
    protected $table = 'plugin_manga_chapters';

    public $timestamps = false;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saved(function (MangaChapter $chapter): void {
            $mangaId = (int) $chapter->manga_id;
            if ($mangaId > 0 && Schema::hasTable('plugin_mangas')) {
                Manga::query()->where('id', $mangaId)->update(['updated_at' => time()]);
            }
            if (! Schema::hasTable('plugin_manga_pics')) {
                return;
            }
            $present = trim((string) $chapter->pics) !== '';
            if (! $chapter->wasChanged('pics') && ! ($chapter->wasRecentlyCreated && $present)) {
                return;
            }
            $chapter->syncPicsFromText();
        });
    }

    public function manga(): BelongsTo
    {
        return $this->belongsTo(Manga::class, 'manga_id');
    }

    public function pics(): HasMany
    {
        return $this->hasMany(MangaPic::class, 'chapter_id')->orderBy('sort')->orderBy('id');
    }

    /** @return list<string> */
    public function picList(): array
    {
        if (Schema::hasTable('plugin_manga_pics')) {
            $rows = $this->relationLoaded('pics') ? $this->getRelation('pics') : $this->pics()->get();
            if ($rows instanceof \Illuminate\Support\Collection && $rows->isNotEmpty()) {
                $out = [];
                foreach ($rows as $pic) {
                    $url = self::safeUrl((string) $pic->url);
                    if ($url !== '') {
                        $out[] = $url;
                    }
                }

                return $out;
            }
        }

        $out = [];
        foreach (preg_split('/\r\n|\r|\n/', (string) $this->pics) ?: [] as $line) {
            $url = self::fallbackUrl((string) $line);
            if ($url !== '') {
                $out[] = $url;
            }
        }

        return $out;
    }

    public function syncPicsFromText(): void
    {
        if (! Schema::hasTable('plugin_manga_pics')) {
            return;
        }
        MangaPic::query()->where('chapter_id', (int) $this->id)->delete();
        $sort = 0;
        foreach (preg_split('/\r\n|\r|\n/', (string) $this->pics) ?: [] as $line) {
            $url = self::safeUrl((string) $line);
            if ($url === '') {
                continue;
            }
            $sort++;
            MangaPic::query()->create([
                'manga_id' => (int) $this->manga_id,
                'chapter_id' => (int) $this->id,
                'url' => $url,
                'sort' => $sort,
                'created_at' => time(),
            ]);
        }
    }

    public static function safeUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '' || str_contains(strtolower($url), 'javascript:')) {
            return '';
        }
        if (preg_match('#^https?://#i', $url) || str_starts_with($url, '/')) {
            return $url;
        }

        return '';
    }

    public static function fallbackUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '' || str_contains(strtolower($url), 'javascript:')) {
            return '';
        }
        if (preg_match('#^(https?:)?//#i', $url) || str_starts_with($url, '/')) {
            return $url;
        }

        return '';
    }
}
