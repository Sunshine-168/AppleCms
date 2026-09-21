<?php

namespace App\Services\Admin\Video;

use App\Models\Video\VideoArt;
use App\Models\Video\VideoArtTag;
use App\Support\AdminOpLog;
use App\Support\AdminPage;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\Schema;

class ArtTagService
{
    public function ready(): bool
    {
        try {
            return Schema::hasTable('video_art_tags') && Schema::hasTable('video_art_tag_rel');
        } catch (\Throwable) {
            return false;
        }
    }

    /** @return list<array{id:int,name:string,slug:string,art_count:int}> */
    public function options(): array
    {
        if (! $this->ready()) {
            return [];
        }
        $rows = VideoArtTag::query()->where('status', 1)->orderByDesc('sort')->orderByDesc('id')->get(['id', 'name', 'slug']);
        $counts = $this->counts($rows->pluck('id')->map(fn ($id) => (int) $id)->all());
        $out = [];
        foreach ($rows as $row) {
            $id = (int) $row->id;
            $out[] = [
                'id' => $id,
                'name' => (string) $row->name,
                'slug' => (string) $row->slug,
                'art_count' => $counts[$id] ?? 0,
            ];
        }

        return $out;
    }

    public function paginate(array $params): array
    {
        if (! $this->ready()) {
            return Result::fail(admin_t('ui.migrate_first'));
        }
        $this->harvest();
        $limit = max(1, (int) ($params['limit'] ?? 20));
        $q = VideoArtTag::query();
        $name = trim((string) ($params['q'] ?? $params['name'] ?? ''));
        if ($name !== '') {
            $q->where(function ($inner) use ($name) {
                $inner->where('name', 'like', '%'.$name.'%')->orWhere('slug', 'like', '%'.$name.'%');
            });
        }
        if ((string) ($params['unused'] ?? '') === '1') {
            $q->whereDoesntHave('arts');
        }
        if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
            $q->where('status', (int) $params['status']);
        }
        $page = $q->orderByDesc('sort')->orderByDesc('id')->paginate($limit);
        $rows = collect($page->items())->map(fn ($row) => $row->toArray())->all();
        $ids = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        $counts = $this->counts($ids);
        foreach ($rows as &$row) {
            $id = (int) ($row['id'] ?? 0);
            $row['art_count'] = $counts[$id] ?? 0;
            $row['url'] = $id > 0 ? vod_url('art_tag', ['slug' => trim((string) ($row['slug'] ?? '')) !== '' ? $row['slug'] : $id]) : '';
        }
        unset($row);

