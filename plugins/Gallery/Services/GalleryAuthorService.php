<?php

namespace Plugins\Gallery\Services;

use App\Support\AdminOpLog;
use App\Support\AdminPage;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Plugins\Gallery\Models\Gallery;
use Plugins\Gallery\Models\GalleryAuthor;

class GalleryAuthorService
{
    public function ready(): bool
    {
        try {
            return Schema::hasTable('plugin_gallery_authors') && Schema::hasTable('plugin_gallery_author_rel');
        } catch (\Throwable) {
            return false;
        }
    }

    /** @return list<array{id:int,name:string,slug:string,gallery_count:int}> */
    public function options(): array
    {
        if (! $this->ready()) {
            return [];
        }
        $rows = GalleryAuthor::query()->where('status', 1)->orderByDesc('sort')->orderByDesc('id')->get(['id', 'name', 'slug']);
        $counts = $this->counts($rows->pluck('id')->map(fn ($id) => (int) $id)->all());
        $out = [];
        foreach ($rows as $row) {
            $id = (int) $row->id;
            $out[] = [
                'id' => $id,
                'name' => (string) $row->name,
                'slug' => (string) $row->slug,
                'gallery_count' => $counts[$id] ?? 0,
            ];
        }

        return $out;
    }

    public function paginate(array $params): array
    {
        if (! $this->ready()) {
            return Result::fail('请先执行数据库迁移');
        }
        $this->harvest();
        $limit = max(1, (int) ($params['limit'] ?? 20));
        $q = GalleryAuthor::query();
        $name = trim((string) ($params['q'] ?? $params['name'] ?? ''));
        if ($name !== '') {
            $q->where(function ($inner) use ($name) {
                $inner->where('name', 'like', '%'.$name.'%')->orWhere('slug', 'like', '%'.$name.'%');
            });
        }
        if ((string) ($params['unused'] ?? '') === '1') {
            $q->whereDoesntHave('galleries');
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
            $slug = trim((string) ($row['slug'] ?? ''));
            $row['gallery_count'] = $counts[$id] ?? 0;
            $row['url'] = $id > 0 ? '/gallery?author='.rawurlencode($slug !== '' ? $slug : (string) ($row['name'] ?? $id)) : '';
        }
        unset($row);

        return Result::success(AdminPage::of($page, $rows));
    }

    public function save(array $data, ?int $id = null): array
    {
        if (! $this->ready()) {
            return Result::fail('请先执行数据库迁移');
        }
        $name = mb_substr(trim((string) ($data['name'] ?? $data['title'] ?? '')), 0, 80);
        if ($name === '') {
            return Result::fail('请填写作者名称');
        }
        $dup = GalleryAuthor::query()->where('name', $name);
        if ($id) {
            $dup->where('id', '!=', $id);
        }
        if ($dup->exists()) {
            return Result::fail('已经有这个作者了');
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
            $row = GalleryAuthor::query()->find($id);
            if (! $row) {
                return Result::fail('数据不存在');
            }
            $old = (string) $row->name;
            $row->fill($payload)->save();
            if ($old !== $name) {
                $this->rewriteComma($old, $name);
            }
            AdminOpLog::write('save', '改了图集作者「'.$name.'」', ['id' => $id]);

            return Result::success(['id' => $id, 'name' => $name]);
        }
        $payload['created_at'] = $now;
        $row = GalleryAuthor::query()->create($payload);
        $newId = (int) $row->id;
        AdminOpLog::write('save', '加了图集作者「'.$name.'」', ['id' => $newId]);

        return Result::success(['id' => $newId, 'name' => $name]);
    }

    public function delete(int $id): array
    {
        if (! $this->ready()) {
            return Result::fail('请先执行数据库迁移');
        }
        $row = GalleryAuthor::query()->find($id);
        if (! $row) {
            return Result::fail('数据不存在');
        }
        $name = (string) $row->name;
        $count = (int) $row->galleries()->count();
        $row->galleries()->detach();
        $row->delete();
        $this->rewriteComma($name, null);
        AdminOpLog::write('delete', $count > 0 ? '删了图集作者「'.$name.'」，并从 '.$count.' 部上拿掉' : '删了图集作者「'.$name.'」', ['id' => $id]);

        return Result::success([], $count > 0 ? '已删除作者，并从 '.$count.' 部上移除' : '已删除');
    }

