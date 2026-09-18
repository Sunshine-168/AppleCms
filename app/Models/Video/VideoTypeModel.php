<?php

namespace App\Models\Video;

use App\Support\QueryCacheTrait;
use App\Support\QueryTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class VideoTypeModel extends Model
{
    protected $table = 'video_types';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    use QueryTrait, QueryCacheTrait;

    public const KINDS = ['hub', 'list', 'single', 'link'];

    protected $appends = ['url'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->where('status', 1)->orderByDesc('sort')->orderBy('id');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(VideoModel::class, 'type_id');
    }

    public function arts(): HasMany
    {
        return $this->hasMany(VideoArt::class, 'type_id');
    }

    /** @return array<string, string> */
    public static function kindLabels(): array
    {
        return [
            'hub' => '频道',
            'list' => '列表',
            'single' => '单页',
            'link' => '外链',
        ];
    }

    public static function kindLabel(?string $kind): string
    {
        $kind = self::normalizeKind($kind);

        return self::kindLabels()[$kind];
    }

    public static function normalizeKind(mixed $raw): string
    {
        $kind = strtolower(trim((string) $raw));

        return in_array($kind, self::KINDS, true) ? $kind : 'list';
    }

    public static function safeJumpUrl(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }
        if (str_starts_with($raw, '/') && ! str_starts_with($raw, '//')) {
            return $raw;
        }
        if (preg_match('#^https?://#i', $raw) === 1) {
            return $raw;
        }

        return null;
    }

    public static function normalizeTpl(string $raw): string
    {
        $raw = trim(str_replace(['\\', '/'], '.', $raw), '.');
        if ($raw === '') {
            return '';
        }
        if (! preg_match('/^[A-Za-z0-9._-]+$/', $raw)) {
            return '';
        }

        return $raw;
    }

    public function kind(): string
    {
        if ((int) ($this->attributes['mid'] ?? 1) !== 2) {
            return 'list';
        }
        if (! $this->hasTypeColumn('kind')) {
            return 'list';
        }

        return self::normalizeKind($this->attributes['kind'] ?? 'list');
    }

    public function acceptsArticles(): bool
    {
        return in_array($this->kind(), ['list', 'single'], true);
    }

    public function jumpUrl(): ?string
    {
        if ($this->kind() !== 'link' || ! $this->hasTypeColumn('jump_url')) {
            return null;
        }

        return self::safeJumpUrl((string) ($this->attributes['jump_url'] ?? ''));
    }

    public function pageSize(int $fallback = 20): int
    {
        $n = 0;
        if ($this->hasTypeColumn('page_size')) {
            $n = (int) ($this->attributes['page_size'] ?? 0);
        }
        if ($n < 1) {
            $n = $fallback;
        }

        return min(100, max(1, $n));
    }

    public function singleArt(): ?VideoArt
    {
        return VideoArt::query()->listed()
            ->where('type_id', (int) $this->id)
            ->orderByDesc('sort')
            ->orderByDesc('id')
            ->first();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    public function getUrlAttribute(): string
    {
        $mid = (int) ($this->attributes['mid'] ?? 1);
        if ($mid === 2) {
            $jump = $this->jumpUrl();
            if ($jump !== null) {
                return $jump;
            }

            return url('/art/type/'.$this->id);
        }
        if ($mid === 3) {
            return url('/website').'?type_id='.$this->id;
        }
        $slug = trim((string) $this->slug);

        return vod_url('type', ['id' => $slug !== '' ? $slug : $this->id]);
    }

    public function descendantIds(): array
    {
        $ids = [(int) $this->id];
        $pending = [(int) $this->id];
        $guard = 0;
        while ($pending && $guard < 50) {
            $children = self::query()->whereIn('parent_id', $pending)->pluck('id')->map(fn ($id) => (int) $id)->all();
            $children = array_values(array_diff($children, $ids));
            $ids = array_merge($ids, $children);
            $pending = $children;
            $guard++;
        }

        return array_values(array_unique($ids));
    }

    private function hasTypeColumn(string $column): bool
    {
        try {
            return Schema::hasColumn($this->getTable(), $column);
        } catch (\Throwable) {
            return false;
        }
    }
}
