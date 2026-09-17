<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Video\InteractionService;
use App\Services\Video\SiteFrontService;
use App\Cms\CmsViewContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VodController extends Controller
{
    public function __construct(
        private readonly SiteFrontService $front,
        private readonly CmsViewContext $context,
        private readonly InteractionService $interaction,
    ) {}

    public function index(): View
    {
        $site = $this->front->bootSite();
        $this->context->setSeo($site['title'], $site['keyword'], $site['description']);

        return view($this->front->themeView('index.index'), compact('site'));
    }

    public function type(Request $request, string $id): View
    {
        $site = $this->front->bootSite();
        $type = $this->front->findType(preg_replace('/\.html$/i', '', $id));
        if (! $type) {
            throw new NotFoundHttpException();
        }
        $this->context->setType($type);
        $filters = $this->front->applyRequestFilters($request);
        $this->context->setSeo(
            $type->seo_title ?: $this->seoTitle((string) ($site['seo_title_type'] ?? '{type} - {site}'), [
                '{name}' => (string) $type->name,
                '{type}' => (string) $type->name,
                '{site}' => (string) $site['title'],
            ]),
            $type->seo_keywords ?: $site['keyword'],
            $type->seo_description ?: $site['description']
        );

        return view($this->front->themeView('vod.type'), compact('site', 'type', 'filters'));
    }

    public function show(Request $request): View
    {
        $site = $this->front->bootSite();
        $filters = $this->front->applyRequestFilters($request);
        $this->context->setSeo('筛选 - '.$site['title'], $site['keyword'], $site['description']);

        return view($this->front->themeView('vod.show'), compact('site', 'filters'));
    }

    public function detail(int|string $id): View
    {
        $id = $this->vodId($id);
        $site = $this->front->bootSite();
        $video = $this->front->findVideo($id);
        if (! $video) {
            throw new NotFoundHttpException();
        }
        $this->context->setVideo($video);
        $this->context->setType($video->type);
        $this->front->bumpHits($video);
        $this->context->setSeo(
            $this->seoTitle((string) ($site['seo_title_vod'] ?? '{name} - {site}'), [
                '{name}' => (string) $video->title,
                '{type}' => (string) ($video->type?->name ?? ''),
                '{site}' => (string) $site['title'],
            ]),
            $video->title,
            (string) $video->description
        );
        $member = Auth::guard('member')->user();
        $favorited = $member ? $this->interaction->isFavorited((int) $member->id, $video->id) : false;
        $plots = $video->relationLoaded('plots') ? $video->plots : collect();

        return view($this->front->themeView('vod.detail'), compact('site', 'video', 'favorited', 'plots'));
    }

    public function play(int|string $id, int|string|null $sid = null, int|string|null $nid = null): View|RedirectResponse
    {
        $id = $this->vodId($id);
        $sid = $sid === null ? null : $this->vodId($sid);
        $nid = $nid === null ? null : $this->vodId($nid);
        $site = $this->front->bootSite();
        $video = $this->front->findVideo($id);
        if (! $video) {
            throw new NotFoundHttpException();
        }
        $member = Auth::guard('member')->user();
        $paid = $this->interaction->consumePlayPoints($member, $video);
        if ($paid['code'] !== 0) {
            return redirect($member ? url('/member') : url('/member/login'))->with('error', $paid['msg']);
        }
        $trysee = (int) ($paid['data']['trysee'] ?? $paid['data']['trysee_seconds'] ?? 0);
        [$source, $episode] = $this->front->resolvePlay($video, $sid, $nid, 'play');
        $this->context->setVideo($video);
        $this->context->setType($video->type);
        $this->context->setSource($source);
        $this->context->setEpisode($episode);
        $epName = $episode?->display_name ?? '';
        $this->context->setSeo(
            trim($video->title.' '.$epName).' - '.$site['title'],
            $video->title,
            (string) $video->description
        );
        if ($member && $trysee < 1) {
            $this->interaction->recordHistory((int) $member->id, $video, (int) ($source?->id ?: 0), (int) ($episode?->id ?: 0));
        }

        return view($this->front->themeView('vod.play'), compact('site', 'video', 'source', 'episode', 'trysee'));
    }

    public function down(int|string $id, int|string|null $sid = null, int|string|null $nid = null): View
    {
        $id = $this->vodId($id);
        $sid = $sid === null ? null : $this->vodId($sid);
        $nid = $nid === null ? null : $this->vodId($nid);
        $site = $this->front->bootSite();
        $video = $this->front->findVideo($id);
        if (! $video) {
            throw new NotFoundHttpException();
        }
        [$source, $episode] = $this->front->resolvePlay($video, $sid, $nid, 'down');
        $downSources = $video->sources->where('status', 1)->filter(fn ($s) => (string) $s->type === 'down')->values();
        if ($downSources->isEmpty()) {
            $downSources = $video->sources->where('status', 1)->values();
        }
        foreach ($downSources as $src) {
            foreach ($src->episodes as $ep) {
                $ep->setAttribute('down_url', $this->front->resolveDownUrl($src, $ep, (int) $video->id));
            }
        }
        $this->context->setVideo($video);
        $this->context->setType($video->type);
        $this->context->setSource($source);
        $this->context->setEpisode($episode);
        $this->context->setSeo($video->title.' 下载 - '.$site['title'], $video->title, (string) $video->description);

        return view($this->front->themeView('vod.down'), compact('site', 'video', 'source', 'episode', 'downSources'));
    }

    public function search(Request $request): View
    {
        $site = $this->front->bootSite();
        $q = app(\App\Services\Video\SynonymService::class)->expand(trim((string) $request->query('wd', $request->query('q', ''))));
        $this->front->applyRequestFilters($request);
        $this->context->setSeo(($q !== '' ? $q.' - ' : '').'搜索 - '.$site['title'], $q, '');

        return view($this->front->themeView('vod.search'), compact('site', 'q'));
    }

    public function tag(string $slug): View
    {
        $site = $this->front->bootSite();
        $tag = $this->front->findTag(preg_replace('/\.html$/i', '', $slug));
        if (! $tag) {
            throw new NotFoundHttpException();
        }
        $this->context->setSeo($tag->name.' - '.$site['title'], $tag->name, '');

        return view($this->front->themeView('vod.tag'), compact('site', 'tag'));
    }

    public function latest(Request $request): View
    {
        $site = $this->front->bootSite();
        $this->front->applyRequestFilters($request);
        $this->context->setSeo('最新更新 - '.$site['title'], $site['keyword'], $site['description']);

        return view($this->front->themeView('vod.latest'), compact('site'));
    }

    public function actors(): View
    {
        $site = $this->front->bootSite();
        $this->context->setSeo('演员库 - '.$site['title'], $site['keyword'], $site['description']);
        $actors = \App\Models\Video\ActorModel::query()
            ->where('status', 1)
            ->orderByDesc('sort')
            ->orderByDesc('id')
            ->paginate((int) config('video.per_page', 24));
        $this->context->setPaginator($actors);

        return view($this->front->themeView('vod.actors'), compact('site', 'actors'));
    }

    public function topics(): View
    {
        $site = $this->front->bootSite();
        $this->context->setSeo('专题 - '.$site['title'], $site['keyword'], $site['description']);
        $aid = 30;
        $mid = 3;

        return view($this->front->themeView('vod.topics'), compact('site', 'aid', 'mid'));
    }

    public function topicSearch(Request $request): View
    {
        $site = $this->front->bootSite();
        $q = app(\App\Services\Video\SynonymService::class)->expand(trim((string) $request->query('wd', $request->query('q', ''))));
        $this->context->setSeo(($q !== '' ? $q.' - ' : '').'搜专题 - '.$site['title'], $q, '');
        $aid = 33;
        $mid = 3;

        return view($this->front->themeView('vod.topic-search'), compact('site', 'q', 'aid', 'mid'));
    }

    public function arts(Request $request, int|string|null $id = null): View
    {
        $site = $this->front->bootSite();
        $this->context->setSeo('资讯 - '.$site['title'], $site['keyword'], $site['description']);
        $wd = trim((string) $request->query('wd', ''));
        $typeId = (int) $request->query('type_id', $request->query('t', 0));
        if ($id !== null && $id !== '') {
            $typeKey = preg_replace('/\.html$/i', '', (string) $id);
            $typeQuery = \App\Models\Video\VideoTypeModel::query();
            if (\Illuminate\Support\Facades\Schema::hasColumn('video_types', 'mid')) {
                $typeQuery->where('mid', 2);
            }
            $type = is_numeric($typeKey)
                ? $typeQuery->find((int) $typeKey)
                : (clone $typeQuery)->where('slug', $typeKey)->first();
            if ($type) {
                $typeId = (int) $type->id;
                $this->context->setType($type);
                $this->context->setSeo($type->name.' - '.$site['title'], $type->seo_keywords ?: $site['keyword'], $type->seo_description ?: $site['description']);
            }
        }
        $arts = \Illuminate\Support\Facades\Schema::hasTable('video_arts')
            ? \App\Models\Video\VideoArt::query()->where('status', 1)
                ->when($wd !== '', fn ($q) => $q->where('title', 'like', '%'.$wd.'%'))
                ->when($typeId > 0, fn ($q) => $q->where('type_id', $typeId))
                ->orderByDesc('id')->paginate(20)->withQueryString()
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        $this->context->setPaginator($arts);

        return view($this->front->themeView('vod.arts'), compact('site', 'arts'));
    }

    public function actor(int|string $id): View
    {
        $id = $this->vodId($id);
        $site = $this->front->bootSite();
        $actor = $this->front->findActor($id);
        if (! $actor) {
            throw new NotFoundHttpException();
        }
        $this->context->setSeo($actor->name.' - '.$site['title'], $actor->name, (string) $actor->content);
        $videos = $actor->videos()->published()->with(['type', 'stat'])->orderByDesc('id')->paginate((int) config('video.per_page', 24));
        $this->context->setPaginator($videos);

        return view($this->front->themeView('vod.actor'), compact('site', 'actor', 'videos'));
    }

    public function topic(string $id): View
    {
        $site = $this->front->bootSite();
        $topic = $this->front->findTopic(preg_replace('/\.html$/i', '', $id));
        if (! $topic) {
            throw new NotFoundHttpException();
        }
        $this->front->bumpTopicHits($topic);
        $seoTitle = trim((string) ($topic->seo_title ?? ''));
        $seoKey = trim((string) ($topic->seo_key ?? ''));
        $seoDes = trim((string) ($topic->seo_des ?? ''));
        $this->context->setSeo(
            $seoTitle !== '' ? $seoTitle : ($topic->name.' - '.$site['title']),
            $seoKey !== '' ? $seoKey : (string) $topic->name,
            $seoDes !== '' ? $seoDes : (string) $topic->blurb
        );
        $videos = $topic->videos()->published()->with(['type', 'stat'])->paginate((int) config('video.per_page', 24));
        $this->context->setPaginator($videos);
        $arts = collect();
        if (
            method_exists($topic, 'arts')
            && \Illuminate\Support\Facades\Schema::hasTable('video_topic_art_rel')
            && \Illuminate\Support\Facades\Schema::hasTable('video_arts')
        ) {
            $arts = $topic->arts()->where('video_arts.status', 1)->get();
        }
        $obj = $topic;
        $vod_list = $videos;
        $art_list = $arts;
        $mid = 3;
        $aid = 34;
        $view = 'vod.topic';
        $tpl = trim((string) ($topic->tpl ?? ''));
        if ($tpl !== '' && preg_match('/^[A-Za-z0-9_\-]+$/', $tpl)) {
            $resolved = $this->front->themeView('vod.'.$tpl);
            if (view()->exists($resolved)) {
                $view = 'vod.'.$tpl;
            }
        }

        return view($this->front->themeView($view), compact(
            'site', 'topic', 'videos', 'arts', 'obj', 'vod_list', 'art_list', 'mid', 'aid'
        ));
    }

    public function websites(Request $request): View
    {
        $site = $this->front->bootSite();
        $this->context->setSeo('网址导航 - '.$site['title'], $site['keyword'], $site['description']);
        $wd = trim((string) $request->query('wd', ''));
        $typeId = (int) $request->query('type_id', $request->query('t', 0));
        $list = \Illuminate\Support\Facades\Schema::hasTable('video_websites')
            ? \App\Models\Video\VideoWebsite::query()->where('status', 1)
                ->when($wd !== '', fn ($q) => $q->where('name', 'like', '%'.$wd.'%'))
                ->when($typeId > 0 && \Illuminate\Support\Facades\Schema::hasColumn('video_websites', 'type_id'), fn ($q) => $q->where('type_id', $typeId))
                ->orderByDesc('sort')->orderBy('id')->get()
            : collect();

        return view($this->front->themeView('vod.websites'), compact('site', 'list'));
    }

    public function website(int|string $id): View
    {
        $site = $this->front->bootSite();
        $website = $this->front->findWebsite($this->vodId($id));
        if (! $website) {
            throw new NotFoundHttpException();
        }
        $this->context->setSeo($website->name.' - '.$site['title'], $website->name, (string) $website->blurb);

        return view($this->front->themeView('vod.website'), compact('site', 'website'));
    }

    public function art(int|string $id): View
    {
        $site = $this->front->bootSite();
        $art = $this->front->findArt($this->vodId($id));
        if (! $art) {
            throw new NotFoundHttpException();
        }
        $this->context->setSeo($art->title.' - '.$site['title'], $art->title, mb_substr(strip_tags((string) $art->content), 0, 120));
        $art->increment('hits');

        return view($this->front->themeView('vod.art'), compact('site', 'art'));
    }

    public function role(int|string $id): View
    {
        $site = $this->front->bootSite();
        $role = $this->front->findRole($this->vodId($id));
        if (! $role) {
            throw new NotFoundHttpException();
        }
        $this->context->setSeo($role->name.' - '.$site['title'], $role->name, (string) $role->blurb);

        return view($this->front->themeView('vod.role'), compact('site', 'role'));
    }

    public function roles(Request $request): View
    {
        $site = $this->front->bootSite();
        $this->context->setSeo('角色 - '.$site['title'], $site['keyword'], $site['description']);
        $wd = trim((string) $request->query('wd', ''));
        $roles = \Illuminate\Support\Facades\Schema::hasTable('video_roles')
            ? \App\Models\Video\VideoRole::query()->where('status', 1)
                ->when($wd !== '', fn ($q) => $q->where('name', 'like', '%'.$wd.'%'))
                ->orderByDesc('sort')->orderByDesc('id')->paginate((int) config('video.per_page', 24))->withQueryString()
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 24);
        $this->context->setPaginator($roles);

        return view($this->front->themeView('vod.roles'), compact('site', 'roles'));
    }

    public function plots(\Illuminate\Http\Request $request): View
    {
        $site = $this->front->bootSite();
        $videoId = (int) $request->query('video_id', 0);
        $this->context->setSeo('分集剧情 - '.$site['title'], $site['keyword'], $site['description']);
        $q = \Illuminate\Support\Facades\Schema::hasTable('video_plots')
            ? \App\Models\Video\VideoPlot::query()->orderBy('episode_num')->orderBy('sort')->orderBy('id')
            : null;
        if ($q && $videoId > 0) {
            $q->where('video_id', $videoId);
        }
        $plots = $q
            ? $q->paginate(20)->withQueryString()
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        $this->context->setPaginator($plots);

        return view($this->front->themeView('vod.plot'), compact('site', 'plots', 'videoId'));
    }

    public function plot(int|string $id): View
    {
        $site = $this->front->bootSite();
        $plot = $this->front->findPlot($this->vodId($id));
        if (! $plot) {
            throw new NotFoundHttpException();
        }
        $video = $plot->video;
        $this->context->setSeo(($plot->title ?: '剧情').' - '.$site['title'], (string) $plot->title, mb_substr(strip_tags((string) $plot->content), 0, 120));

        return view($this->front->themeView('vod.plot'), compact('site', 'plot', 'video'));
    }

    /** @param  array<string, string>  $vars */
    private function seoTitle(string $tpl, array $vars): string
    {
        $tpl = trim($tpl);
        if ($tpl === '') {
            $tpl = '{name} - {site}';
        }

        return str_replace(array_keys($vars), array_values($vars), $tpl);
    }

    private function vodId(int|string $id): int
    {
        return (int) preg_replace('/\.html$/i', '', (string) $id);
    }
}
