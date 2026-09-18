<?php

namespace App\Models\Video;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Schema;

class VideoArt extends VideoOpsModel
{
    public const FLAGS = ['top', 'recommend', 'hot'];

    protected $table = 'video_arts';

    protected $appends = ['url'];

    protected static function booted(): void
    {
        static::addGlobalScope('alive', function (Builder $query) {
            try {
                if (! Schema::hasColumn('video_arts', 'deleted_at')) {
                    return;
                }
            } catch (\Throwable) {
                return;
            }
            $query->where(function (Builder $inner) {
                $inner->where('deleted_at', 0)->orWhereNull('deleted_at');
            });
        });
    }

    public function getUrlAttribute(): string
    {
        return vod_url('art', ['id' => $this->id]);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(VideoTypeModel::class, 'type_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(VideoArtTag::class, 'video_art_tag_rel', 'art_id', 'tag_id');
    }

    /** @return list<string> */
    public function flagList(): array
    {
        $raw = trim((string) ($this->flags ?? ''));
        if ($raw === '') {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('trim', explode(',', $raw)))));
    }

    public function hasFlag(string $name): bool
    {
        $name = trim($name);

        return $name !== '' && in_array($name, $this->flagList(), true);
    }

    public function isListed(): bool
    {
        if ((int) $this->status !== 1) {
            return false;
        }
        if (! Schema::hasColumn($this->getTable(), 'published_at')) {
            return true;
        }
        $at = (int) ($this->published_at ?? 0);

        return $at === 0 || $at <= time();
    }

    public function scopeListed(Builder $query): Builder
    {
        $table = $query->getModel()->getTable();
        $query->where($table.'.status', 1);
        if (Schema::hasColumn($table, 'published_at')) {
            $now = time();
            $query->where(function (Builder $inner) use ($table, $now) {
                $inner->where($table.'.published_at', 0)
                    ->orWhereNull($table.'.published_at')
                    ->orWhere($table.'.published_at', '<=', $now);
            });
        }

        return $query;
    }

    public function scopeWithFlag(Builder $query, string $flag): Builder
    {
        $flag = trim($flag);
        if ($flag === '' || ! in_array($flag, self::FLAGS, true)) {
            return $query;
        }
        $table = $query->getModel()->getTable();
        $col = $table.'.flags';

        return $query->where(function (Builder $inner) use ($col, $flag) {
            $inner->where($col, $flag)
                ->orWhere($col, 'like', $flag.',%')
                ->orWhere($col, 'like', '%,'.$flag.',%')
                ->orWhere($col, 'like', '%,'.$flag);
        });
    }

    public static function normalizeFlags(mixed $raw): string
    {
        $parts = is_array($raw)
            ? $raw
            : (preg_split('/[,，]/u', (string) $raw) ?: []);
        $out = [];
        foreach ($parts as $part) {
            $flag = strtolower(trim((string) $part));
            if (in_array($flag, self::FLAGS, true) && ! in_array($flag, $out, true)) {
                $out[] = $flag;
            }
        }

        return implode(',', $out);
    }
}
