<?php

namespace App\Http\Controllers\Api\App;

use App\Cms\CmsViewContext;
use App\Http\Controllers\Controller;
use App\Models\Video\VideoArt;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoPlayerModel;
use App\Models\Video\VideoPlot;
use App\Models\Video\VideoRole;
use App\Models\Video\VideoTypeModel;
use App\Services\Admin\Video\ArtTagService;
use App\Services\Video\InteractionService;
use App\Services\Video\SiteFrontService;
use App\Services\Video\SynonymService;
use App\Services\Video\Tags\ArtTag;
use App\Services\Video\Tags\CommentTag;
use App\Services\Video\Tags\FilterTag;
use App\Services\Video\Tags\SlideTag;
use App\Services\Video\Tags\TopicTag;
use App\Services\Video\Tags\TypeTag;
use App\Services\Video\Tags\VodTag;
use App\Support\AppApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class VodController extends Controller
{
    public function __construct(
        private readonly SiteFrontService $front,
        private readonly CmsViewContext $context,
        private readonly InteractionService $interaction,
        private readonly VodTag $vodTag,
        private readonly TypeTag $typeTag,
    ) {}

    public function config(): JsonResponse
    {
        $site = $this->front->bootSite();

        return AppApi::ok([
            'site' => AppApi::site($site),
            'types' => $this->typeTag->get(['type' => 'top'])->map(fn ($t) => AppApi::type($t))->values()->all(),
            'orders' => config('video.orders', []),
        ]);
    }

    public function home(): JsonResponse
    {
        $site = $this->front->bootSite();
        $slides = app(SlideTag::class)->get(['slot' => 'home', 'num' => 8])->map(fn ($s) => [
            'id' => (int) $s->id,
            'name' => (string) ($s->name ?? ''),
            'pic' => (string) ($s->pic ?? ''),
            'url' => (string) ($s->url ?? ''),
        ])->values()->all();
        $recommend = $this->vodTag->get(['flag' => 'recommend', 'num' => 12]);
        $latest = $this->vodTag->get(['num' => 12, 'order' => 'time']);
        $hot = $this->vodTag->get(['flag' => 'hot', 'num' => 12, 'order' => 'hits']);
        $arts = app(ArtTag::class)->get(['num' => 6]);
        $topics = app(TopicTag::class)->get(['num' => 6, 'by' => 'sort', 'order' => 'desc']);

        return AppApi::ok([
            'site' => AppApi::site($site),
            'slides' => $slides,
            'types' => $this->typeTag->get(['type' => 'top'])->map(fn ($t) => AppApi::type($t))->values()->all(),
            'recommend' => collect($recommend)->map(fn ($v) => AppApi::videoCard($v))->values()->all(),
            'latest' => collect($latest)->map(fn ($v) => AppApi::videoCard($v))->values()->all(),
            'hot' => collect($hot)->map(fn ($v) => AppApi::videoCard($v))->values()->all(),
            'arts' => collect($arts instanceof LengthAwarePaginator ? $arts->items() : $arts)->map(fn ($a) => AppApi::art($a))->values()->all(),
            'topics' => collect($topics instanceof LengthAwarePaginator ? $topics->items() : $topics)->map(fn ($t) => $this->topicCard($t))->values()->all(),
        ]);
    }

    public function types(): JsonResponse
    {
        $this->front->bootSite();
        $top = $this->typeTag->get(['type' => 'top']);

        return AppApi::ok([
            'types' => $top->map(function ($type) {
                $row = AppApi::type($type);
                $row['children'] = $type->children?->map(fn ($c) => AppApi::type($c))->values()->all() ?? [];

                return $row;
            })->values()->all(),
        ]);
    }

    public function type(Request $request, string $id): JsonResponse
    {
        $site = $this->front->bootSite();
        $type = $this->front->findType(preg_replace('/\.html$/i', '', $id));
        if (! $type) {
            return AppApi::missing('分类不存在');
        }
        $kind = method_exists($type, 'kind') ? $type->kind() : 'list';
        if ($kind === 'link') {
            return AppApi::ok(['type' => AppApi::type($type), 'jump' => $type->jumpUrl()]);
        }
        if ($kind === 'single') {
            $art = $type->singleArt();
            if (! $art) {
                return AppApi::missing('内容不存在');
            }

            return AppApi::ok(['type' => AppApi::type($type), 'art' => AppApi::art($art, true)]);
        }
        $this->context->setType($type);
        $filters = $this->front->applyRequestFilters($request);
        $list = $this->vodTag->get(['page' => 1, 'num' => $type->pageSize((int) config('video.per_page', 24))]);

        return AppApi::ok(array_merge(
            AppApi::page($list, 'list', fn ($v) => AppApi::videoCard($v)),
            [
                'type' => AppApi::type($type),
                'filters' => $filters,
                'filter_groups' => $this->filterGroups(),
                'site' => AppApi::site($site),
            ]
        ));
    }

    public function show(Request $request): JsonResponse
    {
        $this->front->bootSite();
        $filters = $this->front->applyRequestFilters($request);
        $list = $this->vodTag->get(['page' => 1, 'num' => (int) config('video.per_page', 24)]);

        return AppApi::ok(array_merge(
            AppApi::page($list, 'list', fn ($v) => AppApi::videoCard($v)),
            ['filters' => $filters, 'filter_groups' => $this->filterGroups()]
        ));
    }

    public function search(Request $request): JsonResponse
    {
        $this->front->bootSite();
        $q = app(SynonymService::class)->expand(trim((string) $request->query('wd', $request->query('q', ''))));
        $this->front->applyRequestFilters($request);
        $list = $this->vodTag->get(['page' => 1, 'wd' => $q, 'num' => (int) config('video.per_page', 24)]);

        return AppApi::ok(array_merge(
            AppApi::page($list, 'list', fn ($v) => AppApi::videoCard($v)),
            ['q' => $q]
        ));
    }

    public function latest(Request $request): JsonResponse
    {
        $this->front->bootSite();
        $this->front->applyRequestFilters($request);
        $list = $this->vodTag->get(['page' => 1, 'order' => 'time', 'num' => (int) config('video.per_page', 24)]);

        return AppApi::ok(AppApi::page($list, 'list', fn ($v) => AppApi::videoCard($v)));
    }

    public function tag(string $slug): JsonResponse
    {
        $this->front->bootSite();
        $tag = $this->front->findTag(preg_replace('/\.html$/i', '', $slug));
        if (! $tag) {
            return AppApi::missing('标签不存在');
        }
        $list = $this->vodTag->get(['page' => 1, 'tag' => $tag->id, 'num' => (int) config('video.per_page', 24)]);

        return AppApi::ok(array_merge(
            AppApi::page($list, 'list', fn ($v) => AppApi::videoCard($v)),
            ['tag' => ['id' => (int) $tag->id, 'name' => (string) $tag->name, 'slug' => (string) ($tag->slug ?? '')]]
        ));
    }

    public function detail(int $id): JsonResponse
    {
        $this->front->bootSite();
        $video = $this->front->findVideo($id);
        if (! $video) {
            return AppApi::missing('影片不存在');
        }
        $this->front->bumpHits($video);
        $member = Auth::guard('member')->user();
        $favorited = $member ? $this->interaction->isFavorited((int) $member->id, $video->id) : false;
        $comments = app(CommentTag::class)->get(['id' => $video->id, 'num' => 20]);
        $roles = collect();
        try {
            if (Schema::hasTable('video_roles')) {
                $roles = VideoRole::query()->where('status', 1)->where('video_id', $video->id)
                    ->orderByDesc('sort')->orderByDesc('id')->get();
            }
        } catch (\Throwable) {
            $roles = collect();
        }

        return AppApi::ok([
            'video' => AppApi::videoDetail($video, $favorited),
            'comments' => AppApi::comments($comments),
            'roles' => $roles->map(fn ($r) => [
                'id' => (int) $r->id,
                'name' => (string) $r->name,
                'avatar' => (string) ($r->pic ?? $r->avatar ?? ''),
            ])->values()->all(),
        ]);
    }

    public function play(int $id, ?int $sid = null, ?int $nid = null): JsonResponse
    {
        $this->front->bootSite();
        $video = $this->front->findVideo($id);
        if (! $video) {
            return AppApi::missing('影片不存在');
        }
        $member = Auth::guard('member')->user();
        $paid = $this->interaction->consumePlayPoints($member, $video);
        $trysee = (int) ($paid['data']['trysee'] ?? $paid['data']['trysee_seconds'] ?? 0);
        $payError = ($paid['code'] ?? 1) !== 0 ? (string) $paid['msg'] : '';
        [$source, $episode] = $this->front->resolvePlay($video, $sid, $nid, 'play');
        $rawUrl = $payError !== '' ? '' : $this->front->resolvePlayUrl($source, $episode);
        $playUrl = $rawUrl;
        $playerCode = trim((string) ($source?->player ?: $source?->name ?: ''));
        $parser = null;
        if ($playerCode !== '') {
            $parser = VideoPlayerModel::query()->where('status', 1)->where('code', $playerCode)->orderByDesc('sort')->first();
        }
        if (! $parser) {
            $parser = VideoPlayerModel::query()->where('status', 1)->orderByDesc('sort')->first();
        }
        if ($parser && trim((string) $parser->parse) !== '' && $rawUrl !== '') {
            $playUrl = str_replace(['{url}', '{id}'], [rawurlencode($rawUrl), (string) $id], (string) $parser->parse);
        }
        $engine = VideoPlayerModel::playPlan($parser, $playUrl, $rawUrl);
        $playUrl = $engine['media'];
        $rawUrl = $engine['media'];
        $engine = $engine['engine'];
        if ($member && $payError === '' && $trysee < 1) {
            $this->interaction->recordHistory((int) $member->id, $video, (int) ($source?->id ?: 0), (int) ($episode?->id ?: 0));
        }

        return AppApi::ok([
            'video' => AppApi::videoCard($video),
            'source' => $source ? AppApi::source($source) : null,
            'episode' => $episode ? AppApi::episode($episode, $payError === '') : null,
            'play_url' => $playUrl,
            'raw_url' => $rawUrl,
            'engine' => $engine,
            'trysee' => $trysee,
            'pay_error' => $payError,
        ]);
    }

    public function down(int $id, ?int $sid = null, ?int $nid = null): JsonResponse
    {
        $this->front->bootSite();
        $video = $this->front->findVideo($id);
        if (! $video) {
            return AppApi::missing('影片不存在');
        }
        [$source, $episode] = $this->front->resolvePlay($video, $sid, $nid, 'down');
        $downSources = $video->sources->where('status', 1)->filter(fn ($s) => (string) $s->type === 'down')->values();
        if ($downSources->isEmpty()) {
            $downSources = $video->sources->where('status', 1)->values();
        }
        foreach ($downSources as $src) {
            foreach ($src->episodes as $ep) {
                $ep->setAttribute('url', $this->front->resolveDownUrl($src, $ep, (int) $video->id));
            }
        }

        return AppApi::ok([
            'video' => AppApi::videoCard($video),
            'source' => $source ? AppApi::source($source) : null,
            'episode' => $episode ? AppApi::episode($episode, true) : null,
            'sources' => $downSources->map(fn ($s) => AppApi::source($s))->values()->all(),
        ]);
    }

    public function actors(): JsonResponse
    {
        $this->front->bootSite();
        $actors = \App\Models\Video\ActorModel::query()
            ->where('status', 1)
            ->orderByDesc('sort')
            ->orderByDesc('id')
            ->paginate((int) config('video.per_page', 24));

        return AppApi::ok(AppApi::page($actors, 'list', fn ($a) => [
            'id' => (int) $a->id,
            'name' => (string) $a->name,
            'avatar' => (string) ($a->avatar ?? $a->pic ?? ''),
        ]));
    }

    public function actor(int $id): JsonResponse
    {
        $this->front->bootSite();
        $actor = $this->front->findActor($id);
        if (! $actor) {
            return AppApi::missing('演员不存在');
        }
        $videos = $actor->videos()->published()->with(['type', 'stat'])->orderByDesc('id')
            ->paginate((int) config('video.per_page', 24));

        return AppApi::ok(array_merge(
            AppApi::page($videos, 'list', fn ($v) => AppApi::videoCard($v)),
            ['actor' => [
                'id' => (int) $actor->id,
                'name' => (string) $actor->name,
                'avatar' => (string) ($actor->avatar ?? $actor->pic ?? ''),
                'content' => (string) ($actor->content ?? ''),
            ]]
        ));
    }

    public function topics(): JsonResponse
    {
        $this->front->bootSite();
        $topics = app(TopicTag::class)->get(['page' => 1, 'num' => 20]);
        if (! $topics instanceof LengthAwarePaginator) {
            $topics = new LengthAwarePaginator($topics, $topics->count(), 20);
        }

        return AppApi::ok(AppApi::page($topics, 'list', fn ($t) => $this->topicCard($t)));
    }

    public function topicSearch(Request $request): JsonResponse
    {
        $this->front->bootSite();
        $q = app(SynonymService::class)->expand(trim((string) $request->query('wd', $request->query('q', ''))));
        $topics = app(TopicTag::class)->get(['page' => 1, 'wd' => $q, 'num' => 20]);
        if (! $topics instanceof LengthAwarePaginator) {
            $topics = new LengthAwarePaginator($topics, is_countable($topics) ? count($topics) : 0, 20);
        }

        return AppApi::ok(array_merge(
            AppApi::page($topics, 'list', fn ($t) => $this->topicCard($t)),
            ['q' => $q]
        ));
    }

    public function topic(string $id): JsonResponse
    {
        $this->front->bootSite();
        $topic = $this->front->findTopic(preg_replace('/\.html$/i', '', $id));
        if (! $topic) {
            return AppApi::missing('专题不存在');
        }
        $this->front->bumpTopicHits($topic);
        $videos = $topic->videos()->published()->with(['type', 'stat'])->paginate((int) config('video.per_page', 24));
        $arts = collect();
        if (method_exists($topic, 'arts') && Schema::hasTable('video_topic_art_rel') && Schema::hasTable('video_arts')) {
            $arts = $topic->arts()->listed()->get();
        }

        return AppApi::ok(array_merge(
            AppApi::page($videos, 'list', fn ($v) => AppApi::videoCard($v)),
            [
                'topic' => $this->topicCard($topic, true),
                'arts' => $arts->map(fn ($a) => AppApi::art($a))->values()->all(),
            ]
        ));
    }

    public function arts(Request $request, int|string|null $id = null): JsonResponse
    {
        $this->front->bootSite();
        $wd = trim((string) $request->query('wd', ''));
        $typeId = (int) $request->query('type_id', $request->query('t', 0));
        if ($id !== null && $id !== '') {
            $typeKey = preg_replace('/\.html$/i', '', (string) $id);
            $typeQuery = VideoTypeModel::query();
            if (Schema::hasColumn('video_types', 'mid')) {
                $typeQuery->where('mid', 2);
            }
            $type = is_numeric($typeKey)
                ? $typeQuery->find((int) $typeKey)
                : (clone $typeQuery)->where('slug', $typeKey)->first();
            if ($type) {
                $typeId = (int) $type->id;
            }
        }
        $currentType = null;
        $typeIds = [];
        if ($typeId > 0 && Schema::hasTable('video_types')) {
            $currentType = VideoTypeModel::query()->find($typeId);
            if ($currentType && Schema::hasColumn('video_types', 'mid') && (int) ($currentType->mid ?? 0) !== 2) {
                $currentType = null;
                $typeId = 0;
            }
            $typeIds = $currentType ? $currentType->descendantIds() : [];
        }
        if ($currentType && method_exists($currentType, 'kind')) {
            if ($currentType->kind() === 'link') {
                return AppApi::ok(['type' => AppApi::type($currentType), 'jump' => $currentType->jumpUrl()]);
            }
            if ($currentType->kind() === 'single') {
                $art = $currentType->singleArt();
                if (! $art) {
                    return AppApi::missing('内容不存在');
                }

                return AppApi::ok(['type' => AppApi::type($currentType), 'art' => AppApi::art($art, true)]);
            }
        }
        $perPage = $currentType ? $currentType->pageSize(20) : 20;
        $arts = Schema::hasTable('video_arts')
            ? VideoArt::query()->listed()
                ->when($wd !== '', fn ($q) => $q->where('title', 'like', '%'.$wd.'%'))
                ->when($typeIds !== [], fn ($q) => $q->whereIn('type_id', $typeIds))
                ->orderByDesc('id')->paginate($perPage)
            : new LengthAwarePaginator([], 0, $perPage);

        return AppApi::ok(array_merge(
            AppApi::page($arts, 'list', fn ($a) => AppApi::art($a)),
            ['type' => AppApi::type($currentType), 'q' => $wd]
        ));
    }

    public function artTag(string $slug): JsonResponse
    {
        $this->front->bootSite();
        $tag = app(ArtTagService::class)->findPublic(preg_replace('/\.html$/i', '', $slug));
        if (! $tag) {
            return AppApi::missing('标签不存在');
        }
        $arts = $tag->arts()->listed()->orderByDesc('id')->paginate(20);

        return AppApi::ok(array_merge(
            AppApi::page($arts, 'list', fn ($a) => AppApi::art($a)),
            ['tag' => ['id' => (int) $tag->id, 'name' => (string) $tag->name]]
        ));
    }

    public function art(int $id): JsonResponse
    {
        $this->front->bootSite();
        $art = $this->front->findArt($id);
        if (! $art) {
            return AppApi::missing('文章不存在');
        }
        $art->increment('hits');

        return AppApi::ok(['art' => AppApi::art($art, true)]);
    }

    public function roles(Request $request): JsonResponse
    {
        $this->front->bootSite();
        $wd = trim((string) $request->query('wd', ''));
        $roles = Schema::hasTable('video_roles')
            ? VideoRole::query()->where('status', 1)
                ->when($wd !== '', fn ($q) => $q->where('name', 'like', '%'.$wd.'%'))
                ->orderByDesc('sort')->orderByDesc('id')
                ->paginate((int) config('video.per_page', 24))
            : new LengthAwarePaginator([], 0, 24);

        return AppApi::ok(AppApi::page($roles, 'list', fn ($r) => [
            'id' => (int) $r->id,
            'name' => (string) $r->name,
            'avatar' => (string) ($r->pic ?? $r->avatar ?? ''),
        ]));
    }

    public function role(int $id): JsonResponse
    {
        $this->front->bootSite();
        $role = $this->front->findRole($id);
        if (! $role) {
            return AppApi::missing('角色不存在');
        }

        return AppApi::ok(['role' => [
            'id' => (int) $role->id,
            'name' => (string) $role->name,
            'blurb' => (string) ($role->blurb ?? ''),
            'content' => (string) ($role->content ?? ''),
            'avatar' => (string) ($role->pic ?? $role->avatar ?? ''),
            'video_id' => (int) ($role->video_id ?? 0),
        ]]);
    }

    public function plots(Request $request): JsonResponse
    {
        $this->front->bootSite();
        $videoId = (int) $request->query('video_id', 0);
        $q = Schema::hasTable('video_plots')
            ? VideoPlot::query()->orderBy('episode_num')->orderBy('sort')->orderBy('id')
            : null;
        if ($q && $videoId > 0) {
            $q->where('video_id', $videoId);
        }
        $plots = $q ? $q->paginate(20) : new LengthAwarePaginator([], 0, 20);

        return AppApi::ok(array_merge(
            AppApi::page($plots, 'list', fn ($p) => [
                'id' => (int) $p->id,
                'title' => (string) ($p->title ?? ''),
                'episode_num' => (int) ($p->episode_num ?? 0),
                'video_id' => (int) ($p->video_id ?? 0),
            ]),
            ['video_id' => $videoId]
        ));
    }

    public function plot(int $id): JsonResponse
    {
        $this->front->bootSite();
        $plot = $this->front->findPlot($id);
        if (! $plot) {
            return AppApi::missing('剧情不存在');
        }

        return AppApi::ok([
            'plot' => [
                'id' => (int) $plot->id,
                'title' => (string) ($plot->title ?? ''),
                'episode_num' => (int) ($plot->episode_num ?? 0),
                'content' => (string) ($plot->content ?? ''),
                'video_id' => (int) ($plot->video_id ?? 0),
            ],
            'video' => $plot->video ? AppApi::videoCard($plot->video) : null,
        ]);
    }

    public function websites(Request $request): JsonResponse
    {
        $this->front->bootSite();
        $portal = $this->front->websitePortal(
            trim((string) $request->query('wd', '')),
            (int) $request->query('type_id', $request->query('t', 0))
        );

        return AppApi::ok([
            'types' => collect($portal['types'])->map(fn ($t) => AppApi::type($t))->values()->all(),
            'list' => collect($portal['list'])->map(fn ($w) => $this->websiteCard($w))->values()->all(),
            'hot' => collect($portal['hot'])->map(fn ($w) => $this->websiteCard($w))->values()->all(),
            'type_id' => (int) $portal['typeId'],
            'q' => (string) $portal['wd'],
        ]);
    }

    public function website(int $id): JsonResponse
    {
        $this->front->bootSite();
        $website = $this->front->findWebsite($id);
        if (! $website) {
            return AppApi::missing('网址不存在');
        }

        return AppApi::ok([
            'website' => $this->websiteCard($website, true),
            'related' => $this->front->relatedWebsites($website)->map(fn ($w) => $this->websiteCard($w))->values()->all(),
        ]);
    }

    public function websiteGo(int $id): JsonResponse
    {
        $website = $this->front->findWebsite($id);
        if (! $website) {
            return AppApi::missing('网址不存在');
        }
        $this->front->bumpWebsiteHits((int) $website->id);

        return AppApi::ok(['url' => trim((string) $website->url)]);
    }

    /** @return list<array<string, mixed>> */
    private function filterGroups(): array
    {
        return app(FilterTag::class)->get([])->map(fn ($g) => [
            'name' => $g->name,
            'label' => $g->label,
            'current' => $g->current,
            'choices' => collect($g->choices)->map(fn ($c) => [
                'value' => $c['value'],
                'label' => $c['label'],
                'active' => $c['active'],
            ])->values()->all(),
        ])->values()->all();
    }

    private function topicCard(mixed $topic, bool $detail = false): array
    {
        $row = [
            'id' => (int) $topic->id,
            'name' => (string) $topic->name,
            'pic' => (string) ($topic->pic ?? $topic->cover ?? ''),
            'blurb' => (string) ($topic->blurb ?? ''),
        ];
        if ($detail) {
            $row['content'] = (string) ($topic->content ?? '');
        }

        return $row;
    }

    private function websiteCard(mixed $website, bool $detail = false): array
    {
        $row = [
            'id' => (int) $website->id,
            'name' => (string) $website->name,
            'url' => (string) ($website->url ?? ''),
            'logo' => (string) ($website->logo ?? $website->pic ?? ''),
            'blurb' => (string) ($website->blurb ?? ''),
            'hits' => (int) ($website->hits ?? 0),
        ];
        if ($detail) {
            $row['content'] = (string) ($website->content ?? '');
        }

        return $row;
    }
}
