<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ActorDetailRequest;
use App\Http\Requests\Api\ActorListRequest;
use App\Models\Actor;

class ActorController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->checkConfig();
    }

    public function getList(ActorListRequest $request)
    {
        $param = $request->validated();
        
        $offset = intval($param['offset'] ?? 0);
        $limit = intval($param['limit'] ?? 20);
        
        $query = Actor::where('actor_status', 1);
        
        if (isset($param['id'])) {
            $query->where('actor_id', intval($param['id']));
        }
        
        if (!empty($param['actor_name'])) {
            $query->where('actor_name', 'like', '%' . $this->formatSqlString($param['actor_name']) . '%');
        }
        
        $total = $query->count();
        
        $order = 'actor_time DESC';
        if (!empty($param['orderby'])) {
            $order = 'actor_' . $param['orderby'] . ' DESC';
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

    public function getDetail(ActorDetailRequest $request)
    {
        $id = (int) $request->validated()['id'];
        
        $actor = Actor::where('actor_id', $id)
                     ->where('actor_status', 1)
                     ->first();
        
        if (!$actor) {
            return $this->response(1002, '数据不存在');
        }
        
        return $this->response(1, '获取成功', $actor);
    }
}
