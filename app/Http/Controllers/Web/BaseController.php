<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends Controller
{
    public function __construct()
    {
        if (app()->runningInConsole()) {
            return;
        }
        $this->checkSiteStatus();
        $this->shareConfig();
        $this->shareUserContext();
        $this->shareCommentConfig();
    }

    protected function checkSiteStatus()
    {
        $status = config('maccms.site.site_status');
        if ($status === '0') {
            $msg = config('maccms.site.site_close_tip');
            abort(503, $msg);
        }
    }

    protected function shareConfig(): void
    {
        View::share('maccms', $this->buildMaccmsContext());
    }

    protected function shareUserContext(): void
    {
        $groupList = Group::query()->get()->keyBy('group_id');
        $visitor = [
            'user_id' => 0,
            'user_name' => 'Visitor',
            'user_portrait' => 'static_new/images/touxiang.png',
            'group_id' => 1,
            'points' => 0,
        ];

        if (Auth::check()) {
            $authUser = Auth::user();
            $authUser->normalizeMembership();
            $authUser->queueLegacyCookies();
            $authUser->loadMissing('group');
            $user = array_merge($visitor, $authUser->toArray());
            $user['group'] = $authUser->group?->toArray() ?? ($groupList[1]->toArray() ?? null);
        } else {
            $user = $visitor;
            $user['group'] = $groupList[1]->toArray() ?? null;
        }

        View::share('user', $user);
    }

    protected function shareCommentConfig(): void
    {
        View::share('comment', config('maccms.comment', []));
    }

    protected function buildMaccmsContext(): array
    {
        $site = config('maccms.site', []);
        $app = config('maccms.app', []);
        $user = config('maccms.user', []);
        $templateDir = (string) request()->attributes->get('maccms.template_dir', $site['template_dir'] ?? 'default');
        $htmlDir = (string) request()->attributes->get('maccms.html_dir', $site['html_dir'] ?? 'html');
        $adsDir = (string) request()->attributes->get('maccms.ads_dir', $site['ads_dir'] ?? 'ads');
        $isWap = (int) request()->attributes->get('maccms.is_wap', 0);

        $root = rtrim(url('/'), '/');
        $controller = strtolower(class_basename(optional(request()->route())->getController()));
        $action = strtolower((string) optional(request()->route())->getActionMethod());

        return array_merge($site, [
            'path' => $root,
            'path_tpl' => $root . '/template/' . $templateDir . '/' . $htmlDir . '/',
            'path_ads' => $root . '/template/' . $templateDir . '/' . $adsDir . '/',
            'user_status' => $user['status'] ?? '0',
            'date' => date('Y-m-d'),
            'search_hot' => $app['search_hot'] ?? '',
            'art_extend_class' => $app['art_extend_class'] ?? '',
            'vod_extend_class' => $app['vod_extend_class'] ?? '',
            'vod_extend_state' => $app['vod_extend_state'] ?? '',
            'vod_extend_version' => $app['vod_extend_version'] ?? '',
            'vod_extend_area' => $app['vod_extend_area'] ?? '',
            'vod_extend_lang' => $app['vod_extend_lang'] ?? '',
            'vod_extend_year' => $app['vod_extend_year'] ?? '',
            'vod_extend_weekday' => $app['vod_extend_weekday'] ?? '',
            'actor_extend_area' => $app['actor_extend_area'] ?? '',
            'http_type' => request()->getScheme() . '://',
            'http_url' => url()->full(),
            'seo' => config('maccms.seo', []),
            'controller_action' => trim($controller . '/' . $action, '/'),
            'mid' => function_exists('mac_get_mid') ? (mac_get_mid($controller) ?? 0) : 0,
            'aid' => function_exists('mac_get_aid') ? (mac_get_aid($controller, $action) ?? 0) : 0,
            'mob_status' => (string) $isWap,
        ]);
    }

    protected function normalizeSearchKeyword(?string $keyword): string
    {
        $keyword = trim((string) $keyword);
        if ($keyword === '') {
            return '';
        }

        if (function_exists('mac_filter_words')) {
            $keyword = (string) mac_filter_words($keyword);
        }

        if (function_exists('mac_search_len_check')) {
            $params = array_fill_keys([
                'wd', 'tag', 'class', 'letter', 'name', 'state', 'level',
                'area', 'lang', 'version', 'actor', 'director', 'starsign', 'blood',
            ], '');
            $params['wd'] = $keyword;
            $keyword = (string) (mac_search_len_check($params)['wd'] ?? $keyword);
        } else {
            $keyword = mb_substr($keyword, 0, (int) (config('maccms.app.search_len', 100)));
        }

        return trim($keyword);
    }

    protected function ensureSearchAllowed(Request $request, bool $ajax = false): ?Response
    {
        if (config('maccms.app.search') !== '1') {
            abort(403, 'Search is closed');
        }

        $page = max(1, (int) $request->input('page', 1));
        $timespan = (int) config('maccms.app.search_timespan', 0);
        if ($page === 1 && $timespan > 0 && !$this->checkSessionTimespan('last_searchtime', $timespan)) {
            $msg = __('search_frequently') . $timespan . __('seconds');
            if ($ajax) {
                return response()->json(['code' => 1002, 'msg' => $msg]);
            }

            return $this->renderJump($msg, url()->previous());
        }

        if ((string) config('maccms.app.search_verify', '0') === '1' && !$ajax && !Session::get('search_verify')) {
            return response()
                ->view('public.verify', [
                    'type' => 'search',
                    'id' => (string) $request->input('id', ''),
                    'rootPath' => rtrim(url('/'), '/'),
                ])
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        }

        return null;
    }

    protected function checkSessionTimespan(string $key, int $timespan): bool
    {
        $lastTime = (int) Session::get($key, 0);
        if ($lastTime > 0 && (time() - $lastTime) < $timespan) {
            return false;
        }

        Session::put($key, time());

        return true;
    }

    protected function renderJump(string $msg, string $url = 'javascript:history.back(-1);', int $wait = 3, int $status = 200): Response
    {
        return response()
            ->view('public.jump', compact('msg', 'url', 'wait'))
            ->setStatusCode($status);
    }

    protected function pageError(string $msg = ''): Response
    {
        $msg = $msg !== '' ? $msg : __('controller/an_error_occurred');

        return $this->renderJump($msg, 'javascript:history.back(-1);', 3, 404);
    }

    /**
     * 过滤敏感词
     */
    protected function filterWords($content)
    {
        $filterWords = config('maccms.site.filter_words');
        if (!empty($filterWords)) {
            $words = explode(',', $filterWords);
            foreach ($words as $word) {
                $content = str_ireplace(trim($word), '***', $content);
            }
        }
        return $content;
    }

    /**
     * 格式化图片URL
     */
    protected function formatImageUrl($url)
    {
        if (empty($url)) {
            return '';
        }
        
        if (strpos($url, 'http') === 0) {
            return $url;
        }
        
        return asset($url);
    }

    /**
     * 检查提交频率
     */
    protected function checkFrequency($cookieName, $timespan = 3)
    {
        if (\Illuminate\Support\Facades\Session::has($cookieName)) {
            $lastTime = \Illuminate\Support\Facades\Session::get($cookieName);
            if (time() - $lastTime < $timespan) {
                return false;
            }
        }
        \Illuminate\Support\Facades\Session::put($cookieName, time());
        return true;
    }

    /**
     * 获取用户信息（用于评论、留言等）
     */
    protected function getUserInfo()
    {
        if (Auth::check()) {
            $user = Auth::user();
            return [
                'user_id' => $user->user_id,
                'name' => $user->user_nick_name ?: $user->user_name,
            ];
        }
        return [
            'user_id' => 0,
            'name' => 'Visitor',
        ];
    }

    /**
     * 解析播放列表
     */
    protected function parsePlayList($from, $url)
    {
        if (empty($from) || empty($url)) {
            return [];
        }

        $playerList = explode('$$$', $from);
        $urlList = explode('$$$', $url);
        
        $result = [];
        foreach ($playerList as $key => $playerCode) {
            if (!isset($urlList[$key])) continue;
            
            $episodeList = explode('#', $urlList[$key]);
            $episodes = [];
            foreach ($episodeList as $episode) {
                $parts = explode('$', $episode);
                if (count($parts) >= 2) {
                    $episodes[] = [
                        'name' => $parts[0],
                        'url' => $parts[1],
                        'from' => $playerCode
                    ];
                }
            }
            
            $result[] = [
                'player_code' => $playerCode,
                'player_name' => $this->getPlayerName($playerCode),
                'urls' => $episodes
            ];
        }
        
        return $result;
    }

    /**
     * 获取播放器名称
     */
    protected function getPlayerName($code)
    {
        $players = config('maccms.vodplayer', []);
        return $players[$code]['show'] ?? $code;
    }

    /**
     * 生成详情页链接
     */
    protected function generateDetailLink($mid, $data)
    {
        $prefixMap = [
            '1' => 'vod',
            '2' => 'art',
            '3' => 'topic',
            '8' => 'actor',
            '9' => 'role',
            '11' => 'website',
        ];
        
        $prefix = $prefixMap[$mid] ?? 'vod';
        $id = $data[$prefix . '_id'] ?? 0;
        
        $routeMap = [
            '1' => 'vod.detail',
            '2' => 'art.detail',
            '3' => 'topic.detail',
            '8' => 'actor.detail',
            '9' => 'role.detail',
            '11' => 'website.detail',
        ];
        
        $route = $routeMap[$mid] ?? 'vod.detail';
        return route($route, ['id' => $id]);
    }

    /**
     * 获取分类列表
     */
    protected function getTypes($mid)
    {
        return \App\Models\Type::where('type_mid', $mid)
                  ->where('type_status', 1)
                  ->orderBy('type_sort', 'asc')
                  ->get();
    }
}
