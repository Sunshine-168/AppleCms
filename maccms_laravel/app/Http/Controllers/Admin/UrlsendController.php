<?php

namespace App\Http\Controllers\Admin;

use App\Models\Actor;
use App\Models\Art;
use App\Models\Manga;
use App\Models\Role;
use App\Models\Topic;
use App\Models\Vod;
use App\Models\Website;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class UrlsendController extends BaseController
{
    protected string $breakpointCachePrefix = 'urlsend_break_';

    public function index(Request $request)
    {
        if ($request->isMethod('post')) {
            $config = $request->input('urlsend', []);
            $this->saveConfigSection('urlsend', $config);
            return $this->success('保存成功');
        }

        $config = config('maccms.urlsend', []);
        $extends = $this->getUrlsendExtends($config);
        $siteUrl = $this->getPushSiteUrl();
        $urlsendBreakBaiduPush = Cache::get($this->breakpointCachePrefix . 'baidu_push');
        $urlsendBreakBaidufastPush = Cache::get($this->breakpointCachePrefix . 'baidufast_push');

        return view('admin.urlsend.index', compact(
            'config',
            'extends',
            'siteUrl',
            'urlsendBreakBaiduPush',
            'urlsendBreakBaidufastPush'
        ));
    }

    public function data(Request $request)
    {
        $data = $this->buildPushData($request);
        return response()->json([
            'code' => empty($data['list']) ? 1001 : 1,
            'msg' => empty($data['list']) ? __('admin/urlsend/no_data') : sprintf(__('admin/urlsend/tip'), $data['total'], $data['pagecount'], $data['page']),
            'data' => $data,
        ]);
    }

    public function push(Request $request)
    {
        $ac = strtolower((string) $request->input('ac', ''));
        if ($ac === '') {
            return $this->error(__('param_err'));
        }

        $driverClass = 'App\\Libraries\\UrlSend\\' . ucfirst($ac);
        if (!class_exists($driverClass)) {
            return $this->error(__('param_err'));
        }

        $data = $this->buildPushData($request);
        $logs = [];
        if (empty($data['list'])) {
            $logs[] = __('admin/urlsend/no_data');
            return view('admin.urlsend.result', compact('logs'));
        }

        $logs[] = sprintf(__('admin/urlsend/tip'), $data['total'], $data['pagecount'], $data['page']);
        foreach ($data['list'] as $item) {
            $logs[] = $item['id'] . '、' . e($item['name']) . '&nbsp;<a href="' . e($item['url']) . '">' . e($item['url']) . '</a>';
        }

        $this->hydrateLegacyGlobals();
        $driver = new $driverClass();
        $result = $driver->submit($data);
        $logs[] = $result['msg'] ?? 'unknown';

        $cacheKey = $this->breakpointCachePrefix . $ac . '_push';
        $nextUrl = null;
        if (($result['code'] ?? 0) === 1 && $data['page'] < $data['pagecount']) {
            $query = $request->query();
            $query['page'] = $data['page'] + 1;
            $nextUrl = route('admin.urlsend.push', $query);
            Cache::put($cacheKey, $nextUrl, now()->addHours(2));
        } else {
            Cache::forget($cacheKey);
            if (($result['code'] ?? 0) === 1) {
                $logs[] = __('admin/urlsend/complete');
            }
        }

        return view('admin.urlsend.result', [
            'logs' => $logs,
            'nextUrl' => $nextUrl,
        ]);
    }

    protected function buildPushData(Request $request): array
    {
        $mid = (int) $request->input('mid', 1);
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, (int) $request->input('limit', 50));
        $ids = trim((string) $request->input('ids', ''));
        $ac2 = (string) $request->input('ac2', 'all');
        $range = (string) $request->input('range', '0');

        $colTime = $range === '1' ? 'time_add' : 'time';
        $today = strtotime(date('Y-m-d'));

        $map = [
            1 => ['model' => Vod::query(), 'status' => 'vod_status', 'id' => 'vod_id', 'name' => 'vod_name', 'time' => 'vod_', 'func' => 'mac_url_vod_detail'],
            2 => ['model' => Art::query(), 'status' => 'art_status', 'id' => 'art_id', 'name' => 'art_name', 'time' => 'art_', 'func' => 'mac_url_art_detail'],
            3 => ['model' => Topic::query(), 'status' => 'topic_status', 'id' => 'topic_id', 'name' => 'topic_name', 'time' => 'topic_', 'func' => 'mac_url_topic_detail'],
            8 => ['model' => Actor::query(), 'status' => 'actor_status', 'id' => 'actor_id', 'name' => 'actor_name', 'time' => 'actor_', 'func' => 'mac_url_actor_detail'],
            9 => ['model' => Role::query(), 'status' => 'role_status', 'id' => 'role_id', 'name' => 'role_name', 'time' => 'role_', 'func' => 'mac_url_role_detail'],
            11 => ['model' => Website::query(), 'status' => 'website_status', 'id' => 'website_id', 'name' => 'website_name', 'time' => 'website_', 'func' => 'mac_url_website_detail'],
            12 => ['model' => Manga::query(), 'status' => 'manga_status', 'id' => 'manga_id', 'name' => 'manga_name', 'time' => 'manga_', 'func' => 'mac_url_manga_detail'],
        ];

        if (!isset($map[$mid])) {
            return ['list' => [], 'urls' => [], 'total' => 0, 'pagecount' => 0, 'page' => $page];
        }

        $config = $map[$mid];
        /** @var Builder $query */
        $query = clone $config['model'];
        $query->where($config['status'], 1);
        if ($ac2 === 'today') {
            $query->where($config['time'] . $colTime, '>', $today);
        }
        if ($ids !== '') {
            $query->whereIn($config['id'], array_filter(array_map('trim', explode(',', $ids))));
        }

        $total = (clone $query)->count();
        $pagecount = $total > 0 ? (int) ceil($total / $limit) : 0;
        $items = $query->orderBy($config['id'], 'asc')
            ->forPage($page, $limit)
            ->get();

        $siteUrl = rtrim($this->getPushSiteUrl(), '/');
        $list = [];
        $urls = [];
        foreach ($items as $item) {
            $row = $item->toArray();
            $id = $row[$config['id']] ?? null;
            $name = $row[$config['name']] ?? '';
            $url = function_exists($config['func']) ? $siteUrl . '/' . ltrim((string) $config['func']($row), '/') : '';
            $list[] = ['id' => $id, 'name' => $name, 'url' => $url, 'row' => $row];
            if ($id !== null) {
                $urls[$id] = $url;
            }
        }

        return [
            'list' => $list,
            'urls' => $urls,
            'total' => $total,
            'pagecount' => $pagecount,
            'page' => $page,
            'mid' => $mid,
            'ac' => $request->input('ac'),
        ];
    }

    protected function hydrateLegacyGlobals(): void
    {
        $GLOBALS['config'] = config('maccms');
        $GLOBALS['http_type'] = request()->getScheme() . '://';
    }

    protected function getPushSiteUrl(): string
    {
        $siteUrl = trim((string) config('maccms.site.site_url', ''));
        if ($siteUrl === '') {
            return url('/');
        }
        if (str_starts_with($siteUrl, 'http://') || str_starts_with($siteUrl, 'https://')) {
            return rtrim($siteUrl, '/');
        }

        return request()->getScheme() . '://' . trim($siteUrl, '/');
    }

    protected function getUrlsendExtends(array $config): array
    {
        $list = [];
        $html = [];
        $drivers = [
            'baidu' => ['name' => '百度推送', 'view' => 'admin.extend.urlsend.baidu'],
            'baidufast' => ['name' => '百度快速推送', 'view' => 'admin.extend.urlsend.baidufast'],
        ];

        foreach ($drivers as $key => $driver) {
            $list[$key] = $driver['name'];
            $html[] = view($driver['view'], compact('config'))->render();
        }

        return [
            'ext_list' => $list,
            'ext_html' => implode("\n", $html),
        ];
    }

    protected function saveConfigSection(string $key, array $data): void
    {
        $configFile = config_path('maccms.php');
        $currentConfig = config('maccms', []);
        $currentConfig[$key] = array_merge($currentConfig[$key] ?? [], $data);
        $content = "<?php\n\nreturn " . var_export($currentConfig, true) . ";\n";
        File::put($configFile, $content);
    }
}
