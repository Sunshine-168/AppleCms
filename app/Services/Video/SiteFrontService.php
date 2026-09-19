<?php

namespace App\Services\Video;

use App\Models\Video\ActorModel;
use App\Models\Video\VideoDownloader;
use App\Models\Video\VideoEpisodeModel;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoServer;
use App\Models\Video\VideoSourceModel;
use App\Models\Video\VideoTagModel;
use App\Models\Video\VideoTopicModel;
use App\Models\Video\VideoTypeModel;
use App\Cms\CmsViewContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SiteFrontService
{
    public function __construct(
        private readonly CmsViewContext $context,
        private readonly VideoSettingService $settings,
    ) {}

    /** @return array<string, mixed> */
    public function bootSite(): array
    {
        $theme = (string) config('video.theme', 'default');
        $bind = null;
        try {
            $bind = DomainBindService::findActive();
            $boundTheme = trim((string) ($bind?->theme ?? ''));
            if ($boundTheme !== '' && DomainBindService::themeExists($boundTheme)) {
                $theme = $boundTheme;
            }
        } catch (\Throwable) {
        }
        $site = array_merge([
            'title' => config('app.name'),
            'keyword' => '',
            'description' => '',
            'theme' => $theme,
        ], config('video.site', []), $this->settings->site());
        $site = DomainBindService::apply($site, $bind);
        $theme = (string) ($site['theme'] ?? $theme);
        $this->context->setSite($site);
        config(['video.theme' => $theme]);

        return $site;
    }

    public function siteClosed(): ?string
    {
        $site = $this->settings->site();
        if ((int) ($site['site_closed'] ?? 0) === 1) {
            return (string) ($site['site_close_tip'] ?? '站点维护中');
        }

        return null;
    }

    public function themeView(string $name): string
    {
        $theme = (string) config('video.theme', 'default');
        $view = "themes.{$theme}.{$name}";
        if (view()->exists($view)) {
            return $view;
        }

        return "themes.default.{$name}";
    }

    public function themeViewOr(string $name, string $fallback): string
    {
        $name = $this->normalizeThemeName($name);
        $fallback = $this->normalizeThemeName($fallback);
        $theme = (string) config('video.theme', 'default');
        foreach ([$name, $fallback] as $key) {
            $preferred = "themes.{$theme}.{$key}";
            if (view()->exists($preferred)) {
                return $preferred;
            }
            $def = "themes.default.{$key}";
            if (view()->exists($def)) {
                return $def;
            }
        }

        return "themes.default.{$fallback}";
    }

    public function artListView(VideoTypeModel $type): string
    {
        $fallback = $type->kind() === 'hub' ? 'vod.art-hub' : 'vod.arts';
        $tpl = VideoTypeModel::normalizeTpl((string) ($type->tpl_list ?? ''));
        if ($tpl === '') {
            return $this->themeViewOr($fallback, $fallback);
        }

        return $this->themeViewOr($tpl, $fallback);
    }

    public function artShowView(?VideoTypeModel $type): string
    {
        $tpl = VideoTypeModel::normalizeTpl((string) ($type?->tpl_detail ?? ''));
        if ($tpl === '') {
            return $this->themeView('vod.art');
        }

        return $this->themeViewOr($tpl, 'vod.art');
    }

    private function normalizeThemeName(string $name): string
    {
        $name = trim(str_replace(['/', '\\'], '.', $name), '.');
        if ($name === '') {
            return 'vod.arts';
        }
        if (! str_contains($name, '.')) {
            return 'vod.'.$name;
        }

        return $name;
    }

    public function applyRequestFilters(Request $request): array
    {
        $filters = [];
        foreach (['year', 'area', 'lang', 'letter', 'class', 'order', 'weekday', 'serial'] as $key) {
            $value = trim((string) $request->query($key, ''));
            if ($value !== '') {
                $filters[$key] = $value;
            }
        }
        $wd = trim((string) $request->query('wd', $request->query('q', '')));
        if ($wd !== '') {
            $filters['wd'] = app(SynonymService::class)->expand($wd);
        }
        $this->context->setFilters($filters);

        return $filters;
    }

    public function findType(string $id): ?VideoTypeModel
    {
        $query = VideoTypeModel::query()->active();

        return is_numeric($id)
            ? $query->find((int) $id)
            : $query->where('slug', $id)->first();
    }

    public function findVideo(int $id): ?VideoModel
    {
        $with = ['type', 'stat', 'tags', 'actors', 'sources.episodes'];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('video_plots')) {
                $with[] = 'plots';
            }
        } catch (\Throwable) {
        }

        return VideoModel::query()
            ->with($with)
            ->published()
            ->find($id);
    }

    public function findTag(string $slug): ?VideoTagModel
    {
        $query = VideoTagModel::query()->where('status', 1);

        return is_numeric($slug)
            ? $query->find((int) $slug)
            : $query->where('slug', $slug)->orWhere('name', $slug)->first();
    }

    public function findActor(int $id): ?ActorModel
    {
        return ActorModel::query()->where('status', 1)->find($id);
    }

    public function findTopic(string $id): ?VideoTopicModel
    {
        $query = VideoTopicModel::query()->where('status', 1);

        return is_numeric($id)
            ? $query->find((int) $id)
            : $query->where('slug', $id)->first();
    }

    public function findRole(int $id): ?\App\Models\Video\VideoRole
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('video_roles')) {
            return null;
        }

        $query = \App\Models\Video\VideoRole::query()->where('status', 1);
        if (\Illuminate\Support\Facades\Schema::hasTable('actors')) {
            $query->with('actor');
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('videos')) {
            $query->with('video');
        }

        return $query->find($id);
    }

    public function findArt(int $id): ?\App\Models\Video\VideoArt
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('video_arts')) {
            return null;
        }

        return \App\Models\Video\VideoArt::query()->listed()->find($id);
    }

    public function findPlot(int $id): ?\App\Models\Video\VideoPlot
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('video_plots')) {
            return null;
        }

        return \App\Models\Video\VideoPlot::query()->with('video')->find($id);
    }

    public function findWebsite(int $id): ?\App\Models\Video\VideoWebsite
    {
        if (! Schema::hasTable('video_websites')) {
            return null;
        }

        return \App\Models\Video\VideoWebsite::query()->where('status', 1)->find($id);
    }

    /** @return array{types: \Illuminate\Support\Collection, list: \Illuminate\Support\Collection, groups: list<array{type:\App\Models\Video\VideoTypeModel|null,items:\Illuminate\Support\Collection}>, hot: \Illuminate\Support\Collection, typeId: int, wd: string} */
    public function websitePortal(string $wd = '', int $typeId = 0): array
    {
        $wd = trim($wd);
        $types = collect();
        if (Schema::hasTable('video_types') && Schema::hasColumn('video_types', 'mid')) {
            $types = VideoTypeModel::query()->active()->where('mid', 3)
                ->orderByDesc('sort')->orderBy('id')->get();
        }
        $typeIds = [];
        if ($typeId > 0) {
            $typeIds = $this->websiteTypeDescendantIds($types, $typeId);
            if ($typeIds === []) {
                $typeIds = [$typeId];
            }
        }
        $list = collect();
        if (Schema::hasTable('video_websites')) {
            $q = \App\Models\Video\VideoWebsite::query()->where('status', 1);
            if ($wd !== '') {
                $q->where(function ($inner) use ($wd) {
                    $inner->where('name', 'like', '%'.$wd.'%')
                        ->orWhere('blurb', 'like', '%'.$wd.'%')
                        ->orWhere('url', 'like', '%'.$wd.'%');
                });
            }
            if ($typeIds !== [] && Schema::hasColumn('video_websites', 'type_id')) {
                $q->whereIn('type_id', $typeIds);
            }
            $list = $q->orderByDesc('sort')->orderBy('id')->get();
        }
        $groups = [];
        if ($types->isEmpty() || ($wd !== '' && $typeId < 1)) {
            if ($list->isNotEmpty()) {
                $groups[] = ['type' => null, 'items' => $list];
            }
        } else {
            $byType = $list->groupBy(fn ($row) => (int) ($row->type_id ?? 0));
            $shown = [];
            $roots = $types->filter(fn ($t) => (int) ($t->parent_id ?? 0) < 1);
            $walk = $typeId > 0
                ? $types->filter(fn ($t) => in_array((int) $t->id, $typeIds, true))
                : $roots;
            foreach ($walk as $type) {
                $ids = $this->websiteTypeDescendantIds($types, (int) $type->id);
                $items = collect();
                foreach ($ids as $tid) {
                    $items = $items->merge($byType->get($tid, collect()));
                    $shown[$tid] = true;
                }
                $items = $items->unique('id')->values();
                if ($items->isEmpty() && $typeId < 1) {
                    continue;
                }
                $groups[] = ['type' => $type, 'items' => $items];
            }
            if ($typeId < 1) {
                $orphan = $byType->get(0, collect());
                foreach ($byType as $tid => $rows) {
                    if ((int) $tid > 0 && ! isset($shown[(int) $tid])) {
                        $orphan = $orphan->merge($rows);
                    }
                }
                $orphan = $orphan->unique('id')->values();
                if ($orphan->isNotEmpty()) {
                    $groups[] = ['type' => null, 'items' => $orphan];
                }
            }
        }
        $hot = collect();
        if (Schema::hasTable('video_websites') && Schema::hasColumn('video_websites', 'hits')) {
            $hot = \App\Models\Video\VideoWebsite::query()->where('status', 1)
                ->where('hits', '>', 0)
                ->orderByDesc('hits')->orderByDesc('sort')->orderBy('id')
                ->limit(10)->get();
        }

        return compact('types', 'list', 'groups', 'hot', 'typeId', 'wd');
    }

    /** @param \Illuminate\Support\Collection<int, VideoTypeModel> $types @return list<int> */
    public function websiteTypeDescendantIds($types, int $rootId): array
    {
        if ($rootId < 1) {
            return [];
        }
        $byParent = [];
        foreach ($types as $type) {
            $byParent[(int) ($type->parent_id ?? 0)][] = (int) $type->id;
        }
        $out = [];
        $stack = [$rootId];
        while ($stack !== []) {
            $id = array_pop($stack);
            if (in_array($id, $out, true)) {
                continue;
            }
            $out[] = $id;
            foreach ($byParent[$id] ?? [] as $child) {
                $stack[] = $child;
            }
        }

        return $out;
    }

    /** @return \Illuminate\Support\Collection<int, \App\Models\Video\VideoWebsite> */
    public function relatedWebsites(\App\Models\Video\VideoWebsite $website, int $limit = 8)
    {
        if (! Schema::hasTable('video_websites')) {
            return collect();
        }
        $q = \App\Models\Video\VideoWebsite::query()
            ->where('status', 1)
            ->where('id', '!=', (int) $website->id);
        $typeId = (int) ($website->type_id ?? 0);
        if ($typeId > 0 && Schema::hasColumn('video_websites', 'type_id')) {
            $q->where('type_id', $typeId);
        }
        if (Schema::hasColumn('video_websites', 'hits')) {
            $q->orderByDesc('hits');
        }

        return $q->orderByDesc('sort')->orderBy('id')->limit($limit)->get();
    }

    public function bumpWebsiteHits(int $id): void
    {
        if ($id < 1 || ! Schema::hasTable('video_websites') || ! Schema::hasColumn('video_websites', 'hits')) {
            return;
        }
        \App\Models\Video\VideoWebsite::query()->where('id', $id)->increment('hits');
    }

    public function resolvePlay(VideoModel $video, ?int $sid, ?int $nid, string $kind = 'play'): array
    {
        $sources = $video->sources->where('status', 1)->values();
        if ($kind !== '') {
            $typed = $sources->filter(fn ($s) => (string) ($s->type ?: 'play') !== 'down');
            if ($kind === 'down') {
                $typed = $sources->filter(fn ($s) => (string) $s->type === 'down');
            }
            if ($typed->isNotEmpty()) {
                $sources = $typed->values();
            }
        }
        $source = $sid
            ? $video->sources->firstWhere('id', $sid)
            : $this->preferPlayableSource($sources);
        if (! $source instanceof VideoSourceModel) {
            return [null, null];
        }
        $episodes = $source->episodes->where('status', 1)->values();
        $episode = null;
        if ($nid) {
            $episode = $episodes->firstWhere('id', $nid)
                ?: $episodes->firstWhere('episode_num', $nid)
                ?: $episodes->values()->get(max(0, $nid - 1));
        } else {
            $episode = $episodes->first();
        }

        return [$source, $episode instanceof VideoEpisodeModel ? $episode : null];
    }

    public function resolvePlayUrl(?VideoSourceModel $source, ?VideoEpisodeModel $episode): string
    {
        $url = trim((string) ($episode?->url ?? ''));
        if ($url === '') {
            return '';
        }
        $serverId = (int) ($source?->server_id ?? 0);
        if ($serverId < 1 || ! Schema::hasTable('video_servers')) {
            return $url;
        }
        if (preg_match('#^(https?:)?//#i', $url)) {
            return $url;
        }
        $server = VideoServer::query()->where('status', 1)->find($serverId);
        $prefix = rtrim(trim((string) ($server?->url ?? '')), '/');
        if ($prefix === '') {
            return $url;
        }

        return $prefix.'/'.ltrim($url, '/');
    }

    /**
     * Prefer direct media lines (m3u8/mp4) over cloud HTML pages when picking the default source.
     *
     * @param  \Illuminate\Support\Collection<int, VideoSourceModel>  $sources
     */
    private function preferPlayableSource($sources): ?VideoSourceModel
    {
        if ($sources->isEmpty()) {
            return null;
        }
        $ranked = $sources->sortBy(function (VideoSourceModel $source) {
            $player = strtolower(trim((string) ($source->player ?: $source->name ?: '')));
            $ep = $source->episodes->where('status', 1)->sortBy('episode_num')->first()
                ?: $source->episodes->first();
            $url = strtolower(trim((string) ($ep?->url ?? '')));
            $score = 50;
            if (str_contains($player, 'yun') || str_contains($player, 'iframe') || str_contains($player, 'parse')) {
                // HTML/cloud players decode HEVC better in Chromium than raw m3u8.
                $score = 0;
            } elseif (str_contains($player, 'm3u8') || preg_match('/\.m3u8(\?|$)/', $url)) {
                $score = 20;
            } elseif (preg_match('/\.(mp4|webm|ogg)(\?|$)/', $url)) {
                $score = 10;
            } elseif ($url !== '' && ! preg_match('/\.(m3u8|mp4|webm|ogg|flv)(\?|$)/', $url)) {
                $score = 5;
            }

            return [$score, (int) ($source->sort ?? 0) * -1, (int) $source->id];
        })->values();

        $first = $ranked->first();

        return $first instanceof VideoSourceModel ? $first : null;
    }

    public function bumpHits(VideoModel $video): void
    {
        $now = time();
        $stat = $video->stat;
        if (! $stat) {
            $video->stat()->create([
                'hits' => 1,
                'hits_day' => 1,
                'hits_week' => 1,
                'hits_month' => 1,
                'up' => 0,
                'down' => 0,
                'score' => $video->score ?: 0,
                'score_all' => 0,
                'score_num' => 0,
                'updated_at' => $now,
            ]);

            return;
        }
        $stat->increment('hits');
        $stat->increment('hits_day');
        $stat->increment('hits_week');
        $stat->increment('hits_month');
        $stat->updated_at = $now;
        $stat->save();
    }

    public function bumpTopicHits(VideoTopicModel $topic): void
    {
        try {
            if (! Schema::hasTable('video_topics')) {
                return;
            }
        } catch (\Throwable) {
            return;
        }
        $dirty = false;
        foreach (['hits', 'hits_day', 'hits_week', 'hits_month'] as $col) {
            try {
                if (! Schema::hasColumn('video_topics', $col)) {
                    continue;
                }
            } catch (\Throwable) {
                continue;
            }
            $topic->setAttribute($col, (int) $topic->getAttribute($col) + 1);
            $dirty = true;
        }
        try {
            if (Schema::hasColumn('video_topics', 'time_hits')) {
                $topic->setAttribute('time_hits', time());
                $dirty = true;
            }
        } catch (\Throwable) {
        }
        if ($dirty) {
            $topic->save();
        }
    }

    public function resolveDownUrl(?VideoSourceModel $source, ?VideoEpisodeModel $episode, int $videoId = 0): string
    {
        $url = trim((string) ($episode?->url ?? ''));
        if ($url === '') {
            return '';
        }
        if (! Schema::hasTable('video_downloaders')) {
            return $url;
        }
        $downerCode = trim((string) ($source?->downer ?? $source?->player ?? $source?->name ?? ''));
        $parser = null;
        $base = VideoDownloader::query()->where('status', 1);
        if ($downerCode !== '') {
            $parser = (clone $base)->where(function ($q) use ($downerCode) {
                $q->where('code', $downerCode)->orWhere('name', $downerCode);
            })->orderByDesc('sort')->first();
        }
        if (! $parser) {
            foreach ((clone $base)->orderByDesc('sort')->get() as $row) {
                $code = trim((string) $row->code);
                if ($code !== '' && (str_starts_with($url, $code) || str_starts_with(strtolower($url), strtolower($code)))) {
                    $parser = $row;
                    break;
                }
            }
        }
        $parse = trim((string) ($parser?->parse ?? ''));
        if ($parse === '') {
            return $url;
        }
        if (str_contains($parse, '{url}') || str_contains($parse, '{id}')) {
            return str_replace(['{url}', '{id}'], [rawurlencode($url), (string) $videoId], $parse);
        }

        return $parse.$url;
    }
}
