<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ArtDetailRequest;
use App\Http\Requests\Api\ArtListRequest;
use App\Models\Art;

class ArtController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->checkConfig();
    }

    public function getList(ArtListRequest $request)
    {
        $param = $request->validated();
        
        $offset = intval($param['offset'] ?? 0);
        $limit = intval($param['limit'] ?? 20);
        
        $query = Art::where('art_status', 1);
        
        if (isset($param['type_id'])) {
            $query->where('type_id', intval($param['type_id']));
        }
        
        if (isset($param['id'])) {
            $query->where('art_id', intval($param['id']));
        }
        
        if (!empty($param['art_name'])) {
            $query->where('art_name', 'like', '%' . $this->formatSqlString($param['art_name']) . '%');
        }
        
        $total = $query->count();
        
        $order = 'art_time DESC';
        if (!empty($param['orderby'])) {
            $order = 'art_' . $param['orderby'] . ' DESC';
        }
        
        $list = $query->orderByRaw($order)
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

    public function getDetail(ArtDetailRequest $request)
    {
        $id = (int) $request->validated()['id'];
        
        $art = Art::where('art_id', $id)
                  ->where('art_status', 1)
                  ->first();
        
        if (!$art) {
            return $this->response(1002, '数据不存在');
        }
        
        return $this->response(1, '获取成功', $art);
    }
}
