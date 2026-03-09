<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\LinkListRequest;
use App\Models\Link;

class LinkController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getList(LinkListRequest $request)
    {
        $param = $request->validated();
        $offset = intval($param['offset'] ?? 0);
        $limit = intval($param['limit'] ?? 20);

        $query = Link::query();

        if (isset($param['type']) && $param['type'] !== '') {
            $query->where('link_type', intval($param['type']));
        }

        $total = $query->count();
        $by = $param['orderby'] ?? 'sort';
        if (!in_array($by, ['id', 'sort'])) {
            $by = 'sort';
        }

        $rows = $query->orderByRaw('link_' . $by . ' DESC')
            ->skip($offset)
            ->take($limit)
            ->get();

        return $this->response(1, '获取成功', compact('offset', 'limit', 'total', 'rows'));
    }
}