    /**
     * @param  list<int|string>  $ids
     */
    public function batch(array $ids, string $action, mixed $value = ''): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return Result::fail('请先勾选作者');
        }
        $ok = 0;
        $fail = 0;
        foreach ($ids as $id) {
            $res = match ($action) {
                'status' => $this->save(['status' => (int) $value], $id),
                'delete' => $this->delete($id),
                default => Result::fail('不支持的操作'),
            };
            if ((int) ($res['code'] ?? 1) === 0) {
                $ok++;
            } else {
                $fail++;
            }
        }
        if ($ok === 0) {
            return Result::fail('操作失败');
        }

        return Result::success(['ok' => $ok, 'fail' => $fail], $fail > 0 ? ('完成 '.$ok.' 个，'.$fail.' 个未处理') : '操作成功');
    }

    public function find(int $id): ?GalleryAuthor
    {
        if ($id < 1 || ! $this->ready()) {
            return null;
        }

        return GalleryAuthor::query()->find($id);
    }

    public function findPublic(string $key): ?GalleryAuthor
    {
        $key = trim($key);
        if ($key === '' || ! $this->ready()) {
            return null;
        }
        $q = GalleryAuthor::query()->where('status', 1);
        if (ctype_digit($key)) {
            return $q->find((int) $key);
        }

        return $q->where(function ($inner) use ($key) {
            $inner->where('slug', $key)->orWhere('name', $key);
        })->first();
    }

    /** @return list<int> */
    public function idsForGallery(int $galleryId): array
    {
        if ($galleryId < 1 || ! $this->ready()) {
            return [];
        }
        $gallery = Gallery::query()->find($galleryId);
        if (! $gallery) {
            return [];
        }

        return $gallery->authorRels()->pluck('plugin_gallery_authors.id')->map(fn ($id) => (int) $id)->all();
    }

    public function syncGallery(int $galleryId, array $data): void
    {
        if ($galleryId < 1 || ! Schema::hasTable('plugin_galleries')) {
            return;
        }
        $gallery = Gallery::query()->find($galleryId);
        if (! $gallery) {
            return;
        }
        $names = $this->collectNames($data);
        if ($this->ready()) {
            $ids = [];
            foreach ($names as $name) {
                $author = $this->firstOrCreate($name);
                if ($author) {
                    $ids[] = (int) $author->id;
                }
            }
            $gallery->authorRels()->sync(array_values(array_unique($ids)));
            $names = GalleryAuthor::query()->whereIn('id', $ids)->orderByDesc('sort')->orderByDesc('id')->pluck('name')->all();
        }
        if (Schema::hasColumn('plugin_galleries', 'author')) {
            $gallery->author = mb_substr(implode(',', $names), 0, 80);
            $gallery->save();
        }
    }

    public function harvest(): void
    {
        if (! $this->ready() || ! Schema::hasTable('plugin_galleries') || ! Schema::hasColumn('plugin_galleries', 'author')) {
            return;
        }
        Gallery::query()->select('id', 'author')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                $names = $this->splitNames((string) ($row->author ?? ''));
                if ($names === []) {
                    continue;
                }
                $ids = [];
                foreach ($names as $name) {
                    $author = $this->firstOrCreate($name);
                    if ($author) {
                        $ids[] = (int) $author->id;
                    }
                }
                $row->authorRels()->sync(array_values(array_unique($ids)));
            }
        }, 'id');
    }

    public function detachGallery(int $galleryId): void
    {
        if ($galleryId < 1 || ! $this->ready()) {
            return;
        }
        $gallery = Gallery::query()->find($galleryId);
        $gallery?->authorRels()->detach();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<string>
     */
    public function collectNames(array $data): array
    {
        $names = [];
        if ((array_key_exists('author_ids', $data) || array_key_exists('author_ids[]', $data)) && $this->ready()) {
            $raw = $data['author_ids'] ?? $data['author_ids[]'] ?? [];
            if (! is_array($raw)) {
                $raw = preg_split('/[,，]/u', (string) $raw) ?: [];
            }
            $ids = array_values(array_unique(array_filter(array_map('intval', $raw))));
            if ($ids !== []) {
                foreach (GalleryAuthor::query()->whereIn('id', $ids)->pluck('name') as $name) {
                    $names[] = (string) $name;
                }
            }
        }
        foreach (['author', 'authors', 'author_extra'] as $key) {
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
        $rows = DB::table('plugin_gallery_author_rel')
            ->selectRaw('author_id, count(*) as c')
            ->whereIn('author_id', $ids)
            ->groupBy('author_id')
            ->get();
        foreach ($rows as $row) {
            $counts[(int) $row->author_id] = (int) $row->c;
        }

        return $counts;
    }

    private function firstOrCreate(string $name): ?GalleryAuthor
    {
        $name = mb_substr(trim($name), 0, 80);
        if ($name === '' || ! $this->ready()) {
            return null;
        }
        $row = GalleryAuthor::query()->where('name', $name)->first();
        if ($row) {
            return $row;
        }
        $now = time();

        return GalleryAuthor::query()->create([
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
            $base = 'a-'.substr(md5($raw), 0, 8);
        }
        $base = mb_substr($base, 0, 70);
        $slug = $base;
        $n = 2;
        while (GalleryAuthor::query()->where('slug', $slug)->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))->exists()) {
            $slug = mb_substr($base, 0, 70).'-'.$n;
            $n++;
            if ($n > 50) {
                $slug = 'a-'.substr(md5($raw.$n), 0, 10);
                break;
            }
        }

        return $slug;
    }

    private function rewriteComma(string $old, ?string $new): void
    {
        if (! Schema::hasTable('plugin_galleries') || ! Schema::hasColumn('plugin_galleries', 'author')) {
            return;
        }
        $old = trim($old);
        if ($old === '') {
            return;
        }
        Gallery::query()->where('author', 'like', '%'.$old.'%')->orderBy('id')->chunkById(100, function ($rows) use ($old, $new) {
            foreach ($rows as $row) {
                $names = $this->splitNames((string) ($row->author ?? ''));
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
                $row->author = mb_substr(implode(',', $out), 0, 80);
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
            $parts = preg_split('/[,，|｜\/／]+/u', (string) $raw) ?: [];
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
            $name = mb_substr(trim((string) $part), 0, 80);
            if ($name === '' || in_array($name, $out, true)) {
                continue;
            }
            $out[] = $name;
            if (count($out) >= 5) {
                break;
            }
        }

        return $out;
    }
}
