<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\TypeListRequest;
use App\Models\Type;

class TypeController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getList(TypeListRequest $request)
    {
        $param = $request->validated();
        $offset = intval($param['offset'] ?? 0);
        $limit = intval($param['limit'] ?? 20);

        $query = Type::query()->where('type_status', 1);

        if (isset($param['mid']) && $param['mid'] !== '') {
            $query->where('type_mid', $this->formatSqlString($param['mid']));
        }
        if (isset($param['parent']) && $param['parent'] !== '') {
            $query->where('type_pid', intval($param['parent']));
        }
        if (!empty($param['ids'])) {
            $query->whereIn('type_id', array_map('intval', explode(',', $param['ids'])));
        }

        $total = $query->count();
        $by = $param['orderby'] ?? 'sort';
        if (!in_array($by, ['id', 'sort'])) {
            $by = 'sort';
        }

        $rows = $query->orderByRaw('type_pid asc, type_' . $by . ' DESC')
            ->skip($offset)
            ->take($limit)
            ->get();

        return $this->response(1, '获取成功', compact('offset', 'limit', 'total', 'rows'));
    }

    public function getAllList()
    {
        $rows = Type::query()
            ->where('type_status', 1)
            ->orderByRaw('type_pid asc, type_sort desc, type_id desc')
            ->get();

        return $this->response(1, '获取成功', ['rows' => $rows]);
    }
}
