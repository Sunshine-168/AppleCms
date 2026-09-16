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

        return view($this->front->themeView('vod.detail'), compact('site', 'video', 'favorited'));
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
        if ($member) {
            $this->interaction->recordHistory((int) $member->id, $video, (int) ($source?->id ?: 0), (int) ($episode?->id ?: 0));
        }

        return view($this->front->themeView('vod.play'), compact('site', 'video', 'source', 'episode'));
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
        $this->context->setVideo($video);
        $this->context->setType($video->type);
        $this->context->setSource($source);
        $this->context->setEpisode($episode);
        $this->context->setSeo($video->title.' 下载 - '.$site['title'], $video->title, (string) $video->description);

        return view($this->front->themeView('vod.down'), compact('site', 'video', 'source', 'episode'));
    }

    public function search(Request $request): View
    {
        $site = $this->front->bootSite();
        $q = trim((string) $request->query('wd', $request->query('q', '')));
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
        $this->context->setSeo($topic->name.' - '.$site['title'], $topic->name, (string) $topic->blurb);
        $videos = $topic->videos()->published()->with(['type', 'stat'])->paginate((int) config('video.per_page', 24));
        $this->context->setPaginator($videos);

        return view($this->front->themeView('vod.topic'), compact('site', 'topic', 'videos'));
    }

    public function websites(): View
    {
        $site = $this->front->bootSite();
        $this->context->setSeo('网址导航 - '.$site['title'], $site['keyword'], $site['description']);
        $list = \Illuminate\Support\Facades\Schema::hasTable('video_websites')
            ? \App\Models\Video\VideoWebsite::query()->where('status', 1)->orderByDesc('sort')->orderBy('id')->get()
            : collect();

        return view($this->front->themeView('vod.websites'), compact('site', 'list'));
    }

    public function art(int|string $id): View
    {
        $site = $this->front->bootSite();
        $art = $this->front->findArt($this->vodId($id));
        if (! $art) {
            throw new NotFoundHttpException();
        }
        $this->context->setSeo($art->title.' - '.$site['title'], $art->title, mb_substr(strip_tags((string) $art->content), 0, 120));

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
