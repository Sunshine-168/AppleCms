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
        $roles = collect();
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('video_roles')) {
                $roles = \App\Models\Video\VideoRole::query()
                    ->where('status', 1)
                    ->where('video_id', $video->id)
                    ->orderByDesc('sort')
                    ->orderByDesc('id')
                    ->get();
            }
        } catch (\Throwable) {
            $roles = collect();
        }

        return view($this->front->themeView('vod.detail'), compact('site', 'video', 'favorited', 'plots', 'roles'));
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

    public function arts(Request $request, int|string|null $id = null): View|RedirectResponse
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
                $seoTitle = trim((string) ($type->seo_title ?? ''));
                $this->context->setSeo(
                    $seoTitle !== '' ? $seoTitle : ($type->name.' - '.$site['title']),
                    $type->seo_keywords ?: $site['keyword'],
                    $type->seo_description ?: $site['description']
                );
            }
        }
        $typeIds = [];
        $currentType = null;
        if ($typeId > 0 && \Illuminate\Support\Facades\Schema::hasTable('video_types')) {
            $currentType = \App\Models\Video\VideoTypeModel::query()->find($typeId);
            if ($currentType && \Illuminate\Support\Facades\Schema::hasColumn('video_types', 'mid') && (int) ($currentType->mid ?? 0) !== 2) {
                $currentType = null;
                $typeId = 0;
            }
            $typeIds = $currentType ? $currentType->descendantIds() : [];
        } elseif ($typeId > 0) {
            $typeIds = [$typeId];
        }
        if ($currentType) {
            $kind = $currentType->kind();
            if ($kind === 'link') {
                $jump = $currentType->jumpUrl();
                if ($jump === null) {
                    throw new NotFoundHttpException();
                }
                if (str_starts_with($jump, '/')) {
                    return redirect($jump);
                }

                return redirect()->away($jump);
            }
            if ($kind === 'single') {
                $art = $currentType->singleArt();
                if (! $art) {
                    throw new NotFoundHttpException();
                }

                return $this->renderArtPage($site, $art);
            }
        }
        $perPage = $currentType ? $currentType->pageSize(20) : 20;
        $arts = \Illuminate\Support\Facades\Schema::hasTable('video_arts')
            ? \App\Models\Video\VideoArt::query()->listed()
                ->when($wd !== '', function ($q) use ($wd) {
                    $q->where(function ($inner) use ($wd) {
                        $inner->where('title', 'like', '%'.$wd.'%');
                        if (\Illuminate\Support\Facades\Schema::hasColumn('video_arts', 'blurb')) {
                            $inner->orWhere('blurb', 'like', '%'.$wd.'%');
                        }
                        if (\Illuminate\Support\Facades\Schema::hasColumn('video_arts', 'tag')) {
                            $inner->orWhere('tag', 'like', '%'.$wd.'%');
                        }
                    });
                })
                ->when($typeIds !== [], fn ($q) => $q->whereIn('type_id', $typeIds))
                ->orderByDesc('id')->paginate($perPage)->withQueryString()
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        $this->context->setPaginator($arts);
        $artTypes = $this->artTypeTree();
        $currentTag = null;
        $children = $currentType ? $currentType->children : collect();
        $view = $currentType
            ? $this->front->artListView($currentType)
            : $this->front->themeView('vod.arts');

        return view($view, compact('site', 'arts', 'artTypes', 'typeId', 'currentType', 'currentTag', 'children'));
    }

    public function artTag(string $slug): View
    {
        $site = $this->front->bootSite();
        $key = preg_replace('/\.html$/i', '', $slug);
        $tag = app(\App\Services\Admin\Video\ArtTagService::class)->findPublic((string) $key);
        if (! $tag) {
            throw new NotFoundHttpException();
        }
        $this->context->setSeo($tag->name.' - '.$site['title'], $tag->name, $site['description']);
        $arts = $tag->arts()->listed()->orderByDesc('id')->paginate(20)->withQueryString();
        $this->context->setPaginator($arts);
        $artTypes = $this->artTypeTree();
        $typeId = 0;
        $currentType = null;
        $currentTag = $tag;
        $children = collect();

        return view($this->front->themeView('vod.arts'), compact('site', 'arts', 'artTypes', 'typeId', 'currentType', 'currentTag', 'children'));
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
            $arts = $topic->arts()->listed()->get();
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
        $portal = $this->front->websitePortal($wd, $typeId);

        return view($this->front->themeView('vod.websites'), array_merge(compact('site'), $portal));
    }

    public function website(int|string $id): View
    {
        $site = $this->front->bootSite();
        $website = $this->front->findWebsite($this->vodId($id));
        if (! $website) {
            throw new NotFoundHttpException();
        }
        $this->context->setSeo($website->name.' - '.$site['title'], $website->name, (string) $website->blurb);
        $related = $this->front->relatedWebsites($website);

        return view($this->front->themeView('vod.website'), compact('site', 'website', 'related'));
    }

    public function websiteGo(int|string $id): RedirectResponse
    {
        $website = $this->front->findWebsite($this->vodId($id));
        if (! $website) {
            throw new NotFoundHttpException();
        }
        $this->front->bumpWebsiteHits((int) $website->id);
        $url = trim((string) $website->url);
        if ($url === '') {
            return redirect(vod_url('website', ['id' => $website->id]));
        }

        return redirect()->away($url);
    }

    public function art(int|string $id): View
    {
        $site = $this->front->bootSite();
        $art = $this->front->findArt($this->vodId($id));
        if (! $art) {
            throw new NotFoundHttpException();
        }

        return $this->renderArtPage($site, $art);
    }

    /** @param  array<string, mixed>  $site */
    private function renderArtPage(array $site, \App\Models\Video\VideoArt $art): View
    {
        $art->increment('hits');
        $type = null;
        if ((int) $art->type_id > 0 && \Illuminate\Support\Facades\Schema::hasTable('video_types')) {
            $type = \App\Models\Video\VideoTypeModel::query()->find((int) $art->type_id);
            if ($type) {
                $this->context->setType($type);
            }
        }
        $listedBase = \App\Models\Video\VideoArt::query()->listed();
        if ((int) $art->type_id > 0) {
            $listedBase->where('type_id', (int) $art->type_id);
        }
        $prev = (clone $listedBase)->where('id', '<', $art->id)->orderByDesc('id')->first();
        $next = (clone $listedBase)->where('id', '>', $art->id)->orderBy('id')->first();
        $related = \App\Models\Video\VideoArt::query()->listed()
            ->where('id', '!=', $art->id)
            ->when((int) $art->type_id > 0, fn ($q) => $q->where('type_id', (int) $art->type_id))
            ->orderByDesc('id')
            ->limit(6)
            ->get();
        $seoTitle = trim((string) ($art->seo_title ?? ''));
        $seoKey = trim((string) ($art->seo_key ?? ''));
        $seoDes = trim((string) ($art->seo_des ?? ''));
        if ($seoDes === '') {
            $seoDes = trim((string) ($art->blurb ?? ''));
        }
        if ($seoDes === '') {
            $seoDes = mb_substr(strip_tags((string) $art->content), 0, 120);
        }
        $this->context->setSeo(
            $seoTitle !== '' ? $seoTitle : ($art->title.' - '.$site['title']),
            $seoKey !== '' ? $seoKey : (string) $art->title,
            $seoDes
        );
        $artTags = collect();
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('video_art_tags') && \Illuminate\Support\Facades\Schema::hasTable('video_art_tag_rel')) {
                $artTags = $art->tags()->where('video_art_tags.status', 1)->orderByDesc('video_art_tags.sort')->orderByDesc('video_art_tags.id')->get();
            }
        } catch (\Throwable) {
            $artTags = collect();
        }

        return view($this->front->artShowView($type), compact('site', 'art', 'type', 'prev', 'next', 'related', 'artTags'));
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

    /** @return \Illuminate\Support\Collection<int, \App\Models\Video\VideoTypeModel> */
    private function artTypeTree(): \Illuminate\Support\Collection
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('video_types')) {
            return collect();
        }
        $q = \App\Models\Video\VideoTypeModel::query()
            ->where('status', 1)
            ->where('parent_id', 0)
            ->orderByDesc('sort')
            ->orderBy('id');
        $hasMid = \Illuminate\Support\Facades\Schema::hasColumn('video_types', 'mid');
        if ($hasMid) {
            $q->where('mid', 2);
        }

        return $hasMid
            ? $q->with(['children' => fn ($rel) => $rel->where('mid', 2)])->get()
            : $q->with(['children'])->get();
    }
}
