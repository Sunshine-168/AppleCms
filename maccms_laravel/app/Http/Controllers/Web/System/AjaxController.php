<?php

namespace App\Http\Controllers\Web\System;

use App\Http\Controllers\Web\BaseController;
use App\Models\Vod;
use App\Models\Art;
use App\Models\Topic;
use App\Models\Actor;
use App\Models\Role;
use App\Models\Website;
use App\Models\Type;
use App\Models\Comment;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AjaxController extends BaseController
{
    public function data(Request $request)
    {
        $mid = $request->input('mid');
        $limit = intval($request->input('limit', 10));
        $page = intval($request->input('page', 1));
        $typeId = $request->input('tid');
        
        if (!in_array($mid, ['1', '2', '3', '8', '9', '11'])) {
            return response()->json(['code' => 1001, 'msg' => '参数错误']);
        }
        
        $limit = in_array($limit, [10, 20, 30]) ? $limit : 10;
        $page = max(1, min(20, $page));
        
        $config = $this->getModelConfig();
        $modelClass = $config['models'][$mid];
        $prefix = $config['prefixes'][$mid];
        
        $query = $modelClass::query();
        $query->where($prefix . '_status', 1);
        
        if (!empty($typeId) && in_array($mid, ['1', '2'])) {
            $type = Type::find($typeId);
            if ($type) {
                if ($type->type_pid == 0) {
                    $childIds = Type::where('type_pid', $typeId)->pluck('type_id')->toArray();
                    $childIds[] = $typeId;
                    $query->where(function($q) use ($childIds) {
                        $q->whereIn('type_id', $childIds)
                          ->orWhereIn('type_id_1', $childIds);
                    });
                } else {
                    $query->where(function($q) use ($typeId) {
                        $q->where('type_id', $typeId)
                          ->orWhere('type_id_1', $typeId);
                    });
                }
            }
        }
        
        $items = $query->orderBy($prefix . '_time', 'desc')
                      ->skip(($page - 1) * $limit)
                      ->take($limit)
                      ->get();
        
        $list = [];
        foreach ($items as $item) {
            $data = $item->toArray();
            
            unset($data[$prefix . '_time_hits'], $data[$prefix . '_time_make']);
            
            $data[$prefix . '_time'] = date('Y-m-d H:i:s', $data[$prefix . '_time']);
            $data[$prefix . '_time_add'] = date('Y-m-d H:i:s', $data[$prefix . '_time_add']);
            
            if ($mid == '1') {
                unset($data['vod_play_from'], $data['vod_play_server'], 
                      $data['vod_play_note'], $data['vod_play_url'],
                      $data['vod_down_from'], $data['vod_down_server'], 
                      $data['vod_down_note'], $data['vod_down_url']);
            }
            
            $data['detail_link'] = $this->generateDetailLink($mid, $data);
            
            $data[$prefix . '_pic'] = $this->formatImageUrl($data[$prefix . '_pic'] ?? '');
            $data[$prefix . '_pic_thumb'] = $this->formatImageUrl($data[$prefix . '_pic_thumb'] ?? '');
            $data[$prefix . '_pic_slide'] = $this->formatImageUrl($data[$prefix . '_pic_slide'] ?? '');
            
            $list[] = $data;
        }
        
        return response()->json([
            'code' => 1,
            'msg' => 'success',
            'page' => $page,
            'limit' => $limit,
            'total' => $query->count(),
            'list' => $list
        ]);
    }

    public function suggest(Request $request)
    {
        $appConfig = config('maccms.app');
        if ($appConfig['search'] != '1') {
            return response()->json(['code' => 999, 'msg' => '搜索功能已关闭']);
        }
        
        $mid = $request->input('mid');
        $wd = $request->input('wd');
        $limit = max(1, min(20, intval($request->input('limit', 10))));
        
        if (empty($wd) || !in_array($mid, ['1', '2', '3', '8', '9', '11'])) {
            return response()->json(['code' => 1001, 'msg' => '参数错误']);
        }
        
        $config = $this->getModelConfig();
        $modelClass = $config['models'][$mid];
        $prefix = $config['prefixes'][$mid];
        
        $query = $modelClass::query();
        $query->where($prefix . '_status', 1);
        
        $searchField = $prefix . '_name';
        if ($mid == '1' && !empty($appConfig['search_vod_rule'])) {
            $searchField .= '|' . $appConfig['search_vod_rule'];
        }
        
        $fields = explode('|', $searchField);
        $query->where(function($q) use ($fields, $wd) {
            foreach ($fields as $field) {
                $q->orWhere($field, 'like', '%' . $wd . '%');
            }
        });
        
        $items = $query->orderBy($prefix . '_time', 'desc')
                      ->take($limit)
                      ->get();
        
        $list = [];
        foreach ($items as $item) {
            $list[] = [
                'id' => $item->{$prefix . '_id'},
                'name' => $item->{$prefix . '_name'},
            ];
        }
        
        return response()->json([
            'code' => 1,
            'msg' => 'success',
            'list' => $list
        ]);
    }

    public function hits(Request $request)
    {
        $mid = (string) $request->input('mid', '');
        $id = (int) $request->input('id', 0);
        $type = (string) $request->input('type', '');

        $modelInfo = $this->resolveLegacyModel($mid, ['1', '2', '3', '8', '9', '11']);
        if ($id < 1 || $modelInfo === null) {
            return response()->json(['code' => 1001, 'msg' => '参数错误']);
        }

        [$modelClass, $prefix] = $modelInfo;
        $item = $modelClass::query()->find($id);
        if (!$item) {
            return response()->json(['code' => 1002, 'msg' => '数据不存在']);
        }

        $data = [
            'hits' => (int) ($item->{$prefix . '_hits'} ?? 0),
            'hits_day' => (int) ($item->{$prefix . '_hits_day'} ?? 0),
            'hits_week' => (int) ($item->{$prefix . '_hits_week'} ?? 0),
            'hits_month' => (int) ($item->{$prefix . '_hits_month'} ?? 0),
        ];

        if ($type === 'update') {
            $oldTime = (int) ($item->{$prefix . '_time_hits'} ?? 0);
            $new = getdate();
            $old = $oldTime > 0 ? getdate($oldTime) : null;
            $weekStart = mktime(0, 0, 0, $new['mon'], $new['mday'], $new['year']) - ($new['wday'] * 86400);
            $weekEnd = mktime(23, 59, 59, $new['mon'], $new['mday'], $new['year']) + ((6 - $new['wday']) * 86400);

            $data['hits']++;
            $data['hits_month'] = ($old && $new['year'] === $old['year'] && $new['mon'] === $old['mon']) ? $data['hits_month'] + 1 : 1;
            $data['hits_week'] = ($oldTime >= $weekStart && $oldTime <= $weekEnd) ? $data['hits_week'] + 1 : 1;
            $data['hits_day'] = ($old && $new['year'] === $old['year'] && $new['mon'] === $old['mon'] && $new['mday'] === $old['mday']) ? $data['hits_day'] + 1 : 1;

            $item->update([
                $prefix . '_hits' => $data['hits'],
                $prefix . '_hits_day' => $data['hits_day'],
                $prefix . '_hits_week' => $data['hits_week'],
                $prefix . '_hits_month' => $data['hits_month'],
                $prefix . '_time_hits' => time(),
            ]);
        }

        return response()->json(['code' => 1, 'msg' => 'ok', 'data' => $data]);
    }

    public function score(Request $request)
    {
        $mid = (string) $request->input('mid', '');
        $id = (int) $request->input('id', 0);
        $score = (int) $request->input('score', 0);

        $modelInfo = $this->resolveLegacyModel($mid, ['1', '2', '3', '8', '9', '11']);
        if ($id < 1 || $modelInfo === null) {
            return response()->json(['code' => 1001, 'msg' => '参数错误']);
        }

        [$modelClass, $prefix] = $modelInfo;
        $item = $modelClass::query()->find($id);
        if (!$item) {
            return response()->json(['code' => 1002, 'msg' => '数据不存在']);
        }

        $data = [
            'score' => (float) ($item->{$prefix . '_score'} ?? 0),
            'score_num' => (int) ($item->{$prefix . '_score_num'} ?? 0),
            'score_all' => (int) ($item->{$prefix . '_score_all'} ?? 0),
        ];

        if ($score > 0) {
            $cookie = $prefix . '-score-' . $id;
            if ($request->cookie($cookie)) {
                return response()->json(['code' => 1002, 'msg' => '您已操作过']);
            }

            $data['score_num']++;
            $data['score_all'] += $score;
            $data['score'] = (float) number_format($data['score_all'] / max(1, $data['score_num']), 1, '.', '');

            $item->update([
                $prefix . '_score' => $data['score'],
                $prefix . '_score_num' => $data['score_num'],
                $prefix . '_score_all' => $data['score_all'],
            ]);

            return response()
                ->json(['code' => 1, 'msg' => __('score_ok'), 'data' => $data])
                ->cookie($cookie, 't', 30);
        }

        return response()->json(['code' => 1, 'msg' => __('score_ok'), 'data' => $data]);
    }

    public function digg(Request $request)
    {
        $mid = (string) $request->input('mid', '');
        $id = (int) $request->input('id', 0);
        $type = (string) $request->input('type', '');

        $modelInfo = $this->resolveLegacyModel($mid, ['1', '2', '3', '4', '8', '9', '11']);
        if ($id < 1 || $modelInfo === null) {
            return response()->json(['code' => 1001, 'msg' => '参数错误']);
        }

        [$modelClass, $prefix] = $modelInfo;
        $item = $modelClass::query()->find($id);
        if (!$item) {
            return response()->json(['code' => 1002, 'msg' => '数据不存在']);
        }

        if ($type !== '') {
            $cookie = $prefix . '-digg-' . $id;
            if ($request->cookie($cookie)) {
                return response()->json(['code' => 1002, 'msg' => '您已操作过']);
            }

            if ($type === 'up') {
                $item->increment($prefix . '_up');
            } elseif ($type === 'down') {
                $item->increment($prefix . '_down');
            }

            return response()
                ->json([
                    'code' => 1,
                    'msg' => 'ok',
                    'data' => [
                        'up' => (int) $item->fresh()->{$prefix . '_up'},
                        'down' => (int) $item->fresh()->{$prefix . '_down'},
                    ],
                ])
                ->cookie($cookie, 't', 30);
        }

        return response()->json([
            'code' => 1,
            'msg' => 'ok',
            'data' => [
                'up' => (int) ($item->{$prefix . '_up'} ?? 0),
                'down' => (int) ($item->{$prefix . '_down'} ?? 0),
            ],
        ]);
    }

    public function pwd(Request $request)
    {
        $mid = (string) $request->input('mid', '');
        $id = (int) $request->input('id', 0);
        $type = (string) $request->input('type', '');
        $pwd = (string) $request->input('pwd', '');

        if ($id < 1 || $pwd === '' || !in_array($mid, ['1', '2'], true) || !in_array($type, ['1', '4', '5'], true)) {
            return response()->json(['code' => 1001, 'msg' => '参数错误']);
        }

        $key = $mid . '-' . $type . '-' . $id;
        if (Session::get($key) === '1') {
            return response()->json(['code' => 1002, 'msg' => '请勿重复提交']);
        }

        $lastPwd = (int) Session::get('last_pwd', 0);
        if ($lastPwd > 0 && (time() - $lastPwd) < 5) {
            return response()->json(['code' => 1003, 'msg' => '请求过于频繁']);
        }

        if ($mid === '1') {
            $info = Vod::query()->find($id);
            if (!$info) {
                return response()->json(['code' => 1011, 'msg' => '数据不存在']);
            }
            $column = $type === '1' ? 'vod_pwd' : ($type === '4' ? 'vod_pwd_play' : 'vod_pwd_down');
        } else {
            $info = Art::query()->find($id);
            if (!$info) {
                return response()->json(['code' => 1021, 'msg' => '数据不存在']);
            }
            $column = 'art_pwd';
        }

        if ((string) ($info->{$column} ?? '') !== $pwd) {
            return response()->json(['code' => 1012, 'msg' => '密码错误']);
        }

        Session::put('last_pwd', time());
        Session::put($key, '1');

        return response()->json(['code' => 1, 'msg' => 'ok']);
    }

    public function desktop(Request $request): StreamedResponse
    {
        $name = (string) $request->input('name', config('maccms.site.site_name'));
        $url = (string) $request->input('url', url('/'));
        if (!str_starts_with($url, 'http')) {
            $url = 'http://' . ltrim($url, '/');
        }

        $shortcut = "[InternetShortcut]\nURL={$url}\nIDList=\nIconIndex=1\n[{000214A0-0000-0000-C000-000000000046}]\nProp3=19,2";
        $filename = rawurlencode($name) . '.url';

        return response()->streamDownload(function () use ($shortcut) {
            echo $shortcut;
        }, $filename, ['Content-Type' => 'application/octet-stream']);
    }

    public function referer(Request $request)
    {
        $domain = (string) $request->input('domain', '');
        $url = (string) $request->input('url', '');
        $type = (string) $request->input('type', '');

        if ($domain === '' || $url === '') {
            return response()->json(['code' => 1001, 'msg' => '参数错误']);
        }

        $website = Website::query()
            ->where(function ($query) use ($domain) {
                $query->where('website_jumpurl', 'like', 'http://' . $domain . '%')
                    ->orWhere('website_jumpurl', 'like', 'https://' . $domain . '%');
            })
            ->first();

        if (!$website) {
            return response()->json(['code' => 1002, 'msg' => '数据不存在']);
        }

        $data = [
            'referer' => (int) ($website->website_referer ?? 0),
            'referer_day' => (int) ($website->website_referer_day ?? 0),
            'referer_week' => (int) ($website->website_referer_week ?? 0),
            'referer_month' => (int) ($website->website_referer_month ?? 0),
        ];

        Visit::query()->create([
            'visit_mid' => 11,
            'visit_rid' => $website->website_id,
            'visit_type' => 4,
            'visit_time' => time(),
            'visit_ip' => ip2long($request->ip()),
        ]);

        if ($type === 'update') {
            $oldTime = (int) ($website->website_time_referer ?? 0);
            $new = getdate();
            $old = $oldTime > 0 ? getdate($oldTime) : null;
            $weekStart = mktime(0, 0, 0, $new['mon'], $new['mday'], $new['year']) - ($new['wday'] * 86400);
            $weekEnd = mktime(23, 59, 59, $new['mon'], $new['mday'], $new['year']) + ((6 - $new['wday']) * 86400);

            $data['referer']++;
            $data['referer_month'] = ($old && $new['year'] === $old['year'] && $new['mon'] === $old['mon']) ? $data['referer_month'] + 1 : 1;
            $data['referer_week'] = ($oldTime >= $weekStart && $oldTime <= $weekEnd) ? $data['referer_week'] + 1 : 1;
            $data['referer_day'] = ($old && $new['year'] === $old['year'] && $new['mon'] === $old['mon'] && $new['mday'] === $old['mday']) ? $data['referer_day'] + 1 : 1;

            $website->update([
                'website_referer' => $data['referer'],
                'website_referer_day' => $data['referer_day'],
                'website_referer_week' => $data['referer_week'],
                'website_referer_month' => $data['referer_month'],
                'website_time_referer' => time(),
            ]);
        }

        return response()->json(['code' => 1, 'msg' => 'ok', 'data' => $data]);
    }

    protected function getModelConfig()
    {
        return [
            'models' => [
                '1' => Vod::class,
                '2' => Art::class,
                '3' => Topic::class,
                '8' => Actor::class,
                '9' => Role::class,
                '11' => Website::class,
            ],
            'prefixes' => [
                '1' => 'vod',
                '2' => 'art',
                '3' => 'topic',
                '8' => 'actor',
                '9' => 'role',
                '11' => 'website',
            ],
        ];
    }

    protected function resolveLegacyModel(string $mid, array $allowed): ?array
    {
        if (!in_array($mid, $allowed, true)) {
            return null;
        }

        $map = $this->getModelConfig();
        $models = $map['models'] + ['4' => Comment::class];
        $prefixes = $map['prefixes'] + ['4' => 'comment'];

        return isset($models[$mid], $prefixes[$mid]) ? [$models[$mid], $prefixes[$mid]] : null;
    }
}
