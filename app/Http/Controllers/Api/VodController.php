<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\VodDetailRequest;
use App\Http\Requests\Api\VodListRequest;
use App\Models\Vod;
use App\Models\Type;

class VodController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $check = $this->checkConfig();
        if ($check) {
            // API not enabled
        }
    }

    public function getList(VodListRequest $request)
    {
        $param = $request->validated();
        
        $offset = intval($param['offset'] ?? 0);
        $limit = intval($param['limit'] ?? 20);
        
        $query = Vod::where('vod_status', 1);
        
        if (isset($param['type_id'])) {
            $query->where('type_id', intval($param['type_id']));
        }
        
        if (isset($param['id'])) {
            $query->where('vod_id', intval($param['id']));
        }
        
        if (!empty($param['vod_letter'])) {
            $query->where('vod_letter', $this->formatSqlString($param['vod_letter']));
        }
        
        if (!empty($param['vod_tag'])) {
            $query->where('vod_tag', 'like', '%' . $this->formatSqlString($param['vod_tag']) . '%');
        }
        
        if (!empty($param['vod_name'])) {
            $query->where('vod_name', 'like', '%' . $this->formatSqlString($param['vod_name']) . '%');
        }
        
        if (!empty($param['vod_area'])) {
            $query->where('vod_area', $this->formatSqlString($param['vod_area']));
        }
        
        if (!empty($param['vod_year'])) {
            $query->where('vod_year', $this->formatSqlString($param['vod_year']));
        }
        
        $total = $query->count();
        
        $order = 'vod_time DESC';
        if (!empty($param['orderby'])) {
            $order = 'vod_' . $param['orderby'] . ' DESC';
        }
        
        $field = ['vod_id', 'vod_name', 'vod_actor', 'vod_hits', 'vod_hits_day', 
                 'vod_hits_week', 'vod_hits_month', 'vod_time', 'vod_remarks', 
                 'vod_score', 'vod_area', 'vod_year'];
        
        $list = $query->select($field)
                     ->orderByRaw($order)
                     ->skip($offset)
                     ->take($limit)
                     ->get();
        
        return $this->response(1, '获取成功', [
            'offset' => $offset,
            'limit' => $limit,
            'total' => $total,
            'rows' => $list
        ]);
    }

    public function getDetail(VodDetailRequest $request)
    {
        $validated = $request->validated();
        $id = (int) ($validated['id'] ?? $validated['vod_id'] ?? 0);
        
        $vod = Vod::where('vod_id', $id)
                  ->where('vod_status', 1)
                  ->first();
        
        if (!$vod) {
            return $this->response(1002, '数据不存在');
        }
        
        // Format play URLs
        $playList = $this->parsePlayList($vod->vod_play_from, $vod->vod_play_url);
        $downList = $this->parsePlayList($vod->vod_down_from, $vod->vod_down_url);
        
        $data = $vod->toArray();
        $data['play_list'] = $playList;
        $data['down_list'] = $downList;
        
        return $this->response(1, '获取成功', $data);
    }

    private function parsePlayList($from, $url)
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
                'player_name' => $playerCode, // Could get from config
                'urls' => $episodes
            ];
        }
        
        return $result;
    }
}
