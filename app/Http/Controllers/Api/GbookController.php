<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\GbookListRequest;
use App\Models\Gbook;

class GbookController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getList(GbookListRequest $request)
    {
        $param = $request->validated();
        $offset = intval($param['offset'] ?? 0);
        $limit = intval($param['limit'] ?? 20);

        $query = Gbook::query()->where('gbook_status', 1);

        foreach (['rid' => 'gbook_rid', 'uid' => 'gbook_uid'] as $key => $column) {
            if (isset($param[$key]) && $param[$key] !== '') {
                $query->where($column, intval($param[$key]));
            }
        }

        $total = $query->count();
        $by = $param['orderby'] ?? 'time';
        if (!in_array($by, ['id', 'time'])) {
            $by = 'time';
        }

        $rows = $query->orderByRaw('gbook_' . $by . ' DESC')
            ->skip($offset)
            ->take($limit)
            ->get();

        return $this->response(1, '获取成功', compact('offset', 'limit', 'total', 'rows'));
    }
}
