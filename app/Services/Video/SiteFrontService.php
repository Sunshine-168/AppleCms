<?php

namespace App\Services\Video;

use App\Models\Video\ActorModel;
use App\Models\Video\VideoDomain;
use App\Models\Video\VideoEpisodeModel;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoSourceModel;
use App\Models\Video\VideoTagModel;
use App\Models\Video\VideoTopicModel;
use App\Models\Video\VideoTypeModel;
use App\Cms\CmsViewContext;
use Illuminate\Http\Request;

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
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('video_domains')) {
                $host = (string) request()->getHost();
                $bind = VideoDomain::query()->where('status', 1)->where('host', $host)->first();
                if ($bind && trim((string) $bind->theme) !== '') {
                    $theme = (string) $bind->theme;
                }
            }
        } catch (\Throwable) {
        }
        $site = array_merge([
            'title' => config('app.name'),
            'keyword' => '',
            'description' => '',
            'theme' => $theme,
        ], config('video.site', []), $this->settings->site());
        $site['theme'] = $theme;
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

    public function applyRequestFilters(Request $request): array
    {
        $filters = [];
        foreach (['year', 'area', 'lang', 'letter', 'class', 'order'] as $key) {
            $value = trim((string) $request->query($key, ''));
            if ($value !== '') {
                $filters[$key] = $value;
            }
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
        return VideoModel::query()
            ->with(['type', 'stat', 'tags', 'actors', 'sources.episodes'])
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

        return \App\Models\Video\VideoRole::query()->where('status', 1)->find($id);
    }

    public function findArt(int $id): ?\App\Models\Video\VideoArt
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('video_arts')) {
            return null;
        }

        return \App\Models\Video\VideoArt::query()->where('status', 1)->find($id);
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
            : $sources->first();
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
}