        return Result::success(AdminPage::of($page, $rows));
    }

    public function save(array $data, ?int $id = null): array
    {
        if (! $this->ready()) {
            return Result::fail(admin_t('ui.migrate_first'));
        }
        $name = mb_substr(trim((string) ($data['name'] ?? $data['title'] ?? '')), 0, 60);
        if ($name === '') {
            return Result::fail(admin_t('ui.please_fill_tag_name'));
        }
        $dup = VideoArtTag::query()->where('name', $name);
        if ($id) {
            $dup->where('id', '!=', $id);
        }
        if ($dup->exists()) {
            return Result::fail(admin_t('ui.tag_dup'));
        }
        $now = time();
        $slugIn = trim((string) ($data['slug'] ?? ''));
        $payload = [
            'name' => $name,
            'slug' => $this->uniqueSlug($slugIn !== '' ? $slugIn : $name, $id),
            'sort' => max(0, (int) ($data['sort'] ?? 0)),
            'status' => (int) ($data['status'] ?? 1) === 0 ? 0 : 1,
            'updated_at' => $now,
        ];
        if ($id) {
            $row = VideoArtTag::query()->find($id);
            if (! $row) {
                return Result::fail(admin_t('ui.data_missing'));
            }
            $old = (string) $row->name;
            $row->fill($payload)->save();
            if ($old !== $name) {
                $this->rewriteComma($old, $name);
            }
            AdminOpLog::write('save', '改了文章标签「'.$name.'」', ['id' => $id]);

            return Result::success(['id' => $id]);
        }
        $payload['created_at'] = $now;
        $row = VideoArtTag::query()->create($payload);
        $newId = (int) $row->id;
        AdminOpLog::write('save', '加了文章标签「'.$name.'」', ['id' => $newId]);

        return Result::success(['id' => $newId]);
    }

    public function delete(int $id): array
    {
        if (! $this->ready()) {
            return Result::fail(admin_t('ui.migrate_first'));
        }
        $row = VideoArtTag::query()->find($id);
        if (! $row) {
            return Result::fail(admin_t('ui.data_missing'));
        }
        $name = (string) $row->name;
        $count = (int) $row->arts()->count();
        $row->arts()->detach();
        $row->delete();
        $this->rewriteComma($name, null);
        AdminOpLog::write('delete', $count > 0 ? '删了文章标签「'.$name.'」，并从 '.$count.' 篇上拿掉' : '删了文章标签「'.$name.'」', ['id' => $id]);

        return Result::success([], $count > 0 ? admin_t('ui.tag_deleted_from_n', ['n' => $count]) : admin_t('ui.deleted'));
    }

    /**
     * @param  list<int|string>  $ids
     */
    public function batch(array $ids, string $action, mixed $value = ''): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return Result::fail(admin_t('ui.please_select_tags'));
        }
        $ok = 0;
        $fail = 0;
        foreach ($ids as $id) {
            $res = match ($action) {
                'status' => $this->save(['status' => (int) $value], $id),
                'delete' => $this->delete($id),
                default => Result::fail(admin_t('ui.unsupported_op')),
            };
            if ((int) ($res['code'] ?? 1) === 0) {
                $ok++;
            } else {
                $fail++;
            }
        }
        if ($ok === 0) {
            return Result::fail(admin_t('ui.op_fail'));
        }

        return Result::success(['ok' => $ok, 'fail' => $fail], $fail > 0 ? admin_t('ui.batch_n_unhandled', ['ok' => $ok, 'fail' => $fail]) : admin_t('ui.op_ok'));
    }

    public function find(int $id): ?VideoArtTag
    {
        if ($id < 1 || ! $this->ready()) {
            return null;
        }

        return VideoArtTag::query()->find($id);
    }

    public function findPublic(string $key): ?VideoArtTag
    {
        $key = trim($key);
        if ($key === '' || ! $this->ready()) {
            return null;
        }
        $q = VideoArtTag::query()->where('status', 1);
        if (ctype_digit($key)) {
            return $q->find((int) $key);
        }

        return $q->where(function ($inner) use ($key) {
            $inner->where('slug', $key)->orWhere('name', $key);
        })->first();
    }

    /** @return list<int> */
    public function idsForArt(int $artId): array
    {
        if ($artId < 1 || ! $this->ready()) {
            return [];
        }

        return VideoArtTag::query()
            ->whereHas('arts', fn ($q) => $q->where('video_arts.id', $artId))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @param  list<int>  $ids
     * @return list<array{id:int,name:string}>
     */
    public function labels(array $ids): array
    {
        if (! $this->ready() || $ids === []) {
            return [];
        }
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if ($ids === []) {
            return [];
        }
        $map = VideoArtTag::query()->whereIn('id', $ids)->pluck('name', 'id')->all();
        $out = [];
        foreach ($ids as $id) {
            if (isset($map[$id])) {
                $out[] = ['id' => $id, 'name' => (string) $map[$id]];
            }
        }

        return $out;
    }

    public function syncArt(int $artId, array $data): void
    {
        if ($artId < 1 || ! Schema::hasTable('video_arts')) {
            return;
        }
        $art = VideoArt::query()->withoutGlobalScope('alive')->find($artId);
        if (! $art) {
            return;
        }
        $names = $this->collectNames($data);
        if ($this->ready()) {
            $ids = [];
            foreach ($names as $name) {
                $tag = $this->firstOrCreate($name);
                if ($tag) {
                    $ids[] = (int) $tag->id;
                }
            }
            $art->tags()->sync(array_values(array_unique($ids)));
            $names = VideoArtTag::query()->whereIn('id', $ids)->orderByDesc('sort')->orderByDesc('id')->pluck('name')->all();
        }
        if (Schema::hasColumn('video_arts', 'tag')) {
            $art->tag = mb_substr(implode(',', $names), 0, 255);
            $art->save();
        }
    }

    public function harvest(): void
    {
        if (! $this->ready() || ! Schema::hasTable('video_arts') || ! Schema::hasColumn('video_arts', 'tag')) {
            return;
        }
        VideoArt::query()->withoutGlobalScope('alive')->select('id', 'tag')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                $names = $this->splitNames((string) ($row->tag ?? ''));
                if ($names === []) {
                    continue;
                }
                $ids = [];
                foreach ($names as $name) {
                    $tag = $this->firstOrCreate($name);
                    if ($tag) {
                        $ids[] = (int) $tag->id;
                    }
                }
                $row->tags()->sync(array_values(array_unique($ids)));
            }
        }, 'id');
    }

    public function detachArt(int $artId): void
    {
        if ($artId < 1 || ! $this->ready()) {
            return;
        }
        $art = VideoArt::query()->withoutGlobalScope('alive')->find($artId);
        $art?->tags()->detach();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<string>
     */
    public function collectNames(array $data): array
    {
        $names = [];
        if ((array_key_exists('tag_ids', $data) || array_key_exists('tag_ids[]', $data)) && $this->ready()) {
            $raw = $data['tag_ids'] ?? $data['tag_ids[]'] ?? [];
            if (! is_array($raw)) {
                $raw = preg_split('/[,，]/u', (string) $raw) ?: [];
            }
            $ids = array_values(array_unique(array_filter(array_map('intval', $raw))));
            if ($ids !== []) {
                foreach (VideoArtTag::query()->whereIn('id', $ids)->pluck('name') as $name) {
                    $names[] = (string) $name;
                }
            }
        }
        foreach (['tag', 'tag_extra', 'tags'] as $key) {
            if (array_key_exists($key, $data)) {
                $names = array_merge($names, $this->splitNames($data[$key]));
            }
        }

        return $this->uniqueNames($names);
    }

    /** @param  list<int>  $ids @return array<int, int> */
    private function counts(array $ids): array
    {
        $counts = [];
        if ($ids === [] || ! $this->ready()) {
            return $counts;
        }
        $rows = \Illuminate\Support\Facades\DB::table('video_art_tag_rel')
            ->selectRaw('tag_id, count(*) as c')
            ->whereIn('tag_id', $ids)
            ->groupBy('tag_id')
            ->get();
        foreach ($rows as $row) {
            $counts[(int) $row->tag_id] = (int) $row->c;
        }

        return $counts;
    }

    private function firstOrCreate(string $name): ?VideoArtTag
    {
        $name = mb_substr(trim($name), 0, 60);
        if ($name === '' || ! $this->ready()) {
            return null;
        }
        $row = VideoArtTag::query()->where('name', $name)->first();
        if ($row) {
            return $row;
        }
        $now = time();

        return VideoArtTag::query()->create([
            'name' => $name,
            'slug' => $this->uniqueSlug($name, null),
            'sort' => 0,
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function uniqueSlug(string $raw, ?int $exceptId): string
    {
        $base = strtolower(trim($raw));
        $base = preg_replace('/\s+/', '-', $base) ?? $base;
        $base = preg_replace('/[^a-z0-9\-]+/', '', $base) ?? '';
        $base = trim($base, '-');
        if ($base === '') {
            $base = 't-'.substr(md5($raw), 0, 8);
        }
        $base = mb_substr($base, 0, 70);
        $slug = $base;
        $n = 2;
        while (VideoArtTag::query()->where('slug', $slug)->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))->exists()) {
            $slug = mb_substr($base, 0, 70).'-'.$n;
            $n++;
            if ($n > 50) {
                $slug = 't-'.substr(md5($raw.$n), 0, 10);
                break;
            }
        }

        return $slug;
    }

    private function rewriteComma(string $old, ?string $new): void
    {
        if (! Schema::hasTable('video_arts') || ! Schema::hasColumn('video_arts', 'tag')) {
            return;
        }
        $old = trim($old);
        if ($old === '') {
            return;
        }
        VideoArt::query()->withoutGlobalScope('alive')->where('tag', 'like', '%'.$old.'%')->orderBy('id')->chunkById(100, function ($rows) use ($old, $new) {
            foreach ($rows as $row) {
                $names = $this->splitNames((string) ($row->tag ?? ''));
                $out = [];
                foreach ($names as $name) {
                    if ($name === $old) {
                        if ($new !== null && $new !== '' && ! in_array($new, $out, true)) {
                            $out[] = $new;
                        }
                        continue;
                    }
                    if (! in_array($name, $out, true)) {
                        $out[] = $name;
                    }
                }
                $row->tag = mb_substr(implode(',', $out), 0, 255);
                $row->save();
            }
        }, 'id');
    }

    /** @return list<string> */
    private function splitNames(mixed $raw): array
    {
        if (is_array($raw)) {
            $parts = $raw;
        } else {
            $parts = preg_split('/[,，]/u', (string) $raw) ?: [];
        }

        return $this->uniqueNames($parts);
    }

    /**
     * @param  list<mixed>  $parts
     * @return list<string>
     */
    private function uniqueNames(array $parts): array
    {
        $out = [];
        foreach ($parts as $part) {
            $name = mb_substr(trim((string) $part), 0, 60);
            if ($name === '' || in_array($name, $out, true)) {
                continue;
            }
            $out[] = $name;
            if (count($out) >= 20) {
                break;
            }
        }

        return $out;
    }
}
