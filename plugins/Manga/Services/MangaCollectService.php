<?php

namespace Plugins\Manga\Services;

use App\Models\Video\CollectSourceModel;
use App\Services\Collect\PlayUrlParser;
use App\Support\Plugins\PluginManager;
use App\Support\Utils\Result;
use Illuminate\Support\Facades\Schema;
use Plugins\Manga\Models\Manga;
use Plugins\Manga\Models\MangaChapter;
use Plugins\Manga\Models\MangaType;

class MangaCollectService
{
    public const MID = 2;

    public function __construct(private readonly PlayUrlParser $parser) {}

    public function ready(): bool
    {
        try {
            return app(PluginManager::class)->isEnabled('manga') && Schema::hasTable('plugin_mangas');
        } catch (\Throwable) {
            return false;
        }
    }

    public function isMangaSource(CollectSourceModel $source): bool
    {
        return (int) ($source->mid ?? 1) === self::MID;
    }

    /** @return list<array{id:int,name:string,parent_id:int}> */
    public function localTypes(): array
    {
        if (! Schema::hasTable('plugin_manga_types')) {
            return [];
        }
        $out = [];
        foreach (MangaType::query()->orderBy('sort')->orderBy('id')->get(['id', 'name', 'parent_id']) as $row) {
            $out[] = [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'parent_id' => (int) $row->parent_id,
            ];
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array{action:string,msg:string,title:string,id?:int,chapters_added?:int}
     */
    public function upsert(CollectSourceModel $source, array $item, bool $requireBind = true): array
    {
        if (! $this->ready()) {
            return ['action' => 'skipped', 'msg' => '漫画插件未启用', 'title' => ''];
        }
        $title = trim((string) ($item['manga_name'] ?? $item['vod_name'] ?? ''));
        if ($title === '') {
            return ['action' => 'skipped', 'msg' => '无标题', 'title' => ''];
        }
        $bind = $this->bindMap($source);
        $remoteType = (int) ($item['type_id'] ?? 0);
        $typeId = (int) ($bind[(string) $remoteType] ?? 0);
        if ($typeId < 1 && $remoteType > 0 && Schema::hasTable('plugin_manga_types') && MangaType::query()->where('id', $remoteType)->exists()) {
            $typeId = $remoteType;
        }
        if ($requireBind && $typeId < 1) {
            return ['action' => 'skipped', 'msg' => '未绑定分类', 'title' => $title, 'remote_type' => $remoteType];
        }

        $collectId = mb_substr(trim((string) ($item['manga_id'] ?? $item['vod_id'] ?? $item['src_url'] ?? '')), 0, 80);
        $now = time();
        $cover = trim((string) ($item['manga_pic'] ?? $item['vod_pic'] ?? ''));
        $author = mb_substr(trim((string) ($item['manga_author'] ?? $item['vod_actor'] ?? '')), 0, 80);
        $found = $this->findExisting($source, $collectId, $title, $author);
        if (($found['action'] ?? '') === 'skipped') {
            return [
                'action' => 'skipped',
                'msg' => (string) ($found['msg'] ?? '跳过'),
                'title' => $title,
                'id' => (int) ($found['id'] ?? 0),
            ];
        }
        /** @var Manga|null $manga */
        $manga = $found['manga'] ?? null;
        $tags = mb_substr(trim((string) ($item['manga_tag'] ?? $item['vod_class'] ?? '')), 0, 255);
        $content = (string) ($item['manga_content'] ?? $item['vod_content'] ?? '');
        $remarks = mb_substr(trim((string) ($item['manga_remarks'] ?? $item['vod_remarks'] ?? '')), 0, 80);
        $serialize = $this->serializeFlag($item);
        $status = array_key_exists('status', $item) ? ((int) $item['status'] === 1 ? 1 : 0) : 1;

        $payload = [
            'title' => mb_substr($title, 0, 200),
            'updated_at' => $now,
        ];
        if (Schema::hasColumn('plugin_mangas', 'type_id')) {
            $payload['type_id'] = $typeId;
        }
        if (Schema::hasColumn('plugin_mangas', 'cover') && $cover !== '') {
            $payload['cover'] = mb_substr($cover, 0, 500);
        }
        if (Schema::hasColumn('plugin_mangas', 'author') && $author !== '') {
            $payload['author'] = $author;
        }
        if (Schema::hasColumn('plugin_mangas', 'tags') && $tags !== '') {
            $payload['tags'] = $tags;
        }
        if (Schema::hasColumn('plugin_mangas', 'content') && $content !== '') {
            $payload['content'] = $content;
        }
        if (Schema::hasColumn('plugin_mangas', 'remarks') && $remarks !== '') {
            $payload['remarks'] = $remarks;
        }
        if (Schema::hasColumn('plugin_mangas', 'serialize')) {
            $payload['serialize'] = $serialize;
        }
        if (Schema::hasColumn('plugin_mangas', 'status') && ! $manga) {
            $payload['status'] = $status;
        }
        if (Schema::hasColumn('plugin_mangas', 'yid') && ! $manga) {
            $audit = false;
            try {
                $audit = (string) app(\App\Services\Video\VideoSettingService::class)->get('manga_collect_audit', '0') === '1';
            } catch (\Throwable) {
                $audit = false;
            }
            $payload['yid'] = $audit ? 1 : 0;
        }
        if (Schema::hasColumn('plugin_mangas', 'collect_source_id')) {
            $payload['collect_source_id'] = (int) $source->id;
        }
        if (Schema::hasColumn('plugin_mangas', 'collect_id') && $collectId !== '') {
            $payload['collect_id'] = $collectId;
        }

        $action = 'updated';
        if (! $manga) {
            $payload['created_at'] = $now;
            if (Schema::hasColumn('plugin_mangas', 'hits')) {
                $payload['hits'] = 0;
            }
            $manga = new Manga();
            $manga->fill($payload);
            $manga->save();
            $action = 'created';
        } else {
            $manga->fill($payload);
            $manga->save();
        }

        if (array_key_exists('tags', $payload)) {
            try {
                app(MangaTagService::class)->syncManga((int) $manga->id, ['tags' => (string) ($payload['tags'] ?? '')]);
            } catch (\Throwable) {
            }
        }
        if (array_key_exists('author', $payload)) {
            try {
                app(MangaAuthorService::class)->syncManga((int) $manga->id, ['author' => (string) ($payload['author'] ?? '')]);
            } catch (\Throwable) {
            }
        }

        $chapters = $this->mergeChapters($manga, $item);
        $msg = $chapters > 0
            ? ('ok · 新增 '.$chapters.' 话')
            : ($action === 'created' ? 'ok · 无章节' : 'ok · 无新章');

        return ['action' => $action, 'msg' => $msg, 'title' => $title, 'id' => (int) $manga->id, 'chapters_added' => $chapters];
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public function ingestRemote(array $item): array
    {
        if (! $this->ready()) {
            return Result::fail('漫画插件未启用');
        }
        $now = time();
        $source = CollectSourceModel::query()->firstOrCreate(
            ['name' => '漫画入库'],
            [
                'api_url' => 'inbound',
                'api_type' => 'json',
                'mid' => self::MID,
                'param' => '',
                'bind_json' => '{}',
                'status' => 1,
                'sort' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
        if ((int) ($source->mid ?? 0) !== self::MID && Schema::hasColumn('collect_sources', 'mid')) {
            $source->mid = self::MID;
            $source->save();
        }
        $localType = (int) ($item['local_type_id'] ?? $item['type_id'] ?? 0);
        if ($localType > 0) {
            $bind = $this->bindMap($source);
            $bind[(string) $localType] = $localType;
            $item['type_id'] = $localType;
            $source->bind_json = json_encode($bind, JSON_UNESCAPED_UNICODE);
            $source->updated_at = $now;
            $source->save();
        }

        $row = $this->upsert($source, $item, false);
        if (($row['action'] ?? '') === 'skipped' && ($row['msg'] ?? '') === '无标题') {
            return Result::fail('缺少 manga_name');
        }

        return Result::success($row, (string) ($row['msg'] ?? 'ok'));
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public function mergeChapters(Manga $manga, array $item): int
    {
        if (! Schema::hasTable('plugin_manga_chapters')) {
            return 0;
        }
        $localize = false;
        try {
            $localize = (string) app(\App\Services\Video\VideoSettingService::class)->get('manga_collect_localize', '0') === '1';
        } catch (\Throwable) {
            $localize = false;
        }
        $changed = 0;
        foreach ($this->chapterPayloads($item) as $i => $chapter) {
            $name = mb_substr(trim((string) ($chapter['name'] ?? '')), 0, 120);
            $pics = $this->picsFromRaw((string) ($chapter['pics'] ?? $chapter['url'] ?? ''));
            if ($name === '') {
                $name = '第'.($i + 1).'话';
            }
            $row = MangaChapter::query()
                ->where('manga_id', $manga->id)
                ->where('name', $name)
                ->first();
            if (! $row) {
                if ($localize && $pics !== '') {
                    $pics = $this->localizePics((int) $manga->id, $pics);
                }
                $fill = [
                    'manga_id' => (int) $manga->id,
                    'name' => $name,
                    'sort' => (int) ($chapter['sort'] ?? ($i + 1)),
                    'pics' => $pics,
                    'created_at' => time(),
                ];
                if (Schema::hasColumn('plugin_manga_chapters', 'vip')) {
                    $fill['vip'] = 0;
                }
                $row = new MangaChapter();
                $row->fill($fill);
                $row->save();
                $changed++;
                continue;
            }
            // 增量：已有章节且已有图则不覆盖，只补空图。
            if ($pics !== '' && trim((string) $row->pics) === '') {
                if ($localize) {
                    $pics = $this->localizePics((int) $manga->id, $pics, (int) $row->id);
                }
                $row->pics = $pics;
                $row->save();
                $changed++;
            }
        }

        return $changed;
    }

    public function picsFromRaw(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return '';
        }
        $urls = [];
        if (str_contains($raw, '<img')) {
            if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $raw, $m)) {
                foreach ($m[1] as $src) {
                    $urls[] = (string) $src;
                }
            }
        } else {
            $norm = str_replace(['###', "\r\n", "\r"], ["\n", "\n", "\n"], $raw);
            foreach (preg_split('/\n+/', $norm) ?: [] as $line) {
                $line = trim((string) $line);
                if ($line !== '') {
                    $urls[] = $line;
                }
            }
        }
        $out = [];
        foreach ($urls as $url) {
            $safe = MangaChapter::safeUrl($url);
            if ($safe !== '' && $this->looksLikeImage($safe)) {
                $out[] = $safe;
            }
        }

        return implode("\n", array_values(array_unique($out)));
    }

    /** Download remote images into public/uploads/manga when enabled. */
    private function localizePics(int $mangaId, string $pics, int $chapterId = 0): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $pics) ?: [];
        $out = [];
        $dir = public_path('uploads/manga/'.$mangaId.($chapterId > 0 ? '/'.$chapterId : ''));
        if (! is_dir($dir) && ! @mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return $pics;
        }
        foreach ($lines as $i => $line) {
            $url = MangaChapter::safeUrl((string) $line);
            if ($url === '') {
                continue;
            }
            if (! preg_match('#^https?://#i', $url)) {
                $out[] = $url;
                continue;
            }
            try {
                $bin = @file_get_contents($url);
                if ($bin === false || $bin === '') {
                    $out[] = $url;
                    continue;
                }
                $ext = pathinfo((string) (parse_url($url, PHP_URL_PATH) ?: ''), PATHINFO_EXTENSION) ?: 'jpg';
                $ext = preg_replace('/[^a-z0-9]/i', '', (string) $ext) ?: 'jpg';
                $name = 'p'.($i + 1).'_'.substr(md5($url), 0, 8).'.'.$ext;
                $path = $dir.DIRECTORY_SEPARATOR.$name;
                if (@file_put_contents($path, $bin) === false) {
                    $out[] = $url;
                    continue;
                }
                $out[] = '/uploads/manga/'.$mangaId.($chapterId > 0 ? '/'.$chapterId : '').'/'.$name;
            } catch (\Throwable) {
                $out[] = $url;
            }
        }

        return implode("\n", $out);
    }

    /** @param  array<string, mixed>  $item */
    private function serializeFlag(array $item): int
    {
        if (array_key_exists('serialize', $item)) {
            return (int) $item['serialize'] === 1 ? 1 : 0;
        }
        $end = (int) ($item['manga_isend'] ?? $item['vod_isend'] ?? 0);
        if ($end === 1) {
            return 1;
        }
        $serial = trim((string) ($item['manga_serial'] ?? $item['vod_serial'] ?? ''));
        if ($serial === '完结' || $serial === '全' || str_contains($serial, '完结')) {
            return 1;
        }

        return 0;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return list<array{name:string,pics:string,sort:int}>
     */
    private function chapterPayloads(array $item): array
    {
        $direct = $item['chapters'] ?? null;
        if (is_array($direct) && $direct !== []) {
            $out = [];
            foreach (array_values($direct) as $i => $row) {
                if (! is_array($row)) {
                    continue;
                }
                $out[] = [
                    'name' => (string) ($row['name'] ?? $row['chapter_name'] ?? ''),
                    'pics' => (string) ($row['pics'] ?? $row['images'] ?? $row['url'] ?? ''),
                    'sort' => (int) ($row['sort'] ?? ($i + 1)),
                ];
            }
            if ($out !== []) {
                return $out;
            }
        }
        $chapterName = trim((string) ($item['chapter_name'] ?? ''));
        $images = (string) ($item['images'] ?? $item['pics'] ?? '');
        if ($chapterName !== '' && $images !== '') {
            return [['name' => $chapterName, 'pics' => $images, 'sort' => 1]];
        }
        $from = (string) ($item['manga_play_from'] ?? $item['vod_play_from'] ?? 'default');
        $url = (string) ($item['manga_play_url'] ?? $item['vod_play_url'] ?? '');
        if ($url === '') {
            return [];
        }
        $url = str_replace(['||', '###'], ['//', "\n"], $url);
        if ($from === '') {
            $from = 'default';
        }
        $out = [];
        foreach ($this->parser->parse($from, $url) as $group) {
            foreach ($group['episodes'] as $ep) {
                $name = trim((string) ($ep['name'] ?? ''));
                $pics = trim((string) ($ep['url'] ?? ''));
                if ($name === '' && $pics === '') {
                    continue;
                }
                if ($name !== '' && ! str_contains($name, '/') && ! preg_match('#^https?://#i', $name)) {
                    $out[] = [
                        'name' => $name,
                        'pics' => $pics,
                        'sort' => (int) ($ep['num'] ?? (count($out) + 1)),
                    ];
                    continue;
                }
                if ($out !== []) {
                    $last = count($out) - 1;
                    $out[$last]['pics'] = trim($out[$last]['pics']."\n".$name.($pics !== '' ? "\n".$pics : ''));
                }
            }
        }

        return $out;
    }

    private function looksLikeImage(string $url): bool
    {
        $path = strtolower((string) (parse_url($url, PHP_URL_PATH) ?: $url));
        if (preg_match('/\.(html?|php|asp|aspx|jsp)(\?|$)/i', $path) === 1) {
            return false;
        }

        return true;
    }

    /** @return array{manga?:?Manga,action?:string,msg?:string,id?:int} */
    private function findExisting(CollectSourceModel $source, string $collectId, string $title, string $author = ''): array
    {
        if ($collectId !== '' && Schema::hasColumn('plugin_mangas', 'collect_id')) {
            $row = Manga::query()
                ->where('collect_source_id', (int) $source->id)
                ->where('collect_id', $collectId)
                ->first();
            if ($row) {
                return ['manga' => $row];
            }
        }

        $sameTitle = Manga::query()->where('title', $title)->get();
        if ($sameTitle->isEmpty()) {
            return ['manga' => null];
        }
        if ($author !== '') {
            foreach ($sameTitle as $row) {
                $existAuthor = trim((string) ($row->author ?? ''));
                if ($existAuthor === '' || mb_strtolower($existAuthor) === mb_strtolower($author)) {
                    return ['manga' => $row];
                }
            }
            $first = $sameTitle->first();

            return [
                'action' => 'skipped',
                'msg' => '同名不同作者，已跳过（#'.(int) $first->id.' · '.$first->author.'）',
                'id' => (int) $first->id,
            ];
        }

        return ['manga' => $sameTitle->first()];
    }

    /** @return array<string, int> */
    private function bindMap(CollectSourceModel $source): array
    {
        $bind = json_decode((string) ($source->bind_json ?? ''), true);

        return is_array($bind) ? array_map('intval', $bind) : [];
    }
}
