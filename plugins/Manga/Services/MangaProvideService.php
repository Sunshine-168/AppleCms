<?php

namespace Plugins\Manga\Services;

use App\Services\Video\VideoSettingService;
use Illuminate\Support\Facades\Schema;
use Plugins\Manga\Models\Manga;
use Plugins\Manga\Models\MangaChapter;
use Plugins\Manga\Models\MangaType;

class MangaProvideService
{
    public function ready(): bool
    {
        return app(MangaService::class)->ready();
    }

    /** @return array<string, mixed> */
    public function payload(\Illuminate\Http\Request $request): array
    {
        $ac = (string) $request->query('ac', 'list');
        $page = max(1, (int) $request->query('pg', 1));
        $limit = 20;
        $typeId = (int) $request->query('t', 0);
        $wd = trim((string) $request->query('wd', ''));
        $ids = (string) $request->query('ids', '');
        $hours = (int) $request->query('h', 0);

        $query = Manga::query()->published()->with(['type', 'chapters']);
        if ($typeId > 0 && Schema::hasColumn('plugin_mangas', 'type_id')) {
            $typeIds = [$typeId];
            if (Schema::hasTable('plugin_manga_types')) {
                $typeIds = array_merge(
                    $typeIds,
                    MangaType::query()->where('parent_id', $typeId)->pluck('id')->all()
                );
            }
            $query->whereIn('type_id', array_values(array_unique(array_map('intval', $typeIds))));
        }
        if ($wd !== '') {
            $query->where('title', 'like', '%'.$wd.'%');
        }
        if ($ids !== '') {
            $query->whereIn('id', array_filter(array_map('intval', explode(',', $ids))));
        }
        if ($hours > 0) {
            $query->where('updated_at', '>=', time() - $hours * 3600);
        }

        $paginator = $query->orderByDesc('id')->paginate($limit, ['*'], 'pg', $page);
        $detail = $ac === 'detail' || $ac === 'videolist' || $ids !== '';
        $list = [];
        foreach ($paginator->items() as $manga) {
            $list[] = $this->format($manga, $detail);
        }

        $payload = [
            'code' => 1,
            'msg' => '数据列表',
            'page' => $paginator->currentPage(),
            'pagecount' => $paginator->lastPage(),
            'limit' => (string) $limit,
            'total' => $paginator->total(),
            'list' => $list,
        ];
        if ($ac === 'list' && Schema::hasTable('plugin_manga_types')) {
            $payload['class'] = MangaType::query()
                ->where('status', 1)
                ->orderByDesc('sort')
                ->orderBy('id')
                ->get()
                ->map(static fn (MangaType $t) => [
                    'type_id' => (int) $t->id,
                    'type_name' => (string) $t->name,
                ])
                ->values()
                ->all();
        }

        return $payload;
    }

    public function format(Manga $manga, bool $detail): array
    {
        $row = [
            'manga_id' => (int) $manga->id,
            'vod_id' => (int) $manga->id,
            'manga_name' => (string) $manga->title,
            'vod_name' => (string) $manga->title,
            'type_id' => (int) ($manga->type_id ?? 0),
            'type_name' => (string) ($manga->type?->name ?? ''),
            'vod_time' => $manga->updated_at ? date('Y-m-d H:i:s', (int) $manga->updated_at) : '',
            'manga_remarks' => (string) ($manga->remarks ?? ''),
            'vod_remarks' => (string) ($manga->remarks ?? ''),
            'manga_play_from' => 'default',
            'vod_play_from' => 'default',
        ];
        if ($detail) {
            $eps = [];
            foreach ($manga->chapters as $ep) {
                /** @var MangaChapter $ep */
                $name = (string) ($ep->name ?: ('第'.$ep->id.'话'));
                $pics = $ep->picList();
                $eps[] = $name.'$'.implode('###', $pics);
            }
            $playUrl = implode('#', $eps);
            $row['manga_pic'] = (string) ($manga->cover ?? '');
            $row['vod_pic'] = (string) ($manga->cover ?? '');
            $row['manga_author'] = (string) ($manga->author ?? '');
            $row['vod_actor'] = (string) ($manga->author ?? '');
            $row['manga_content'] = (string) ($manga->content ?? '');
            $row['vod_content'] = (string) ($manga->content ?? '');
            $row['manga_tag'] = (string) ($manga->tags ?? '');
            $row['manga_serial'] = ((int) ($manga->serialize ?? 0) === 1) ? '完结' : '连载';
            $row['manga_isend'] = (int) ($manga->serialize ?? 0) === 1 ? 1 : 0;
            $row['manga_play_url'] = $playUrl;
            $row['vod_play_url'] = $playUrl;
        }

        return $row;
    }

    public function guarded(\Illuminate\Http\Request $request): bool
    {
        $need = trim((string) app(VideoSettingService::class)->get('provide_key', ''));
        if ($need === '') {
            return true;
        }
        $given = (string) $request->query('key', $request->header('X-Provide-Key', $request->input('key', '')));

        return $given !== '' && hash_equals($need, $given);
    }
}
