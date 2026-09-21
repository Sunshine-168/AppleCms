<?php

namespace App\Http\Controllers\Admin\Video;

use App\Http\Controllers\Controller;
use App\Services\Admin\Video\SiteModuleService;
use App\Support\Utils\Ajax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteModule extends Controller
{
    public function __construct(private readonly SiteModuleService $modules) {}

    public function index(string $module): View|RedirectResponse
    {
        $cfg = $this->cfg($module);
        $redirect = trim((string) ($cfg['redirect'] ?? ''));
        if ($redirect !== '') {
            return redirect($redirect);
        }
        $pluginView = trim((string) ($cfg['view'] ?? ''));
        if ($pluginView !== '') {
            $allowed = $cfg['desks'] ?? [];
            $default = (string) ($cfg['default_desk'] ?? ($allowed[0] ?? 'works'));
            $desk = strtolower(trim((string) request()->query('desk', '')));
            if ($allowed !== [] && ! in_array($desk, $allowed, true)) {
                $desk = $default;
            }
            $payload = [
                'title' => $cfg['title'],
                'hint' => $cfg['hint'] ?? '',
                'module' => $module,
                'desk' => $desk,
            ];
            if ($module === 'mangas') {
                $desk = strtolower(trim((string) request()->query('desk', '')));
                if ($desk === 'types') {
                    return redirect('/admin/video/manga-types');
                }
                $payload['types'] = [];
                $payload['works'] = [];
                $payload['filterMangaId'] = (int) request()->query('manga_id', 0);
                $payload['filterMangaTitle'] = '';
                $payload['filterTypeId'] = (int) request()->query('type_id', 0);
                $payload['filterTagId'] = (int) request()->query('tag_id', 0);
                $payload['filterAuthorId'] = (int) request()->query('author_id', 0);
                $payload['filterQ'] = trim((string) request()->query('q', ''));
                $payload['filterSerialize'] = (string) request()->query('serialize', '');
                $payload['filterRecommend'] = (string) request()->query('recommend', '');
                $payload['tags'] = [];
                $payload['authors'] = [];
                $payload['filterTag'] = null;
                $payload['filterAuthor'] = null;
                try {
                    $svc = app(\Plugins\Manga\Services\MangaService::class);
                    $payload['types'] = $svc->adminTypes();
                    $payload['works'] = $svc->adminWorkOptions();
                    if ($payload['filterMangaId'] > 0) {
                        $filter = $svc->findAny($payload['filterMangaId']);
                        $payload['filterMangaTitle'] = $filter ? (string) $filter->title : '';
                    }
                    $tagSvc = app(\Plugins\Manga\Services\MangaTagService::class);
                    $payload['tagsReady'] = $tagSvc->ready();
                    // 列表筛选用 chip，不在此拉全量标签（量大会拖垮页）。
                    $payload['tags'] = [];
                    if ($payload['filterTagId'] > 0) {
                        $tag = $tagSvc->find($payload['filterTagId']);
                        if ($tag) {
                            $payload['filterTag'] = [
                                'id' => (int) $tag->id,
                                'name' => (string) $tag->name,
                                'slug' => (string) ($tag->slug ?? ''),
                            ];
                        }
                    }
                    $authorSvc = app(\Plugins\Manga\Services\MangaAuthorService::class);
                    $payload['authorsReady'] = $authorSvc->ready();
                    $payload['authors'] = [];
                    if ($payload['filterAuthorId'] > 0) {
                        $author = $authorSvc->find($payload['filterAuthorId']);
                        if ($author) {
                            $payload['filterAuthor'] = [
                                'id' => (int) $author->id,
                                'name' => (string) $author->name,
                                'slug' => (string) ($author->slug ?? ''),
                            ];
                        }
                    }
                    $payload['filterMemberId'] = (int) request()->query('member_id', 0);
                    $payload['filterMemberName'] = '';
                    $payload['favorQueues'] = ['all' => 0, 'today' => 0, 'missing' => 0];
                    $payload['commentQueues'] = ['all' => 0, 'pending' => 0, 'pass' => 0];
                    if ($desk === 'favors' || $payload['filterMemberId'] > 0) {
                        $favorSvc = app(\Plugins\Manga\Services\MangaFavorAdminService::class);
                        $payload['favorQueues'] = $favorSvc->queues();
                        $focus = $favorSvc->focus($payload['filterMemberId'], $payload['filterMangaId']);
                        $payload['filterMemberName'] = $focus['member_name'];
                        if ($payload['filterMangaId'] > 0 && $payload['filterMangaTitle'] === '') {
                            $payload['filterMangaTitle'] = $focus['manga_title'];
                        }
                    }
                    if ($desk === 'comments') {
                        $payload['commentQueues'] = app(\Plugins\Manga\Services\MangaCommentAdminService::class)->queues();
                    }
                } catch (\Throwable) {
                    $payload['types'] = [];
                    $payload['works'] = [];
                    $payload['tags'] = [];
                    $payload['tagsReady'] = false;
                    $payload['authors'] = [];
                    $payload['authorsReady'] = false;
                }
            }
            if ($module === 'mall_goods') {
                $payload['groups'] = [];
                try {
                    $payload['groups'] = app(\Plugins\Mall\Services\MallService::class)->adminGroups();
                } catch (\Throwable) {
                    $payload['groups'] = [];
                }
            }
            $handler = trim((string) ($cfg['handler'] ?? ''));
            if ($handler !== '' && class_exists($handler)) {
                $svc = app($handler);
                if (method_exists($svc, 'boardPayload')) {
                    $extra = $svc->boardPayload($payload);
                    if (is_array($extra)) {
                        $payload = array_merge($payload, $extra);
                    }
                }
            }

            return view($pluginView, $payload);
        }
        if ($module === 'comments') {
            return view('admin.video.comments', [
                'title' => $cfg['title'],
                'queues' => $this->modules->commentQueues(1),
                'scope' => 'vod',
            ]);
        }
        if ($module === 'topics') {
            return view('admin.video.topics', [
                'title' => $cfg['title'],
            ]);
        }
        if ($module === 'arts') {
            $tagId = (int) request()->query('tag_id', 0);
            $tags = app(\App\Services\Admin\Video\ArtTagService::class)->options();
            $filterTag = null;
            foreach ($tags as $tag) {
                if ((int) ($tag['id'] ?? 0) === $tagId) {
                    $filterTag = $tag;
                    break;
                }
            }

            return view('admin.video.arts', [
                'title' => $cfg['title'],
                'types' => $this->modules->artTypeOptions(),
                'queues' => $this->modules->artQueues(),
                'looseCount' => $this->modules->artLooseCount(),
                'tags' => $tags,
                'tagsReady' => app(\App\Services\Admin\Video\ArtTagService::class)->ready(),
                'filterTag' => $filterTag,
                'recycleCount' => $this->modules->artRecycleCount(),
            ]);
        }
        if ($module === 'slides') {
            return view('admin.video.slides', [
                'title' => $cfg['title'],
                'queues' => $this->modules->slideQueues(),
            ]);
        }
        if ($module === 'roles') {
            $videoId = (int) request()->query('video_id', request()->query('video', 0));
            $actorId = (int) request()->query('actor_id', request()->query('actor', 0));
            $focus = $this->modules->roleFocus($videoId, $actorId);

            return view('admin.video.roles', [
                'title' => $cfg['title'],
                'queues' => $this->modules->roleQueues(),
                'videoId' => $videoId,
                'actorId' => $actorId,
                'videoTitle' => $focus['video_title'],
                'actorName' => $focus['actor_name'],
            ]);
        }
        if ($module === 'plots') {
            $videoId = (int) request()->query('video_id', request()->query('video', 0));
            $focus = $this->modules->plotFocus($videoId);

            return view('admin.video.plots', [
                'title' => $cfg['title'],
                'queues' => $this->modules->plotQueues(),
                'videoId' => $videoId,
                'videoTitle' => $focus['video_title'],
            ]);
        }
        if ($module === 'members') {
            return view('admin.video.members', [
                'title' => $cfg['title'],
                'groups' => $this->modules->memberGroupOptions(),
                'queues' => $this->modules->memberQueues(),
            ]);
        }
        if ($module === 'orders') {
            return view('admin.video.orders', [
                'title' => $cfg['title'],
                'queues' => $this->modules->orderQueues(),
            ]);
        }
        if ($module === 'groups') {
            return view('admin.video.groups', [
                'title' => $cfg['title'],
                'queues' => $this->modules->groupQueues(),
            ]);
        }
        if ($module === 'cards') {
            return view('admin.video.cards', [
                'title' => $cfg['title'],
                'queues' => $this->modules->cardQueues(),
            ]);
        }
        if ($module === 'plogs') {
            return view('admin.video.plogs', [
                'title' => $cfg['title'],
                'queues' => $this->modules->plogQueues(),
            ]);
        }
        if ($module === 'ads') {
            return view('admin.video.ads', [
                'title' => $cfg['title'],
                'queues' => $this->modules->adQueues(),
                'types' => $this->modules->vodTypeOptions(),
            ]);
        }
        if ($module === 'links') {
            return view('admin.video.links', [
                'title' => $cfg['title'],
                'queues' => $this->modules->linkQueues(),
            ]);
        }
        if ($module === 'websites') {
            $typeId = (int) request()->query('type_id', request()->query('type', 0));
            $flinkUrl = app(\App\Support\Plugins\PluginManager::class)->isEnabled('friendlink')
                ? '/admin/video/flinks'
                : '/admin/video/links';

            return view('admin.video.websites', [
                'title' => $cfg['title'],
                'queues' => $this->modules->websiteQueues(),
                'types' => $this->modules->websiteTypeOptions(),
                'typeId' => $typeId,
                'flinkUrl' => $flinkUrl,
            ]);
        }
        if ($module === 'domains') {
            return view('admin.video.domains', [
                'title' => $cfg['title'],
                'queues' => $this->modules->domainQueues(),
                'themes' => $this->modules->domainThemeOptions(),
                'currentHost' => \App\Services\Video\DomainBindService::currentHost(),
            ]);
        }
        if ($module === 'classes') {
            return view('admin.video.classes', [
                'title' => $cfg['title'],
                'queues' => $this->modules->classQueues(),
            ]);
        }
        if ($module === 'synonyms') {
            return view('admin.video.synonyms', [
                'title' => $cfg['title'],
                'queues' => $this->modules->synonymQueues(),
            ]);
        }
        if ($module === 'players') {
            return view('admin.video.players', [
                'title' => $cfg['title'],
                'queues' => $this->modules->playerQueues(),
            ]);
        }
        if ($module === 'downloaders') {
            return view('admin.video.downloaders', [
                'title' => $cfg['title'],
                'queues' => $this->modules->downloaderQueues(),
            ]);
        }
        if ($module === 'servers') {
            return view('admin.video.servers', [
                'title' => $cfg['title'],
                'queues' => $this->modules->serverQueues(),
                'onServers' => $this->modules->enabledServers(),
            ]);
        }
        if ($module === 'unions') {
            return view('admin.video.unions', [
                'title' => $cfg['title'],
                'queues' => $this->modules->unionQueues(),
            ]);
        }
        if ($module === 'collect_logs') {
            $sourceId = (int) request()->query('collect_source_id', 0);
            $ok = (string) request()->query('ok', '');
            if ($ok !== '0' && $ok !== '1') {
                $ok = '';
            }

            return view('admin.video.collect_logs', [
                'title' => $cfg['title'],
                'queues' => $this->modules->collectLogQueues(),
                'sourceId' => $sourceId,
                'sourceName' => $sourceId > 0 ? $this->modules->collectSourceName($sourceId) : '',
                'okPrefill' => $ok,
                'todayPrefill' => (string) request()->query('today', '') === '1' ? '1' : '',
            ]);
        }
        if ($module === 'collect_tasks') {
            $sourceId = (int) request()->query('collect_source_id', 0);

            return view('admin.video.collect_tasks', [
                'title' => $cfg['title'],
                'queues' => $this->modules->collectTaskQueues(),
                'sourceId' => $sourceId,
                'sourceName' => $sourceId > 0 ? $this->modules->collectSourceName($sourceId) : '',
            ]);
        }
        if ($module === 'collect_temps') {
            $sourceId = (int) request()->query('collect_source_id', 0);

            return view('admin.video.collect_temps', [
                'title' => $cfg['title'],
                'queues' => $this->modules->collectTempQueues(),
                'toTemp' => $this->modules->collectToTempEnabled(),
                'sourceId' => $sourceId,
                'sourceName' => $sourceId > 0 ? $this->modules->collectSourceName($sourceId) : '',
            ]);
        }
        if ($module === 'searchwords') {
            return view('admin.video.searchwords', [
                'title' => $cfg['title'],
                'queues' => $this->modules->searchWordQueues(),
            ]);
        }
        if ($module === 'reports') {
            return view('admin.video.reports', [
                'title' => $cfg['title'],
                'queues' => $this->modules->reportQueues(),
            ]);
        }
        if ($module === 'guestbooks') {
            return view('admin.video.guestbooks', [
                'title' => $cfg['title'],
                'queues' => $this->modules->guestbookQueues(),
                'audit' => $this->modules->guestbookAuditEnabled(),
            ]);
        }
        if ($module === 'playfails') {
            return view('admin.video.playfails', [
                'title' => $cfg['title'],
                'queues' => $this->modules->playFailQueues(),
            ]);
        }
        if ($module === 'pms') {
            return view('admin.video.pms', [
                'title' => $cfg['title'],
                'queues' => $this->modules->pmQueues(),
                'toId' => (int) request()->query('to', 0),
            ]);
        }
        if ($module === 'notifies') {
            return view('admin.video.notifies', [
                'title' => $cfg['title'],
                'queues' => $this->modules->notifyQueues(),
                'memberId' => (int) request()->query('member', 0),
            ]);
        }
        if ($module === 'withdraws') {
            return view('admin.video.withdraws', [
                'title' => $cfg['title'],
                'queues' => $this->modules->withdrawQueues(),
            ]);
        }
        if ($module === 'invites') {
            return view('admin.video.invites', [
                'title' => $cfg['title'],
                'queues' => $this->modules->inviteQueues(),
            ]);
        }
        if ($module === 'audits') {
            return view('admin.video.audits', [
                'title' => $cfg['title'],
                'queues' => $this->modules->auditQueues(),
            ]);
        }
        if ($module === 'favorites') {
            $memberId = (int) request()->query('member_id', request()->query('member', 0));
            $videoId = (int) request()->query('video_id', request()->query('video', 0));
            $focus = $this->modules->favoriteFocus($memberId, $videoId);

            return view('admin.video.favorites', [
                'title' => $cfg['title'],
                'queues' => $this->modules->favoriteQueues(),
                'memberId' => $memberId,
                'videoId' => $videoId,
                'memberName' => $focus['member_name'],
                'videoTitle' => $focus['video_title'],
            ]);
        }
        if ($module === 'accesslogs') {
            $ip = trim((string) request()->query('ip', ''));
            $to = '/admin/system/runtime?desk=access&view=logs';
            if ($ip !== '') {
                $to .= '&ip='.rawurlencode(substr($ip, 0, 64));
            }

            return redirect($to);
        }
        if ($module === 'botlogs') {
            return view('admin.video.botlogs', [
                'title' => $cfg['title'],
                'queues' => $this->modules->botlogQueues(),
            ]);
        }

        return view('admin.video.module', [
            'module' => $module,
            'title' => $cfg['title'],
            'hint' => $cfg['hint'] ?? '',
            'fields' => $cfg['fields'],
            'cols' => $cfg['cols'],
            'search' => $cfg['search'],
        ]);
    }

    public function list(Request $request, string $module): JsonResponse
    {
        $this->cfg($module);
        $data = $this->modules->lists($module, $request->all());

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function save(Request $request, string $module): JsonResponse
    {
        $this->cfg($module);
        $id = (int) $request->input('id', 0);
        $data = $this->modules->save($module, $request->all(), $id > 0 ? $id : null);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function delete(Request $request, string $module): JsonResponse
    {
        $this->cfg($module);
        $data = $this->modules->delete($module, (int) $request->input('id', 0));

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function batch(Request $request, string $module): JsonResponse
    {
        $this->cfg($module);
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }
        $data = $this->modules->batch(
            $module,
            is_array($ids) ? $ids : [],
            (string) $request->input('action', ''),
            $request->input('value', '')
        );

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function topicVideos(int $id): JsonResponse
    {
        $data = $this->modules->topicVideos($id);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function saveTopicVideos(Request $request, int $id): JsonResponse
    {
        $data = $this->modules->saveTopicVideos($id, (string) $request->input('video_ids', ''));

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function topicArts(int $id): JsonResponse
    {
        $data = $this->modules->topicArts($id);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function saveTopicArts(Request $request, int $id): JsonResponse
    {
        $data = $this->modules->saveTopicArts($id, (string) $request->input('art_ids', ''));

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function generateCards(Request $request): JsonResponse
    {
        $data = $this->modules->generateCards((int) $request->input('count', 10), (int) $request->input('points', 100));

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function generateInvites(Request $request): JsonResponse
    {
        $data = $this->modules->generateInvites(
            (int) $request->input('count', 10),
            (int) $request->input('points', 0),
            (int) $request->input('member_id', 0)
        );

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function runCollectTask(Request $request): JsonResponse
    {
        $data = $this->modules->runCollectTask((int) $request->input('id', 0));

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function ensurePlayers(): JsonResponse
    {
        $data = $this->modules->ensurePlayers();

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function createArt(): View
    {
        return view('admin.video.art_form', [
            'art' => [
                'status' => 1,
                'hits' => 0,
                'type_id' => $this->modules->resolveArtTypeId((int) request()->query('type_id', 0)),
                'title' => '',
                'blurb' => '',
                'cover' => '',
                'content' => '',
                'source' => '',
                'author' => '',
                'tag' => '',
                'tag_ids' => [],
                'tag_extra' => '',
                'flags' => '',
                'sort' => 0,
                'seo_title' => '',
                'seo_key' => '',
                'seo_des' => '',
                'published_at' => 0,
            ],
            'isEdit' => false,
            'types' => $this->modules->artTypeOptions(),
            'selectedTags' => [],
            'tagsReady' => app(\App\Services\Admin\Video\ArtTagService::class)->ready(),
        ]);
    }

    public function editArt(int $id): View
    {
        $art = $this->modules->getArt($id);
        if ($art === null) {
            abort(404);
        }
        $tagSvc = app(\App\Services\Admin\Video\ArtTagService::class);
        $tagIds = array_map('intval', is_array($art['tag_ids'] ?? null) ? $art['tag_ids'] : []);

        return view('admin.video.art_form', [
            'art' => $art,
            'isEdit' => true,
            'types' => $this->modules->artTypeOptions(),
            'selectedTags' => $tagSvc->labels($tagIds),
            'tagsReady' => $tagSvc->ready(),
        ]);
    }

    public function showArtTags(): View
    {
        return view('admin.video.art_tags', [
            'title' => admin_t('nav.art_tags'),
            'ready' => app(\App\Services\Admin\Video\ArtTagService::class)->ready(),
        ]);
    }

    public function createArtTag(): View
    {
        return view('admin.video.art_tag_form', [
            'tag' => ['name' => '', 'slug' => '', 'sort' => 0, 'status' => 1, 'art_count' => 0],
            'isEdit' => false,
        ]);
    }

    public function editArtTag(int $id): View
    {
        $row = app(\App\Services\Admin\Video\ArtTagService::class)->find($id);
        if ($row === null) {
            abort(404);
        }

        return view('admin.video.art_tag_form', [
            'tag' => [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'slug' => (string) $row->slug,
                'sort' => (int) $row->sort,
                'status' => (int) $row->status,
                'art_count' => (int) $row->arts()->count(),
                'url' => (string) $row->url,
            ],
            'isEdit' => true,
        ]);
    }

    public function listArtTags(\Illuminate\Http\Request $request): JsonResponse
    {
        $data = app(\App\Services\Admin\Video\ArtTagService::class)->paginate($request->all());

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function saveArtTag(\Illuminate\Http\Request $request): JsonResponse
    {
        $id = (int) $request->input('id', 0);
        $data = app(\App\Services\Admin\Video\ArtTagService::class)->save($request->all(), $id > 0 ? $id : null);

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function deleteArtTag(\Illuminate\Http\Request $request): JsonResponse
    {
        $data = app(\App\Services\Admin\Video\ArtTagService::class)->delete((int) $request->input('id', 0));

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function batchArtTags(\Illuminate\Http\Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }
        $data = app(\App\Services\Admin\Video\ArtTagService::class)->batch(
            is_array($ids) ? $ids : [],
            (string) $request->input('action', ''),
            $request->input('value', '')
        );

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function showArtRecycle(): View
    {
        return view('admin.video.art_recycle', [
            'title' => admin_t('nav.art_recycle'),
            'ready' => \Illuminate\Support\Facades\Schema::hasTable('video_arts')
                && \Illuminate\Support\Facades\Schema::hasColumn('video_arts', 'deleted_at'),
            'count' => $this->modules->artRecycleCount(),
        ]);
    }

    public function emptyArtRecycle(): JsonResponse
    {
        $data = $this->modules->emptyArtRecycle();

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function showArtMedia(): View
    {
        $board = app(\App\Services\Admin\System\SysFileService::class)->pageBoard();
        $ui = is_array($board['ui'] ?? null) ? $board['ui'] : [];
        $ui['title'] = admin_t('nav.art_media');
        $ui['lead'] = admin_t('ui.art_media_lead');
        $ui['empty'] = admin_t('ui.art_media_empty');
        $ui['empty_hint'] = admin_t('ui.art_media_empty_hint');
        $board['ui'] = $ui;
        $board['hide_extras'] = true;

        return view('admin.system.file.index', $board);
    }

    public function showArtComments(): View
    {
        $ready = \Illuminate\Support\Facades\Schema::hasTable('video_comments')
            && \Illuminate\Support\Facades\Schema::hasColumn('video_comments', 'mid');

        return view('admin.video.comments', [
            'title' => admin_t('nav.art_comments'),
            'queues' => $ready ? $this->modules->commentQueues(2) : ['all' => 0, 'pending' => 0, 'pass' => 0, 'report' => 0],
            'scope' => 'art',
            'ready' => $ready,
        ]);
    }

    public function listArtComments(Request $request): JsonResponse
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('video_comments')
            || ! \Illuminate\Support\Facades\Schema::hasColumn('video_comments', 'mid')) {
            return Ajax::fail(admin_t('ui.migrate_first'));
        }
        $params = $request->all();
        $params['comment_mid'] = 2;
        $data = $this->modules->lists('comments', $params);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function saveArtComment(Request $request): JsonResponse
    {
        $payload = $request->all();
        $payload['mid'] = 2;
        $id = (int) $request->input('id', 0);
        $data = $this->modules->save('comments', $payload, $id > 0 ? $id : null);

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function deleteArtComment(Request $request): JsonResponse
    {
        $data = $this->modules->delete('comments', (int) $request->input('id', 0));

        return Ajax::message($data['code'], $data['msg'], $data['data']);
    }

    public function batchArtComments(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }
        $data = $this->modules->batch(
            'comments',
            is_array($ids) ? $ids : [],
            (string) $request->input('action', ''),
            $request->input('value', '')
        );

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function showArtFlags(): View
    {
        $board = $this->modules->artFlagBoard();

        return view('admin.video.art_flags', [
            'title' => admin_t('nav.art_flags'),
            'ready' => $board['ready'],
            'flags' => $board['flags'],
        ]);
    }

    public function createUnion(): View
    {
        return view('admin.video.union_form', [
            'union' => ['status' => 1, 'sort' => 0],
            'isEdit' => false,
        ]);
    }

    public function editUnion(int $id): View
    {
        $union = $this->modules->getUnion($id);
        if ($union === null) {
            abort(404);
        }

        return view('admin.video.union_form', [
            'union' => $union,
            'isEdit' => true,
        ]);
    }

    public function createCollectTask(Request $request): View
    {
        $sourceId = (int) $request->query('collect_source_id', 0);

        return view('admin.video.collect_task_form', [
            'task' => [
                'status' => 1,
                'pages' => 1,
                'hours' => 24,
                'cron_expression' => '0 * * * *',
                'collect_source_id' => $sourceId,
            ],
            'isEdit' => false,
            'sources' => $this->modules->collectSourceOptions(),
            'cronPresets' => $this->modules->collectCronPresets(),
            'hourPresets' => $this->modules->collectHourPresets(),
        ]);
    }

    public function editCollectTask(int $id): View
    {
        $task = $this->modules->getCollectTask($id);
        if ($task === null) {
            abort(404);
        }

        return view('admin.video.collect_task_form', [
            'task' => $task,
            'isEdit' => true,
            'sources' => $this->modules->collectSourceOptions(),
            'cronPresets' => $this->modules->collectCronPresets(),
            'hourPresets' => $this->modules->collectHourPresets(),
        ]);
    }

    public function createAuditRule(): View
    {
        return view('admin.video.audit_form', [
            'rule' => [
                'status' => 1,
                'scope' => 'title',
                'action' => 'skip',
                'is_regex' => 0,
                'sort' => 0,
                'words' => '',
            ],
            'isEdit' => false,
            'scopes' => $this->modules->auditScopeOptions(),
            'actions' => $this->modules->auditActionOptions(),
        ]);
    }

    public function editAuditRule(int $id): View
    {
        $rule = $this->modules->getAuditRule($id);
        if ($rule === null) {
            abort(404);
        }

        return view('admin.video.audit_form', [
            'rule' => $rule,
            'isEdit' => true,
            'scopes' => $this->modules->auditScopeOptions(),
            'actions' => $this->modules->auditActionOptions(),
        ]);
    }

    public function tryAuditRule(Request $request): JsonResponse
    {
        $data = $this->modules->tryAuditRule($request->all());

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function trySynonym(Request $request): JsonResponse
    {
        $data = $this->modules->trySynonym($request->all());

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function tryDownloader(Request $request): JsonResponse
    {
        $data = $this->modules->tryDownloader($request->all());

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function tryServer(Request $request): JsonResponse
    {
        $data = $this->modules->tryServer($request->all());

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    public function adoptUnion(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }
        if (is_array($ids) && $ids !== []) {
            $data = $this->modules->adoptUnions($ids);
        } else {
            $data = $this->modules->adoptUnion((int) $request->input('id', 0));
        }

        return Ajax::message($data['code'], $data['msg'], $data['data'] ?? []);
    }

    /** @return array<string, mixed> */
    private function cfg(string $module): array
    {
        try {
            return $this->modules->config($module);
        } catch (\InvalidArgumentException) {
            abort(404);
        }
    }
}
