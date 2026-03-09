<?php

namespace App\Http\Controllers\Api;

use App\Models\Actor;
use App\Models\Art;
use App\Models\Manga;
use App\Models\Role;
use App\Models\Type;
use App\Models\Vod;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProvideController extends PublicApiController
{
    protected array $modelMap = [
        'vod' => Vod::class,
        'art' => Art::class,
        'actor' => Actor::class,
        'role' => Role::class,
        'manga' => Manga::class,
        'website' => Website::class,
    ];

    protected array $midMap = [
        'vod' => 1,
        'art' => 2,
        'actor' => 8,
        'role' => 9,
        'website' => 11,
        'manga' => 12,
    ];

    public function index()
    {
        return $this->response(1, '获取成功', array_keys($this->modelMap));
    }

    public function vod(Request $request) { return $this->outputSection('vod', $request); }
    public function art(Request $request) { return $this->outputSection('art', $request); }
    public function actor(Request $request) { return $this->outputSection('actor', $request); }
    public function role(Request $request) { return $this->outputSection('role', $request); }
    public function manga(Request $request) { return $this->outputSection('manga', $request); }
    public function website(Request $request) { return $this->outputSection('website', $request); }

    protected function outputSection(string $section, Request $request)
    {
        if ($resp = $this->ensureApiSectionEnabled($section)) {
            return $resp;
        }

        $params = $request->all();
        $cacheKey = 'provide_' . $section . '_' . md5(json_encode($params));
        $cacheTime = intval(data_get($this->config, 'api.' . $section . '.cachetime', 0));

        $payload = $cacheTime > 0
            ? Cache::remember($cacheKey, $cacheTime, fn () => $this->buildSectionPayload($section, $params))
            : $this->buildSectionPayload($section, $params);

        if (($params['at'] ?? 'json') === 'xml') {
            return response($this->toXml($section, $payload), 200)->header('Content-Type', 'application/xml; charset=utf-8');
        }

        return response()->json($payload, 200, [], JSON_UNESCAPED_UNICODE);
    }

    protected function buildSectionPayload(string $section, array $params): array
    {
        $modelClass = $this->modelMap[$section];
        $prefix = $section;
        $query = $modelClass::query()->where($prefix . '_status', 1);

        if (!empty($params['ids'])) {
            $query->whereIn($prefix . '_id', array_map('intval', explode(',', $params['ids'])));
        }

        if (!empty($params['t'])) {
            $query->where('type_id', intval($params['t']));
        } else {
            $typeFilter = trim((string) data_get($this->config, 'api.' . $section . '.typefilter', ''));
            if ($typeFilter !== '') {
                $query->whereIn('type_id', array_map('intval', explode(',', $typeFilter)));
            }
        }

        if (!empty($params['wd'])) {
            $query->where($prefix . '_name', 'like', '%' . trim($params['wd']) . '%');
        }

        if (!empty($params['h'])) {
            $query->where($prefix . '_time', '>', time() - intval($params['h']) * 3600);
        }

        if ($section === 'vod' && isset($params['isend']) && $params['isend'] !== '') {
            $query->where('vod_isend', intval($params['isend']));
        }

        if ($section === 'vod' && !empty($params['year'])) {
            $years = explode(',', str_replace('-', ',', trim($params['year'])));
            $query->whereIn('vod_year', $years);
        }

        $page = max(1, intval($params['pg'] ?? 1));
        $pageSize = max(1, min(100, intval($params['pagesize'] ?? data_get($this->config, 'api.' . $section . '.pagesize', 20))));

        $total = (clone $query)->count();
        $list = $query->orderByDesc($prefix . '_time')
            ->forPage($page, $pageSize)
            ->get()
            ->map(fn ($item) => $this->transformItem($section, $item->toArray(), $params))
            ->values()
            ->all();

        $payload = [
            'code' => 1,
            'msg' => '获取成功',
            'page' => $page,
            'pagecount' => $pageSize > 0 ? (int) ceil($total / $pageSize) : 1,
            'limit' => $pageSize,
            'total' => $total,
            'list' => $list,
        ];

        if (($params['ac'] ?? 'list') !== 'detail') {
            $payload['class'] = Type::query()
                ->where('type_status', 1)
                ->where('type_mid', $this->midMap[$section])
                ->orderBy('type_sort')
                ->get(['type_id', 'type_pid', 'type_name'])
                ->toArray();
        }

        if ($section === 'vod' && ($params['ac'] ?? '') === 'detail' && !empty($params['ids']) && ctype_digit((string) $params['ids'])) {
            if (data_get($this->config, 'api.vod.detail_inc_hits', 0)) {
                Vod::query()->where('vod_id', intval($params['ids']))->increment('vod_hits');
            }
        }

        return $payload;
    }

    protected function transformItem(string $section, array $item, array $params): array
    {
        $prefix = $section;
        $type = Type::query()->find($item['type_id'] ?? 0);
        if ($type) {
            $item['type_name'] = $type->type_name;
        }

        $timeField = $prefix . '_time';
        if (!empty($item[$timeField])) {
            $item[$timeField] = date('Y-m-d H:i:s', (int) $item[$timeField]);
        }

        $picField = $prefix . '_pic';
        if (!empty($item[$picField])) {
            $imgBase = rtrim((string) data_get($this->config, 'api.' . $section . '.imgurl', url('/')), '/');
            $pic = $item[$picField];
            if (str_starts_with($pic, 'mac:')) {
                $item[$picField] = str_replace('mac:', (str_starts_with($imgBase, 'https') ? 'https:' : 'http:'), $pic);
            } elseif (!str_starts_with($pic, 'http') && !str_starts_with($pic, '//')) {
                $item[$picField] = $imgBase . '/' . ltrim($pic, '/');
            }
        }

        if ($section === 'vod' && ($params['ac'] ?? '') !== 'detail') {
            $item['vod_play_from'] = str_replace('$$$', ',', $item['vod_play_from'] ?? '');
        }

        if ($section === 'manga' && ($params['ac'] ?? '') !== 'detail') {
            $item['manga_chapter_from'] = str_replace('$$$', ',', $item['manga_chapter_from'] ?? '');
        }

        return $item;
    }

    protected function toXml(string $section, array $payload): string
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?><rss version="5.1">';
        $xml .= '<list page="' . $payload['page'] . '" pagecount="' . $payload['pagecount'] . '" pagesize="' . $payload['limit'] . '" recordcount="' . $payload['total'] . '">';
        foreach ($payload['list'] as $item) {
            $xml .= '<item>';
            foreach ($item as $key => $value) {
                if (is_array($value)) {
                    continue;
                }
                $xml .= '<' . $key . '><![CDATA[' . $value . ']]></' . $key . '>';
            }
            $xml .= '</item>';
        }
        $xml .= '</list>';

        if (!empty($payload['class'])) {
            $xml .= '<class>';
            foreach ($payload['class'] as $type) {
                $xml .= '<ty id="' . $type['type_id'] . '" pid="' . $type['type_pid'] . '"><![CDATA[' . $type['type_name'] . ']]></ty>';
            }
            $xml .= '</class>';
        }

        $xml .= '</rss>';
        return $xml;
    }
}
